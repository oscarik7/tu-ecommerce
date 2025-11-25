<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taskinho - Bienvenido</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .acai-gradient {
            background: linear-gradient(100deg, #601ff8ff 0%, #a78bfa 100%);
        }
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(139, 92, 246, 0.3);
        }
        .ingredient-badge {
            transition: all 0.2s ease;
        }
        .ingredient-badge.selected {
            background-color: #8b5cf6;
            color: white;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Header/Hero -->
    <div class="acai-gradient text-white">
        <nav class="flex justify-between items-center px-8 py-4">
            <div class="text-3xl font-bold">Taskinho Açaí</div>
            <div class="flex gap-8 items-center">
                <a href="#productos" class="hover:text-purple-200 transition">Productos</a>
                <a href="#contacto" class="hover:text-purple-200 transition">Contacto</a>
                <button class="bg-white text-purple-600 px-6 py-2 rounded-full font-semibold hover:bg-purple-100 transition">Ordenes</button>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="px-8 py-20 text-center max-w-4xl mx-auto">
            <h1 class="text-5xl font-bold mb-6">Açaí Fresco y Delicioso</h1>
            <p class="text-xl mb-8 text-purple-100">Descubre la combinación perfecta de sabor, salud y frescura en cada bowl</p>
        </div>
    </div>

    <!-- Productos Section -->
    <div id="productos" class="px-8 py-16 max-w-6xl mx-auto">
        <h2 class="text-4xl font-bold text-center text-gray-800 mb-4">Nuestros Productos</h2>
        <p class="text-center text-gray-600 mb-12">Personaliza tu bowl con los ingredientes que prefieras</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Producto 1: Bowl Clásico -->
            <div class="product-card bg-white border-2 border-purple-200 rounded-2xl overflow-hidden">
                <div class="h-48 bg-gradient-to-br from-purple-300 to-purple-500 flex items-center justify-center text-6xl">
                    <img src="{{ asset('images/products/acai.jpg') }}" alt="Bowl Clásico" class="h-48 w-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Bowl Clásico</h3>
                    <p class="text-gray-600 mb-4">Açaí puro con granola, frutas frescas y miel</p>
                    <div class="text-3xl font-bold text-purple-600 mb-4">₲20.000</div>
                    
                    <!-- Ingredientes Personalizables -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Ingredientes Adicionales:</label>
                        <div class="flex flex-wrap gap-2">
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Plátano</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Fresa</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Coco</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Granola</button>
                        </div>
                    </div>

                    <button class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition">
                        Agregar al Carrito
                    </button>
                </div>
            </div>

            <!-- Producto 2: Bowl Tropical -->
            <div class="product-card bg-white border-2 border-purple-200 rounded-2xl overflow-hidden">
                <div class="h-48 bg-gradient-to-br from-purple-300 to-purple-500 flex items-center justify-center text-6xl">
                    🥭
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Bowl Tropical</h3>
                    <p class="text-gray-600 mb-4">Açaí con mango, piña, coco y nueces</p>
                    <div class="text-3xl font-bold text-purple-600 mb-4">₲15.000</div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Ingredientes Adicionales:</label>
                        <div class="flex flex-wrap gap-2">
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Miel</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Polen</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Choco Chips</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Almendras</button>
                        </div>
                    </div>

                    <button class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition">
                        Agregar al Carrito
                    </button>
                </div>
            </div>

            <!-- Producto 3: Bowl Fitness -->
            <div class="product-card bg-white border-2 border-purple-200 rounded-2xl overflow-hidden">
                <div class="h-48 bg-gradient-to-br from-purple-300 to-purple-500 flex items-center justify-center text-6xl">
                    💪
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Bowl Fitness</h3>
                    <p class="text-gray-600 mb-4">Açaí bajo en calorías con proteína y frutas</p>
                    <div class="text-3xl font-bold text-purple-600 mb-4">₲11.99</div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Ingredientes Adicionales:</label>
                        <div class="flex flex-wrap gap-2">
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Proteína</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Avena</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Linaza</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Yogurt</button>
                        </div>
                    </div>

                    <button class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition">
                        Agregar al Carrito
                    </button>
                </div>
            </div>

            <!-- Producto 4: Bowl Premium -->
            <div class="product-card bg-white border-2 border-purple-200 rounded-2xl overflow-hidden">
                <div class="h-48 bg-gradient-to-br from-purple-300 to-purple-500 flex items-center justify-center text-6xl">
                    ✨
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Bowl Premium</h3>
                    <p class="text-gray-600 mb-4">Édición especial con ingredientes premium importados</p>
                    <div class="text-3xl font-bold text-purple-600 mb-4">₲13.99</div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Ingredientes Adicionales:</label>
                        <div class="flex flex-wrap gap-2">
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Oreos</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Nutella</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">M&Ms</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Maní</button>
                        </div>
                    </div>

                    <button class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition">
                        Agregar al Carrito
                    </button>
                </div>
            </div>

            <!-- Producto 5: Batido Açaí -->
            <div class="product-card bg-white border-2 border-purple-200 rounded-2xl overflow-hidden">
                <div class="h-48 bg-gradient-to-br from-purple-300 to-purple-500 flex items-center justify-center text-6xl">
                    🥤
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Batido Açaí</h3>
                    <p class="text-gray-600 mb-4">Licuado fresco de açaí con leche y frutas</p>
                    <div class="text-3xl font-bold text-purple-600 mb-4">₲6.99</div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Ingredientes Adicionales:</label>
                        <div class="flex flex-wrap gap-2">
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Leche Coco</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Banana</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Vainilla</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Miel</button>
                        </div>
                    </div>

                    <button class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition">
                        Agregar al Carrito
                    </button>
                </div>
            </div>

            <!-- Producto 6: Sorbet Açaí -->
            <div class="product-card bg-white border-2 border-purple-200 rounded-2xl overflow-hidden">
                <div class="h-48 bg-gradient-to-br from-purple-300 to-purple-500 flex items-center justify-center text-6xl">
                    🍧
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Sorbet Açaí</h3>
                    <p class="text-gray-600 mb-4">Helado artesanal de açaí suave y cremoso</p>
                    <div class="text-3xl font-bold text-purple-600 mb-4">₲5.99</div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Ingredientes Adicionales:</label>
                        <div class="flex flex-wrap gap-2">
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Chocolate</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Caramelo</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Frutos Rojos</button>
                            <button class="ingredient-badge px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-purple-500 hover:text-white" onclick="toggleIngredient(this)">Coco</button>
                        </div>
                    </div>

                    <button class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition">
                        Agregar al Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer id="contacto" class="acai-gradient text-white mt-20">
        <div class="px-8 py-12 max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h4 class="text-xl font-bold mb-4">🍓 Açaí Paradise</h4>
                    <p class="text-purple-100">Tu lugar favorito para disfrutar de açaí fresco y delicioso</p>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-4">Horario</h4>
                    <p class="text-purple-100">Lunes - Domingo: 8:00 AM - 10:00 PM</p>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-4">Contacto</h4>
                    <p class="text-purple-100">📱 +123 456 7890</p>
                    <p class="text-purple-100">📧 info@Taskinhoacai.com</p>
                </div>
            </div>
            <div class="border-t border-purple-400 pt-8 text-center text-purple-100">
                <p>&copy; 2025 Taskinho Açaí. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleIngredient(button) {
            button.classList.toggle('selected');
        }
    </script>
</body>
</html>