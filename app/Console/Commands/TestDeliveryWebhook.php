<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\DeliveryIntegration;
use Illuminate\Support\Str;

class TestDeliveryWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:delivery-webhook {provider : The provider slug (uber-eats, deliveroo, talabat)} {--store= : Optional Store ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate an incoming delivery webhook to test integration logic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $provider = $this->argument('provider');

        // 1. Find Integration credentials
        $query = DeliveryIntegration::withoutGlobalScopes()->where('provider', $provider);
        if ($this->option('store')) {
            $query->where('store_id', $this->option('store'));
        }

        $integration = $query->first();

        if (!$integration) {
            // Create a temporary mock integration if none exists, just for testing flow? 
            // Better to ask user to create one first.
            $this->error("No active integration found for provider: {$provider}. Please enable it in the admin panel first.");
            return 1;
        }

        $this->info("Testing {$provider} webhook for Store ID: {$integration->store_id}");

        // 2. Generate Payload
        $payload = $this->generatePayload($provider);
        $body = json_encode($payload);

        // 3. Generate Headers & Signature
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($provider === 'uber-eats') {
            // X-Uber-Signature: HMAC-SHA256 of body using client_secret
            // Ensure we use the raw client_secret, typically it's the 'webhook_secret' or 'client_secret' depending on provider impl.
            // UberEatsProvider.php uses $integration->client_secret
            $secret = $integration->client_secret;
            if (!$secret) {
                $this->warn("Client Secret is missing. Signature verification may fail.");
                $secret = 'dummy_secret';
            }
            $signature = hash_hmac('sha256', $body, $secret);
            $headers['X-Uber-Signature'] = $signature;
        } elseif ($provider === 'deliveroo') {
            // X-Deliveroo-Hmac-Sha256
            // Signature = HMAC(SHA256, key=webhook_secret, msg=guid + ' ' + body)
            $secret = $integration->webhook_secret;
            if (!$secret) {
                $this->warn("Webhook Secret is missing. Signature verification may fail.");
                $secret = 'dummy_secret';
            }
            $guid = Str::uuid()->toString();
            $headers['X-Deliveroo-Sequence-Guid'] = $guid;
            $message = $guid . ' ' . $body;
            $signature = hash_hmac('sha256', $message, $secret);
            $headers['X-Deliveroo-Hmac-Sha256'] = $signature;
        }
        // Talabat (current impl has no verification, so no headers needed)


        // 4. Send Request
        $url = url("/api/webhook/delivery/{$provider}");
        $this->info("Sending POST request to: {$url}");

        try {
            $response = Http::withHeaders($headers)
                ->withBody($body, 'application/json')
                ->post($url);

            if ($response->successful()) {
                $this->info("✅ Success! Status: " . $response->status());
                $this->info("Response: " . $response->body());
            } else {
                $this->error("❌ Failed. Status: " . $response->status());
                $this->error("Response: " . $response->body());

                if ($response->status() === 401) {
                    $this->line("Hint: Check if your API/Webhook secrets match what is in the database.");
                }
            }

        } catch (\Exception $e) {
            $this->error("Connection Error: " . $e->getMessage());
        }

        return 0;
    }

    private function generatePayload($provider)
    {
        $id = uniqid();
        $baseItem = [
            'name' => 'Test Burger',
            'quantity' => 1,
            'price' => 10.00,
            'notes' => 'Extra cheese'
        ];

        switch ($provider) {
            case 'uber-eats':
                // Based on UberEatsProvider::parseOrderPayload
                return [
                    'id' => 'UB-' . $id,
                    'display_id' => 'AAAA1',
                    'cart' => [
                            'items' => [
                                [
                                    'title' => 'Cheeseburger',
                                    'quantity' => 2,
                                    'price' => ['amount' => 1500, 'currency_code' => 'AED'], // cents
                                    'special_instructions' => 'No onions',
                                    'external_id' => 'ITEM-001'
                                ]
                            ]
                        ],
                    'eater' => [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'phone' => '+971500000000'
                    ],
                    'payment' => [
                        'charges' => [
                            'total' => ['amount' => 3000, 'currency_code' => 'AED']
                        ]
                    ],
                    // Store ID is usually in a meta field or URL param in real webhooks,
                    // but our controller logic attempts to find store_id in body if missing.
                    // Let's add it for safety if the controller supports it.
                    'store' => ['id' => 'TEST-STORE-ID']
                ];

            case 'deliveroo':
                // Based on DeliverooProvider::parseOrderPayload
                return [
                    'id' => 'DEL-' . $id,
                    'total_price' => 45.50,
                    'currency' => 'AED',
                    'customer' => [
                            'name' => 'Jane Smith',
                            'phone_number' => '+971550000000'
                        ],
                    'items' => [
                        [
                            'name' => 'Pepperoni Pizza',
                            'quantity' => 1,
                            'price' => 45.50,
                            'notes' => 'Crispy please',
                            'id' => 'ITEM-002'
                        ]
                    ],
                    'notes' => 'Ring the doorbell',
                    'store' => ['id' => 'TEST-STORE-ID']
                ];

            case 'talabat':
                // Based on TalabatProvider::parseOrderPayload
                return [
                    'orderId' => 'TB-' . $id,
                    'total' => 25.00,
                    'currency' => 'AED',
                    'customer' => [
                            'firstName' => 'Ahmed',
                            'mobile' => '+971560000000'
                        ],
                    'items' => [
                        [
                            'name' => 'Shawarma',
                            'quantity' => 2,
                            'price' => 12.50,
                            'comment' => 'Spicy',
                            'remoteCode' => 'ITEM-003'
                        ]
                    ],
                    'comment' => 'Leave at reception',
                    'store' => ['id' => 'TEST-STORE-ID']
                ];

            default:
                return ['error' => 'Unknown provider format'];
        }
    }
}
