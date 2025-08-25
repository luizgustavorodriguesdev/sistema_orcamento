<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Editor from '@/Components/Editor.vue'; // Importamos o nosso componente de editor

// Recebemos o produto e a lista de categorias como props do controller.
const props = defineProps({
    product: Object,
    categories: Array,
});

// Preenchemos o formulário com os dados existentes do produto, incluindo a categoria.
const form = useForm({
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
    promotional_price: props.product.promotional_price,
    category_id: props.product.category_id,
});

// Função para submeter as alterações.
const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>

<template>
    <Head :title="'Editar Produto: ' + product.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Produto: {{ product.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <form @submit.prevent="submit">
                            <!-- Campo Nome do Produto -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.name" required autofocus />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                            <!-- Campo Categoria -->
                            <div class="mt-4">
                                <label for="category_id" class="block font-medium text-sm text-gray-700">Categoria</label>
                                <select id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.category_id">
                                    <option :value="null">-- Nenhuma --</option>
                                    <!-- O loop 'v-for' agora deve funcionar, pois a prop 'categories' está a ser recebida corretamente. -->
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="text-sm text-red-600 mt-2">{{ form.errors.category_id }}</p>
                            </div>

                            <!-- Campo Descrição -->
                            <div class="mt-4">
                                <label for="description" class="block font-medium text-sm text-gray-700">Descrição</label>
                                <Editor id="description" v-model="form.description" />
                                <p v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</p>
                            </div>

                            <!-- Campos de Preço + Promoção -->
                           <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="price" class="block font-medium text-sm text-gray-700">Preço Normal (R$)</label>
                                    <input id="price" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.price" required />
                                    <p v-if="form.errors.price" class="text-sm text-red-600 mt-2">{{ form.errors.price }}</p>
                                </div>
                                <div>
                                    <label for="promotional_price" class="block font-medium text-sm text-gray-700">Preço Promocional (Opcional)</label>
                                    <input id="promotional_price" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.promotional_price" />
                                    <p v-if="form.errors.promotional_price" class="text-sm text-red-600 mt-2">{{ form.errors.promotional_price }}</p>
                                </div>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Atualizar...' : 'Atualizar Produto' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
