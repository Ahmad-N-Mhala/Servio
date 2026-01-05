<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Providers\DeliverooProvider;
use App\Services\Delivery\Providers\NoonProvider;
use App\Services\Delivery\Providers\TalabatProvider;
use App\Services\Delivery\Providers\KeetaProvider;
use InvalidArgumentException;

class DeliveryService
{
    public static function getProvider(string $providerSlug): DeliveryProviderInterface
    {
        return match ($providerSlug) {
            'deliveroo' => new DeliverooProvider(),
            'noon' => new NoonProvider(),
            'talabat' => new TalabatProvider(),
            'keeta' => new KeetaProvider(),
            'uber_eats' => new \App\Services\Delivery\Providers\UberEatsProvider(),
            'careem' => new \App\Services\Delivery\Providers\CareemProvider(),
            default => new NoonProvider(), // Fallback
        };
    }
}
