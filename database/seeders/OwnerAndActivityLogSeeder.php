<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// php artisan db:seed --class=OwnerAndActivityLogSeeder
class OwnerAndActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Limpiar caché de permisos ──────────────────────
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 2. Crear permisos necesarios ──────────────────────
        $permisos = [
            'view activity log',
            'view dashboard',
            'view reports',
            'manage employees',   // para ver funcionarios
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // ── 3. Crear roles si no existen ──────────────────────
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'admin']);

        // ── 4. Asignar permisos al owner ──────────────────────
        $ownerRole->syncPermissions($permisos);

        // ── 5. Crear usuario dueño ────────────────────────────
        $owner = User::firstOrCreate(
            ['email' => 'walkerren77@gmail.com'],
            [
                'name'      => 'Walker (Dueño)',
                'email'     => 'walkerren77@gmail.com',
                'phone'     => null,
                'password'  => Hash::make('4b3L#Ts$2026!xQr'),
                'city'      => 'Ciudad del Este',
                'is_active' => true,
            ]
        );

        $owner->syncRoles([$ownerRole]);

        $this->command->info('✅ Permisos asignados al rol owner:');
        foreach ($permisos as $p) {
            $this->command->info("   · {$p}");
        }
        $this->command->info('✅ Usuario dueño listo:');
        $this->command->info('   📧 Email:    walkerren77@gmail.com');
        $this->command->info('   🔑 Password: 4b3L#Ts$2026!xQr');
        $this->command->warn('   ⚠️  Cambiá la contraseña después del primer login!');
    }
}
