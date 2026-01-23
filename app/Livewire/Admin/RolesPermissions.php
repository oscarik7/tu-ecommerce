<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RolesPermissions extends Component
{
    use WithPagination;

    // Tabs
    public $activeTab = 'roles'; // 'roles', 'permissions', 'users'

    // Modal Roles
    public $showRoleModal = false;
    public $editingRoleId = null;
    public $roleName = '';
    public $rolePermissions = [];

    // Modal Permisos
    public $showPermissionModal = false;
    public $editingPermissionId = null;
    public $permissionName = '';
    public $permissionDescription = '';

    // Modal Asignar Rol a Usuario
    public $showUserRoleModal = false;
    public $selectedUserId = null;
    public $selectedUserRoles = [];

    // Búsqueda
    public $searchUser = '';

    // Permisos agrupados por categoría
    public $permissionGroups = [
        'dashboard' => ['icon' => '📊', 'label' => 'Dashboard'],
        'products' => ['icon' => '🍨', 'label' => 'Productos'],
        'categories' => ['icon' => '📁', 'label' => 'Categorías'],
        'orders' => ['icon' => '🛒', 'label' => 'Pedidos'],
        'delivery' => ['icon' => '📍', 'label' => 'Delivery'],
        'payments' => ['icon' => '💳', 'label' => 'Pagos'],
        'users' => ['icon' => '👥', 'label' => 'Usuarios'],
        'pos' => ['icon' => '🏪', 'label' => 'Punto de Venta'],
        'reports' => ['icon' => '📈', 'label' => 'Reportes'],
        'settings' => ['icon' => '⚙️', 'label' => 'Configuración'],
    ];

    protected $rules = [
        'roleName' => 'required|string|max:50',
        'permissionName' => 'required|string|max:100',
    ];

    protected $messages = [
        'roleName.required' => 'El nombre del rol es obligatorio.',
        'permissionName.required' => 'El nombre del permiso es obligatorio.',
    ];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // ==========================================
    // ROLES
    // ==========================================

    public function createRole()
    {
        $this->resetRoleForm();
        $this->showRoleModal = true;
    }

    public function editRole($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        
        $this->editingRoleId = $role->id;
        $this->roleName = $role->name;
        $this->rolePermissions = $role->permissions->pluck('id')->toArray();
        $this->showRoleModal = true;
    }

    public function saveRole()
    {
        $this->validate([
            'roleName' => 'required|string|max:50',
        ]);

        // Verificar que no sea un rol protegido si está editando
        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);
            
            // No permitir cambiar el nombre de admin o customer
            if (in_array($role->name, ['admin', 'customer']) && $role->name !== $this->roleName) {
                session()->flash('error', 'No puedes cambiar el nombre de los roles del sistema (admin, customer).');
                return;
            }
        }

        try {
            DB::beginTransaction();

            if ($this->editingRoleId) {
                $role = Role::findOrFail($this->editingRoleId);
                $role->update(['name' => $this->roleName]);
            } else {
                // Verificar si ya existe
                if (Role::where('name', $this->roleName)->exists()) {
                    session()->flash('error', 'Ya existe un rol con ese nombre.');
                    return;
                }
                $role = Role::create(['name' => $this->roleName, 'guard_name' => 'web']);
            }

            // Sincronizar permisos
            $permissions = Permission::whereIn('id', $this->rolePermissions)->get();
            $role->syncPermissions($permissions);

            DB::commit();

            session()->flash('message', $this->editingRoleId ? 'Rol actualizado correctamente.' : 'Rol creado correctamente.');
            $this->closeRoleModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar el rol: ' . $e->getMessage());
        }
    }

    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);

        // Proteger roles del sistema
        if (in_array($role->name, ['admin', 'customer'])) {
            session()->flash('error', 'No puedes eliminar los roles del sistema.');
            return;
        }

        // Verificar si hay usuarios con este rol
        $usersCount = User::role($role->name)->count();
        if ($usersCount > 0) {
            session()->flash('error', "No puedes eliminar este rol porque hay {$usersCount} usuario(s) asignados.");
            return;
        }

        $role->delete();
        session()->flash('message', 'Rol eliminado correctamente.');
    }

    public function closeRoleModal()
    {
        $this->showRoleModal = false;
        $this->resetRoleForm();
    }

    private function resetRoleForm()
    {
        $this->editingRoleId = null;
        $this->roleName = '';
        $this->rolePermissions = [];
    }

    // ==========================================
    // PERMISOS
    // ==========================================

    public function createPermission()
    {
        $this->resetPermissionForm();
        $this->showPermissionModal = true;
    }

    public function editPermission($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        
        $this->editingPermissionId = $permission->id;
        $this->permissionName = $permission->name;
        $this->showPermissionModal = true;
    }

    public function savePermission()
    {
        $this->validate([
            'permissionName' => 'required|string|max:100',
        ]);

        try {
            if ($this->editingPermissionId) {
                $permission = Permission::findOrFail($this->editingPermissionId);
                $permission->update(['name' => $this->permissionName]);
                session()->flash('message', 'Permiso actualizado correctamente.');
            } else {
                if (Permission::where('name', $this->permissionName)->exists()) {
                    session()->flash('error', 'Ya existe un permiso con ese nombre.');
                    return;
                }
                Permission::create(['name' => $this->permissionName, 'guard_name' => 'web']);
                session()->flash('message', 'Permiso creado correctamente.');
            }

            $this->closePermissionModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el permiso: ' . $e->getMessage());
        }
    }

    public function deletePermission($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);

        // Verificar si está asignado a algún rol
        if ($permission->roles()->count() > 0) {
            session()->flash('error', 'No puedes eliminar este permiso porque está asignado a uno o más roles.');
            return;
        }

        $permission->delete();
        session()->flash('message', 'Permiso eliminado correctamente.');
    }

    public function closePermissionModal()
    {
        $this->showPermissionModal = false;
        $this->resetPermissionForm();
    }

    private function resetPermissionForm()
    {
        $this->editingPermissionId = null;
        $this->permissionName = '';
    }

    // ==========================================
    // USUARIOS - ASIGNACIÓN DE ROLES
    // ==========================================

    public function openUserRoleModal($userId)
    {
        $user = User::findOrFail($userId);
        
        $this->selectedUserId = $user->id;
        $this->selectedUserRoles = $user->roles->pluck('id')->toArray();
        $this->showUserRoleModal = true;
    }

    public function saveUserRoles()
    {
        $user = User::findOrFail($this->selectedUserId);
        
        // Obtener roles por ID
        $roles = Role::whereIn('id', $this->selectedUserRoles)->get();
        
        // Sincronizar roles
        $user->syncRoles($roles);
        
        session()->flash('message', "Roles actualizados para {$user->name}.");
        $this->closeUserRoleModal();
    }

    public function closeUserRoleModal()
    {
        $this->showUserRoleModal = false;
        $this->selectedUserId = null;
        $this->selectedUserRoles = [];
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Agrupar permisos por prefijo
     */
    public function getGroupedPermissionsProperty()
    {
        $permissions = Permission::orderBy('name')->get();
        $grouped = [];

        foreach ($permissions as $permission) {
            // Extraer el grupo del nombre (ej: "manage products" -> "products")
            $parts = explode(' ', $permission->name);
            $group = count($parts) > 1 ? end($parts) : 'general';
            
            // Mapear a grupos conocidos
            $groupKey = $this->mapPermissionToGroup($permission->name);
            
            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [];
            }
            $grouped[$groupKey][] = $permission;
        }

        return $grouped;
    }

    private function mapPermissionToGroup($permissionName)
    {
        $mapping = [
            'dashboard' => 'dashboard',
            'products' => 'products',
            'categories' => 'categories',
            'orders' => 'orders',
            'delivery' => 'delivery',
            'payments' => 'payments',
            'users' => 'users',
            'pos' => 'pos',
            'pedidostv' => 'pos',
            'reports' => 'reports',
            'settings' => 'settings',
            'roles' => 'settings',
        ];

        foreach ($mapping as $keyword => $group) {
            if (str_contains(strtolower($permissionName), $keyword)) {
                return $group;
            }
        }

        return 'general';
    }

    /**
     * Obtener etiqueta amigable del permiso
     */
    public function getPermissionLabel($permissionName)
    {
        $labels = [
            'manage products' => 'Gestionar Productos',
            'manage categories' => 'Gestionar Categorías',
            'manage orders' => 'Gestionar Pedidos',
            'manage delivery zones' => 'Gestionar Zonas de Delivery',
            'manage payment methods' => 'Gestionar Métodos de Pago',
            'manage users' => 'Gestionar Usuarios',
            'view dashboard' => 'Ver Dashboard',
            'view pos' => 'Usar Punto de Venta',
            'view pedidostv' => 'Ver Pantalla de Pedidos',
            'manage roles' => 'Gestionar Roles y Permisos',
            'view reports' => 'Ver Reportes',
        ];

        return $labels[$permissionName] ?? ucwords($permissionName);
    }

    public function render()
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->paginate(20);
        
        $users = User::with('roles')
            ->when($this->searchUser, function ($query) {
                $query->where('name', 'like', '%' . $this->searchUser . '%')
                      ->orWhere('email', 'like', '%' . $this->searchUser . '%');
            })
            ->orderBy('name')
            ->paginate(15);

        $allRoles = Role::orderBy('name')->get();
        $allPermissions = Permission::orderBy('name')->get();

        return view('livewire.admin.roles-permissions', [
            'roles' => $roles,
            'permissions' => $permissions,
            'users' => $users,
            'allRoles' => $allRoles,
            'allPermissions' => $allPermissions,
        ])->layout('components.layouts.admin', ['title' => 'Roles y Permisos']);
    }
}