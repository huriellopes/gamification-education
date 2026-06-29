<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Button from '@/Components/Button.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    Pencil,
    Plus,
    Power,
    Trash2,
    CheckCircle,
    XCircle
} from '@lucide/vue';
import { maskPhone, maskCnpj, maskCep, slugify } from '@/Utils/mask';

const props = defineProps({
    institutions: {
        type: Array,
        default: () => [],
    },
});

const institutionColumns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Nome / Descrição', sortable: true },
    { key: 'razao_social', label: 'Razão Social', sortable: true },
    { key: 'cnpj', label: 'CNPJ', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center' },
    { key: 'subjects_count', label: 'Matérias', sortable: true, align: 'center' },
    { key: 'users_count', label: 'Membros', sortable: true, align: 'center' },
    { key: 'actions', label: 'Ações', align: 'right' },
];

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
    const activeVal = inst.is_active === 1 || inst.is_active?.value === 1 || inst.is_active === 'active';
    const actionText = activeVal ? 'desativar' : 'ativar';
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
</script>

<template>
    <Head title="Gerenciar Instituições" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-zinc-100">
                    Gerenciamento de Instituições
                </h2>
                <Button @click="openCreateInstitution">
                    <template #icon><Plus class="h-4 w-4" /></template>
                    <span class="hidden md:inline">Cadastrar</span>
                </Button>
            </div>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md">
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
                            <Tooltip :text="(item.is_active === 1 || item.is_active?.value === 1) ? 'Desativar' : 'Ativar'">
                                <button
                                    @click="confirmToggleInstitution(item)"
                                    class="rounded-lg p-1.5 transition-colors border border-zinc-800 bg-zinc-900/50"
                                    :class="(item.is_active === 1 || item.is_active?.value === 1) ? 'text-red-500 hover:text-red-400 hover:bg-red-500/10' : 'text-emerald-500 hover:text-emerald-400 hover:bg-emerald-500/10'"
                                    type="button"
                                >
                                    <Power class="h-4 w-4" />
                                </button>
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
                                <button
                                    @click="confirmDeleteInstitution(item)"
                                    class="rounded-lg p-1.5 text-red-500 hover:text-red-400 hover:bg-red-500/10 border border-zinc-800 bg-zinc-900/50 transition-colors"
                                    type="button"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </Tooltip>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Base Modal: Cadastro / Edição de Instituição -->
        <BaseModal
            :show="isInstitutionModalOpen"
            :title="isEditingInstitution ? 'Editar Instituição' : 'Nova Instituição'"
            maxWidth="3xl"
            @close="isInstitutionModalOpen = false"
        >
            <form @submit.prevent="submitInstitution" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Nome</label>
                        <input
                            v-model="instForm.name"
                            type="text"
                            required
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Slug</label>
                        <input
                            v-model="instForm.slug"
                            type="text"
                            required
                            @input="wasSlugManuallyEdited = true"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Razão Social</label>
                        <input
                            v-model="instForm.razao_social"
                            type="text"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">CNPJ</label>
                        <input
                            :value="instForm.cnpj"
                            @input="instForm.cnpj = maskCnpj($event.target.value)"
                            type="text"
                            placeholder="00.000.000/0000-00"
                            maxlength="18"
                            class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Descrição</label>
                    <textarea
                        v-model="instForm.description"
                        rows="2"
                        class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-zinc-200 focus:border-indigo-500 focus:outline-none"
                    ></textarea>
                </div>

                <!-- Address Section (ViaCEP auto lookup) -->
                <div class="border-t border-zinc-850 pt-4 space-y-4">
                    <h4 class="text-sm font-bold text-white">Endereço da Unidade</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">CEP</label>
                            <div class="flex gap-2">
                                <input
                                    :value="instForm.address.cep"
                                    @input="instForm.address.cep = maskCep($event.target.value)"
                                    type="text"
                                    placeholder="00000-000"
                                    maxlength="9"
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                                />
                                <Button
                                    type="button"
                                    variant="secondary"
                                    :disabled="isSearchingCep"
                                    @click="searchCep"
                                >
                                    {{ isSearchingCep ? '...' : 'Buscar' }}
                                </Button>
                            </div>
                            <span v-if="cepErrorMessage" class="text-xs text-rose-400 font-medium block mt-1">{{ cepErrorMessage }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Logradouro / Rua</label>
                            <input
                                v-model="instForm.address.logradouro"
                                type="text"
                                class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Número</label>
                            <input
                                v-model="instForm.address.numero"
                                type="text"
                                class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Complemento</label>
                            <input
                                v-model="instForm.address.complemento"
                                type="text"
                                class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Bairro</label>
                            <input
                                v-model="instForm.address.bairro"
                                type="text"
                                class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase text-zinc-400">Cidade / UF</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="instForm.address.cidade"
                                    type="text"
                                    placeholder="Cidade"
                                    class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-indigo-500"
                                />
                                <input
                                    v-model="instForm.address.uf"
                                    type="text"
                                    placeholder="UF"
                                    maxlength="2"
                                    class="w-12 text-center rounded-xl border border-zinc-800 bg-zinc-950 px-2 py-2.5 text-sm text-white focus:border-indigo-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phones List -->
                <div class="border-t border-zinc-850 pt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-white">Telefones para Contato</h4>
                        <Button type="button" variant="secondary" size="sm" @click="addPhoneField">
                            <span>+ Adicionar Telefone</span>
                        </Button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="(phone, index) in instForm.phones" :key="index" class="flex gap-2 items-center">
                            <input
                                :value="phone"
                                @input="instForm.phones[index] = maskPhone($event.target.value)"
                                type="text"
                                placeholder="(00) 00000-0000"
                                maxlength="15"
                                class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                            <Button
                                type="button"
                                variant="secondary"
                                @click="removePhoneField(index)"
                                class="text-red-500 border-red-500/20 hover:bg-red-500/10"
                            >
                                <template #icon><Trash2 class="h-4 w-4" /></template>
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-850">
                    <Button variant="secondary" type="button" @click="isInstitutionModalOpen = false">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="instForm.processing">
                        {{ instForm.processing ? 'Salvando...' : 'Salvar Instituição' }}
                    </Button>
                </div>
            </form>
        </BaseModal>

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
