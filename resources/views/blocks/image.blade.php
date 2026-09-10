@props(['props' => []])

<img
    src="{{ $props['asset']?->url() }}"
    alt="{{ $props['alt'] ?? '' }}"
    {{-- not-prose: a page's .prose wrapper defaults to margin-top/bottom on
         bare <img> tags (content-image spacing), unwanted on a UI block. --}}
    class="not-prose w-full object-cover"
    loading="lazy"
    decoding="async"
    draggable="false"
>
