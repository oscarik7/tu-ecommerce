<div>
    {{-- Mensajes Flash --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">✕</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">✕</button>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="setTab('roles')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'roles' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    🎭 Roles
                    <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $roles->count() }}</span>
                </button>
                <button wire:click="setTab('permissions')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'permissions' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    🔐 Permisos
                    <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $allPermissions->count() }}</span>
                </button>
                <button wire:click="setTab('users')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'users' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    👥 Usuarios
                </button>
            </nav>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TAB: ROLES --}}
    {{-- ============================================ --}}
    @if($activeTab === 'roles')
        <div class="mb-4 flex justify-between items-center">
            <p class="text-gray-600">Administra los roles y sus permisos asociados.</p>
            <button wire:click="createRole" 
                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Rol
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
                <div class="bg-white rounded-xl shadow-md overflow-hidden border-2 {{ in_array($role->name, ['admin', 'customer']) ? 'border-purple-200' : 'border-gray-100' }}">
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                @if($role->name === 'admin')
                                    <span class="text-2xl">👑</span>
                                @elseif($role->name === 'customer')
                                    <span class="text-2xl">🛒</span>
                                @elseif($role->name === 'worker')
                                    <span class="text-2xl">👷</span>
                                @elseif($role->name === 'tv')
                                    <span class="text-2xl">📺</span>
                                @else
                                    <span class="text-2xl">🎭</span>
                                @endif
                                <h3 class="text-lg font-bold text-gray-900 capitalize">{{ $role->name }}</h3>
                            </div>
                            @if(in_array($role->name, ['admin', 'customer']))
                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">Sistema</span>
                            @endif
                        </div>

                        <div class="flex gap-4 text-sm text-gray-600 mb-4">
                            <div class="flex items-center gap-1">
                                <span>👥</span>
                                <span>{{ $role->users_count }} usuarios</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span>🔐</span>
                                <span>{{ $role->permissions_count }} permisos</span>
                            </div>
                        </div>

                        {{-- Lista de permisos (colapsable) --}}
                        <div x-data="{ expanded: false }">
                            @if($role->permissions->count() > 0)
                                <button @click="expanded = !expanded" class="text-xs text-purple-600 hover:text-purple-700 font-medium mb-2">
                                    <span x-text="expanded ? '▼ Ocultar permisos' : '▶ Ver permisos'"></span>
                                </button>
                                <div x-show="expanded" x-collapse class="space-y-1">
                                    @foreach($role->permissions->take(10) as $permission)
                                        <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded mr-1 mb-1">
                                            {{ $this->getPermissionLabel($permission->name) }}
                                        </span>
                                    @endforeach
                                    @if($role->permissions->count() > 10)
                                        <span class="text-xs text-gray-500">+{{ $role->permissions->count() - 10 }} más</span>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-gray-400 italic">Sin permisos asignados</p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 px-5 py-3 flex justify-end gap-2">
                        <button wire:click="editRole({{ $role->id }})" 
                            class="text-purple-600 hover:text-purple-800 font-medium text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar
                        </button>
                        @if(!in_array($role->name, ['admin', 'customer']))
                            <button wire:click="deleteRole({{ $role->id }})" 
                                wire:confirm="¿Estás seguro de eliminar el rol '{{ $role->name }}'?"
                                class="text-red-600 hover:text-red-800 font-medium text-sm flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Eliminar
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- TAB: PERMISOS --}}
    {{-- ============================================ --}}
    @if($activeTab === 'permissions')
        <div class="mb-4 flex justify-between items-center">
            <p class="text-gray-600">Lista de permisos disponibles en el sistema.</p>
            <button wire:click="createPermission" 
                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Permiso
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permiso</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles con este permiso</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($permissions as $permission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🔐</span>
                                    <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $permission->name }}</code>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $this->getPermissionLabel($permission->name) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($permission->roles as $role)
                                        <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full capitalize">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-xs italic">Sin asignar</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="editPermission({{ $permission->id }})" 
                                        class="text-purple-600 hover:text-purple-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    @if($permission->roles->count() === 0)
                                        <button wire:click="deletePermission({{ $permission->id }})" 
                                            wire:confirm="¿Eliminar el permiso '{{ $permission->name }}'?"
                                            class="text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No hay permisos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $permissions->links() }}
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- TAB: USUARIOS --}}
    {{-- ============================================ --}}
    @if($activeTab === 'users')
        <div class="mb-4">
            <p class="text-gray-600 mb-4">Asigna roles a los usuarios del sistema.</p>
            <input wire:model.live.debounce.300ms="searchUser" 
                type="text" 
                placeholder="🔍 Buscar usuario por nombre o email..."
                class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium
                                            {{ $role->name === 'admin' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $role->name === 'customer' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $role->name === 'worker' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $role->name === 'tv' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ !in_array($role->name, ['admin', 'customer', 'worker', 'tv']) ? 'bg-gray-100 text-gray-700' : '' }}">
                                            @if($role->name === 'admin') 👑 @endif
                                            @if($role->name === 'customer') 🛒 @endif
                                            @if($role->name === 'worker') 👷 @endif
                                            @if($role->name === 'tv') 📺 @endif
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-xs italic">Sin roles</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button wire:click="openUserRoleModal({{ $user->id }})" 
                                    class="text-purple-600 hover:text-purple-800 font-medium text-sm flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Editar Roles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- MODAL: CREAR/EDITAR ROL --}}
    {{-- ============================================ --}}
    @if($showRoleModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 overflow-y-auto">
            <div class="bg-white rounded-xl max-w-2xl w-full my-8 max-h-[90vh] flex flex-col" wire:click.stop>
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ $editingRoleId ? 'Editar Rol' : 'Nuevo Rol' }}
                        </h2>
                        <button wire:click="closeRoleModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 flex-1 overflow-y-auto">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Rol *</label>
                        <input wire:model="roleName" type="text" 
                            placeholder="Ej: supervisor, cajero, repartidor"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            {{ $editingRoleId && in_array($roleName, ['admin', 'customer']) ? 'disabled' : '' }}>
                        @error('roleName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Permisos Asignados</label>
                        
                        <div class="space-y-4">
                            @foreach($this->groupedPermissions as $group => $groupPermissions)
                                @php
                                    $groupInfo = $permissionGroups[$group] ?? ['icon' => '📋', 'label' => ucfirst($group)];
                                @endphp
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-lg">{{ $groupInfo['icon'] }}</span>
                                        <h4 class="font-medium text-gray-900">{{ $groupInfo['label'] }}</h4>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach($groupPermissions as $permission)
                                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-2 rounded transition">
                                                <input type="checkbox" 
                                                    wire:model="rolePermissions" 
                                                    value="{{ $permission->id }}"
                                                    class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                                <span class="text-sm text-gray-700">{{ $this->getPermissionLabel($permission->name) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                    <button wire:click="closeRoleModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button wire:click="saveRole" 
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                        {{ $editingRoleId ? 'Actualizar' : 'Crear Rol' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- MODAL: CREAR/EDITAR PERMISO --}}
    {{-- ============================================ --}}
    @if($showPermissionModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-xl max-w-md w-full" wire:click.stop>
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ $editingPermissionId ? 'Editar Permiso' : 'Nuevo Permiso' }}
                        </h2>
                        <button wire:click="closePermissionModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Permiso *</label>
                        <input wire:model="permissionName" type="text" 
                            placeholder="Ej: manage inventory, view reports"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('permissionName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-2">
                            💡 Tip: Usa el formato "acción recurso" (ej: manage products, view dashboard)
                        </p>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                    <button wire:click="closePermissionModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button wire:click="savePermission" 
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                        {{ $editingPermissionId ? 'Actualizar' : 'Crear Permiso' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- MODAL: ASIGNAR ROLES A USUARIO --}}
    {{-- ============================================ --}}
    @if($showUserRoleModal)
        @php
            $selectedUser = \App\Models\User::find($selectedUserId);
        @endphp
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-xl max-w-md w-full" wire:click.stop>
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-900">Editar Roles de Usuario</h2>
                        <button wire:click="closeUserRoleModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    @if($selectedUser)
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $selectedUser->name }}</div>
                                <div class="text-sm text-gray-500">{{ $selectedUser->email }}</div>
                            </div>
                        </div>
                    @endif

                    <label class="block text-sm font-medium text-gray-700 mb-3">Selecciona los roles:</label>
                    <div class="space-y-2">
                        @foreach($allRoles as $role)
                            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition
                                {{ in_array($role->id, $selectedUserRoles) ? 'border-purple-300 bg-purple-50' : 'border-gray-200' }}">
                                <input type="checkbox" 
                                    wire:model="selectedUserRoles" 
                                    value="{{ $role->id }}"
                                    class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        @if($role->name === 'admin') 👑 @endif
                                        @if($role->name === 'customer') 🛒 @endif
                                        @if($role->name === 'worker') 👷 @endif
                                        @if($role->name === 'tv') 📺 @endif
                                        <span class="font-medium capitalize">{{ $role->name }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $role->permissions_count ?? $role->permissions->count() }} permisos</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                    <button wire:click="closeUserRoleModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button wire:click="saveUserRoles" 
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>