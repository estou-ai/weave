@props(['props' => []])

<x-hero :lines="$props['lines'] ?? []" :background="$props['background']?->url()">
    @foreach ($props['buttons'] ?? [] as $btn)
        <x-button :variant="$btn['variant'] ?? 'light'" :href="$btn['href'] ?? '#'">{{ $btn['label'] ?? '' }}</x-button>
    @endforeach
</x-hero>
