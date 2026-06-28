<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import MetricCard from '@/Components/MetricCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import Tooltip from '@/Components/Tooltip.vue';
import HelpTooltip from '@/Components/HelpTooltip.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle,
    Eye,
    Pencil,
    Plus,
    Power,
    RotateCcw,
    School,
    Trash,
    Trash2,
    UserCheck,
    Users,
    XCircle,
} from '@lucide/vue';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { maskPhone, maskCnpj, maskCep, slugify } from '@/Utils/mask';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({}),
    },
    institutions: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    deletedModels: {
        type: Array,
        default: () => [],
    },
    reports: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref(new URLSearchParams(window.location.search).get('tab') || 'institutions');
const isViewingSubject = ref(false);

watch(activeTab, (newTab) => {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', newTab);
    window.history.replaceState({}, '', url.toString());
});

// Confirm Modal Dynamic State
const confirmState = ref({
    show: false,
    title: '',
    message: '',
    type: 'danger',
    onConfirm: () => {},
});

const triggerConfirm = (title, message, type, onConfirm) => {
    confirmState.value = {
        show: true,
        title,
        message,
        type,
        onConfirm: () => {
            onConfirm();
            confirmState.value.show = false;
        },
    };
};

// Institution Modal & Form
const isInstitutionModalOpen = ref(false);
const isEditingInstitution = ref(false);
const selectedInstitutionId = ref(null);
const wasSlugManuallyEdited = ref(false);

const instForm = useForm({
    name: '',
    description: '',
    razao_social: '',
    cnpj: '',
    slug: '',
    address: {
        cep: '',
        logradouro: '',
        numero: '',
        complemento: '',
        bairro: '',
        cidade: '',
        uf: '',
    },
    phones: [''],
});

watch(() => instForm.name, (newName) => {
    if (!isEditingInstitution.value && !wasSlugManuallyEdited.value) {
        instForm.slug = slugify(newName);
    }
});

const isSearchingCep = ref(false);
const cepErrorMessage = ref('');

const searchCep = async () => {
    const rawCep = instForm.address.cep.replace(/\D/g, '');
    if (rawCep.length !== 8) {
        cepErrorMessage.value = 'CEP deve conter 8 dígitos.';
        return;
    }

    isSearchingCep.value = true;
    cepErrorMessage.value = '';

    const fetchWithTimeout = (url, timeout = 3000) => {
        return Promise.race([
            fetch(url).then(res => {
                if (!res.ok) throw new Error('API error');
                return res.json();
            }),
            new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout')), timeout))
        ]);
    };

    try {
        const data = await fetchWithTimeout(`https://viacep.com.br/ws/${rawCep}/json/`);
        if (data.erro) {
            throw new Error('ViaCEP error');
        }
        instForm.address.logradouro = data.logradouro || '';
        instForm.address.bairro = data.bairro || '';
        instForm.address.cidade = data.localidade || '';
        instForm.address.uf = data.uf || '';
    } catch (e) {
        console.warn('ViaCEP falhou, tentando fallback BrasilAPI...', e);
        try {
            const data = await fetchWithTimeout(`https://brasilapi.com.br/api/cep/v1/${rawCep}`);
            instForm.address.logradouro = data.street || '';
            instForm.address.bairro = data.neighborhood || '';
            instForm.address.cidade = data.city || '';
            instForm.address.uf = data.state || '';
        } catch (err) {
            console.error('Ambas APIs de CEP falharam.', err);
            cepErrorMessage.value = 'Não foi possível buscar o CEP automaticamente. Por favor, preencha manualmente.';
        }
    } finally {
        isSearchingCep.value = false;
    }
};

const addPhoneField = () => {
    instForm.phones.push('');
};

const removePhoneField = (index) => {
    if (instForm.phones.length > 1) {
        instForm.phones.splice(index, 1);
    } else {
        instForm.phones[0] = '';
    }
};

const openCreateInstitution = () => {
    isEditingInstitution.value = false;
    wasSlugManuallyEdited.value = false;
    instForm.reset();
    instForm.address = {
        cep: '',
        logradouro: '',
        numero: '',
        complemento: '',
        bairro: '',
        cidade: '',
        uf: '',
    };
    instForm.phones = [''];
    cepErrorMessage.value = '';
    isInstitutionModalOpen.value = true;
};

const openEditInstitution = (inst) => {
    isEditingInstitution.value = true;
    wasSlugManuallyEdited.value = true;
    selectedInstitutionId.value = inst.id;
    instForm.name = inst.name;
    instForm.description = inst.description || '';
    instForm.razao_social = inst.razao_social || '';
    instForm.cnpj = inst.cnpj || '';
    instForm.slug = inst.slug || '';

    if (inst.address) {
        instForm.address = {
            cep: inst.address.cep || '',
            logradouro: inst.address.logradouro || '',
            numero: inst.address.numero || '',
            complemento: inst.address.complemento || '',
            bairro: inst.address.bairro || '',
            cidade: inst.address.cidade || '',
            uf: inst.address.uf || '',
        };
    } else {
        instForm.address = {
            cep: '',
            logradouro: '',
            numero: '',
            complemento: '',
            bairro: '',
            cidade: '',
            uf: '',
        };
    }

    instForm.phones = inst.phones && Array.isArray(inst.phones) && inst.phones.length > 0
        ? [...inst.phones]
        : [''];

    cepErrorMessage.value = '';
    isInstitutionModalOpen.value = true;
};

