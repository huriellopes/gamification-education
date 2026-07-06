<script setup>
import BaseModal from '@/Components/BaseModal.vue';
import Button from '@/Components/Button.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DataTable from '@/Components/DataTable.vue';
import PageHeader from '@/Components/PageHeader.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Tooltip from '@/Components/Tooltip.vue';
import { __ } from '@/i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { maskCep, maskCnpj, maskPhone, slugify } from '@/Utils/mask';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle, Pencil, Plus, Power, Trash2, XCircle } from '@lucide/vue';
import { ref, watch } from 'vue';

defineProps({
    institutions: {
        type: Array,
        default: () => [],
    },
});

const institutionColumns = [
    { key: 'id', label: 'ID', sortable: true },
    {
        key: 'name',
        label: __('superadmin.institutions.col_name_description'),
        sortable: true,
    },
    {
        key: 'razao_social',
        label: __('superadmin.institutions.col_razao_social'),
        sortable: true,
    },
    {
        key: 'cnpj',
        label: __('superadmin.institutions.col_cnpj'),
        sortable: true,
    },
    {
        key: 'is_active',
        label: __('common.status'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'subjects_count',
        label: __('superadmin.institutions.col_subjects'),
        sortable: true,
        align: 'center',
    },
    {
        key: 'users_count',
        label: __('superadmin.institutions.col_members'),
        sortable: true,
        align: 'center',
    },
    { key: 'actions', label: __('common.actions'), align: 'right' },
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

watch(
    () => instForm.name,
    (newName) => {
        if (!isEditingInstitution.value && !wasSlugManuallyEdited.value) {
            instForm.slug = slugify(newName);
        }
    },
);

const isSearchingCep = ref(false);
const cepErrorMessage = ref('');

const searchCep = async () => {
    const rawCep = instForm.address.cep.replace(/\D/g, '');
    if (rawCep.length !== 8) {
        cepErrorMessage.value = __('superadmin.institutions.cep_invalid');
        return;
    }

    isSearchingCep.value = true;
    cepErrorMessage.value = '';

    const fetchWithTimeout = (url, timeout = 3000) => {
        return Promise.race([
            fetch(url).then((res) => {
                if (!res.ok) throw new Error('API error');
                return res.json();
            }),
            new Promise((_, reject) =>
                setTimeout(() => reject(new Error('Timeout')), timeout),
            ),
        ]);
    };

    try {
        const data = await fetchWithTimeout(
            `https://viacep.com.br/ws/${rawCep}/json/`,
        );
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
            const data = await fetchWithTimeout(
                `https://brasilapi.com.br/api/cep/v1/${rawCep}`,
            );
            instForm.address.logradouro = data.street || '';
            instForm.address.bairro = data.neighborhood || '';
            instForm.address.cidade = data.city || '';
            instForm.address.uf = data.state || '';
        } catch (err) {
            console.error('Ambas APIs de CEP falharam.', err);
            cepErrorMessage.value = __(
                'superadmin.institutions.cep_lookup_failed',
            );
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

    instForm.phones =
        inst.phones && Array.isArray(inst.phones) && inst.phones.length > 0
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
    const activeVal =
        inst.is_active === 1 ||
        inst.is_active?.value === 1 ||
        inst.is_active === 'active';
    const actionText = activeVal
        ? __('superadmin.institutions.action_deactivate')
        : __('superadmin.institutions.action_activate');
    triggerConfirm(
        __('superadmin.institutions.confirm_status_title'),
        __('superadmin.institutions.confirm_status_message', {
            action: actionText,
            name: inst.name,
        }),
        'warning',
        () => {
            router.post(route('super-admin.institutions.toggle', inst.id));
        },
    );
};

const confirmDeleteInstitution = (inst) => {
    triggerConfirm(
        __('superadmin.institutions.confirm_delete_title'),
        __('superadmin.institutions.confirm_delete_message', {
            name: inst.name,
        }),
        'danger',
        () => {
            router.delete(route('super-admin.institutions.destroy', inst.id));
        },
    );
};
</script>

<template>
    <Head :title="__('superadmin.institutions.head_title')" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="__('superadmin.institutions.header')">
                <template #actions>
                    <Button @click="openCreateInstitution">
                        <template #icon><Plus class="h-4 w-4" /></template>
                        <span class="hidden md:inline">{{
                            __('superadmin.institutions.register')
                        }}</span>
                    </Button>
                </template>
            </PageHeader>
        </template>

        <div class="bg-zinc-950 py-6 text-zinc-100">
            <div
                class="rounded-2xl border border-zinc-800 bg-zinc-900/30 p-6 backdrop-blur-md"
            >
                <DataTable
                    :items="institutions"
                    :columns="institutionColumns"
                    :searchPlaceholder="
                        __('superadmin.institutions.search_placeholder')
                    "
                >
                    <template #id="{ item }">
                        <span class="font-mono text-zinc-400"
                            >#{{ item.id }}</span
                        >
                    </template>
                    <template #name="{ item }">
                        <div class="text-zinc-150 font-semibold">
                            {{ item.name }}
                        </div>
                        <div class="max-w-xs truncate text-xs text-zinc-400">
                            {{
                                item.description ||
                                __('superadmin.institutions.no_description')
                            }}
                        </div>
                    </template>
                    <template #is_active="{ item }">
                        <span
                            v-if="
                                item.is_active === 1 ||
                                item.is_active?.value === 1
                            "
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400"
                        >
                            <CheckCircle class="h-3 w-3" />
                            {{ __('superadmin.institutions.active') }}
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-400"
                        >
                            <XCircle class="h-3 w-3" />
                            {{ __('superadmin.institutions.inactive') }}
                        </span>
                    </template>
                    <template #subjects_count="{ item }">
                        <span class="font-bold text-indigo-400">{{
                            item.subjects_count
                        }}</span>
                    </template>
                    <template #users_count="{ item }">
                        <span class="font-bold text-emerald-400">{{
                            item.users_count
                        }}</span>
                    </template>
                    <template #actions="{ item }">
                        <div class="flex items-center justify-end gap-2">
                            <Tooltip
                                :text="
                                    item.is_active === 1 ||
                                    item.is_active?.value === 1
                                        ? __(
                                              'superadmin.institutions.tooltip_deactivate',
                                          )
                                        : __(
                                              'superadmin.institutions.tooltip_activate',
                                          )
                                "
                            >
                                <button
                                    @click="confirmToggleInstitution(item)"
                                    class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-1.5 transition-colors"
                                    :class="
                                        item.is_active === 1 ||
                                        item.is_active?.value === 1
                                            ? 'text-red-500 hover:bg-red-500/10 hover:text-red-400'
                                            : 'text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-400'
                                    "
                                    type="button"
                                >
                                    <Power class="h-4 w-4" />
                                </button>
                            </Tooltip>

                            <Tooltip
                                :text="
                                    __('superadmin.institutions.tooltip_edit')
                                "
                            >
                                <Button
                                    variant="icon"
                                    @click="openEditInstitution(item)"
                                    class="text-indigo-400"
                                >
                                    <template #icon
                                        ><Pencil class="h-4 w-4"
                                    /></template>
                                </Button>
                            </Tooltip>

                            <Tooltip
                                :text="
                                    __('superadmin.institutions.tooltip_delete')
                                "
                            >
                                <button
                                    @click="confirmDeleteInstitution(item)"
                                    class="rounded-lg border border-zinc-800 bg-zinc-900/50 p-1.5 text-red-500 transition-colors hover:bg-red-500/10 hover:text-red-400"
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
            :title="
                isEditingInstitution
                    ? __('superadmin.institutions.modal_edit_title')
                    : __('superadmin.institutions.modal_new_title')
            "
            maxWidth="3xl"
            @close="isInstitutionModalOpen = false"
        >
            <form @submit.prevent="submitInstitution" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{
                                __('superadmin.institutions.label_name')
                            }}</label
                        >
                        <TextInput
                            v-model="instForm.name"
                            type="text"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{
                                __('superadmin.institutions.label_slug')
                            }}</label
                        >
                        <TextInput
                            v-model="instForm.slug"
                            type="text"
                            required
                            @input="wasSlugManuallyEdited = true"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{
                                __('superadmin.institutions.label_razao_social')
                            }}</label
                        >
                        <TextInput
                            v-model="instForm.razao_social"
                            type="text"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                            >{{
                                __('superadmin.institutions.label_cnpj')
                            }}</label
                        >
                        <TextInput
                            :model-value="instForm.cnpj"
                            @input="
                                instForm.cnpj = maskCnpj($event.target.value)
                            "
                            type="text"
                            placeholder="00.000.000/0000-00"
                            maxlength="18"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                        >{{
                            __('superadmin.institutions.label_description')
                        }}</label
                    >
                    <TextareaInput v-model="instForm.description" rows="2" />
                </div>

                <!-- Address Section (ViaCEP auto lookup) -->
                <div class="space-y-4 border-t border-zinc-850 pt-4">
                    <h4 class="text-sm font-bold text-white">
                        {{ __('superadmin.institutions.address_section') }}
                    </h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                                >{{
                                    __('superadmin.institutions.label_cep')
                                }}</label
                            >
                            <div class="flex gap-2">
                                <TextInput
                                    :model-value="instForm.address.cep"
                                    @input="
                                        instForm.address.cep = maskCep(
                                            $event.target.value,
                                        )
                                    "
                                    type="text"
                                    placeholder="00000-000"
                                    maxlength="9"
                                />
                                <Button
                                    type="button"
                                    variant="secondary"
                                    :disabled="isSearchingCep"
                                    @click="searchCep"
                                >
                                    {{
                                        isSearchingCep
                                            ? '...'
                                            : __(
                                                  'superadmin.institutions.search',
                                              )
                                    }}
                                </Button>
                            </div>
                            <span
                                v-if="cepErrorMessage"
                                class="mt-1 block text-xs font-medium text-rose-400"
                                >{{ cepErrorMessage }}</span
                            >
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                                >{{
                                    __('superadmin.institutions.label_street')
                                }}</label
                            >
                            <TextInput
                                v-model="instForm.address.logradouro"
                                type="text"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                                >{{
                                    __('superadmin.institutions.label_number')
                                }}</label
                            >
                            <TextInput
                                v-model="instForm.address.numero"
                                type="text"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                                >{{
                                    __(
                                        'superadmin.institutions.label_complement',
                                    )
                                }}</label
                            >
                            <TextInput
                                v-model="instForm.address.complemento"
                                type="text"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                                >{{
                                    __(
                                        'superadmin.institutions.label_neighborhood',
                                    )
                                }}</label
                            >
                            <TextInput
                                v-model="instForm.address.bairro"
                                type="text"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase text-zinc-400"
                                >{{
                                    __('superadmin.institutions.label_city_uf')
                                }}</label
                            >
                            <div class="flex gap-2">
                                <TextInput
                                    v-model="instForm.address.cidade"
                                    type="text"
                                    :placeholder="
                                        __(
                                            'superadmin.institutions.placeholder_city',
                                        )
                                    "
                                />
                                <TextInput
                                    v-model="instForm.address.uf"
                                    type="text"
                                    :placeholder="
                                        __(
                                            'superadmin.institutions.placeholder_uf',
                                        )
                                    "
                                    maxlength="2"
                                    class="w-12 text-center"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phones List -->
                <div class="space-y-4 border-t border-zinc-850 pt-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-white">
                            {{ __('superadmin.institutions.phones_section') }}
                        </h4>
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="addPhoneField"
                        >
                            <span>{{
                                __('superadmin.institutions.add_phone')
                            }}</span>
                        </Button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div
                            v-for="(phone, index) in instForm.phones"
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <TextInput
                                :model-value="phone"
                                @input="
                                    instForm.phones[index] = maskPhone(
                                        $event.target.value,
                                    )
                                "
                                type="text"
                                placeholder="(00) 00000-0000"
                                maxlength="15"
                            />
                            <Button
                                type="button"
                                variant="secondary"
                                @click="removePhoneField(index)"
                                class="border-red-500/20 text-red-500 hover:bg-red-500/10"
                            >
                                <template #icon
                                    ><Trash2 class="h-4 w-4"
                                /></template>
                            </Button>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-zinc-850 pt-4"
                >
                    <Button
                        variant="secondary"
                        type="button"
                        @click="isInstitutionModalOpen = false"
                    >
                        {{ __('common.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="instForm.processing">
                        {{
                            instForm.processing
                                ? __('superadmin.institutions.saving')
                                : __('superadmin.institutions.save_institution')
                        }}
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
