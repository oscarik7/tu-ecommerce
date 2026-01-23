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
        $workerRole = Role::create(['name' => 'worker']);
        $tvRole = Role::create(['name' => 'tv']);

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
        $worker->assignRole('worker');
        
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
            ['name' => 'Por Kilo', 'slug' => 'por-kilo', 'description' => 'Productos vendidos por peso (solo tienda)', 'is_active' => true],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // ========================================
        // PRODUCTOS CON VARIANTES (sale_type = 'unit')
        // ========================================

        // 1. Açaí Bowl Clásico
        $acaiBowlClasico = Product::create([
            'category_id' => 1,
            'name' => 'Açaí Bowl Clásico',
            'slug' => 'acai-bowl-clasico',
            'description' => 'Bowl de açaí con granola, banana y miel',
            'ingredients' => 'Açaí, granola, banana, miel',
            'sale_type' => 'unit',
            'price_per_kg' => null,
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $acaiBowlClasico->id, 'volume' => 300, 'price' => 25000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlClasico->id, 'volume' => 400, 'price' => 30000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlClasico->id, 'volume' => 500, 'price' => 35000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlClasico->id, 'volume' => 700, 'price' => 45000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlClasico->id, 'volume' => 1000, 'price' => 60000, 'stock' => 30, 'is_active' => true]);

        // 2. Açaí Bowl Tropical
        $acaiBowlTropical = Product::create([
            'category_id' => 1,
            'name' => 'Açaí Bowl Tropical',
            'slug' => 'acai-bowl-tropical',
            'description' => 'Bowl de açaí con frutas tropicales',
            'ingredients' => 'Açaí, mango, piña, coco rallado, granola',
            'sale_type' => 'unit',
            'price_per_kg' => null,
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $acaiBowlTropical->id, 'volume' => 300, 'price' => 28000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlTropical->id, 'volume' => 400, 'price' => 34000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlTropical->id, 'volume' => 500, 'price' => 40000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlTropical->id, 'volume' => 700, 'price' => 50000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlTropical->id, 'volume' => 1000, 'price' => 65000, 'stock' => 30, 'is_active' => true]);

        // 3. Açaí Bowl Proteico
        $acaiBowlProteico = Product::create([
            'category_id' => 1,
            'name' => 'Açaí Bowl Proteico',
            'slug' => 'acai-bowl-proteico',
            'description' => 'Bowl de açaí con proteínas y frutos secos',
            'ingredients' => 'Açaí, whey protein, almendras, nueces, chía',
            'sale_type' => 'unit',
            'price_per_kg' => null,
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $acaiBowlProteico->id, 'volume' => 300, 'price' => 30000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlProteico->id, 'volume' => 400, 'price' => 38000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlProteico->id, 'volume' => 500, 'price' => 45000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiBowlProteico->id, 'volume' => 700, 'price' => 55000, 'stock' => 50, 'is_active' => true]);

        // 4. Smoothie Açaí Berry
        $smoothieBerry = Product::create([
            'category_id' => 2,
            'name' => 'Smoothie Açaí Berry',
            'slug' => 'smoothie-acai-berry',
            'description' => 'Smoothie de açaí con frutos rojos',
            'ingredients' => 'Açaí, frutillas, arándanos, leche de almendras',
            'sale_type' => 'unit',
            'price_per_kg' => null,
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $smoothieBerry->id, 'volume' => 300, 'price' => 20000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $smoothieBerry->id, 'volume' => 400, 'price' => 26000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $smoothieBerry->id, 'volume' => 500, 'price' => 30000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $smoothieBerry->id, 'volume' => 700, 'price' => 38000, 'stock' => 50, 'is_active' => true]);

        // 5. Smoothie Green Power
        $smoothieGreen = Product::create([
            'category_id' => 2,
            'name' => 'Smoothie Green Power',
            'slug' => 'smoothie-green-power',
            'description' => 'Smoothie verde energizante con açaí',
            'ingredients' => 'Açaí, espinaca, manzana verde, jengibre, limón',
            'sale_type' => 'unit',
            'price_per_kg' => null,
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $smoothieGreen->id, 'volume' => 300, 'price' => 22000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $smoothieGreen->id, 'volume' => 400, 'price' => 28000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $smoothieGreen->id, 'volume' => 500, 'price' => 32000, 'stock' => 50, 'is_active' => true]);

        // 6. Bowl de Frutas Premium
        $bowlFrutas = Product::create([
            'category_id' => 3,
            'name' => 'Bowl de Frutas Premium',
            'slug' => 'bowl-frutas-premium',
            'description' => 'Bowl especial con frutas de estación',
            'ingredients' => 'Mix de frutas frescas, yogurt griego, granola, miel',
            'sale_type' => 'unit',
            'price_per_kg' => null,
            'is_active' => true,
        ]);
        
        ProductVariant::create(['product_id' => $bowlFrutas->id, 'volume' => 400, 'price' => 35000, 'stock' => 40, 'is_active' => true]);
        ProductVariant::create(['product_id' => $bowlFrutas->id, 'volume' => 700, 'price' => 48000, 'stock' => 40, 'is_active' => true]);

        // ========================================
        // PRODUCTOS SOLO POR PESO (sale_type = 'weight')
        // Solo disponibles en POS, NO en e-commerce
        // ========================================

        // 7. Açaí Puro por Kilo
        $acaiPuroKilo = Product::create([
            'category_id' => 4,
            'name' => 'Açaí Puro',
            'slug' => 'acai-puro-kilo',
            'description' => 'Açaí puro sin toppings, ideal para llevar. Solo disponible en tienda.',
            'ingredients' => 'Açaí 100% puro',
            'sale_type' => 'weight',  // 🆕 Solo por peso
            'price_per_kg' => 87000,  // 🆕 87.000 Gs por kilo
            'is_active' => true,
        ]);
        // No tiene variantes porque es solo por peso

        // 8. Açaí con Granola por Kilo
        $acaiGranolaKilo = Product::create([
            'category_id' => 4,
            'name' => 'Açaí con Granola',
            'slug' => 'acai-granola-kilo',
            'description' => 'Açaí con granola casera, vendido por peso. Solo en tienda.',
            'ingredients' => 'Açaí, granola artesanal',
            'sale_type' => 'weight',
            'price_per_kg' => 95000,
            'is_active' => true,
        ]);

        // ========================================
        // PRODUCTOS MIXTOS (sale_type = 'both')
        // Disponible por unidad (web+pos) Y por peso (solo pos)
        // ========================================

        // 9. Açaí Premium - Se vende por vaso O por kilo
        $acaiPremium = Product::create([
            'category_id' => 3,
            'name' => 'Açaí Premium Mix',
            'slug' => 'acai-premium-mix',
            'description' => 'Nuestro açaí premium con toppings especiales. Disponible en vasos o por peso en tienda.',
            'ingredients' => 'Açaí premium, mix de frutas, granola especial, miel orgánica',
            'sale_type' => 'both',     // 🆕 Ambos modos
            'price_per_kg' => 92000,   // 🆕 Precio por kilo (solo POS)
            'is_active' => true,
        ]);
        
        // Variantes para venta por unidad (web + pos)
        ProductVariant::create(['product_id' => $acaiPremium->id, 'volume' => 300, 'price' => 32000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiPremium->id, 'volume' => 400, 'price' => 40000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiPremium->id, 'volume' => 500, 'price' => 48000, 'stock' => 50, 'is_active' => true]);
        ProductVariant::create(['product_id' => $acaiPremium->id, 'volume' => 700, 'price' => 62000, 'stock' => 40, 'is_active' => true]);

        // ========================================
        // ZONAS DE DELIVERY
        // ========================================
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

        // ========================================
        // MÉTODOS DE PAGO
        // ========================================
        $paymentMethods = [
            [
                'name' => 'Efectivo',
                'type' => 'cash',
                'description' => 'Pago en efectivo',
                'instructions' => 'Tener el monto exacto',
                'bank_details' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Tarjeta',
                'type' => 'card',
                'description' => 'Pago con tarjeta de crédito/débito',
                'instructions' => 'Presentar tarjeta al momento del pago',
                'bank_details' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Transferencia',
                'type' => 'bank_transfer',
                'description' => 'Transferencia bancaria',
                'instructions' => 'Realizar la transferencia y enviar comprobante',
                'bank_details' => [
                    'bank' => 'Banco Itaú',
                    'account_number' => '123456789',
                    'account_holder' => 'Taskinho Açaí',
                    'ruc' => '12345678-9',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'QR / Billetera',
                'type' => 'mobile_wallet',
                'description' => 'Pago con billetera digital',
                'instructions' => 'Escanear QR o transferir al número',
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

        // ========================================
        // RESUMEN FINAL
        // ========================================
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║     ✅ BASE DE DATOS INICIALIZADA CON ÉXITO!            ║');
        $this->command->info('╠══════════════════════════════════════════════════════════╣');
        $this->command->info('║  USUARIOS:                                               ║');
        $this->command->info('║  📧 Admin:   admin@acai.com / admin123                   ║');
        $this->command->info('║  👤 Cliente: cliente@example.com / 123456                ║');
        $this->command->info('║  👷 Worker:  trabajador@acai.com / worker123             ║');
        $this->command->info('║  📺 TV:      tv@acai.com / tv123                         ║');
        $this->command->info('╠══════════════════════════════════════════════════════════╣');
        $this->command->info('║  PRODUCTOS:                                              ║');
        $this->command->info('║  📦 Por unidad (Web+POS): 6 productos                    ║');
        $this->command->info('║  ⚖️  Solo por peso (POS):  2 productos                    ║');
        $this->command->info('║  🔄 Ambos modos:          1 producto                     ║');
        $this->command->info('╠══════════════════════════════════════════════════════════╣');
        $this->command->info('║  PRECIOS POR KILO:                                       ║');
        $this->command->info('║  • Açaí Puro:        87.000 Gs/kg                        ║');
        $this->command->info('║  • Açaí con Granola: 95.000 Gs/kg                        ║');
        $this->command->info('║  • Açaí Premium Mix: 92.000 Gs/kg                        ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}