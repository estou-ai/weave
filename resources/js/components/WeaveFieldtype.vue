<template>
    <Teleport to="body" :disabled="!fullScreenMode">
    <div
        class="weave-fieldtype"
        :class="fullScreenMode ? 'fixed inset-0 z-50 overflow-hidden rounded-none bg-gray-100 dark:bg-gray-900' : ''"
    >
        <div :class="fullScreenMode ? 'flex h-full flex-col p-4' : ''">
            <div v-if="fullScreenMode" class="flex items-center justify-between mb-2 shrink-0">
                <div class="flex items-center gap-1 rounded-md border border-gray-200 p-1 dark:border-gray-700">
                    <Button
                        v-for="preset in viewportPresets"
                        :key="preset.key"
                        :text="preset.label"
                        size="xs"
                        :variant="viewport === preset.key ? 'primary' : 'ghost'"
                        @click="viewport = preset.key"
                    />
                </div>

                <Button
                    v-tooltip="__('Exit Fullscreen Mode')"
                    text="Close"
                    icon="x"
                    size="xs"
                    variant="default"
                    @click="toggleFullscreen"
                />
            </div>

            <div v-else class="flex justify-end mb-2 shrink-0">
                <Button
                    v-tooltip="__('Toggle Fullscreen Mode')"
                    icon="fullscreen-open"
                    icon-only
                    size="xs"
                    variant="default"
                    @click="toggleFullscreen"
                />
            </div>

            <div v-if="fullScreenMode" class="flex flex-1 gap-1 min-h-0">
                <div class="weave-scroll w-72 shrink-0 overflow-y-auto">
                    <blocks-panel
                        ref="blocksPanel"
                        :blocks="blocks"
                        :block-types="blockTypesMeta"
                        :selected-id="selectedBlockId"
                        @select="selectedBlockId = $event"
                        @remove="removeBlock"
                        @duplicate="duplicateBlock"
                        @add="addBlock"
                    />
                </div>

                <div class="relative flex flex-1 gap-1 min-w-0">
                    <div class="flex-1 min-w-0 overflow-auto rounded-md bg-gray-200 dark:bg-gray-950">
                        <div class="mx-auto h-full transition-all" :style="{ width: viewportWidth }">
                            <canvas-frame
                                :blocks="blocks"
                                :selected-id="selectedBlockId"
                                @select="selectedBlockId = $event"
                                @reorder="reorderBlocks"
                                @add="addBlock"
                                @prop="updateProp"
                                @contextmenu="openContextMenu"
                                @add-empty="openAddForParent"
                                @move="moveBlock"
                            />
                        </div>
                    </div>

                    <div
                        v-if="selectedNode"
                        ref="propsPanelEl"
                        :class="['weave-scroll', panelPinned
                            ? 'w-80 shrink-0 overflow-y-auto'
                            : ['absolute z-20 w-80 overflow-y-auto shadow-2xl', panelPosition ? '' : 'right-0 top-0']]"
                        :style="panelPinned ? null : {
                            maxHeight: '100%',
                            ...(panelPosition ? { top: panelPosition.top + 'px', left: panelPosition.left + 'px' } : {}),
                        }"
                    >
                        <props-panel
                            :node="selectedNode"
                            :block-type="blockTypesMeta.find((b) => b.type === selectedNode.type)"
                            :node-meta="nodeMeta[selectedBlockId] || {}"
                            :pinned="panelPinned"
                            @close="selectedBlockId = null"
                            @toggle-pin="panelPinned = ! panelPinned"
                            @drag="onPanelDrag"
                            @meta-stale="fetchNodeMeta(selectedBlockId)"
                        />
                    </div>
                </div>
            </div>

            <div v-else class="group relative">
                <canvas-frame :blocks="blocks" :selected-id="null" />

                <div
                    class="absolute inset-0 flex items-center justify-center opacity-0 backdrop-blur-xl transition group-hover:opacity-100"
                    style="background: rgba(17, 24, 39, 0.55);"
                >
                    <Button text="Edit" icon="pencil" variant="primary" @click="toggleFullscreen" />
                </div>
            </div>
        </div>

        <context-menu
            v-if="contextMenu"
            :x="contextMenu.x"
            :y="contextMenu.y"
            :items="contextMenuItems"
            @action="onContextAction"
            @close="contextMenu = null"
        />
    </div>
    </Teleport>
</template>

