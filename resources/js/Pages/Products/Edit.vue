<script setup>
// Importações essenciais, as mesmas do formulário de criação
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// **Diferença Principal**: Definimos as 'props' que o componente espera receber.
// Neste caso, esperamos um objeto 'product' que virá do nosso ProductController.
const props = defineProps({
    product: Object,
});

// Inicializamos o useForm, mas desta vez, preenchemos os campos com os dados
// do produto que recebemos via props. Isso fará com que o formulário já
// apareça preenchido com as informações atuais do produto.
const form = useForm({
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
});

// Função de submissão para a atualização
const submit = () => {
    // Usamos o método 'put' para enviar os dados para a rota 'products.update'.
    // A rota 'products.update' requer o ID do produto, que passamos como segundo argumento
    // para a função route(). Ex: route('products.update', 1)
    form.put(route('products.update', props.product.id), {
        // Opções podem ser adicionadas aqui se necessário
    });
};
</script>

<template>
    <!-- O título da página agora é dinâmico, mostrando o nome do produto que está sendo editado -->
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

                        <!-- O formulário é praticamente idêntico ao de criação -->
                        <form @submit.prevent="submit">
                            <!-- Campo Nome do Produto -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome</label>
                                <input
                                    id="name"
                                    type="text"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                            <!-- Campo Descrição -->
                            <div class="mt-4">
                                <label for="description" class="block font-medium text-sm text-gray-700">Descrição</label>
                                <textarea
                                    id="description"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    v-model="form.description"
                                ></textarea>
                                <p v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</p>
                            </div>

                            <!-- Campo Preço -->
                            <div class="mt-4">
                                <label for="price" class="block font-medium text-sm text-gray-700">Preço</label>
                                <input
                                    id="price"
                                    type="number"
                                    step="0.01"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    v-model="form.price"
                                    required
                                />
                                <p v-if="form.errors.price" class="text-sm text-red-600 mt-2">{{ form.errors.price }}</p>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-4">
                                <button
                                    type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Atualizando...' : 'Atualizar Produto' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
