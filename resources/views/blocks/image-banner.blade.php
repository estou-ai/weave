@props(['props' => []])

@php
    $heights = ['sm' => 'h-[280px]', 'md' => 'h-[407px]', 'lg' => 'h-[520px]'];
@endphp

<x-image-banner :image="$props['asset']?->url()" :height="$heights[$props['height'] ?? 'md'] ?? $heights['md']">
    {{ $props['caption'] ?? '' }}
</x-image-banner>
