<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue'; // Importamos ref e computed para a reatividade

// O nosso controller passa a lista completa de produtos como uma prop.
const props = defineProps({
    products: Array,
});

// Usamos o 'useForm' do Inertia para gerir os dados principais do orçamento.
const form = useForm({
    client_name: '',
    client_contact: '',
    payment_terms: '',
    delivery_info: '',
    // O 'items' aqui será a lista final de produtos a ser enviada para o backend.
    items: [],
});

// --- Lógica Reativa para Montagem do Orçamento ---

// 'quoteItems' é uma variável reativa (um array) que irá armazenar os produtos
// que o vendedor adiciona ao orçamento. Usamos 'ref' para isso.
const quoteItems = ref([]);

// Esta função é chamada quando o vendedor clica para adicionar um produto.
const addItem = (product) => {
    // Verificamos se o produto já está no orçamento.
    const existingItem = quoteItems.value.find(item => item.product_id === product.id);

    if (existingItem) {
        // Se já existe, apenas incrementamos a quantidade.
        existingItem.quantity++;
    } else {
        // Se não existe, adicionamos o produto ao nosso array 'quoteItems'.
        quoteItems.value.push({
            product_id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1, // Começa com quantidade 1.
        });
    }
};

// Função para remover um item do orçamento.
const removeItem = (productId) => {
    quoteItems.value = quoteItems.value.filter(item => item.product_id !== productId);
};

// 'computed' cria uma propriedade que se recalcula automaticamente sempre que
// uma das suas dependências reativas (neste caso, 'quoteItems') muda.
const totalAmount = computed(() => {
    // Usamos o método 'reduce' para somar o subtotal de cada item.
    return quoteItems.value.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0); // O 0 é o valor inicial do total.
});

// Função final para submeter o formulário.
const submit = () => {
    // Antes de enviar, sincronizamos o nosso array reativo 'quoteItems'
    // com o 'form.items' que será enviado para o backend.
    form.items = quoteItems.value.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
    }));

    // Enviamos o formulário para a rota de armazenamento.
    form.post(route('quotes.store'));
};

// Função para formatar a moeda.
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
</script>

<template>
    <Head title="Criar Orçamento" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Criar Novo Orçamento</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Usamos o mesmo evento @submit.prevent para chamar a nossa função 'submit' -->
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Coluna 1 e 2: Formulário e Itens do Orçamento -->
                        <div class="md:col-span-2 space-y-6">
                            <!-- Detalhes do Cliente -->
                            <div class="bg-white p-6 rounded-lg shadow-sm">
                                <h3 class="text-lg font-bold mb-4">Dados do Cliente</h3>
                                <div>
                                    <label for="client_name">Nome do Cliente</label>
                                    <input type="text" v-model="form.client_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <p v-if="form.errors.client_name" class="text-sm text-red-600 mt-1">{{ form.errors.client_name }}</p>
                                </div>
                                <div class="mt-4">
                                    <label for="client_contact">Contacto (Email/Telefone)</label>
                                    <input type="text" v-model="form.client_contact" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <p v-if="form.errors.client_contact" class="text-sm text-red-600 mt-1">{{ form.errors.client_contact }}</p>
                                </div>
                            </div>

                            <!-- Itens do Orçamento -->
                            <div class="bg-white p-6 rounded-lg shadow-sm">
                                <h3 class="text-lg font-bold mb-4">Itens do Orçamento</h3>
                                <div v-if="quoteItems.length === 0" class="text-center text-gray-500 py-4">
                                    Nenhum item adicionado.
                                </div>
                                <div v-else>
                                    <!-- Tabela de itens adicionados -->
                                    <div v-for="item in quoteItems" :key="item.product_id" class="flex items-center justify-between border-b py-2">
                                        <div>
                                            <p class="font-semibold">{{ item.name }}</p>
                                            <p class="text-sm text-gray-600">{{ formatCurrency(item.price) }}</p>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <input type="number" v-model="item.quantity" min="1" class="w-20 text-center rounded-md border-gray-300 shadow-sm">
                                            <button @click="removeItem(item.product_id)" type="button" class="text-red-500 hover:text-red-700">Remover</button>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="form.errors.items" class="text-sm text-red-600 mt-2">{{ form.errors.items }}</p>
                                <div class="text-right font-bold text-xl mt-4">
                                    Total: {{ formatCurrency(totalAmount) }}
                                </div>
                            </div>
                        </div>

                        <!-- Coluna 3: Lista de Produtos Disponíveis -->
                        <div class="md:col-span-1">
                            <div class="bg-white p-6 rounded-lg shadow-sm">
                                <h3 class="text-lg font-bold mb-4">Adicionar Produtos</h3>
                                <div class="space-y-2 max-h-96 overflow-y-auto">
                                    <div v-for="product in products" :key="product.id" class="flex justify-between items-center p-2 rounded-md hover:bg-gray-100">
                                        <div>
                                            <p class="font-semibold">{{ product.name }}</p>
                                            <p class="text-sm text-gray-600">{{ formatCurrency(product.price) }}</p>
                                        </div>
                                        <button @click="addItem(product)" type="button" class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600">Adicionar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Ações do Formulário -->
                    <div class="mt-6 bg-white p-6 rounded-lg shadow-sm">
                         <h3 class="text-lg font-bold mb-4">Detalhes Adicionais</h3>
                         <div>
                            <label for="payment_terms">Condições de Pagamento</label>
                            <textarea v-model="form.payment_terms" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                         </div>
                         <div class="mt-4">
                            <label for="delivery_info">Prazo de Entrega</label>
                            <textarea v-model="form.delivery_info" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                         </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded" :disabled="form.processing">
                                {{ form.processing ? 'A Guardar...' : 'Guardar Orçamento' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
