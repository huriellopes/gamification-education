<script setup>
import { __ } from '@/i18n';
import {
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    ChevronUp,
    Search,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true,
        default: () => [],
    },
    columns: {
        type: Array,
        required: true,
        default: () => [],
    },
    searchPlaceholder: {
        type: String,
        default: () => __('misc.datatable.search_placeholder'),
    },
    pageSize: {
        type: Number,
        default: 10,
    },
    filterKey: {
        type: String,
        default: '',
    },
    filterOptions: {
        type: Array,
        default: () => [],
    },
    filterPlaceholder: {
        type: String,
        default: () => __('common.all'),
    },
    // Extra item keys to include in the search beyond the visible columns
    // (e.g. an email shown inside a merged "name" column).
    searchKeys: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref('');
const selectedFilter = ref('');
const currentPage = ref(1);
const sortKey = ref('');
const sortOrder = ref('asc'); // 'asc' | 'desc'
const localPageSize = ref(props.pageSize);

watch(
    () => props.pageSize,
    (newSize) => {
        localPageSize.value = newSize;
    },
);

// Reset page on search, filter, or page size change
watch([searchQuery, selectedFilter, localPageSize], () => {
    currentPage.value = 1;
});

// Reset page if items count drops below current page range
watch(
    () => props.items,
    () => {
        const totalFiltered = filteredItems.value.length;
        const limit =
            localPageSize.value === -1 ? totalFiltered : localPageSize.value;
        const maxPage = Math.ceil(totalFiltered / limit) || 1;
        if (currentPage.value > maxPage) {
            currentPage.value = maxPage;
        }
    },
    { deep: true },
);

// Handle sorting
const handleSort = (key, sortable) => {
    if (!sortable) return;
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

// Filtered and sorted items
const filteredItems = computed(() => {
    let result = [...props.items];

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        const keys = [
            ...props.columns.map((col) => col.key),
            ...props.searchKeys,
        ];
        result = result.filter((item) => {
            return keys.some((key) => {
                const val = item[key];
                if (val === null || val === undefined) return false;

                // If value is an object or enum, resolve labels if available
                if (typeof val === 'object' && val.label) {
                    return val.label.toLowerCase().includes(query);
                }
                return String(val).toLowerCase().includes(query);
            });
        });
    }

    // Filter by dropdown filter
    if (props.filterKey && selectedFilter.value !== '') {
        result = result.filter((item) => {
            const val = item[props.filterKey];
            const filterVal = selectedFilter.value;

            if (val === null || val === undefined) return false;
            // Check if enum or nested value
            if (typeof val === 'object') {
                if (val.value !== undefined) {
                    return String(val.value) === String(filterVal);
                }
                return false;
            }
            return String(val) === String(filterVal);
        });
    }

    // Sort items
    if (sortKey.value) {
        result.sort((a, b) => {
            let valA = a[sortKey.value];
            let valB = b[sortKey.value];

            // Resolve enum/nested objects for sorting
            if (valA && typeof valA === 'object' && valA.label)
                valA = valA.label;
            if (valB && typeof valB === 'object' && valB.label)
                valB = valB.label;

            if (valA === null || valA === undefined) return 1;
            if (valB === null || valB === undefined) return -1;

            if (typeof valA === 'string' && typeof valB === 'string') {
                return sortOrder.value === 'asc'
                    ? valA.localeCompare(valB)
                    : valB.localeCompare(valA);
            }

            return sortOrder.value === 'asc'
                ? valA > valB
                    ? 1
                    : -1
                : valA < valB
                  ? 1
                  : -1;
        });
    }

    return result;
});

// Paginated items
const paginatedItems = computed(() => {
    if (localPageSize.value === -1) {
        return filteredItems.value;
    }
    const start = (currentPage.value - 1) * localPageSize.value;
    const end = start + localPageSize.value;
    return filteredItems.value.slice(start, end);
});

// Total pages
const totalPages = computed(() => {
    if (localPageSize.value === -1) return 1;
    return Math.ceil(filteredItems.value.length / localPageSize.value) || 1;
});
</script>

