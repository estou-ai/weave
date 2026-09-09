@props(['props' => [], 'editing' => false])

<x-button :variant="$props['variant'] ?? 'dark'" :href="$props['href'] ?? '#'">
    @if ($editing)
        <span data-weave-label>{{ $props['label'] ?? '' }}</span>
    @else
        {{ $props['label'] ?? '' }}
    @endif
</x-button>
