<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Restaurant extends Model
{
    use HasFactory, HasTranslations, SoftDeletes, \App\Traits\TracksDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'city',
        'country',
        'currency',
        'locale',
        'settings',
        'sms_balance',
        'email_balance',
        'state',
        'zip_code',
        'google_map_location',
        'status',
        'logo',
        'receipt_template',
        'next_order_number',
        'service_type',
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
        return asset('storage/' . $value);
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

    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RestaurantSubscription::class)->latest();
    }


    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Check if a specific feature is enabled for this restaurant
     */
    public function hasFeature(string $feature): bool
    {
        // 1. Check Plan Features
        $subscription = $this->subscription;
        if ($subscription && $subscription->plan) {
            $rawFeatures = $subscription->plan->enabled_features;
            $planFeatures = [];

            if (is_array($rawFeatures)) {
                $planFeatures = $rawFeatures;
            } elseif (is_string($rawFeatures)) {
                $planFeatures = json_decode($rawFeatures, true) ?? [];
            }

            if (in_array($feature, $planFeatures)) {
                return true;
            }
        }

        // 2. Check Restaurant Settings Overrides
        $settings = is_array($this->settings) ? $this->settings : [];
        $enabledFeatures = $settings['enabled_features'] ?? [];

        return in_array($feature, $enabledFeatures);
    }

    /**
     * Check if feedback feature is enabled
     */
    public function hasFeedbackFeature(): bool
    {
        return $this->hasFeature('feedback');
    }

}

