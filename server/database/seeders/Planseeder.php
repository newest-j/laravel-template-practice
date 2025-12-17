<?php

namespace Database\Seeders;
// this is use if i want to  Email verification notifications Wallet creation observers 
// use it if you wand some of this side effects and not just create
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class Planseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Plan::factory()->count(5)->create();
        Plan::insert([
            [
                'name' => 'Basic',
                'price' => 5000,
                'currency' => 'NGN',
                'description' => 'Basic subscription plan',
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'price' => 10000,
                'currency' => 'NGN',
                'description' => 'Professional subscription plan',
                'is_active' => true,
            ],
            [
                'name' => 'Standard',
                'price' => 25000,
                'currency' => 'NGN',
                'description' => 'Standard subscription plan',
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'price' => 40000,
                'currency' => 'NGN',
                'description' => 'Enterprise subscription plan',
                'is_active' => true,
            ],
            [
                'name' => 'Starter',
                'price' => 12000,
                'currency' => 'NGN',
                'description' => 'Starter subscription plan',
                'is_active' => true,
            ],
        ]);
    }
}
