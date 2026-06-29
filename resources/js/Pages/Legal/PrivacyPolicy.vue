<script setup>
import PublicLegalLayout from '@/Layouts/PublicLegalLayout.vue';

defineProps({
    content: { type: Object, required: true },
    backToSite: { type: String, default: '' },
    updatedPrefix: { type: String, default: '' },
});
</script>

<template>
    <PublicLegalLayout
        :content="content"
        :back-to-site="backToSite"
        :updated-prefix="updatedPrefix"
    />
</template>
