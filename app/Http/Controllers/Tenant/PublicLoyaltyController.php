<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Reward;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicLoyaltyController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {
    }

    public function checkPoints(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $restaurant = \App\Models\Restaurant::first();
        $customer = $this->loyaltyService->getCustomerByPhone($restaurant, $request->phone);

        if (!$customer) {
            return response()->json([
                'exists' => false,
                'message' => 'Customer not found',
            ]);
        }

        $customer->load('loyaltyPoints');

        return response()->json([
            'exists' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'loyalty_tier' => $customer->loyalty_tier,
                'total_orders' => $customer->total_orders,
                'total_spent' => (float) (string) $customer->total_spent,
                'points' => $customer->current_points,
            ],
        ]);
    }

    public function getRewards(Request $request): JsonResponse
    {
        $restaurant = \App\Models\Restaurant::first();

        $rewards = Reward::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->orderBy('sort_order')
            ->get()
            ->map(function ($reward) {
                return [
                    'id' => $reward->id,
                    'name' => $reward->name[app()->getLocale()] ?? $reward->name['en'] ?? '',
                    'description' => $reward->description,
                    'points_required' => $reward->points_required,
                    'reward_type' => $reward->reward_type,
                    'discount_value' => $reward->discount_value ? (float) (string) $reward->discount_value : null,
                ];
            });

        return response()->json([
            'rewards' => $rewards,
        ]);
    }

    public function redeemReward(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'reward_id' => ['required', 'exists:rewards,id'],
        ]);

        $restaurant = \App\Models\Restaurant::first();
        $customer = $this->loyaltyService->getCustomerByPhone($restaurant, $validated['phone']);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        try {
            $redemption = $this->loyaltyService->redeemReward($customer, $validated['reward_id']);

            return response()->json([
                'success' => true,
                'redemption' => [
                    'id' => $redemption->id,
                    'code' => $redemption->code,
                    'points_used' => $redemption->points_used,
                    'expires_at' => $redemption->expires_at,
                ],
                'remaining_points' => $customer->fresh()->current_points,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getHistory(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $restaurant = \App\Models\Restaurant::first();
        $customer = $this->loyaltyService->getCustomerByPhone($restaurant, $request->phone);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $transactions = $customer->pointTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'points' => $transaction->points,
                    'description' => $transaction->description,
                    'balance_after' => $transaction->balance_after,
                    'created_at' => $transaction->created_at,
                ];
            });

        $redemptions = $customer->rewardRedemptions()
            ->with('reward')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($redemption) {
                return [
                    'id' => $redemption->id,
                    'code' => $redemption->code,
                    'points_used' => $redemption->points_used,
                    'status' => $redemption->status,
                    'reward_name' => $redemption->reward->name[app()->getLocale()] ?? $redemption->reward->name['en'] ?? '',
                    'created_at' => $redemption->created_at,
                    'expires_at' => $redemption->expires_at,
                ];
            });

        return response()->json([
            'transactions' => $transactions,
            'redemptions' => $redemptions,
        ]);
    }
}

