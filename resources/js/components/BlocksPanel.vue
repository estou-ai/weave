<template>
    <div class="p-3 pr-1.5">
        <div class="mb-3 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <button
                type="button"
                class="flex w-full items-center gap-2 bg-gray-50 px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-800"
                @click="toggleAddBlock"
            >
                <Icon :name="addBlockCollapsed ? 'chevron-right' : 'chevron-down'" class="size-4 text-gray-400" />
                Add Block
            </button>

            <div v-if="!addBlockCollapsed" class="border-t border-gray-200 p-2 dark:border-gray-700">
            <div class="weave-scroll max-h-60 overflow-y-auto">
                <div v-for="(items, category) in grouped" :key="category" class="mb-3">
                    <button
                        type="button"
                        class="mb-1 flex w-full items-center gap-1 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400"
                        @click="toggleCategory(category)"
                    >
                        <Icon :name="isCategoryCollapsed(category) ? 'chevron-right' : 'chevron-down'" class="size-3" />
                        {{ category }}
                    </button>

                    <div v-if="!isCategoryCollapsed(category)" class="grid grid-cols-2 gap-2">
                        <div
                            v-for="block in items"
                            :key="block.type"
                            :draggable="addingParentId === null"
                            class="cursor-grab active:cursor-grabbing"
                            @dragstart="onPaletteDragStart($event, block.type)"
                            @dragend="resetDrag"
                        >
                            <button
                                type="button"
                                class="flex h-30 w-full flex-col items-center justify-center gap-2 rounded-md border border-gray-200 p-3 text-center hover:border-sky-400 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                @click="choose(block.type)"
                            >
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-gray-50 dark:bg-gray-800">
                                    <Icon :name="block.icon || 'puzzle-piece'" class="size-5 text-gray-500" />
                                </span>
                                <span class="line-clamp-2 text-xs font-medium">{{ block.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <p v-if="addingParentId === null" class="mt-2 text-xs text-gray-400">Drag a block onto a section to place it there, or click to add at the end.</p>
            </div>
        </div>

        <SortableList
            :model-value="blocks"
            vertical
            :animate="false"
            item-class="sortable-item"
            handle-class="sortable-handle"
            @update:model-value="reorder"
        >
            <template #default="{ items }">
                <div v-show="items.length" class="mb-3 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 bg-gray-50 px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-800"
                        @click="sectionsCollapsed = !sectionsCollapsed"
                    >
                        <Icon :name="sectionsCollapsed ? 'chevron-right' : 'chevron-down'" class="size-4 text-gray-400" />
                        Sections
                    </button>

                <CardList v-if="!sectionsCollapsed">
                    <CardListItem
                        v-for="node in items"
                        :key="node.id"
                        class="sortable-item"
                        @dragenter.prevent
                        @dragover.prevent="onDragOver($event, node.id)"
                        @drop.prevent="onDrop(node.id)"
                    >
                        <div class="w-full">
                            <div
                                v-show="dragType && dropTarget === node.id && dropPosition === 'before'"
                                class="mb-1 h-0.5 rounded bg-sky-500"
                            />

                            <div
                                class="flex items-center gap-2 rounded-md px-2 py-1 cursor-pointer"
                                :class="selectedId === node.id ? 'bg-sky-50 dark:bg-gray-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                                @click="$emit('select', node.id)"
                            >
                                <DragHandle class="sortable-handle" @click.stop />
                                <Button
                                    v-if="node.children"
                                    :icon="isCollapsed(node.id) ? 'chevron-right' : 'chevron-down'"
                                    icon-only
                                    size="2xs"
                                    variant="ghost"
                                    @click.stop="toggleCollapse(node.id)"
                                />
                                <Icon :name="iconFor(node.type)" class="size-4 shrink-0 text-gray-400" />
                                <span class="flex-1 min-w-0 truncate text-sm font-medium">{{ labelFor(node.type) }}</span>
                                <Button icon="duplicate" icon-only size="2xs" variant="ghost" @click.stop="$emit('duplicate', node.id)" />
                                <Button icon="trash" icon-only size="2xs" variant="ghost" @click.stop="$emit('remove', node.id)" />
                            </div>

                            <div v-if="node.children && !isCollapsed(node.id)" class="ms-4 mt-1 space-y-1 rounded-md border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-800/50">
                                <BlockNode
                                    v-for="child in node.children"
                                    :key="child.id"
                                    :node="child"
                                    :block-types="blockTypes"
                                    :selected-id="selectedId"
                                    @select="$emit('select', $event)"
                                    @remove="$emit('remove', $event)"
                                    @add="openAddPicker($event)"
                                />
                            </div>

                            <Button
                                v-if="node.children"
                                text="Add block"
                                icon="add-circle"
                                variant="subtle"
                                size="xs"
                                class="ms-4 mt-1 w-[calc(100%-1rem)] justify-center"
                                @click="addChild(node.id)"
                            />

                            <div
                                v-show="dragType && dropTarget === node.id && dropPosition === 'after'"
                                class="mt-1 h-0.5 rounded bg-sky-500"
                            />
                        </div>
                    </CardListItem>
                </CardList>
                </div>
            </template>
        </SortableList>

        <div
            v-if="!blocks.length"
            class="rounded-md border-2 border-dashed p-6 text-center text-sm text-gray-400"
            :class="dropTarget === 'empty' ? 'border-sky-500' : 'border-gray-200 dark:border-gray-700'"
            @dragenter.prevent
            @dragover.prevent="onDragOverEmpty"
            @drop.prevent="onDropEnd"
        >
            Drag a block here, or use "Add Block" above.
        </div>
    </div>
</template>

<script>
import { SortableList } from '@statamic/cms';
import { Button, CardList, CardListItem, DragHandle, Icon } from '@statamic/cms/ui';
import BlockNode from './BlockNode.vue';

export default {
    components: { SortableList, Button, CardList, CardListItem, DragHandle, Icon, BlockNode },

    props: {
        blocks: { type: Array, default: () => [] },
        blockTypes: { type: Array, default: () => [] },
        selectedId: { type: String, default: null },
    },

    emits: ['select', 'remove', 'duplicate', 'add'],

    data() {
        return {
            addingParentId: null,
            addBlockCollapsed: false,
            dragType: null,
            dropTarget: null,
            dropPosition: null,
            collapsed: {},
            categoryCollapsed: {},
            sectionsCollapsed: true,
        };
    },

    computed: {
        grouped() {
            return this.blockTypes.reduce((groups, block) => {
                (groups[block.category] = groups[block.category] || []).push(block);
                return groups;
            }, {});
        },
    },

    methods: {
        labelFor(type) {
            return this.blockTypes.find((b) => b.type === type)?.label || type;
        },

        iconFor(type) {
            return this.blockTypes.find((b) => b.type === type)?.icon || 'puzzle-piece';
        },

        reorder(items) {
            this.blocks.splice(0, this.blocks.length, ...items);
        },

        isCategoryCollapsed(category) {
            return !! this.categoryCollapsed[category];
        },

        toggleCategory(category) {
            this.categoryCollapsed[category] = ! this.isCategoryCollapsed(category);
        },

        isCollapsed(id) {
            return this.collapsed[id] !== false;
        },

        toggleCollapse(id) {
            this.collapsed[id] = ! this.isCollapsed(id);
        },

        addChild(id) {
            this.collapsed[id] = false;
            this.openAddPicker(id);
        },

        toggleAddBlock() {
            this.addBlockCollapsed = ! this.addBlockCollapsed;
            if (this.addBlockCollapsed) this.addingParentId = null;
        },

        openAddPicker(parentId) {
            this.addingParentId = parentId;
            this.addBlockCollapsed = false;
        },

        choose(type) {
            this.$emit('add', { type, parentId: this.addingParentId || null });
            this.addingParentId = null;
            this.addBlockCollapsed = true;
        },

        onPaletteDragStart(event, type) {
            if (this.addingParentId !== null) return;
            this.dragType = type;
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('text/plain', type);
        },

        onDragOver(event, nodeId) {
            if (!this.dragType) return;
            const rect = event.currentTarget.getBoundingClientRect();
            this.dropTarget = nodeId;
            this.dropPosition = (event.clientY - rect.top) < rect.height / 2 ? 'before' : 'after';
        },

        onDragOverEmpty() {
            if (!this.dragType) return;
            this.dropTarget = 'empty';
        },

        onDrop(nodeId) {
            if (!this.dragType) return;
            const index = this.blocks.findIndex((n) => n.id === nodeId);
            const at = this.dropPosition === 'before' ? index : index + 1;
            this.$emit('add', { type: this.dragType, parentId: null, index: at });
            this.resetDrag();
        },

        onDropEnd() {
            if (!this.dragType) return;
            this.$emit('add', { type: this.dragType, parentId: null, index: this.blocks.length });
            this.resetDrag();
        },

        resetDrag() {
            this.dragType = null;
            this.dropTarget = null;
            this.dropPosition = null;
        },
    },
};
</script>
