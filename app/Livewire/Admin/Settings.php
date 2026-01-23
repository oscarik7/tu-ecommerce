<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings extends Component
{
    // Modo Mantenimiento
    public $maintenanceMode = false;
    public $maintenanceMessage = '';
    public $maintenanceDate = '';

    // Datos de la tienda
    public $storeName = '';
    public $storePhone = '';
    public $storeEmail = '';
    public $storeAddress = '';
    public $storeCity = '';

    // Redes sociales
    public $socialFacebook = '';
    public $socialInstagram = '';
    public $socialWhatsapp = '';

    public function mount()
    {
        $this->loadSettings();
    }

    private function loadSettings()
    {
        // Modo Mantenimiento
        $this->maintenanceMode = Setting::get('maintenance_mode', false);
        $this->maintenanceMessage = Setting::get('maintenance_message', '¡Estamos trabajando en algo increíble para ti! 🚀');
        $this->maintenanceDate = Setting::get('maintenance_date', '');

        // Datos de la tienda
        $this->storeName = Setting::get('store_name', 'Taskinho Açaí');
        $this->storePhone = Setting::get('store_phone', '');
        $this->storeEmail = Setting::get('store_email', '');
        $this->storeAddress = Setting::get('store_address', '');
        $this->storeCity = Setting::get('store_city', 'Ciudad del Este');

        // Redes sociales
        $this->socialFacebook = Setting::get('social_facebook', '');
        $this->socialInstagram = Setting::get('social_instagram', '');
        $this->socialWhatsapp = Setting::get('social_whatsapp', '');
    }

    /**
     * Activar/Desactivar modo mantenimiento rápidamente
     */
    public function toggleMaintenanceMode()
    {
        $this->maintenanceMode = !$this->maintenanceMode;
        Setting::set('maintenance_mode', $this->maintenanceMode ? '1' : '0', 'boolean');
        
        $status = $this->maintenanceMode ? 'activado' : 'desactivado';
        session()->flash('message', "Modo mantenimiento {$status}.");
    }

    /**
     * Guardar configuración de mantenimiento
     */
    public function saveMaintenanceSettings()
    {
        $this->validate([
            'maintenanceMessage' => 'required|string|max:500',
            'maintenanceDate' => 'nullable|date',
        ]);

        Setting::set('maintenance_mode', $this->maintenanceMode ? '1' : '0', 'boolean');
        Setting::set('maintenance_message', $this->maintenanceMessage, 'string');
        Setting::set('maintenance_date', $this->maintenanceDate ?: null, 'string');

        session()->flash('message', 'Configuración de mantenimiento guardada.');
    }

    /**
     * Guardar datos de la tienda
     */
    public function saveStoreSettings()
    {
        $this->validate([
            'storeName' => 'required|string|max:100',
            'storePhone' => 'nullable|string|max:20',
            'storeEmail' => 'nullable|email|max:100',
            'storeAddress' => 'nullable|string|max:200',
            'storeCity' => 'nullable|string|max:100',
        ]);

        Setting::set('store_name', $this->storeName, 'string');
        Setting::set('store_phone', $this->storePhone, 'string');
        Setting::set('store_email', $this->storeEmail, 'string');
        Setting::set('store_address', $this->storeAddress, 'string');
        Setting::set('store_city', $this->storeCity, 'string');

        session()->flash('message', 'Datos de la tienda guardados.');
    }

    /**
     * Guardar redes sociales
     */
    public function saveSocialSettings()
    {
        Setting::set('social_facebook', $this->socialFacebook, 'string');
        Setting::set('social_instagram', $this->socialInstagram, 'string');
        Setting::set('social_whatsapp', $this->socialWhatsapp, 'string');

        session()->flash('message', 'Redes sociales guardadas.');
    }

    /**
     * Limpiar toda la caché de settings
     */
    public function clearCache()
    {
        Cache::flush();
        session()->flash('message', 'Caché limpiada correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.settings')
            ->layout('components.layouts.admin', ['title' => 'Configuración']);
    }
}