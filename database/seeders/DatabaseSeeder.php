<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with sample vendors, products, users and orders
     */
    public function run(): void
    {
        // Create vendors using updateOrCreate
        $vendor1 = Vendor::updateOrCreate(
            ['email' => 'meyorpop@@gmail.com'], // Unique identifier
            [
                'name' => 'Meyor Popoola',
                'password' => bcrypt('password123'),
                'business_name' => 'TechHub Nigeria',
                'phone' => '+2347065216112',
            ]
        );

        $vendor2 = Vendor::updateOrCreate(
            ['email' => 'daridatongs@gmail.com'],
            [
                'name' => 'Dari Daniel',
                'password' => bcrypt('password123'),
                'business_name' => 'Fashion Forward',
                'phone' => '+2347065216112',
            ]
        );

        // Create products for vendor 1 using updateOrCreate
        $product1 = Product::updateOrCreate(
            ['vendor_id' => $vendor1->id, 'name' => 'Wireless Earbuds Pro'],
            [
                'description' => 'Premium noise-cancelling wireless earbuds with 48-hour battery',
                'price' => 45000,
                'stock_quantity' => 50,
                'status' => 'active',
            ]
        );

        $product2 = Product::updateOrCreate(
            ['vendor_id' => $vendor1->id, 'name' => 'USB-C Fast Charger'],
            [
                'description' => '65W ultra-fast charger for laptops and phones',
                'price' => 12000,
                'stock_quantity' => 150,
                'status' => 'active',
            ]
        );

        $product3 = Product::updateOrCreate(
            ['vendor_id' => $vendor1->id, 'name' => 'Mechanical Keyboard'],
            [
                'description' => 'RGB mechanical keyboard with custom switches',
                'price' => 28000,
                'stock_quantity' => 25,
                'status' => 'active',
            ]
        );

        $product4 = Product::updateOrCreate(
            ['vendor_id' => $vendor1->id, 'name' => 'Monitor Stand Arm'],
            [
                'description' => 'Adjustable dual monitor arm stand',
                'price' => 18000,
                'stock_quantity' => 40,
                'status' => 'active',
            ]
        );

        $product5 = Product::updateOrCreate(
            ['vendor_id' => $vendor1->id, 'name' => 'Wireless Mouse'],
            [
                'description' => 'Ergonomic wireless mouse with silent clicks',
                'price' => 8500,
                'stock_quantity' => 200,
                'status' => 'active',
            ]
        );

        // Create products for vendor 2 using updateOrCreate
        $product6 = Product::updateOrCreate(
            ['vendor_id' => $vendor2->id, 'name' => 'Classic Ankara Dress'],
            [
                'description' => 'Beautiful hand-tailored Ankara fabric dress',
                'price' => 22000,
                'stock_quantity' => 15,
                'status' => 'active',
            ]
        );

        $product7 = Product::updateOrCreate(
            ['vendor_id' => $vendor2->id, 'name' => 'Leather Handbag'],
            [
                'description' => 'Premium genuine leather handbag with shoulder strap',
                'price' => 35000,
                'stock_quantity' => 8,
                'status' => 'active',
            ]
        );

        $product8 = Product::updateOrCreate(
            ['vendor_id' => $vendor2->id, 'name' => 'Cotton Blouse'],
            [
                'description' => 'Lightweight cotton blouse perfect for warm weather',
                'price' => 9500,
                'stock_quantity' => 60,
                'status' => 'active',
            ]
        );

        $product9 = Product::updateOrCreate(
            ['vendor_id' => $vendor2->id, 'name' => 'Beaded Necklace'],
            [
                'description' => 'Handcrafted beaded necklace with traditional patterns',
                'price' => 5000,
                'stock_quantity' => 120,
                'status' => 'active',
            ]
        );

        $product10 = Product::updateOrCreate(
            ['vendor_id' => $vendor2->id, 'name' => 'Silk Scarf'],
            [
                'description' => 'Premium silk scarf with vibrant colors',
                'price' => 8000,
                'stock_quantity' => 45,
                'status' => 'active',
            ]
        );

        // Create customers/users using updateOrCreate
        $user1 = User::updateOrCreate(
            ['email' => 'chioma@customer.com'],
            [
                'name' => 'Chioma Adeyemi',
                'password' => bcrypt('password123'),
                'phone' => '+234902345678',
                'address' => '123 Main Street',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'postal_code' => '100001',
                'is_active' => true,
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'tunde@customer.com'],
            [
                'name' => 'Tunde Owolabi',
                'password' => bcrypt('password123'),
                'phone' => '+234908765432',
                'address' => '456 Oak Avenue',
                'city' => 'Ibadan',
                'state' => 'Oyo',
                'postal_code' => '200001',
                'is_active' => true,
            ]
        );

        $user3 = User::updateOrCreate(
            ['email' => 'zainab@customer.com'],
            [
                'name' => 'Zainab Mohammed',
                'password' => bcrypt('password123'),
                'phone' => '+234915432109',
                'address' => '789 Elm Street',
                'city' => 'Kano',
                'state' => 'Kano',
                'postal_code' => '700001',
                'is_active' => true,
            ]
        );

        // Create orders using updateOrCreate with composite key check
        // For orders, we need to check multiple fields to prevent duplicates

        // User 1 orders
        Order::updateOrCreate(
            [
                'user_id' => $user1->id,
                'product_id' => $product1->id,
                'vendor_id' => $vendor1->id,
            ],
            [
                'quantity' => 2,
                'unit_price' => $product1->price,
                'total_price' => $product1->price * 2,
                'status' => 'completed',
                'notes' => 'Delivered successfully',
            ]
        );

        Order::updateOrCreate(
            [
                'user_id' => $user1->id,
                'product_id' => $product6->id,
                'vendor_id' => $vendor2->id,
            ],
            [
                'quantity' => 1,
                'unit_price' => $product6->price,
                'total_price' => $product6->price,
                'status' => 'completed',
                'notes' => null,
            ]
        );

        // User 2 orders
        Order::updateOrCreate(
            [
                'user_id' => $user2->id,
                'product_id' => $product2->id,
                'vendor_id' => $vendor1->id,
            ],
            [
                'quantity' => 3,
                'unit_price' => $product2->price,
                'total_price' => $product2->price * 3,
                'status' => 'completed',
                'notes' => null,
            ]
        );

        Order::updateOrCreate(
            [
                'user_id' => $user2->id,
                'product_id' => $product7->id,
                'vendor_id' => $vendor2->id,
            ],
            [
                'quantity' => 1,
                'unit_price' => $product7->price,
                'total_price' => $product7->price,
                'status' => 'processing',
                'notes' => 'Waiting for shipment',
            ]
        );

        // User 3 orders
        Order::updateOrCreate(
            [
                'user_id' => $user3->id,
                'product_id' => $product3->id,
                'vendor_id' => $vendor1->id,
            ],
            [
                'quantity' => 1,
                'unit_price' => $product3->price,
                'total_price' => $product3->price,
                'status' => 'completed',
                'notes' => null,
            ]
        );

        Order::updateOrCreate(
            [
                'user_id' => $user3->id,
                'product_id' => $product9->id,
                'vendor_id' => $vendor2->id,
            ],
            [
                'quantity' => 4,
                'unit_price' => $product9->price,
                'total_price' => $product9->price * 4,
                'status' => 'completed',
                'notes' => 'Great quality',
            ]
        );

        Order::updateOrCreate(
            [
                'user_id' => $user3->id,
                'product_id' => $product5->id,
                'vendor_id' => $vendor1->id,
            ],
            [
                'quantity' => 2,
                'unit_price' => $product5->price,
                'total_price' => $product5->price * 2,
                'status' => 'pending',
                'notes' => 'Awaiting confirmation',
            ]
        );
    }
}
