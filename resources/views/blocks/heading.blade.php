@props(['props' => [], 'editing' => false])

@php
$level = $props['level'] ?? 'h2';
$align = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'][$props['align'] ?? 'left'] ?? 'text-left';
@endphp

<{{ $level }}
    {{-- not-prose: a page's .prose wrapper would otherwise add its own
         margin/font-size to this heading on top of Weave's own margin panel. --}}
    class="not-prose font-display text-h3 leading-[1.05] tracking-tight text-ink {{ $align }}"
    data-weave-text
    @if ($textStyle = \Estouai\Weave\Support\StyleBuilder::text($props)) style="{{ $textStyle }}" @endif
    @if ($editing) contenteditable="true" data-weave-prop="text" @endif
>{{ $props['text'] ?? '' }}</{{ $level }}>
