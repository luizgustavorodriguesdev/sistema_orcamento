<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';

const props = defineProps({
    categories: Array,
    settings: Object,
    products: Array, // Lista de todos os produtos com as suas escalas de preços
});

const cart = ref([]);

// --- LÓGICA DE PREÇOS DINÂMICOS ---
const getPriceForQuantity = (productId, quantity) => {
    const product = props.products.find(p => p.id === productId);
    if (!product) return 0;

    const tier = product.price_tiers
        .filter(t => t.min_quantity <= quantity)
        .sort((a, b) => b.min_quantity - a.min_quantity)[0];
    return tier ? tier.price : product.price;
};

// Observa o carrinho para recalcular os preços quando a quantidade muda.
watch(cart, (newCart) => {
    newCart.forEach(item => {
        item.price = getPriceForQuantity(item.id, item.quantity);
    });
    localStorage.setItem('quoteCart', JSON.stringify(newCart));
}, { deep: true });

// --- LÓGICA DO CARRINHO ---
const updateQuantity = (item, newQuantity) => {
    const quantity = Math.max(1, parseInt(newQuantity) || 1);
    item.quantity = quantity;
};

const removeItem = (itemId) => {
    cart.value = cart.value.filter(item => item.id !== itemId);
};

const totalAmount = computed(() => {
    return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

onMounted(() => {
    const savedCart = localStorage.getItem('quoteCart');
    if (savedCart) {
        cart.value = JSON.parse(savedCart);
    }
});

// --- LÓGICA DO FORMULÁRIO ---
const form = useForm({
    client_name: '',
    client_contact: '',
    items: [],
});

const submitQuote = () => {
    form.items = cart.value.map(item => ({ id: item.id, quantity: item.quantity }));
    form.post(route('storefront.storeQuote'), {
        onSuccess: () => {
            // Limpa o carrinho após o sucesso
            cart.value = [];
            localStorage.removeItem('quoteCart');
        }
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
</script>

<template>
    <Head title="Carrinho de Orçamento" />

    <div class="bg-gray-100 font-sans min-h-screen">
        <!-- CABEÇALHO -->
        <header class="bg-white shadow-md">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="text-2xl font-bold text-gray-800">
                    <Link href="/">{{ settings.company_name || 'Sua Loja' }}</Link>
                </div>
                <nav class="hidden md:flex space-x-6">
                    <Link v-for="category in categories" :key="category.id" href="#" class="text-gray-600 hover:text-blue-600">{{ category.name }}</Link>
                </nav>
            </div>
        </header>

        <main class="py-12">
            <div class="container mx-auto px-6">
                <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Meu Pedido de Orçamento</h1>

                <div v-if="cart.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Itens do Carrinho -->
                    <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
                        <div v-for="item in cart" :key="item.id" class="flex items-center border-b py-4">
                            <img v-if="item.image" :src="`/storage/${item.image}`" class="w-24 h-24 object-cover rounded-md mr-4">
                            <div v-else class="w-24 h-24 bg-gray-200 rounded-md mr-4"></div>
                            <div class="flex-grow">
                                <h3 class="font-semibold text-lg">{{ item.name }}</h3>
                                <p class="text-gray-600">{{ formatCurrency(item.price) }} / un.</p>
                            </div>
                            <div class="flex items-center">
                                <input 
                                    type="number" 
                                    :value="item.quantity"
                                    @input="updateQuantity(item, $event.target.value)"
                                    min="1" 
                                    class="w-20 text-center rounded-md border-gray-300 shadow-sm mx-4">
                                <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700">Remover</button>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo e Formulário -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h2 class="text-xl font-bold border-b pb-4 mb-4">Resumo do Pedido</h2>
                            <div class="flex justify-between mb-4">
                                <span>Total</span>
                                <span class="font-bold text-lg">{{ formatCurrency(totalAmount) }}</span>
                            </div>
                            <form @submit.prevent="submitQuote">
                                <div class="mt-6">
                                    <label for="client_name" class="block font-medium text-sm text-gray-700">Seu Nome</label>
                                    <input type="text" v-model="form.client_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <p v-if="form.errors.client_name" class="text-sm text-red-600 mt-1">{{ form.errors.client_name }}</p>
                                </div>
                                <div class="mt-4">
                                    <label for="client_contact" class="block font-medium text-sm text-gray-700">Seu Email ou Telefone</label>
                                    <input type="text" v-model="form.client_contact" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <p v-if="form.errors.client_contact" class="text-sm text-red-600 mt-1">{{ form.errors.client_contact }}</p>
                                </div>
                                <button type="submit" class="w-full mt-6 bg-green-500 text-white font-bold py-3 px-4 rounded hover:bg-green-600 transition-colors" :disabled="form.processing">
                                    {{ form.processing ? 'A Enviar...' : 'Finalizar e Gerar Orçamento' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center bg-white rounded-lg shadow-lg p-12">
                    <h2 class="text-2xl font-bold text-gray-800">O seu carrinho de orçamento está vazio.</h2>
                    <p class="text-gray-600 mt-2">Adicione produtos da nossa vitrine para começar.</p>
                    <Link href="/" class="mt-6 inline-block bg-blue-500 text-white font-bold py-3 px-6 rounded hover:bg-blue-600 transition-colors">
                        Voltar à Vitrine
                    </Link>
                </div>
            </div>
        </main>

        <!-- RODAPÉ -->
        <footer class="bg-gray-800 text-white mt-12">
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
