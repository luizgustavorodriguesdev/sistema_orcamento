<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    settings: Object,
});

// --- LÓGICA DO CARRINHO DE ORÇAMENTO ---
// 1. Criamos uma variável reativa para guardar os itens do carrinho.
const cart = ref([]);

// 2. Função para adicionar um produto ao carrinho.
const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.images && product.images.length > 0 ? product.images[0].path : null,
            quantity: 1,
        });
    }
    // Guarda o carrinho no armazenamento local do navegador para persistir os dados.
    localStorage.setItem('quoteCart', JSON.stringify(cart.value));
};

// 3. Quando o componente é montado, tentamos carregar o carrinho que possa já existir.
onMounted(() => {
    const savedCart = localStorage.getItem('quoteCart');
    if (savedCart) {
        cart.value = JSON.parse(savedCart);
    }
});


const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};
</script>

<template>
    <Head :title="settings.company_name || 'Início'" />

    <div class="bg-gray-100 font-sans">
        <!-- CABEÇALHO -->
        <header class="bg-white shadow-md sticky top-0 z-40">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="text-2xl font-bold text-gray-800">
                    <Link href="/">{{ settings.company_name || 'Sua Loja' }}</Link>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <Link v-for="category in categories" :key="category.id" href="#" class="text-gray-600 hover:text-blue-600">{{ category.name }}</Link>
                    <!-- 4. Ícone do Carrinho de Orçamento -->
                    <Link :href="route('storefront.cart')" class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="cart.length > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ cart.length }}</span>
                    </Link>
                </nav>
            </div>
        </header>

        <main>
            <!-- BANNER -->
            <section class="bg-blue-600 text-white">
                <div class="container mx-auto px-6 py-20 text-center">
                    <h1 class="text-4xl font-bold mb-2">Os Melhores Brindes Personalizados</h1>
                    <p class="text-lg">Encontre o presente perfeito para qualquer ocasião.</p>
                </div>
            </section>

            <!-- GRELHA DE PRODUTOS -->
            <section class="py-12">
                <div class="container mx-auto px-6">
                    <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Nossos Produtos</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                        <!-- 5. Card de Produto Atualizado -->
                        <div v-for="product in products.data" :key="product.id" class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                            <Link href="#" class="block">
                                <img v-if="product.images && product.images.length > 0" :src="`/storage/${product.images[0].path}`" alt="Imagem do Produto" class="w-full h-56 object-cover">
                                <div v-else class="w-full h-56 bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500">Sem Imagem</span>
                                </div>
                            </Link>
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2 flex-grow">{{ product.name }}</h3>
                                <p class="text-gray-600 text-lg font-bold mb-4">{{ formatCurrency(product.price) }}</p>
                                <button @click="addToCart(product)" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition-colors">
                                    Adicionar ao Orçamento
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PAGINAÇÃO -->
                    <div class="mt-12 flex justify-center">
                        <div class="flex">
                            <template v-for="(link, key) in products.links" :key="key">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-4 py-2 mx-1 text-sm rounded-md"
                                    :class="{ 'bg-blue-500 text-white': link.active, 'text-gray-700 bg-white hover:bg-gray-100': !link.active }"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-4 py-2 mx-1 text-sm rounded-md text-gray-400 cursor-not-allowed"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- RODAPÉ -->
        <footer class="bg-gray-800 text-white">
            <div class="container mx-auto px-6 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <h3 class="text-2xl font-bold">{{ settings.company_name || 'Sua Loja' }}</h3>
                        <p class="text-gray-400">{{ settings.company_address || 'Endereço da sua empresa' }}</p>
                    </div>
                    <div class="text-center md:text-right">
                        <p class="font-semibold">Contacto</p>
                        <p class="text-gray-400">{{ settings.company_phone || '(XX) XXXX-XXXX' }}</p>
                        <p class="text-gray-400">{{ settings.company_email || 'contato@sualoja.com' }}</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
