@props(['props' => [], 'editing' => false])

@php
$level = $props['level'] ?? 'h2';
$align = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'][$props['align'] ?? 'left'] ?? 'text-left';
@endphp

<{{ $level }}
    class="font-display text-h3 leading-[1.05] tracking-tight text-ink {{ $align }}"
    data-weave-text
    @if ($textStyle = \Estouai\Weave\Support\StyleBuilder::text($props)) style="{{ $textStyle }}" @endif
    @if ($editing) contenteditable="true" data-weave-prop="text" @endif
>{{ $props['text'] ?? '' }}</{{ $level }}>
