// Mirrors the `preset` options on Blocks/Columns.php (width = Column's flex-grow share).
// Still a flex-grow ratio, not a real CSS percentage (see Column.php) — these
// just sum to 100 so they read as literal percentages.
export const COLUMN_PRESETS = {
    '50-50': [50, 50],
    '33-33-33': [34, 33, 33],
    '66-33': [67, 33],
};

function widthsForPreset(preset) {
    return COLUMN_PRESETS[preset] || COLUMN_PRESETS['50-50'];
}

export function columnsForPreset(preset) {
    return widthsForPreset(preset).map((width) => ({
        id: crypto.randomUUID(),
        type: 'column',
        props: { width },
        children: [],
    }));
}

// Reuses existing columns (and their content) by index when a preset changes;
// extra slots get fresh empty columns, columns beyond the new count are dropped.
export function resizeColumnsForPreset(existingChildren, preset) {
    return widthsForPreset(preset).map((width, i) => {
        const existing = existingChildren[i];
        return existing
            ? { ...existing, props: { ...existing.props, width } }
            : { id: crypto.randomUUID(), type: 'column', props: { width }, children: [] };
    });
}