const submitInstitution = () => {
    if (isEditingInstitution.value) {
        instForm.put(
            route(
                'super-admin.institutions.update',
                selectedInstitutionId.value,
            ),
            {
                onSuccess: () => {
                    isInstitutionModalOpen.value = false;
                    instForm.reset();
                },
            },
        );
    } else {
        instForm.post(route('super-admin.institutions.store'), {
            onSuccess: () => {
                isInstitutionModalOpen.value = false;
                instForm.reset();
            },
        });
    }
};

const confirmToggleInstitution = (inst) => {
    const actionText = inst.is_active ? 'desativar' : 'ativar';
    triggerConfirm(
        'Confirmar Alteração de Status',
        `Tem certeza de que deseja ${actionText} a instituição "${inst.name}"? Isso afetará o acesso de seus membros.`,
        'warning',
        () => {
            router.post(route('super-admin.institutions.toggle', inst.id));
        },
    );
};

const confirmDeleteInstitution = (inst) => {
    triggerConfirm(
        'Excluir Instituição',
        `Tem certeza de que deseja excluir a instituição "${inst.name}"? Ela será enviada para a lixeira.`,
        'danger',
        () => {
            router.delete(route('super-admin.institutions.destroy', inst.id));
        },
    );
};

// User Modal & Form
const isUserModalOpen = ref(false);
const isEditingUser = ref(false);
const selectedUserId = ref(null);

const userForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'student',
    institution_id: '',
});

const openCreateUser = () => {
    isEditingUser.value = false;
    userForm.reset();
    isUserModalOpen.value = true;
};

const openEditUser = (user) => {
    isEditingUser.value = true;
    selectedUserId.value = user.id;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.password = '';
    userForm.role = user.role;
    userForm.institution_id = user.institution_id || '';
    isUserModalOpen.value = true;
};

const submitUser = () => {
    if (isEditingUser.value) {
        userForm.put(route('super-admin.users.update', selectedUserId.value), {
            onSuccess: () => {
                isUserModalOpen.value = false;
                userForm.reset();
            },
        });
    } else {
        userForm.post(route('super-admin.users.store'), {
            onSuccess: () => {
                isUserModalOpen.value = false;
                userForm.reset();
            },
        });
    }
};

const confirmToggleUser = (user) => {
    const actionText = user.is_active ? 'desativar' : 'ativar';
    triggerConfirm(
        'Confirmar Alteração de Status',
        `Tem certeza de que deseja ${actionText} o usuário "${user.name}"?`,
        'warning',
        () => {
            router.post(route('super-admin.users.toggle', user.id));
        },
    );
};

const confirmDeleteUser = (user) => {
    triggerConfirm(
        'Excluir Usuário',
        `Tem certeza de que deseja excluir o usuário "${user.name}"? Ele será enviado para a lixeira.`,
        'danger',
        () => {
            router.delete(route('super-admin.users.destroy', user.id));
        },
    );
};

const confirmImpersonate = (user) => {
    triggerConfirm(
        'Personificar Usuário',
        `Deseja iniciar a simulação/personificação da conta de "${user.name}"? Você será redirecionado ao painel dele.`,
        'info',
        () => {
            router.post(route('super-admin.impersonate', user.id));
        },
    );
};

const confirmRestore = (del) => {
    triggerConfirm(
        'Restaurar Registro',
        `Deseja restaurar este registro apagado (${del.model})?`,
        'info',
        () => {
            router.post(route('super-admin.deleted-models.restore', del.id));
        },
    );
};

// Subject Modal & Form
const isSubjectModalOpen = ref(false);
const isEditingSubject = ref(false);
const selectedSubjectId = ref(null);

const subjectForm = useForm({
    name: '',
    slug: '',
    description: '',
    duration: '',
    institution_id: '',
});

watch(() => subjectForm.name, (newName) => {
    if (!isEditingSubject.value) {
        subjectForm.slug = slugify(newName);
    }
});

const openCreateSubject = () => {
    isEditingSubject.value = false;
    isViewingSubject.value = false;
    subjectForm.reset();
    isSubjectModalOpen.value = true;
};

const openEditSubject = (sub) => {
    isEditingSubject.value = true;
    selectedSubjectId.value = sub.id;
    subjectForm.name = sub.name;
    subjectForm.slug = sub.slug || '';
    subjectForm.description = sub.description || '';
    subjectForm.duration = sub.duration || '';
    subjectForm.institution_id = sub.institution_id || '';
    isSubjectModalOpen.value = true;
};