<template>
    <div class="space-y-4">
        <!-- Controls: Search and Filters -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="relative max-w-md flex-1">
                <div
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                >
                    <Search class="h-4 w-4 text-zinc-400" />
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="text-zinc-150 w-full rounded-xl border border-zinc-800 bg-zinc-950/50 py-2.5 pl-10 pr-4 text-sm placeholder-zinc-500 backdrop-blur-md focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
            </div>

            <div class="flex items-center gap-3">
                <div
                    v-if="filterKey && filterOptions.length > 0"
                    class="flex items-center gap-2"
                >
                    <select
                        v-model="selectedFilter"
                        class="rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-2.5 text-sm text-zinc-200 backdrop-blur-md focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <option value="">{{ filterPlaceholder }}</option>
                        <option
                            v-for="opt in filterOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <div v-if="items.length > 0" class="flex items-center gap-2">
                    <span class="text-xs font-medium text-zinc-400">{{
                        __('misc.datatable.show')
                    }}</span>
                    <select
                        v-model.number="localPageSize"
                        class="rounded-xl border border-zinc-800 bg-zinc-950/50 px-3 py-2 text-sm text-zinc-200 backdrop-blur-md focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="30">30</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                        <option :value="-1">{{ __('common.all') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div
            class="overflow-x-auto rounded-xl border border-zinc-800 bg-zinc-900/10 backdrop-blur-xl"
        >
            <table
                class="w-full border-collapse text-left text-sm text-zinc-300"
            >
                <thead
                    class="border-b border-zinc-800 bg-zinc-800/40 text-xs uppercase text-zinc-400"
                >
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            @click="handleSort(col.key, col.sortable)"
                            :class="[
                                'select-none px-4 py-3 font-semibold transition-colors',
                                col.sortable
                                    ? 'cursor-pointer hover:bg-zinc-800/70 hover:text-zinc-100'
                                    : '',
                                col.align === 'right'
                                    ? 'text-right'
                                    : col.align === 'center'
                                      ? 'text-center'
                                      : 'text-left',
                            ]"
                        >
                            <div
                                class="inline-flex items-center gap-1.5"
                                :class="[
                                    col.align === 'right'
                                        ? 'w-full justify-end'
                                        : col.align === 'center'
                                          ? 'w-full justify-center'
                                          : '',
                                ]"
                            >
                                <span>{{ col.label }}</span>
                                <span
                                    v-if="col.sortable && sortKey === col.key"
                                    class="text-indigo-400"
                                >
                                    <ChevronUp
                                        v-if="sortOrder === 'asc'"
                                        class="h-3 w-3"
                                    />
                                    <ChevronDown v-else class="h-3 w-3" />
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    <tr
                        v-for="(item, index) in paginatedItems"
                        :key="item.id || index"
                        class="group transition-all duration-150 hover:bg-zinc-800/20"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-4 py-3 transition-colors',
                                col.align === 'right'
                                    ? 'text-right'
                                    : col.align === 'center'
                                      ? 'text-center'
                                      : 'text-left',
                            ]"
                        >
                            <!-- Dynamic Cell Slot -->
                            <slot :name="col.key" :item="item">
                                <span class="font-medium text-zinc-200">
                                    {{
                                        item[col.key] &&
                                        typeof item[col.key] === 'object' &&
                                        item[col.key].label !== undefined
                                            ? item[col.key].label
                                            : item[col.key]
                                    }}
                                </span>
                            </slot>
                        </td>
                    </tr>

                    <tr v-if="filteredItems.length === 0">
                        <td
                            :colspan="columns.length"
                            class="py-8 text-center font-medium text-zinc-400"
                        >
                            {{ __('common.no_results') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div
            v-if="filteredItems.length > 0"
            class="flex flex-col items-center justify-between gap-3 border-t border-zinc-800/50 pt-4 sm:flex-row"
        >
            <span class="text-xs font-medium text-zinc-400">
                <template v-if="localPageSize === -1">
                    {{
                        __('misc.datatable.showing_all', {
                            total: filteredItems.length,
                        })
                    }}
                </template>
                <template v-else>
                    {{
                        __('misc.datatable.showing_range', {
                            from: (currentPage - 1) * localPageSize + 1,
                            to: Math.min(
                                currentPage * localPageSize,
                                filteredItems.length,
                            ),
                            total: filteredItems.length,
                        })
                    }}
                </template>
            </span>

            <div
                v-if="totalPages > 1 && localPageSize !== -1"
                class="flex items-center gap-1.5"
            >
                <button
                    type="button"
                    :disabled="currentPage === 1"
                    @click="currentPage--"
                    class="inline-flex rounded-lg border border-zinc-800 bg-zinc-950/40 p-2 text-zinc-400 hover:text-zinc-200 focus:outline-none disabled:opacity-40"
                >
                    <ChevronLeft class="h-4 w-4" />
                </button>

                <button
                    v-for="page in totalPages"
                    :key="page"
                    type="button"
                    @click="currentPage = page"
                    :class="[
                        'inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold transition-all focus:outline-none',
                        currentPage === page
                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20'
                            : 'hover:text-zinc-250 border border-zinc-800 bg-zinc-950/40 text-zinc-400 hover:border-zinc-700',
                    ]"
                >
                    {{ page }}
                </button>

                <button
                    type="button"
                    :disabled="currentPage === totalPages"
                    @click="currentPage++"
                    class="inline-flex rounded-lg border border-zinc-800 bg-zinc-950/40 p-2 text-zinc-400 hover:text-zinc-200 focus:outline-none disabled:opacity-40"
                >
                    <ChevronRight class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>
