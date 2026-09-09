<template>
    <Card>
        <div
            class="mb-3 flex items-center justify-between"
            :class="{ 'cursor-move': ! pinned }"
            @mousedown="startDrag"
        >
            <span class="text-sm font-semibold">{{ blockType ? blockType.label : node.type }}</span>
            <div class="flex items-center gap-1">
                <Button
                    v-tooltip="pinned ? __('Unpin (float over canvas)') : __('Pin (dock in layout)')"
                    icon="pin"
                    icon-only
                    size="sm"
                    :variant="pinned ? 'primary' : 'ghost'"
                    @click="$emit('toggle-pin')"
                />
                <Button icon="x" icon-only variant="ghost" size="sm" @click="$emit('close')" />
            </div>
        </div>

        <div class="space-y-4">
            <PropField
                v-for="field in contentFields"
                :key="field.handle"
                :field="field"
                :value="node.props[field.handle] ?? blockType?.defaults?.[field.handle]"
                :meta="meta[field.handle]"
                @update:value="(value) => updateField(field, value)"
            />
        </div>

        <div v-if="styleSections.length || groups.length" class="mt-5 space-y-1 border-t border-gray-200 pt-3 dark:border-gray-700">
            <div v-for="section in styleSections" :key="section.title">
                <button
                    type="button"
                    class="flex w-full items-center gap-1 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400"
                    @click="toggleSection(section.title)"
                >
                    <Icon :name="isSectionCollapsed(section.title) ? 'chevron-right' : 'chevron-down'" class="size-3" />
                    {{ section.title }}
                </button>

                <div v-if="!isSectionCollapsed(section.title)" class="space-y-4 pb-3">
                    <PropField
                        v-for="field in section.fields"
                        :key="field.handle"
                        :field="field"
                        :value="node.props[field.handle] ?? blockType?.defaults?.[field.handle]"
                        :meta="meta[field.handle]"
                        @update:value="(value) => updateField(field, value)"
                    />
                </div>
            </div>

            <div v-if="groups.length">
                <button
                    type="button"
                    class="flex w-full items-center gap-1 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400"
                    @click="toggleSection('Spacing')"
                >
                    <Icon :name="isSectionCollapsed('Spacing') ? 'chevron-right' : 'chevron-down'" class="size-3" />
                    Spacing
                </button>

                <div v-if="!isSectionCollapsed('Spacing')" class="space-y-4 pb-3">
                    <BoxModelControl
                        v-for="group in groups"
                        :key="group.name"
                        :title="group.title"
                        :fields="group.fields"
                        :node="node"
                    />
                </div>
            </div>
        </div>
    </Card>
</template>

<script>
import { Button, Card, Icon } from '@statamic/cms/ui';
import BoxModelControl from './BoxModelControl.vue';
import PropField from './PropField.vue';
import { resizeColumnsForPreset } from '../lib/columns';

const RELATIONAL_FIELD_TYPES = ['entries', 'assets', 'terms', 'users'];

// Which style-panel section a generic style field (see Blocks/Block.php
// styleSchema()) belongs to. Anything not listed here — a block's own
// propsSchema() fields, e.g. Column's Width — renders up top, unsectioned.
// Spacing (margin/padding) isn't listed: it's already grouped via `group` in
// the blueprint and rendered from `groups` below, not this handle map.
const STYLE_SECTIONS = [
    { title: 'Typography', handles: ['text_color', 'background_color', 'font_size', 'font_family', 'font_weight'] },
    { title: 'Border & Shadow', handles: ['border_radius', 'border_width', 'border_style', 'border_color', 'shadow'] },
    { title: 'Animation', handles: ['animation', 'animation_duration', 'animation_delay'] },
    { title: 'Visibility', handles: ['hide_mobile', 'hide_tablet', 'hide_desktop'] },
];

export default {
    components: { Button, Card, Icon, BoxModelControl, PropField },

    props: {
        node: { type: Object, required: true },
        blockType: { type: Object, default: null },
        // Per-node meta (resolved title/thumbnail for a relationship field's
        // current value) fetched by the parent — see fetchNodeMeta() in
        // WeaveFieldtype.vue. Wins over blockType.meta per field handle,
        // which only ever reflects empty defaultProps().
        nodeMeta: { type: Object, default: () => ({}) },
        pinned: { type: Boolean, default: false },
    },

    emits: ['close', 'toggle-pin', 'drag', 'meta-stale'],

    data() {
        // Collapsed by default — this panel lists every generic style field on
        // every block, and most edits are to the block's own top fields, not
        // typography/border/spacing/visibility.
        return { sectionCollapsed: {} };
    },

    computed: {
        fields() {
            return this.blockType?.fields || [];
        },

        meta() {
            return { ...this.blockType?.meta, ...this.nodeMeta };
        },

        ungroupedFields() {
            return this.fields.filter((field) => ! field.group);
        },

        // The block's own fields (Column's Width, Image's asset/alt, ...) — not a
        // generic style field, so always shown, no section chrome.
        contentFields() {
            const styleHandles = STYLE_SECTIONS.flatMap((section) => section.handles);
            return this.ungroupedFields.filter((field) => ! styleHandles.includes(field.handle));
        },

        styleSections() {
            return STYLE_SECTIONS
                .map((section) => ({
                    title: section.title,
                    fields: this.ungroupedFields.filter((field) => section.handles.includes(field.handle)),
                }))
                .filter((section) => section.fields.length);
        },

        groups() {
            const byName = {};
            for (const field of this.fields) {
                if (! field.group) continue;
                (byName[field.group] = byName[field.group] || []).push(field);
            }
            return Object.entries(byName).map(([name, fields]) => ({
                name,
                title: name.charAt(0).toUpperCase() + name.slice(1),
                fields,
            }));
        },
    },

    methods: {
        isSectionCollapsed(title) {
            return this.sectionCollapsed[title] !== false;
        },

        toggleSection(title) {
            this.sectionCollapsed[title] = ! this.isSectionCollapsed(title);
        },

        // Only meaningful while floating — pinned means docked in the normal layout
        // flow. Reuses native `movementX/Y` instead of tracking previous coordinates
        // by hand.
        startDrag(e) {
            if (this.pinned || e.target.closest('button')) return;
            e.preventDefault();

            const onMove = (ev) => this.$emit('drag', { dx: ev.movementX, dy: ev.movementY });
            const onUp = () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        },

        updateField(field, value) {
            this.node.props[field.handle] = value;

            if (field.handle === 'preset' && this.node.type === 'columns') {
                this.node.children = resizeColumnsForPreset(this.node.children || [], value);
            }

            // Relational fieldtypes (entries, assets, terms, users) render their
            // selected value's title/thumbnail from `meta`, which was fetched once
            // when the block was selected — picking a new value here goes stale
            // until the parent refetches it (see fetchNodeMeta() in
            // WeaveFieldtype.vue).
            if (RELATIONAL_FIELD_TYPES.includes(field.type)) {
                this.$emit('meta-stale');
            }
        },
    },
};
</script>
