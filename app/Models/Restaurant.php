<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Restaurant extends Model
{
    use \App\Traits\TracksDeletes, HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'notification_email',
        'address',
        'city',
        'country',
        'currency',
        'locale',
        'settings',
        'state',
        'zip_code',
        'google_map_location',
        'status',
        'logo',
        'receipt_template',
        'next_order_number',
        'service_type',
        'has_cash_drawer',
    ];

    protected $casts = [
        'next_order_number' => 'integer',
    ];

    protected $appends = ['phone_code', 'currency_rate'];

    public function getPhoneCodeAttribute($value = null)
    {
        $country = \App\Models\Country::where('name', $this->country)->first();

        return $country ? $country->dial_code : '+971';
    }

    public function getCurrencyRateAttribute($value = null)
    {
        $country = \App\Models\Country::where('name', $this->country)->first();

        return $country ? (float) $country->rate : 1.0;
    }

    public function getCurrencyAttribute($value)
    {
        // Fix for legacy data: if currency is AED but country is not UAE, derive from country
        if ($value === 'AED' && $this->country && $this->country !== 'United Arab Emirates') {
            $country = \App\Models\Country::where('name', $this->country)->first();

            return $country ? $country->currency : $value;
        }

        return $value;
    }

    public function getLogoAttribute($value)
    {
        // If logo is empty or null, return null
        if (empty($value)) {
            return null;
        }

        // If it's already a full URL (starts with http:// or https://), return as is
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // Otherwise, prepend the storage path
        return asset('storage/'.$value);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function menuCategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_user', 'restaurant_id', 'email', 'id', 'email')
            ->withPivot('role', 'is_active');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function earningMethods(): HasMany
    {
        return $this->hasMany(EarningMethod::class);
    }

    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RestaurantSubscription::class)->latest();
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    protected $_enabledFeaturesCache = null;

    /**
     * Check if a specific feature is enabled for this restaurant
     */
    public function hasFeature(string $feature): bool
    {
        // 1. Check Restaurant Settings Overrides FIRST (no DB query needed)
        $settings = is_array($this->settings) ? $this->settings : [];
        $enabledFeatures = $settings['enabled_features'] ?? [];

        if (in_array($feature, $enabledFeatures)) {
            return true;
        }

        // 2. Use Memoized Plan Features if available
        if (is_array($this->_enabledFeaturesCache)) {
            return in_array($feature, $this->_enabledFeaturesCache);
        }

        // 3. Load from Subscription Plan and Memoize
        $subscription = $this->subscription;
        $planFeatures = [];

        if ($subscription && $subscription->plan) {
            $rawFeatures = $subscription->plan->enabled_features;

            if (is_array($rawFeatures)) {
                $planFeatures = $rawFeatures;
            } elseif (is_string($rawFeatures)) {
                $planFeatures = json_decode($rawFeatures, true) ?? [];
            }
        }

        $this->_enabledFeaturesCache = $planFeatures;

        return in_array($feature, $this->_enabledFeaturesCache);
    }

    /**
     * Check if feedback feature is enabled
     */
    public function hasFeedbackFeature(): bool
    {
        return $this->hasFeature('feedback');
    }

    protected static function booted()
    {
        static::forceDeleting(function ($restaurant) {
            // 1. Delete HasMany relations (Data owned by restaurant)
            $restaurant->orders()->forceDelete();
            $restaurant->menuItems()->forceDelete();
            $restaurant->menuCategories()->forceDelete();
            $restaurant->customers()->forceDelete();
            $restaurant->staff()->forceDelete();
            $restaurant->rewards()->forceDelete();
            $restaurant->earningMethods()->forceDelete();
            $restaurant->subscription()->forceDelete();

            // 2. Handle Users (BelongsToMany)
            // If a user belongs ONLY to this restaurant, delete the user.
            // If they belong to others, just detach.
            foreach ($restaurant->users as $user) {
                // Check if user has other restaurants
                // Note: We use raw query or relationship count.
                // Since this restaurant is not deleted yet (deleting event), count should include it.
                // So if count <= 1, it's the only one.
                if ($user->restaurants()->count() <= 1) {
                    $user->forceDelete();
                } else {
                    $restaurant->users()->detach($user->id);
                }
            }
        });
    }
}
