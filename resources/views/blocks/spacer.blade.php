@props(['props' => []])

@php
$heights = ['sm' => 'h-4', 'md' => 'h-8', 'lg' => 'h-16', 'xl' => 'h-32'];
@endphp

<div class="{{ $heights[$props['height'] ?? 'md'] ?? 'h-8' }}"></div>
