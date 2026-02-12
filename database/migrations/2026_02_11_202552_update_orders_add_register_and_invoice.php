<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Caja a la que pertenece esta venta
            $table->foreignId('cash_register_id')
                ->nullable()
                ->after('payment_method_id')
                ->constrained()
                ->onDelete('set null');

            // Quién imprimió el ticket (nombre para el ticket)
            $table->unsignedBigInteger('printed_by')->nullable()->after('cash_register_id');
            $table->foreign('printed_by')->references('id')->on('users')->onDelete('set null');

            // Facturación
            $table->boolean('needs_invoice')->default(false)->after('notes');
            $table->string('invoice_document')->nullable()->after('needs_invoice'); // RUC/CI para factura
            $table->string('invoice_company')->nullable()->after('invoice_document'); // Razón social

            // Canal de venta extendido
            // source ya existe: 'web', 'pos'
            // Ahora también: 'delivery_app', 'manual'
            // Solo se extiende el ENUM si usás MySQL.
            // Como usás string, simplemente se documenta aquí:
            // 'web'          = ecommerce web
            // 'pos'          = tienda física (POS)
            // 'delivery_app' = Pedidos Ya, Rappi, etc. (ingresado manualmente)
            // 'manual'       = venta manual con precio especial

            // Datos de Pedidos Ya / delivery app
            $table->string('delivery_app_name')->nullable()->after('source');    // "Pedidos Ya"
            $table->string('delivery_app_order_id')->nullable()->after('delivery_app_name'); // ID externo
            $table->decimal('delivery_app_commission', 10, 2)->nullable()->after('delivery_app_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cash_register_id']);
            $table->dropForeign(['printed_by']);
            $table->dropColumn([
                'cash_register_id',
                'printed_by',
                'needs_invoice',
                'invoice_document',
                'invoice_company',
                'delivery_app_name',
                'delivery_app_order_id',
                'delivery_app_commission',
            ]);
        });
    }
};