const submitSubject = () => {
    if (isEditingSubject.value) {
        subjectForm.put(route('super-admin.subjects.update', selectedSubjectId.value), {
            onSuccess: () => {
                isSubjectModalOpen.value = false;
                subjectForm.reset();
            },
        });
    } else {
        subjectForm.post(route('super-admin.subjects.store'), {
            onSuccess: () => {
                isSubjectModalOpen.value = false;
                subjectForm.reset();
            },
        });
    }
};

const confirmToggleSubject = (sub) => {
    const actionText = sub.is_active === 1 || sub.is_active?.value === 1 ? 'desativar' : 'ativar';
    triggerConfirm(
        'Confirmar Alteração de Status',
        `Tem certeza de que deseja ${actionText} a matéria "${sub.name}"?`,
        'warning',
        () => {
            router.post(route('super-admin.subjects.toggle', sub.id));
        },
    );
};

const confirmDeleteSubject = (sub) => {
    triggerConfirm(
        'Excluir Matéria',
        `Tem certeza de que deseja excluir a matéria "${sub.name}"? Ela será enviada para a lixeira.`,
        'danger',
        () => {
            router.delete(route('super-admin.subjects.destroy', sub.id));
        },
    );
};

// Polling Relativos a Relatórios
const pollInterval = ref(null);

const checkAndStartPolling = () => {
    const hasPending = props.reports.some(r => r.status === 'pending');
    if (hasPending) {
        if (!pollInterval.value) {
            pollInterval.value = setInterval(() => {
                router.reload({
                    only: ['reports', 'metrics'],
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        const stillPending = props.reports.some(r => r.status === 'pending');
                        if (!stillPending) {
                            stopPolling();
                        }
                    }
                });
            }, 4000);
        }
    } else {
        stopPolling();
    }
};

const stopPolling = () => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
    }
};

watch(() => props.reports, () => {
    checkAndStartPolling();
}, { deep: true });

const generalSyncInterval = ref(null);

onMounted(() => {
    checkAndStartPolling();
    generalSyncInterval.value = setInterval(() => {
        router.reload({
            only: ['metrics'],
            preserveScroll: true,
            preserveState: true,
        });
    }, 15000);
});

onUnmounted(() => {
    stopPolling();
    if (generalSyncInterval.value) {
        clearInterval(generalSyncInterval.value);
    }
});

const institutionColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'subjects_count', label: 'Matérias', sortable: true, align: 'center' },
    { key: 'users_count', label: 'Membros', sortable: true, align: 'center' },
    { key: 'actions', label: 'Ações', sortable: false, align: 'right' },
];

const userColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'role', label: 'Papel', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'points', label: 'Pontos XP', sortable: true, align: 'center' },
    { key: 'actions', label: 'Ações', sortable: false, align: 'right' },
];

const subjectColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'institution', label: 'Instituição', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: 'Ações', sortable: false, align: 'right' },
];

const trashColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'model', label: 'Modelo', sortable: true },
    { key: 'values', label: 'Dados', sortable: false },
    { key: 'actions', label: 'Ações', sortable: false, align: 'right' },
];

const reportColumns = [
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Solicitado em', sortable: true },
    { key: 'actions', label: 'Ações', sortable: false, align: 'right' },
];
</script>

