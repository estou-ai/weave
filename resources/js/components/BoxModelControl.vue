<template>
    <div>
        <div class="mb-2 flex items-center justify-between">
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">{{ title }}</label>
            <Button
                v-tooltip="linked ? __('Unlink sides') : __('Link all sides')"
                icon="link"
                icon-only
                size="sm"
                :variant="linked ? 'primary' : 'ghost'"
                @click="toggleLinked"
            />
        </div>
        <div v-if="linked" class="flex flex-col items-center gap-1">
            <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('All sides') }}</span>
            <input
                type="number"
                min="0"
                :max="fields[0].max"
                class="w-full rounded bg-gray-100 px-1 py-1 text-center text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="0"
                :value="node.props[fields[0].handle] ?? ''"
                @input="onInput(fields[0], $event.target.value)"
            />
        </div>

        <div v-else class="grid grid-cols-4 gap-2">
            <div v-for="side in fields" :key="side.handle" class="flex flex-col items-center gap-1">
                <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ SIDE_LABELS[side.side] || side.side }}</span>
                <input
                    type="number"
                    min="0"
                    :max="side.max"
                    class="w-full rounded bg-gray-100 px-1 py-1 text-center text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    placeholder="0"
                    :value="node.props[side.handle] ?? ''"
                    @input="onInput(side, $event.target.value)"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { Button } from '@statamic/cms/ui';

// Plain 4-column grid, not a visual box with corner-positioned inputs (an
// earlier version) — that relied on negative top/bottom offsets escaping this
// control's own box, which the panel's scroll container kept clipping (the top
// input going missing was the recurring symptom). A normal grid can't clip.
const SIDE_LABELS = { top: 'Top', right: 'Right', bottom: 'Bottom', left: 'Left' };

export default {
    components: { Button },

    props: {
        title: { type: String, required: true },
        fields: { type: Array, required: true },
        node: { type: Object, required: true },
    },

    data() {
        return { SIDE_LABELS, linked: false };
    },

    methods: {
        toggleLinked() {
            this.linked = ! this.linked;

            // Sync the other three to whichever value is already set, right
            // away — otherwise "linked" silently does nothing until the next
            // edit, and the 4 sides visibly disagree in the meantime.
            if (this.linked) {
                const value = this.node.props[this.fields[0].handle] ?? 0;
                for (const side of this.fields) this.node.props[side.handle] = value;
            }
        },

        onInput(side, rawValue) {
            const value = rawValue === '' ? null : Number(rawValue);

            if (this.linked) {
                for (const s of this.fields) this.node.props[s.handle] = value;
                return;
            }

            this.node.props[side.handle] = value;
        },
    },
};
</script>
