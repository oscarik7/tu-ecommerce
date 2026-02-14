<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'address',
        'city',
        'is_active',
        // Facturación (migración 01)
        'document',
        'document_type',
        'company_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ==========================================
    // RELACIONES
    // ==========================================

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    // Cajas que este usuario abrió
    public function cashRegistersOpened()
    {
        return $this->hasMany(CashRegister::class, 'opened_by');
    }

    // Caja actualmente abierta por este usuario
    public function openCashRegister()
    {
        return $this->hasOne(CashRegister::class, 'opened_by')
                    ->where('status', 'open');
    }

    // Gastos registrados por este usuario
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'registered_by');
    }

    // Tickets que este usuario imprimió
    public function printedOrders()
    {
        return $this->hasMany(Order::class, 'printed_by');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Verificar si tiene una caja abierta ahora mismo
     */
    public function getHasOpenCashRegisterAttribute(): bool
    {
        return CashRegister::hasOpenRegister($this->id);
    }

    /**
     * Obtener la caja abierta actual
     */
    public function getCurrentCashRegisterAttribute(): ?CashRegister
    {
        return CashRegister::getOpenRegister($this->id);
    }

    /**
     * Etiqueta del tipo de documento
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return match($this->document_type) {
            'ruc'  => 'RUC',
            'ci'   => 'Cédula de Identidad',
            default => 'CI',
        };
    }

    /**
     * Documento formateado para mostrar en facturas
     */
    public function getFormattedDocumentAttribute(): string
    {
        if (!$this->document) return 'Sin documento';
        return $this->document_type_label . ': ' . $this->document;
    }

    /**
     * Nombre para factura (razón social si tiene RUC, nombre si no)
     */
    public function getInvoiceNameAttribute(): string
    {
        if ($this->document_type === 'ruc' && $this->company_name) {
            return $this->company_name;
        }
        return $this->name;
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCustomers($query)
    {
        return $query->role('customer');
    }

    public function scopeStaff($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'worker', 'cashier']);
        });
    }

    // Dentro de la clase User:
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