<script>
import { Fieldtype, clone, debounce } from '@statamic/cms';
import { Button } from '@statamic/cms/ui';
import BlocksPanel from './BlocksPanel.vue';
import CanvasFrame from './Canvas.vue';
import ContextMenu from './ContextMenu.vue';
import PropsPanel from './PropsPanel.vue';
import { findNodeById, containingArray, reassignIds } from '../lib/tree';
import { columnsForPreset } from '../lib/columns';

export default {
    mixins: [Fieldtype],

    components: { Button, BlocksPanel, CanvasFrame, ContextMenu, PropsPanel },

    data() {
        return {
            blocks: clone(this.value) || [],
            selectedBlockId: null,
            fullScreenMode: false,
            panelPinned: false,
            // null = default top-right corner (via the `right-0 top-0` classes).
            // Set on first drag to {top, left} px, measured from the panel's own
            // offsetParent — plain position, no `transform` (a `transform` on this
            // element — even `translate(0,0)` — makes it a containing block for
            // `position: fixed` descendants, which breaks any select/dropdown
            // popup inside the panel that relies on `fixed` to escape overflow).
            panelPosition: null,
            // Per-node relationship-field display data (title/thumbnail for
            // whatever's currently selected), keyed by node id — see
            // fetchNodeMeta(). blockTypesMeta's own `meta` is computed once per
            // block TYPE from empty defaultProps(), so it never has this for an
            // actual selected value.
            nodeMeta: {},
            clipboard: null,
            contextMenu: null,
            viewport: 'desktop',
            viewportPresets: [
                { key: 'desktop', label: 'Desktop' },
                { key: 'tablet', label: 'Tablet' },
                { key: 'mobile', label: 'Mobile' },
            ],
        };
    },

    computed: {
        blockTypesMeta() {
            return this.meta.blockTypes || [];
        },

        viewportWidth() {
            return { desktop: '100%', tablet: '768px', mobile: '390px' }[this.viewport];
        },

        selectedNode() {
            return this.selectedBlockId ? findNodeById(this.blocks, this.selectedBlockId) : null;
        },

        contextMenuItems() {
            return [
                { action: 'duplicate', icon: 'duplicate', label: 'Duplicate' },
                { action: 'copy', icon: 'clipboard', label: 'Copy' },
                { action: 'paste', icon: 'clipboard-check', label: 'Paste', disabled: ! this.clipboard },
                { action: 'remove', icon: 'trash', label: 'Delete' },
            ];
        },
    },

    watch: {
        blocks: {
            deep: true,
            handler: debounce(function () {
                this.update(this.blocks);
            }, 300),
        },

        selectedBlockId(id) {
            if (id && ! this.nodeMeta[id]) this.fetchNodeMeta(id);
        },

        fullScreenMode(on) {
            document.querySelector('.nav-main')?.style.setProperty('visibility', on ? 'hidden' : '');
            document.body.style.setProperty('overflow', on ? 'hidden' : '');
            if (! on) return;
            document.addEventListener('keydown', this.onEscKey, { once: true });
        },
    },

    beforeUnmount() {
        document.removeEventListener('keydown', this.onEscKey);
        document.querySelector('.nav-main')?.style.setProperty('visibility', '');
        document.body.style.setProperty('overflow', '');
    },

    methods: {
        update(value) {
            this.$emit('update:value', value);
        },

        onPanelDrag({ dx, dy }) {
            if (! this.panelPosition) {
                const el = this.$refs.propsPanelEl;
                const rect = el.getBoundingClientRect();
                const parentRect = el.offsetParent.getBoundingClientRect();
                this.panelPosition = { top: rect.top - parentRect.top, left: rect.left - parentRect.left };
            }

            this.panelPosition = { top: this.panelPosition.top + dy, left: this.panelPosition.left + dx };
        },

        async fetchNodeMeta(id) {
            const node = findNodeById(this.blocks, id);
            if (! node) return;

            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch(cp_url('weave/field-meta'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ type: node.type, props: node.props }),
            });

            if (! response.ok) return;

            const { meta } = await response.json();
            this.nodeMeta[id] = meta;
        },

        toggleFullscreen() {
            this.fullScreenMode = ! this.fullScreenMode;
        },

        onEscKey(e) {
            if (e.key === 'Escape') this.fullScreenMode = false;
        },

        addBlock({ type, parentId, index }) {
            const definition = this.blockTypesMeta.find((b) => b.type === type);
            if (! definition) return;

            const node = {
                id: crypto.randomUUID(),
                type,
                props: clone(definition.defaults) || {},
            };

            if (definition.allowsChildren) node.children = [];
            if (type === 'columns') node.children = columnsForPreset(node.props.preset);

            if (parentId) {
                const parent = findNodeById(this.blocks, parentId);
                if (parent) {
                    parent.children = parent.children || [];
                    parent.children.push(node);
                }
            } else {
                const at = Number.isInteger(index) ? index : this.blocks.length;
                this.blocks.splice(at, 0, node);
            }

            this.selectedBlockId = node.id;
        },

        updateProp({ id, prop, value }) {
            const node = findNodeById(this.blocks, id);
            if (! node) return;
            node.props = { ...node.props, [prop]: value };
        },

        reorderBlocks(ids) {
            const siblings = containingArray(this.blocks, ids[0]) || this.blocks;
            const byId = Object.fromEntries(siblings.map((n) => [n.id, n]));
            const reordered = ids.map((id) => byId[id]).filter(Boolean);
            siblings.splice(0, siblings.length, ...reordered);
        },

        removeBlock(id) {
            const removeFrom = (nodes) => {
                const index = nodes.findIndex((n) => n.id === id);
                if (index !== -1) {
                    nodes.splice(index, 1);
                    return true;
                }
                return nodes.some((n) => n.children && removeFrom(n.children));
            };

            removeFrom(this.blocks);

            if (this.selectedBlockId === id) this.selectedBlockId = null;
        },

        duplicateBlock(id) {
            const original = findNodeById(this.blocks, id);
            if (! original) return;

            const copy = reassignIds(clone(original));
            const siblings = containingArray(this.blocks, id) || this.blocks;
            const index = siblings.findIndex((n) => n.id === id);
            siblings.splice(index + 1, 0, copy);
        },

        copyBlock(id) {
            const node = findNodeById(this.blocks, id);
            if (node) this.clipboard = clone(node);
        },

        pasteBlock(afterId) {
            if (! this.clipboard) return;

            const copy = reassignIds(clone(this.clipboard));
            const siblings = containingArray(this.blocks, afterId) || this.blocks;
            const index = siblings.findIndex((n) => n.id === afterId);
            siblings.splice(index === -1 ? siblings.length : index + 1, 0, copy);
            this.selectedBlockId = copy.id;
        },

        moveBlock({ id, parentId }) {
            if (id === parentId) return;

            const target = findNodeById(this.blocks, parentId);
            if (! target) return;

            const siblings = containingArray(this.blocks, id);
            const index = siblings ? siblings.findIndex((n) => n.id === id) : -1;
            if (index === -1) return;

            const [node] = siblings.splice(index, 1);
            target.children = target.children || [];
            target.children.push(node);
            this.selectedBlockId = node.id;
        },

        openAddForParent(id) {
            this.selectedBlockId = id;
            this.$refs.blocksPanel?.openAddPicker(id);
        },

        openContextMenu({ id, x, y }) {
            this.contextMenu = { id, x, y };
        },

        onContextAction(action) {
            const id = this.contextMenu.id;
            this.contextMenu = null;

            if (action === 'duplicate') this.duplicateBlock(id);
            if (action === 'copy') this.copyBlock(id);
            if (action === 'paste') this.pasteBlock(id);
            if (action === 'remove') this.removeBlock(id);
        },
    },
};
</script>

