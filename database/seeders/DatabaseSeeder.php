<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\DeliveryZone;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);
        $workerRole = Role::create(['name' => 'worker']); // CORREGIDO: era $worker
        $tvRole = Role::create(['name' => 'tv']); // CORREGIDO: era $tv

        // Crear permisos
        $permissions = [
            'manage products',
            'manage categories',
            'manage orders',
            'manage delivery zones',
            'manage payment methods',
            'manage users',
            'view dashboard',
            'view pos',
            'view pedidostv'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Asignar todos los permisos al admin
        $adminRole->givePermissionTo(Permission::all());
        
        // Asignar permisos específicos a worker
        $workerRole->givePermissionTo([
            'manage orders',
            'view dashboard',
            'view pedidostv'
        ]);
        
        // Asignar permisos específicos a tv
        $tvRole->givePermissionTo(['view pedidostv']);

        // Usuario Admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@acai.com',
            'phone' => '0981000000',
            'password' => bcrypt('admin123'),
            'city' => 'Ciudad del Este',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Usuario Cliente de prueba
        $customer = User::create([
            'name' => 'Cliente Ejemplo',
            'email' => 'cliente@example.com',
            'phone' => '0981111111',
            'password' => bcrypt('123456'),
            'address' => 'Av. Ejemplo 123',
            'city' => 'Ciudad del Este',
            'is_active' => true,
        ]);
        $customer->assignRole('customer');

        // Usuario worker
        $worker = User::create([
            'name' => 'Empleado Pedidos',
            'email' => 'trabajador@acai.com',
            'phone' => '0981222222',
            'password' => bcrypt('worker123'),
            'city' => 'Ciudad del Este',
            'is_active' => true,
        ]);
        $worker->assignRole('worker'); // CORREGIDO: era $workerRole
        
        // Usuario TV Display
        $tv = User::create([
            'name' => 'Pantalla TV',
            'email' => 'tv@acai.com',
            'phone' => '0981333333',
            'password' => bcrypt('tv123'),
            'city' => 'Ciudad del Este',
            'is_active' => true,
        ]);
        $tv->assignRole('tv');
        
        // Usuario para Mostrador POS
        $mostrador = User::create([
            'name' => 'Cliente Mostrador',
            'email' => 'mostrador@pos.local',
            'phone' => '0000000000',
            'password' => bcrypt(Str::random(16)),
            'city' => 'Ciudad del Este',
            'is_active' => true,
        ]);
        $mostrador->assignRole('customer');

        // Categorías
        $categories = [
            ['name' => 'Açaí Bowls', 'slug' => 'acai-bowls', 'description' => 'Bowls de açaí con diferentes toppings', 'is_active' => true],
            ['name' => 'Smoothies', 'slug' => 'smoothies', 'description' => 'Smoothies de açaí y frutas', 'is_active' => true],
            ['name' => 'Especiales', 'slug' => 'especiales', 'description' => 'Productos especiales de la casa', 'is_active' => true],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // ========================================
        // PRODUCTOS CON VARIANTES
        // ========================================

        // 1. Açaí Bowl Clásico
        $acaiBowlClasico = Product::create([
            'category_id' => 1,
            'name' => 'Açaí Bowl Clásico',
            'slug' => 'acai-bowl-clasico',
            'description' => 'Bowl de açaí con granola, banana y miel',
            'ingredients' => 'Açaí, granola, banana, miel',
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $acaiBowlClasico->id, 'volume' => 300, 'price' => 25000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlClasico->id, 'volume' => 500, 'price' => 35000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlClasico->id, 'volume' => 700, 'price' => 45000, 'stock' => 50, 'is_active' => true]);

        // 2. Açaí Bowl Tropical
        $acaiBowlTropical = Product::create([
            'category_id' => 1,
            'name' => 'Açaí Bowl Tropical',
            'slug' => 'acai-bowl-tropical',
            'description' => 'Bowl de açaí con frutas tropicales',
            'ingredients' => 'Açaí, mango, piña, coco rallado, granola',
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $acaiBowlTropical->id, 'volume' => 300, 'price' => 28000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlTropical->id, 'volume' => 500, 'price' => 40000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlTropical->id, 'volume' => 700, 'price' => 50000, 'stock' => 50, 'is_active' => true]);

        // 3. Açaí Bowl Proteico
        $acaiBowlProteico = Product::create([
            'category_id' => 1,
            'name' => 'Açaí Bowl Proteico',
            'slug' => 'acai-bowl-proteico',
            'description' => 'Bowl de açaí con proteínas y frutos secos',
            'ingredients' => 'Açaí, whey protein, almendras, nueces, chía',
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $acaiBowlProteico->id, 'volume' => 300, 'price' => 30000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlProteico->id, 'volume' => 500, 'price' => 45000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlProteico->id, 'volume' => 700, 'price' => 55000, 'stock' => 50, 'is_active' => true]);

        // 4. Smoothie Açaí Berry
        $smoothieBerry = Product::create([
            'category_id' => 2,
            'name' => 'Smoothie Açaí Berry',
            'slug' => 'smoothie-acai-berry',
            'description' => 'Smoothie de açaí con frutos rojos',
            'ingredients' => 'Açaí, frutillas, arándanos, leche de almendras',
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $smoothieBerry->id, 'volume' => 300, 'price' => 20000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $smoothieBerry->id, 'volume' => 500, 'price' => 30000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $smoothieBerry->id, 'volume' => 700, 'price' => 38000, 'stock' => 50, 'is_active' => true]);

        // 5. Smoothie Green Power
        $smoothieGreen = Product::create([
            'category_id' => 2,
            'name' => 'Smoothie Green Power',
            'slug' => 'smoothie-green-power',
            'description' => 'Smoothie verde energizante con açaí',
            'ingredients' => 'Açaí, espinaca, manzana verde, jengibre, limón',
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $smoothieGreen->id, 'volume' => 300, 'price' => 22000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $smoothieGreen->id, 'volume' => 500, 'price' => 32000, 'stock' => 50, 'is_active' => true]);

        // 6. Açaí Bowl XXL (Solo una variante grande)
        $acaiBowlXXL = Product::create([
            'category_id' => 3,
            'name' => 'Açaí Bowl XXL',
            'slug' => 'acai-bowl-xxl',
            'description' => 'Bowl gigante de açaí para compartir',
            'ingredients' => 'Açaí, mix de frutas, granola, miel, coco',
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $acaiBowlXXL->id, 'volume' => 1000, 'price' => 60000, 'stock' => 30, 'is_active' => true]);
        
        // 7. Bowl de Frutas Premium
        $bowlFrutas = Product::create([
            'category_id' => 3,
            'name' => 'Bowl de Frutas Premium',
            'slug' => 'bowl-frutas-premium',
            'description' => 'Bowl especial con frutas de estación',
            'ingredients' => 'Mix de frutas frescas, yogurt griego, granola, miel',
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $bowlFrutas->id, 'volume' => 400, 'price' => 35000, 'stock' => 40, 'is_active' => true]);
        ProductVariant::create(['product_id' => $bowlFrutas->id, 'volume' => 600, 'price' => 48000, 'stock' => 40, 'is_active' => true]);

        // Zonas de delivery
        $zones = [
            ['name' => 'Centro', 'city' => 'Ciudad del Este', 'delivery_cost' => 10000, 'description' => 'Zona céntrica', 'is_active' => true],
            ['name' => 'Km 4', 'city' => 'Ciudad del Este', 'delivery_cost' => 15000, 'description' => 'Zona Km 4', 'is_active' => true],
            ['name' => 'Km 7', 'city' => 'Ciudad del Este', 'delivery_cost' => 20000, 'description' => 'Zona Km 7', 'is_active' => true],
            ['name' => 'Área 1', 'city' => 'Ciudad del Este', 'delivery_cost' => 15000, 'description' => 'Área 1', 'is_active' => true],
            ['name' => 'Remansito', 'city' => 'Ciudad del Este', 'delivery_cost' => 18000, 'description' => 'Barrio Remansito', 'is_active' => true],
            ['name' => 'Km 11', 'city' => 'Ciudad del Este', 'delivery_cost' => 25000, 'description' => 'Zona Km 11', 'is_active' => true],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::create($zone);
        }

        // Métodos de pago
        $paymentMethods = [
            [
                'name' => 'Transferencia Bancaria',
                'type' => 'bank_transfer',
                'description' => 'Transferencia a cuenta bancaria',
                'instructions' => 'Realizar la transferencia y enviar comprobante',
                'bank_details' => [
                    'bank' => 'Banco Itaú',
                    'account_number' => '123456789',
                    'account_holder' => 'Açaí Store',
                    'ruc' => '12345678-9',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Efectivo',
                'type' => 'cash',
                'description' => 'Pago en efectivo al recibir',
                'instructions' => 'Tener el monto exacto al momento de la entrega',
                'bank_details' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Tarjeta de Crédito/Débito',
                'type' => 'card',
                'description' => 'Pago con tarjeta en tienda',
                'instructions' => 'Presentar tarjeta al momento del pago',
                'bank_details' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Billetera Móvil',
                'type' => 'mobile_wallet',
                'description' => 'Pago con billetera digital (Tigo Money, Personal Pay)',
                'instructions' => 'Transferir al número indicado',
                'bank_details' => [
                    'tigo_money' => '0981-000000',
                    'personal_pay' => '0985-000000',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }

        $this->command->info('✅ Base de datos inicializada con éxito!');
        $this->command->info('📧 Admin: admin@acai.com / admin123');
        $this->command->info('👤 Cliente: cliente@example.com / 123456');
        $this->command->info('👷 Worker: trabajador@acai.com / worker123');
        $this->command->info('📺 TV: tv@acai.com / tv123');
        $this->command->info('🏪 Mostrador: mostrador@pos.local (automático)');
    }
}
