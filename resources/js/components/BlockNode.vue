<template>
    <div class="sortable-item">
        <div
            class="group flex items-center gap-1 rounded px-1 py-1 cursor-pointer"
            :class="selectedId === node.id ? 'bg-sky-50 dark:bg-gray-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
            :style="{ paddingLeft: depth * 14 + 4 + 'px' }"
            @click="$emit('select', node.id)"
        >
            <DragHandle class="sortable-handle shrink-0 opacity-0 group-hover:opacity-100" @click.stop />

            <Button
                v-if="node.children"
                :icon="collapsed ? 'chevron-right' : 'chevron-down'"
                icon-only
                size="2xs"
                variant="ghost"
                class="shrink-0"
                @click.stop="collapsed = ! collapsed"
            />
            <span v-else class="w-4 shrink-0" />

            <span v-if="iconSvg" class="inline-flex size-3.5 shrink-0 items-center justify-center text-gray-400 [&_svg]:size-3.5" v-html="iconSvg" />
            <Icon v-else :name="icon" class="size-3.5 shrink-0 text-gray-400" />
            <span class="shrink-0 text-sm font-medium">{{ label }}</span>
            <span v-if="preview" class="min-w-0 flex-1 truncate text-xs text-gray-400">{{ preview }}</span>
            <span v-else class="flex-1" />

            <div class="flex shrink-0 items-center opacity-0 group-hover:opacity-100">
                <Button icon="duplicate" icon-only size="2xs" variant="ghost" @click.stop="$emit('duplicate', node.id)" />
                <Button icon="trash" icon-only size="2xs" variant="ghost" @click.stop="$emit('remove', node.id)" />
            </div>
        </div>

        <div v-if="node.children && !collapsed" class="border-l border-gray-200 dark:border-gray-700" :style="{ marginLeft: depth * 14 + 11 + 'px' }">
            <SortableList
                :model-value="node.children"
                vertical
                :animate="false"
                item-class="sortable-item"
                handle-class="sortable-handle"
                @update:model-value="reorderChildren"
            >
                <template #default="{ items }">
                    <div>
                        <BlockNode
                            v-for="child in items"
                            :key="child.id"
                            :node="child"
                            :depth="depth + 1"
                            :block-types="blockTypes"
                            :selected-id="selectedId"
                            @select="$emit('select', $event)"
                            @remove="$emit('remove', $event)"
                            @duplicate="$emit('duplicate', $event)"
                            @add="$emit('add', $event)"
                        />
                    </div>
                </template>
            </SortableList>

            <button
                type="button"
                class="mb-1 block text-xs text-gray-400 hover:text-sky-500"
                :style="{ paddingLeft: (depth + 1) * 14 + 4 + 'px' }"
                @click="addChild"
            >
                + Add block
            </button>
        </div>
    </div>
</template>

<script>
import { SortableList } from '@statamic/cms';
import { Button, DragHandle, Icon } from '@statamic/cms/ui';

// Handles a node's props are searched, in order, for the first non-empty
// string/rich-text value to show as an inline preview next to the block's label.
const PREVIEW_HANDLES = ['heading', 'title', 'headline', 'text', 'content', 'quote', 'label'];

function plainText(value) {
    if (typeof value === 'string') return value.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
    if (! Array.isArray(value)) return '';

    return value.map((item) => plainText(item?.text || '') + ' ' + plainText(item?.content || [])).join(' ').replace(/\s+/g, ' ').trim();
}

export default {
    name: 'BlockNode',

    components: { SortableList, Button, DragHandle, Icon },

    props: {
        node: { type: Object, required: true },
        blockTypes: { type: Array, default: () => [] },
        selectedId: { type: String, default: null },
        depth: { type: Number, default: 0 },
    },

    emits: ['select', 'remove', 'duplicate', 'add'],

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

        iconSvg() {
            return this.definition?.iconSvg || null;
        },

        // ponytail: plain-text snippet from known prop handles only, no rich
        // preview (asset thumbnails, tags) — that needs per-node meta fetched
        // from the server (see WeaveFieldtype's fetchNodeMeta), which today
        // only runs for the selected node to avoid one request per row.
        preview() {
            for (const handle of PREVIEW_HANDLES) {
                const text = plainText(this.node.props?.[handle]);
                if (text) return text;
            }

            return null;
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
