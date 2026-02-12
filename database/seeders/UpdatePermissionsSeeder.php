<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\CupSize;

/**
 * SEEDER DE ACTUALIZACIÓN - Solo para agregar nuevos permisos y roles.
 * NO toca los datos ya existentes de productos, zonas, métodos de pago, etc.
 *
 * Ejecutar con: php artisan db:seed --class=UpdatePermissionsSeeder
 */
class UpdatePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // NUEVOS PERMISOS
        // ==========================================
        $newPermissions = [
            // Caja
            'manage cash registers',  // Abrir/cerrar/ver cajas
            'view cash reports',      // Ver reportes de caja
            
            // Empleados
            'manage employees',       // CRUD empleados
            'manage salaries',        // Pagar salarios
            
            // Gastos
            'manage expenses',        // Registrar gastos
            
            // Inventario de vasitos
            'manage inventory',       // Gestionar stock de vasitos
            
            // Personalizaciones
            'manage customizations',  // Gestionar complementos/extras
            
            // Pedidos Ya / Apps
            'manage delivery apps',   // Registrar pedidos de apps externas
        ];

        foreach ($newPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // ==========================================
        // ACTUALIZAR ROL ADMIN (todos los permisos)
        // ==========================================
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(Permission::all());
        }

        // ==========================================
        // ACTUALIZAR ROL WORKER
        // ==========================================
        $workerRole = Role::where('name', 'worker')->first();
        if ($workerRole) {
            $workerRole->givePermissionTo([
                'manage cash registers',
                'manage expenses',
                'manage delivery apps',
                'view cash reports',
            ]);
        }

        // ==========================================
        // NUEVO ROL: CAJERO
        // ==========================================
        $cashierRole = Role::firstOrCreate(
            ['name' => 'cashier', 'guard_name' => 'web']
        );
        $cashierRole->syncPermissions([
            'manage orders',
            'view dashboard',
            'view pos',
            'manage cash registers',
            'manage expenses',
            'manage delivery apps',
        ]);

        // ==========================================
        // STOCK DE VASITOS (poblar si está vacío)
        // ==========================================
        $totalStock = CupSize::sum('stock');
        if ($totalStock == 0) {
            $this->command->info('⚠️  Los vasitos tienen stock 0. Recuerde ajustar el stock inicial en:');
            $this->command->info('   Admin → Inventario → Vasitos');
        }

        // ==========================================
        // RESUMEN
        // ==========================================
        $this->command->info('');
        $this->command->info('✅ Permisos y roles actualizados correctamente.');
        $this->command->info('');
        $this->command->info('NUEVOS PERMISOS AGREGADOS:');
        foreach ($newPermissions as $p) {
            $this->command->info("  ✓ {$p}");
        }
        $this->command->info('');
        $this->command->info('NUEVO ROL AGREGADO:');
        $this->command->info('  ✓ cashier (cajero) - puede abrir/cerrar caja y registrar gastos');
        $this->command->info('');
        $this->command->info('PRÓXIMOS PASOS:');
        $this->command->info('  1. Ingresar precios POS y Pedidos Ya en los productos');
        $this->command->info('  2. Ajustar stock de vasitos en Admin → Inventario');
        $this->command->info('  3. Vincular empleados a usuarios del sistema si aplica');
    }
}