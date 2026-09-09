<template>
    <div>
        <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">
            {{ field.display }}
        </label>
        <p v-if="field.instructions" class="mb-1 text-xs text-gray-500 dark:text-gray-400">{{ field.instructions }}</p>
        <component
            :is="`${field.component ?? field.type}-fieldtype`"
            :value="value"
            :config="field"
            :meta="meta"
            :handle="field.handle"
            @update:value="$emit('update:value', $event)"
        />
    </div>
</template>

<script>
// One field row — extracted from PropsPanel.vue because it's now rendered from
// three places (content fields, each collapsible style section, ungrouped).
export default {
    props: {
        field: { type: Object, required: true },
        value: { default: null },
        meta: { default: null },
    },

    emits: ['update:value'],
};
</script>
