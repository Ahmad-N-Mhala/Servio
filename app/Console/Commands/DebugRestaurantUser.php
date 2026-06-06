<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugRestaurantUser extends Command
{
    protected $signature = 'debug:restaurant-user';

    protected $description = 'Dump contents of restaurant_user table';

    public function handle()
    {
        $this->info('Dumping restaurant_user table:');
        $records = DB::table('restaurant_user')->get();

        if ($records->isEmpty()) {
            $this->error('Table is empty!');
        } else {
            foreach ($records as $record) {
                $this->line(json_encode($record));
            }
        }

        $this->info("\nChecking waiter user:");
        $user = User::where('email', 'waiter@example.com')->first();
        if ($user) {
            $this->info("User found: {$user->email} (ID: {$user->id})");
            $restaurants = $user->restaurants;
            $this->info('Attached restaurants count via Eloquent: '.$restaurants->count());
            foreach ($restaurants as $r) {
                $this->line("- {$r->name} (ID: {$r->id})");
            }
        } else {
            $this->error('User waiter@example.com not found');
        }
    }
}
