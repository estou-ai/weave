<template>
    <iframe
        ref="frame"
        :srcdoc="html"
        style="width: 100%; height: 100%; min-height: 400px; border: 1px solid #eee; border-radius: .25rem;"
        @load="onFrameLoad"
    />
</template>

<script>
import { debounce } from '@statamic/cms';
import { buildNodeStyle, buildTextStyle, PATCHABLE_KEYS, INLINE_EDITED_KEYS } from '../lib/style';

const SKIP_KEYS = [...PATCHABLE_KEYS, ...INLINE_EDITED_KEYS];

export default {
    props: {
        blocks: { type: Array, default: () => [] },
        selectedId: { type: String, default: null },
    },

    emits: ['select', 'reorder', 'add', 'prop', 'contextmenu', 'add-empty', 'move'],

    data() {
        return { html: '' };
    },

    watch: {
        // Only a full server re-render (network round trip -> new srcdoc -> iframe
        // navigates) when the block tree's shape changes (add/remove/reorder/type).
        // A prop/style-only edit (slider drag, color pick, box-model number, text
        // blur) keeps the same shape, so it's patched live into the existing iframe
        // instead — that's what was showing up as a jarring "refresh" per edit.
        blocks: {
            deep: true,
            immediate: true,
            handler: debounce(function () {
                const key = this.structuralKey(this.blocks);
                if (key === this._structureKey) {
                    this.patchStyles();
                    return;
                }
                this._structureKey = key;
                this.fetchPreview();
            }, 250),
        },

        selectedId(id) {
            this.postHighlight(id);
        },
    },

    mounted() {
        window.addEventListener('message', this.onMessage);
    },

    beforeUnmount() {
        window.removeEventListener('message', this.onMessage);
    },

    methods: {
        async fetchPreview() {
            // srcdoc reassignment below is a full iframe navigation — captures scroll
            // now so onFrameLoad() can restore it once the new document settles.
            this._scrollPos = {
                x: this.$refs.frame?.contentWindow?.scrollX ?? 0,
                y: this.$refs.frame?.contentWindow?.scrollY ?? 0,
            };

            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch(cp_url('weave/preview'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ blocks: this.blocks }),
            });

            this.html = await response.text();
        },

        onMessage(event) {
            if (event.data?.type === 'block:selected') {
                this.$emit('select', event.data.id);
            }

            if (event.data?.type === 'block:reorder') {
                this.$emit('reorder', event.data.ids);
            }

            if (event.data?.type === 'block:add') {
                this.$emit('add', { type: event.data.blockType, parentId: event.data.parentId || null, index: event.data.index });
            }

            if (event.data?.type === 'block:move') {
                this.$emit('move', { id: event.data.id, parentId: event.data.parentId });
            }

            if (event.data?.type === 'block:prop') {
                this.$emit('prop', { id: event.data.id, prop: event.data.prop, value: event.data.value });
            }

            if (event.data?.type === 'block:add-empty') {
                this.$emit('add-empty', event.data.id);
            }

            if (event.data?.type === 'block:contextmenu') {
                const rect = this.$refs.frame.getBoundingClientRect();
                this.$emit('contextmenu', { id: event.data.id, x: rect.left + event.data.x, y: rect.top + event.data.y });
            }
        },

        postHighlight(id) {
            this.$refs.frame?.contentWindow?.postMessage({ type: 'block:highlight', id }, '*');
        },

        // @load only fires on a full iframe reload (structural change — add/reorder/
        // move), never on a plain sidebar select, so this can't steal focus every
        // time a block is clicked — only right after it's newly placed on the canvas.
        onFrameLoad() {
            if (this._scrollPos) {
                this.$refs.frame?.contentWindow?.scrollTo(this._scrollPos.x, this._scrollPos.y);
            }

            this.postHighlight(this.selectedId);
            if (this.selectedId) {
                this.$refs.frame?.contentWindow?.postMessage({ type: 'block:focus-text', id: this.selectedId }, '*');
            }
        },

        structuralKey(nodes) {
            return JSON.stringify(nodes.map((n) => ({
                id: n.id,
                type: n.type,
                // Any prop that isn't cheaply patchable (label, href, variant, alt,
                // rounded, hide_*, ...) forces a full re-render when it changes —
                // there's no `style` patch that can express a text/class/attribute
                // change, so treat it the same as add/remove/reorder.
                props: Object.fromEntries(
                    Object.entries(n.props || {}).filter(([k]) => ! SKIP_KEYS.includes(k)),
                ),
                children: n.children ? this.structuralKey(n.children) : undefined,
            })));
        },

        flattenStyles(nodes, acc = []) {
            for (const n of nodes) {
                acc.push({ id: n.id, style: buildNodeStyle(n), textStyle: buildTextStyle(n.props) });
                if (n.children) this.flattenStyles(n.children, acc);
            }
            return acc;
        },

        patchStyles() {
            this.$refs.frame?.contentWindow?.postMessage({ type: 'block:patch', nodes: this.flattenStyles(this.blocks) }, '*');
        },
    },
};
</script>