<template>
    <Head title="Painel Super Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Visão Global — Super Admin
                </h2>
            </div>
        </template>

        <div class="min-h-[calc(100vh-64px)] bg-zinc-950 py-12 text-zinc-100">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Metrics -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <MetricCard
                        title="Usuários"
                        :value="metrics.total_users || 0"
                        color="text-indigo-500"
                    >
                        <template #icon><Users class="h-12 w-12" /></template>
                        <template #icon-header
                            ><Users class="mb-2 h-8 w-8 text-indigo-500"
                        /></template>
                    </MetricCard>
                    <MetricCard
                        title="Instituições"
                        :value="metrics.total_institutions || 0"
                        color="text-emerald-500"
                    >
                        <template #icon><School class="h-12 w-12" /></template>
                        <template #icon-header
                            ><School class="mb-2 h-8 w-8 text-emerald-500"
                        /></template>
                    </MetricCard>
                    <MetricCard
                        title="Matérias"
                        :value="metrics.total_subjects || 0"
                        color="text-pink-500"
                    >
                        <template #icon
                            ><BookOpen class="h-12 w-12"
                        /></template>
                        <template #icon-header
                            ><BookOpen class="mb-2 h-8 w-8 text-pink-500"
                        /></template>
                    </MetricCard>
                    <MetricCard
                        title="Arquivados"
                        :value="metrics.total_deleted || 0"
                        color="text-rose-500"
                    >
                        <template #icon><Trash class="h-12 w-12" /></template>
                        <template #icon-header
                            ><Trash class="mb-2 h-8 w-8 text-rose-500"
                        /></template>
                    </MetricCard>
                </div>

                <!-- Export CSV Reports -->
                <div class="flex flex-wrap items-center justify-end gap-4">
                    <button
                        @click="router.post(route('super-admin.reports.members'), {}, { preserveScroll: true, preserveState: true })"
                        class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg transition-all hover:from-blue-500 hover:to-cyan-500"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            ></path>
                        </svg>
                        Exportar Membros (XLSX)
                    </button>
                    <button
                        @click="router.post(route('super-admin.reports.performance'), {}, { preserveScroll: true, preserveState: true })"
                        class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg transition-all hover:from-emerald-500 hover:to-teal-500"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            ></path>
                        </svg>
                        Exportar Desempenho (XLSX)
                    </button>
                </div>

                <div
                    class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-xl"
                >
                    <!-- Tabs -->
                    <div
                        class="mb-6 flex gap-6 overflow-x-auto border-b border-zinc-800"
                    >
                        <button
                            @click="activeTab = 'institutions'"
                            :class="{
                                'border-b-2 border-indigo-400 font-bold text-indigo-400':
                                    activeTab === 'institutions',
                                'text-zinc-500': activeTab !== 'institutions',
                            }"
                            class="whitespace-nowrap pb-3 transition-colors"
                        >
                            Instituições
                        </button>
                        <button
                            @click="activeTab = 'users'"
                            :class="{
                                'border-b-2 border-indigo-400 font-bold text-indigo-400':
                                    activeTab === 'users',
                                'text-zinc-500': activeTab !== 'users',
                            }"
                            class="whitespace-nowrap pb-3 transition-colors"
                        >
                            Usuários
                        </button>
                        <button
                            @click="activeTab = 'subjects'"
                            :class="{
                                'border-b-2 border-indigo-400 font-bold text-indigo-400':
                                    activeTab === 'subjects',
                                'text-zinc-500': activeTab !== 'subjects',
                            }"
                            class="whitespace-nowrap pb-3 transition-colors"
                        >
                            Matérias
                        </button>
                        <button
                            @click="activeTab = 'trash'"
                            :class="{
                                'border-b-2 border-rose-400 font-bold text-rose-400':
                                    activeTab === 'trash',
                                'text-zinc-500': activeTab !== 'trash',
                            }"
                            class="whitespace-nowrap pb-3 transition-colors"
                        >
                            Lixeira
                        </button>
                        <button
                            @click="activeTab = 'reports'"
                            :class="{
                                'border-b-2 border-indigo-400 font-bold text-indigo-400':
                                    activeTab === 'reports',
                                'text-zinc-500': activeTab !== 'reports',
                            }"
                            class="whitespace-nowrap pb-3 transition-colors"
                        >
                            Relatórios
                        </button>
                    </div>

                    <!-- Tab: Institutions -->
                    <div v-if="activeTab === 'institutions'" class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white">
                                Gerenciamento de Instituições
                            </h3>
                            <Button @click="openCreateInstitution">
                                <template #icon><Plus class="h-4 w-4" /></template>
                                Cadastrar
                            </Button>
                        </div>

                        <DataTable
                            :items="institutions"
                            :columns="institutionColumns"
                            searchPlaceholder="Buscar instituição..."
                        >
                            <template #id="{ item }">
                                <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                            </template>
                            <template #name="{ item }">
                                <div class="font-semibold text-zinc-150">{{ item.name }}</div>
                                <div class="max-w-xs truncate text-xs text-zinc-500">{{ item.description || 'Sem descrição' }}</div>
                            </template>
                            <template #is_active="{ item }">
                                <span v-if="item.is_active === 1 || item.is_active?.value === 1" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400">
                                    <CheckCircle class="h-3 w-3" /> Ativo
                                </span>
                                <span v-else class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-400">
                                    <XCircle class="h-3 w-3" /> Inativo
                                </span>
                            </template>
                            <template #subjects_count="{ item }">
                                <span class="font-bold text-indigo-400">{{ item.subjects_count }}</span>
                            </template>
                            <template #users_count="{ item }">
                                <span class="font-bold text-emerald-400">{{ item.users_count }}</span>
                            </template>
                            <template #actions="{ item }">
                                <div class="flex items-center justify-end gap-2">
                                    <Tooltip :text="item.is_active === 1 || item.is_active?.value === 1 ? 'Desativar' : 'Ativar'">
                                        <Button
                                            variant="icon"
                                            @click="confirmToggleInstitution(item)"
                                            :class="item.is_active === 1 || item.is_active?.value === 1 ? 'text-amber-500' : 'text-emerald-500'"
                                        >
                                            <template #icon><Power class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>

                                    <Tooltip text="Editar">
                                        <Button
                                            variant="icon"
                                            @click="openEditInstitution(item)"
                                            class="text-indigo-400"
                                        >
                                            <template #icon><Pencil class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>

                                    <Tooltip text="Excluir">
                                        <Button
                                            variant="icon"
                                            @click="confirmDeleteInstitution(item)"
                                            class="text-rose-500"
                                        >
                                            <template #icon><Trash2 class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>
                                </div>
                            </template>
                        </DataTable>
                    </div>

                    <!-- Tab: Users -->
                    <div v-if="activeTab === 'users'" class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white">
                                Gerenciamento de Usuários
                            </h3>
                            <Button @click="openCreateUser">
                                <template #icon><Plus class="h-4 w-4" /></template>
                                Cadastrar Usuário
                            </Button>
                        </div>

                        <DataTable
                            :items="users"
                            :columns="userColumns"
                            searchPlaceholder="Buscar usuário..."
                            filterKey="role"
                            :filterOptions="[
                                { value: 'admin', label: 'Administrador' },
                                { value: 'teacher', label: 'Professor' },
                                { value: 'student', label: 'Estudante' }
                            ]"
                            filterPlaceholder="Todos os papéis"
                        >
                            <template #id="{ item }">
                                <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                            </template>
                            <template #name="{ item }">
                                <div class="font-semibold text-zinc-150">{{ item.name }}</div>
                                <div class="text-xs text-zinc-400">{{ item.email }}</div>
                                <div v-if="item.institution" class="mt-0.5 text-xs text-zinc-500 font-medium flex items-center gap-1">
                                    <School class="h-3 w-3" /> {{ item.institution.name }}
                                </div>
                            </template>
                            <template #role="{ item }">
                                <span
                                    class="inline-flex rounded-lg border px-2 py-0.5 text-xs font-bold uppercase"
                                    :class="
                                        item.role === 'super_admin' ? 'text-purple-400 bg-purple-500/10 border-purple-500/20' :
                                        item.role === 'admin' ? 'text-indigo-400 bg-indigo-500/10 border-indigo-500/20' :
                                        item.role === 'teacher' ? 'text-sky-400 bg-sky-500/10 border-sky-500/20' :
                                        'text-amber-400 bg-amber-500/10 border-amber-500/20'
                                    "
                                >
                                    {{
                                        item.role === 'super_admin' ? 'Super Admin' :
                                        item.role === 'admin' ? 'Administrador' :
                                        item.role === 'teacher' ? 'Professor' :
                                        'Estudante'
                                    }}
                                </span>
                            </template>
                            <template #is_active="{ item }">
                                <span v-if="item.is_active === 1 || item.is_active?.value === 1" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400">
                                    <CheckCircle class="h-3 w-3" /> Ativo
                                </span>
                                <span v-else class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-400">
                                    <XCircle class="h-3 w-3" /> Inativo
                                </span>
                            </template>
                            <template #points="{ item }">
                                <span class="font-mono font-bold text-amber-400">{{ item.points }} XP</span>
                            </template>
                            <template #actions="{ item }">
                                <div class="flex items-center justify-end gap-2">
                                    <Tooltip text="Personificar">
                                        <Button
                                            variant="icon"
                                            @click="confirmImpersonate(item)"
                                            class="text-teal-400"
                                        >
                                            <template #icon><UserCheck class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>

                                    <Tooltip :text="item.is_active === 1 || item.is_active?.value === 1 ? 'Desativar' : 'Ativar'">
                                        <Button
                                            variant="icon"
                                            @click="confirmToggleUser(item)"
                                            :class="item.is_active === 1 || item.is_active?.value === 1 ? 'text-amber-500' : 'text-emerald-500'"
                                        >
                                            <template #icon><Power class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>

                                    <Tooltip text="Editar">
                                        <Button
                                            variant="icon"
                                            @click="openEditUser(item)"
                                            class="text-indigo-400"
                                        >
                                            <template #icon><Pencil class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>

                                    <Tooltip text="Excluir">
                                        <Button
                                            variant="icon"
                                            @click="confirmDeleteUser(item)"
                                            class="text-rose-500"
                                        >
                                            <template #icon><Trash2 class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>
                                </div>
                            </template>
                        </DataTable>
                    </div>

                    <!-- Tab: Subjects -->
                    <div v-if="activeTab === 'subjects'" class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white">
                                Gerenciamento de Matérias
                            </h3>
                            <Button @click="openCreateSubject">
                                <template #icon><Plus class="h-4 w-4" /></template>
                                Cadastrar Matéria
                            </Button>
                        </div>

                        <DataTable
                            :items="subjects"
                            :columns="subjectColumns"
                            searchPlaceholder="Buscar matéria..."
                        >
                            <template #id="{ item }">
                                <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                            </template>
                            <template #name="{ item }">
                                <div class="font-semibold text-zinc-150">{{ item.name }}</div>
                                <div class="max-w-xs truncate text-xs text-zinc-500">{{ item.description || 'Sem descrição' }}</div>
                            </template>
                            <template #institution="{ item }">
                                <div v-if="item.institution" class="text-zinc-300 font-medium flex items-center gap-1">
                                    <School class="h-3.5 w-3.5 text-indigo-400" />
                                    {{ item.institution.name }}
                                </div>
                                <span v-else class="text-zinc-500">N/A</span>
                            </template>
                            <template #is_active="{ item }">
                                <span v-if="item.is_active === 1 || item.is_active?.value === 1" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400">
                                    <CheckCircle class="h-3 w-3" /> Ativo
                                </span>
                                <span v-else class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-400">
                                    <XCircle class="h-3 w-3" /> Inativo
                                </span>
                            </template>
                            <template #actions="{ item }">
                                <div class="flex items-center justify-end gap-2">
                                    <Tooltip text="Visualizar">
                                        <Button
                                            variant="icon"
                                            @click="openEditSubject(item); isViewingSubject = true;"
                                            class="text-teal-400"
                                        >
                                            <template #icon><Eye class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>

                                    <Tooltip :text="item.is_active === 1 || item.is_active?.value === 1 ? 'Desativar' : 'Ativar'">
                                        <Button
                                            variant="icon"
                                            @click="confirmToggleSubject(item)"
                                            :class="item.is_active === 1 || item.is_active?.value === 1 ? 'text-amber-500' : 'text-emerald-500'"
                                        >
                                            <template #icon><Power class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>

                                    <Tooltip text="Editar">
                                        <Button
                                            variant="icon"
                                            @click="openEditSubject(item)"
                                            class="text-indigo-400"
                                        >
                                            <template #icon><Pencil class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>

                                    <Tooltip text="Excluir">
                                        <Button
                                            variant="icon"
                                            @click="confirmDeleteSubject(item)"
                                            class="text-rose-500"
                                        >
                                            <template #icon><Trash2 class="h-4 w-4" /></template>
                                        </Button>
                                    </Tooltip>
                                </div>
                            </template>
                        </DataTable>
                    </div>

                    <!-- Tab: Trash -->
                    <div v-if="activeTab === 'trash'" class="space-y-6">
                        <h3 class="text-lg font-bold text-rose-400">Lixeira</h3>
                        <p class="text-sm text-zinc-400">
                            Registros deletados podem ser restaurados aqui.
                        </p>

                        <DataTable
                            :items="deletedModels"
                            :columns="trashColumns"
                            searchPlaceholder="Buscar na lixeira..."
                        >
                            <template #id="{ item }">
                                <span class="text-zinc-500 font-mono">#{{ item.id }}</span>
                            </template>
                            <template #model="{ item }">
                                <span class="font-bold text-amber-500">{{ item.model }}</span>
                            </template>
                            <template #values="{ item }">
                                <span class="text-zinc-400 font-mono text-xs block max-w-lg truncate" :title="JSON.stringify(item.values)">
                                    {{ JSON.stringify(item.values) }}
                                </span>
                            </template>
                            <template #actions="{ item }">
                                <Button
                                    variant="secondary"
                                    @click="confirmRestore(item)"
                                    class="text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/10"
                                >
                                    <template #icon><RotateCcw class="h-4 w-4" /></template>
                                    Restaurar
                                </Button>
                            </template>
                        </DataTable>
                    </div>

                    <!-- Tab: Reports -->
                    <div v-if="activeTab === 'reports'" class="space-y-6">
                        <h3 class="text-lg font-bold text-indigo-400">Fila de Relatórios solicitados</h3>
                        <p class="text-sm text-zinc-400">
                            Os relatórios são processados em segundo plano. Quando concluídos, o download ficará disponível. Os arquivos baixados são removidos automaticamente do servidor.
                        </p>

                        <DataTable
                            :items="reports"
                            :columns="reportColumns"
                            searchPlaceholder="Buscar relatório..."
                        >
                            <template #name="{ item }">
                                <span class="font-semibold text-zinc-200">{{ item.name }}</span>
                            </template>
                            <template #status="{ item }">
                                <span v-if="item.status === 'pending'" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-amber-400 bg-amber-400/10 rounded-full">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    Processando
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-400 bg-emerald-400/10 rounded-full">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                    Concluído
                                </span>
                            </template>
                            <template #created_at="{ item }">
                                <span class="text-zinc-400 font-mono text-xs">
                                    {{ new Date(item.created_at).toLocaleString('pt-BR') }}
                                </span>
                            </template>
                            <template #actions="{ item }">
                                <a
                                    v-if="item.status === 'completed'"
                                    :href="route('reports.download', item.id)"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-800 bg-zinc-950/40 px-4 py-2 text-sm text-indigo-400 hover:bg-zinc-800 hover:text-zinc-200 transition-all font-semibold active:scale-98"
                                >
                                    Baixar Relatório (XLSX)
                                </a>
                                <span v-else class="text-zinc-500 text-xs font-bold animate-pulse">Aguarde...</span>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Base Modal: Institution Form -->
        <BaseModal
            :show="isInstitutionModalOpen"
            :title="
                isEditingInstitution ? 'Editar Instituição' : 'Nova Instituição'
            "
            maxWidth="5xl"
            @close="isInstitutionModalOpen = false"
        >
            <form @submit.prevent="submitInstitution" class="space-y-5 max-h-[100vh] overflow-y-auto pr-2 custom-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome Fantasia</label>
                        <input v-model="instForm.name" type="text" required class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <span v-if="instForm.errors.name" class="text-xs text-red-500 mt-1 block">{{ instForm.errors.name }}</span>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Razão Social</label>
                        <input v-model="instForm.razao_social" type="text" required class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <span v-if="instForm.errors.razao_social" class="text-xs text-red-500 mt-1 block">{{ instForm.errors.razao_social }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">CNPJ <span class="text-zinc-500 font-normal">(Opcional)</span></label>
                        <input :value="instForm.cnpj" @input="instForm.cnpj = maskCnpj($event.target.value)" type="text" placeholder="00.000.000/0001-00" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <span v-if="instForm.errors.cnpj" class="text-xs text-red-500 mt-1 block">{{ instForm.errors.cnpj }}</span>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Slug</label>
                        <input :value="instForm.slug" @input="wasSlugManuallyEdited = true; instForm.slug = slugify($event.target.value)" type="text" required placeholder="ex: minha-instituicao" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <span v-if="instForm.errors.slug" class="text-xs text-red-500 mt-1 block">{{ instForm.errors.slug }}</span>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Descrição</label>
                    <textarea v-model="instForm.description" rows="2" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    <span v-if="instForm.errors.description" class="text-xs text-red-500 mt-1 block">{{ instForm.errors.description }}</span>
                </div>

                <!-- Seção de Endereço -->
                <div class="rounded-xl border border-zinc-800 bg-zinc-900/10 p-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-300 mb-3 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Endereço
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-zinc-400">CEP</label>
                            <div class="flex gap-2">
                                <input :value="instForm.address.cep" @input="instForm.address.cep = maskCep($event.target.value)" type="text" placeholder="00000-000" @keyup.enter.prevent="searchCep" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                <button type="button" @click="searchCep" :disabled="isSearchingCep" class="rounded-xl bg-zinc-800 px-3 text-xs font-semibold text-zinc-300 hover:bg-zinc-700 disabled:opacity-55 flex items-center gap-1">
                                    <span v-if="isSearchingCep" class="h-3 w-3 animate-spin rounded-full border border-zinc-400 border-t-transparent"></span>
                                    <span>Buscar</span>
                                </button>
                            </div>
                            <span v-if="cepErrorMessage" class="text-[10px] text-amber-500 mt-1 block font-semibold">{{ cepErrorMessage }}</span>
                            <span v-if="instForm.errors['address.cep']" class="text-xs text-red-500 mt-1 block">{{ instForm.errors['address.cep'] }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-zinc-400">Logradouro</label>
                            <input v-model="instForm.address.logradouro" type="text" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                            <span v-if="instForm.errors['address.logradouro']" class="text-xs text-red-500 mt-1 block">{{ instForm.errors['address.logradouro'] }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-zinc-400">Número</label>
                            <input v-model="instForm.address.numero" type="text" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                            <span v-if="instForm.errors['address.numero']" class="text-xs text-red-500 mt-1 block">{{ instForm.errors['address.numero'] }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-zinc-400">Complemento <span class="text-zinc-500 font-normal">(Opcional)</span></label>
                            <input v-model="instForm.address.complemento" type="text" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-zinc-400">Bairro</label>
                            <input v-model="instForm.address.bairro" type="text" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                            <span v-if="instForm.errors['address.bairro']" class="text-xs text-red-500 mt-1 block">{{ instForm.errors['address.bairro'] }}</span>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-zinc-400">Cidade</label>
                            <input v-model="instForm.address.cidade" type="text" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                            <span v-if="instForm.errors['address.cidade']" class="text-xs text-red-500 mt-1 block">{{ instForm.errors['address.cidade'] }}</span>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-zinc-400">UF</label>
                            <input v-model="instForm.address.uf" type="text" placeholder="EX: SP" maxlength="2" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                            <span v-if="instForm.errors['address.uf']" class="text-xs text-red-500 mt-1 block">{{ instForm.errors['address.uf'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Seção de Telefones -->
                <div class="rounded-xl border border-zinc-800 bg-zinc-900/10 p-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-300 mb-3 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Telefones
                        </span>
                        <Tooltip text="+ Adicionar">
                            <button type="button" @click="addPhoneField" class="p-1 rounded-lg hover:bg-zinc-800 text-indigo-400 hover:text-indigo-300 transition-all">
                                <Plus class="h-5 w-5" />
                            </button>
                        </Tooltip>
                    </h4>

                    <div class="space-y-2">
                        <div v-for="(phone, index) in instForm.phones" :key="index" class="flex gap-2 items-center">
                            <input :value="instForm.phones[index]" @input="instForm.phones[index] = maskPhone($event.target.value)" type="text" placeholder="(00) 00000-0000" class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            <Tooltip text="Remover Telefone">
                                <button type="button" @click="removePhoneField(index)" class="p-2.5 rounded-xl border border-red-950 bg-red-950/20 text-red-400 hover:bg-red-900/20 hover:text-red-300 transition-all">
                                    <Trash class="h-4 w-4" />
                                </button>
                            </Tooltip>
                        </div>
                        <span v-if="instForm.errors.phones" class="text-xs text-red-500 mt-1 block">{{ instForm.errors.phones }}</span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="isInstitutionModalOpen = false"
                        class="rounded-xl border border-zinc-700 bg-transparent px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="instForm.processing"
                        class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-indigo-500 disabled:opacity-55"
                    >
                        Salvar
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Base Modal: User Form -->
        <BaseModal
            :show="isUserModalOpen"
            :title="isEditingUser ? 'Editar Usuário' : 'Novo Usuário'"
            maxWidth="2xl"
            @close="isUserModalOpen = false"
        >
            <form @submit.prevent="submitUser" class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >Nome</label
                    >
                    <input
                        v-model="userForm.name"
                        type="text"
                        required
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >E-mail</label
                    >
                    <input
                        v-model="userForm.email"
                        type="email"
                        required
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >Senha
                        {{
                            isEditingUser
                                ? '(Deixe em branco para não alterar)'
                                : ''
                        }}</label
                    >
                    <input
                        v-model="userForm.password"
                        type="password"
                        :required="!isEditingUser"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >Função</label
                    >
                    <select
                        v-model="userForm.role"
                        required
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <option value="student">Estudante</option>
                        <option value="teacher">Professor</option>
                        <option value="admin">
                            Administrador de Instituição
                        </option>
                    </select>
                </div>
                <!-- Institution selector if role requires it -->
                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >Instituição</label
                    >
                    <select
                        v-model="userForm.institution_id"
                        :required="
                            userForm.role === 'admin' ||
                            userForm.role === 'teacher'
                        "
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <option value="">Nenhuma / Sem Instituição</option>
                        <option
                            v-for="inst in institutions"
                            :key="inst.id"
                            :value="inst.id"
                        >
                            {{ inst.name }}
                        </option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="isUserModalOpen = false"
                        class="rounded-xl border border-zinc-700 bg-transparent px-4 py-2.5 text-sm font-semibold text-zinc-300 transition-all hover:bg-zinc-800"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="userForm.processing"
                        class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-indigo-500 disabled:opacity-55"
                    >
                        Salvar
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Base Modal: Subject Form / View -->
        <BaseModal
            :show="isSubjectModalOpen"
            :title="isViewingSubject ? 'Visualizar Matéria' : isEditingSubject ? 'Editar Matéria' : 'Nova Matéria'"
            maxWidth="2xl"
            @close="isSubjectModalOpen = false"
        >
            <form @submit.prevent="submitSubject" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome</label>
                        <input
                            v-model="subjectForm.name"
                            type="text"
                            required
                            :disabled="isViewingSubject"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60"
                        />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Slug</label>
                        <input
                            :value="subjectForm.slug"
                            @input="subjectForm.slug = slugify($event.target.value)"
                            type="text"
                            required
                            :disabled="isViewingSubject"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60"
                        />
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Descrição</label>
                    <textarea
                        v-model="subjectForm.description"
                        rows="3"
                        :disabled="isViewingSubject"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60"
                    ></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Instituição</label>
                        <select
                            v-model="subjectForm.institution_id"
                            required
                            :disabled="isViewingSubject"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60"
                        >
                            <option value="" disabled>Selecione uma instituição</option>
                            <option
                                v-for="inst in institutions"
                                :key="inst.id"
                                :value="inst.id"
                            >
                                {{ inst.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Duração</label>
                        <input
                            v-model="subjectForm.duration"
                            type="text"
                            required
                            placeholder="Ex: 80 horas"
                            :disabled="isViewingSubject"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-60"
                        />
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <div v-if="isViewingSubject">
                        <Button variant="secondary" @click="isSubjectModalOpen = false">
                            Fechar
                        </Button>
                    </div>
                    <div v-else class="flex gap-3">
                        <Button variant="secondary" @click="isSubjectModalOpen = false">
                            Cancelar
                        </Button>
                        <Button
                            type="submit"
                            :disabled="subjectForm.processing"
                        >
                            Salvar
                        </Button>
                    </div>
                </div>
            </form>
        </BaseModal>

        <!-- Dynamic Confirm Modal -->
        <ConfirmModal
            :show="confirmState.show"
            :title="confirmState.title"
            :message="confirmState.message"
            :type="confirmState.type"
            @close="confirmState.show = false"
            @confirm="confirmState.onConfirm"
        />
    </AuthenticatedLayout>
</template>
