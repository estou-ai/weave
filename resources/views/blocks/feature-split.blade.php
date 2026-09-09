@props(['props' => []])

<x-feature-split
    :image="$props['image']?->url()"
    :eyebrow="$props['eyebrow'] ?? ''"
    :href="$props['href'] ?? '#'"
    :label="$props['label'] ?? 'Learn more'"
>
    {{ $props['body'] ?? '' }}
</x-feature-split>