<style>
/* Global (not scoped): also used inside BlocksPanel.vue's own template (Add block picker),
   a child component — Vue scoped CSS doesn't cross into child components' templates.
   Statamic's own CP CSS has no rule for these panels' native scrollbar (this addon
   ships no Tailwind build — see vite.config.js), so it falls back to a chunky default
   browser scrollbar. Style it directly instead. */
.weave-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(120, 120, 130, 0.5) transparent;
}

.weave-scroll::-webkit-scrollbar {
    width: 8px;
}

.weave-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.weave-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(120, 120, 130, 0.5);
    border-radius: 9999px;
}

.weave-scroll::-webkit-scrollbar-thumb:hover {
    background-color: rgba(120, 120, 130, 0.75);
}

/* Statamic's own CSS pins every popper-portaled dropdown (select, color picker,
   etc — Reka UI's [data-reka-popper-content-wrapper]) to `z-index: var(--z-index-portal)`,
   which is 2. Our floating props panel sits at z-20 (Tailwind `z-20`) so it wins
   against that by default, burying any dropdown opened from inside it. Bump
   just this attribute above our panel's z-index — same specificity as
   Statamic's rule, so load order decides and this file loads after core CP CSS. */
[data-reka-popper-content-wrapper] {
    z-index: 100 !important;
}
</style>
