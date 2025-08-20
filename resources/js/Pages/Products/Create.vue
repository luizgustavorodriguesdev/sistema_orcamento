<script setup>
// Importações essenciais
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Layout padrão para páginas autenticadas
import { Head, useForm } from '@inertiajs/vue3'; // Head para o título da página e useForm para gerenciar o formulário

// O useForm do Inertia cria um objeto reativo que encapsula os dados do formulário.
// Ele também gerencia o estado de envio (processing), erros de validação (errors), e muito mais.
const form = useForm({
    name: '',          // Valor inicial para o nome do produto
    description: '',   // Valor inicial para a descrição
    price: null,       // Valor inicial para o preço
});

// Esta função será chamada quando o formulário for submetido.
const submit = () => {
    // O método 'post' do objeto 'form' envia os dados para a rota especificada.
    // O Inertia lida com a requisição AJAX em segundo plano.
    // Em caso de sucesso, o controller redirecionará para a lista de produtos.
    // Em caso de erro de validação, o Inertia preencherá o objeto 'form.errors' automaticamente.
    form.post(route('products.store'), {
        // onFinish é um callback que é executado após a requisição ser completada (com sucesso ou erro).
        // Aqui, estamos limpando o campo de senha, o que é uma boa prática (não aplicável aqui, mas bom saber).
        // onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <!-- Define o título que aparecerá na aba do navegador -->
    <Head title="Adicionar Produto" />

    <!-- Utiliza o layout autenticado, que já contém o menu de navegação e a estrutura da página -->
    <AuthenticatedLayout>
        <!-- Slot para o cabeçalho da página -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Novo Produto</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <!-- O formulário chama a função 'submit' ao ser submetido.
                             O modificador '.prevent' impede o comportamento padrão de recarregamento da página. -->
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
                                    autocomplete="name"
                                />
                                <!-- Exibe a mensagem de erro para o campo 'name', se houver alguma vinda do backend -->
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
                                    <!-- O texto do botão muda e ele fica desabilitado enquanto o formulário está sendo processado -->
                                    {{ form.processing ? 'Salvando...' : 'Salvar Produto' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
