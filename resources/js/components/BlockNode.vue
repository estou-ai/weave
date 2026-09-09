<template>
    <div class="sortable-item">
        <div
            class="flex items-center gap-2 rounded-md px-2 py-1 cursor-pointer"
            :class="selectedId === node.id ? 'bg-sky-50 dark:bg-gray-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
            @click="$emit('select', node.id)"
        >
            <DragHandle class="sortable-handle" @click.stop />
            <Button
                v-if="node.children"
                :icon="collapsed ? 'chevron-right' : 'chevron-down'"
                icon-only
                size="2xs"
                variant="ghost"
                @click.stop="collapsed = ! collapsed"
            />
            <Icon :name="icon" class="size-4 shrink-0 text-gray-400" />
            <span class="flex-1 min-w-0 truncate text-sm">{{ label }}</span>
            <Button icon="trash" icon-only size="2xs" variant="ghost" @click.stop="$emit('remove', node.id)" />
        </div>

        <div v-if="node.children && !collapsed" class="ms-4 mt-1 space-y-1 rounded-md border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-800/50">
            <SortableList
                :model-value="node.children"
                vertical
                :animate="false"
                item-class="sortable-item"
                handle-class="sortable-handle"
                @update:model-value="reorderChildren"
            >
                <template #default="{ items }">
                    <div class="space-y-1">
                        <BlockNode
                            v-for="child in items"
                            :key="child.id"
                            :node="child"
                            :block-types="blockTypes"
                            :selected-id="selectedId"
                            @select="$emit('select', $event)"
                            @remove="$emit('remove', $event)"
                            @add="$emit('add', $event)"
                        />
                    </div>
                </template>
            </SortableList>
        </div>

        <Button
            v-if="node.children"
            text="Add block"
            icon="add-circle"
            variant="subtle"
            size="xs"
            class="ms-4 mt-1 w-[calc(100%-1rem)] justify-center"
            @click="addChild"
        />
    </div>
</template>

<script>
import { SortableList } from '@statamic/cms';
import { Button, DragHandle, Icon } from '@statamic/cms/ui';

export default {
    name: 'BlockNode',

    components: { SortableList, Button, DragHandle, Icon },

    props: {
        node: { type: Object, required: true },
        blockTypes: { type: Array, default: () => [] },
        selectedId: { type: String, default: null },
    },

    emits: ['select', 'remove', 'add'],

    data() {
        return { collapsed: true };
    },

    computed: {
        definition() {
            return this.blockTypes.find((b) => b.type === this.node.type);
        },

        label() {
            return this.definition?.label || this.node.type;
        },

        icon() {
            return this.definition?.icon || 'puzzle-piece';
        },
    },

    methods: {
        reorderChildren(items) {
            this.node.children.splice(0, this.node.children.length, ...items);
        },

        addChild() {
            this.collapsed = false;
            this.$emit('add', this.node.id);
        },
    },
};
</script>
