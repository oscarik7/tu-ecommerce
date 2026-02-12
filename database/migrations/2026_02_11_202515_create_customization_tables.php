<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Grupos de personalización (ej: "Complementos", "Extras", "Toppings")
        Schema::create('customization_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // "Complementos", "Toppings", "Extras"
            $table->text('description')->nullable();
            $table->boolean('required')->default(false);   // ¿Es obligatorio elegir al menos 1?
            $table->boolean('multiple')->default(true);    // ¿Permite múltiples opciones?
            $table->integer('max_selections')->nullable(); // null = sin límite
            $table->integer('min_selections')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Opciones dentro de cada grupo
        Schema::create('customization_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customization_group_id')->constrained()->onDelete('cascade');
            $table->string('name');                           // "Granola", "Banana", "Extra Açaí"
            $table->decimal('price', 10, 2)->default(0);     // 0 = incluido, >0 = extra con costo
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Qué grupos de personalización aplican a cada producto
        Schema::create('product_customization_groups', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('customization_group_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->primary(['product_id', 'customization_group_id'], 'prod_custom_group_primary');
        });

        // Personalizaciones elegidas en cada item de orden
        Schema::create('order_item_customizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('customization_option_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0); // Precio al momento de la venta (snapshot)
            $table->string('option_name');                // Snapshot del nombre (por si se elimina la opción)
            $table->timestamps();
        });

        // -------------------------------------------------------
        // Datos de ejemplo para el seeder inicial
        // -------------------------------------------------------
        $complementosId = DB::table('customization_groups')->insertGetId([
            'name' => 'Complementos',
            'description' => 'Elige tus complementos incluidos',
            'required' => false,
            'multiple' => true,
            'max_selections' => null,
            'min_selections' => 0,
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $extrasId = DB::table('customization_groups')->insertGetId([
            'name' => 'Extras',
            'description' => 'Agrega extras con costo adicional',
            'required' => false,
            'multiple' => true,
            'max_selections' => null,
            'min_selections' => 0,
            'is_active' => true,
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Complementos incluidos (precio 0)
        $complementos = [
            'Granola',
            'Banana',
            'Frutilla',
            'Mango',
            'Coco Rallado',
            'Leche Condensada',
            'Miel',
            'Avena',
        ];

        foreach ($complementos as $i => $comp) {
            DB::table('customization_options')->insert([
                'customization_group_id' => $complementosId,
                'name' => $comp,
                'price' => 0,
                'is_active' => true,
                'sort_order' => $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Extras con costo
        $extras = [
            ['name' => 'Extra Açaí',       'price' => 5000],
            ['name' => 'Extra Granola',     'price' => 3000],
            ['name' => 'Proteína Whey',     'price' => 8000],
            ['name' => 'Pasta de Amendoim', 'price' => 5000],
            ['name' => 'Nutella',           'price' => 5000],
        ];

        foreach ($extras as $i => $extra) {
            DB::table('customization_options')->insert([
                'customization_group_id' => $extrasId,
                'name' => $extra['name'],
                'price' => $extra['price'],
                'is_active' => true,
                'sort_order' => $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_customizations');
        Schema::dropIfExists('product_customization_groups');
        Schema::dropIfExists('customization_options');
        Schema::dropIfExists('customization_groups');
    }
};