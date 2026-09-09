@props(['props' => []])

<img
    src="{{ $props['asset']?->url() }}"
    alt="{{ $props['alt'] ?? '' }}"
    class="w-full object-cover"
    loading="lazy"
    decoding="async"
    draggable="false"
>
