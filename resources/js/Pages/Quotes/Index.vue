<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// O controller passa a prop 'quotes' para este componente.
const props = defineProps({
    quotes: Object,
});

// Função para formatar o preço para o padrão brasileiro.
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

// Função para formatar a data para um formato mais legível.
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('pt-BR', options);
};

// Uma propriedade computada para determinar a cor do status.
const statusClass = (status) => {
    switch (status) {
        case 'Aprovado':
            return 'bg-green-100 text-green-800';
        case 'Cancelado':
            return 'bg-red-100 text-red-800';
        default: // Pendente
            return 'bg-yellow-100 text-yellow-800';
    }
};
</script>

<template>
    <Head title="Orçamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Orçamentos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">Lista de Orçamentos</h3>
                            <Link :href="route('quotes.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Criar Orçamento
                            </Link>
                        </div>

                        <!-- Mensagem de sucesso -->
                        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Cliente</th>
                                        <th scope="col" class="px-6 py-3">Vendedor</th>
                                        <th scope="col" class="px-6 py-3">Data</th>
                                        <th scope="col" class="px-6 py-3">Valor Total</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="quote in quotes.data" :key="quote.id" class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ quote.client_name }}</td>
                                        <td class="px-6 py-4">{{ quote.user ? quote.user.name : 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ formatDate(quote.created_at) }}</td>
                                        <td class="px-6 py-4">{{ formatCurrency(quote.total_amount) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(quote.status)">
                                                {{ quote.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="#" class="font-medium text-blue-600 hover:underline">Ver</a>
                                        </td>
                                    </tr>
                                    <tr v-if="quotes.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            Nenhum orçamento encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="mt-4">
                            <!-- O componente de paginação que já corrigimos antes -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
