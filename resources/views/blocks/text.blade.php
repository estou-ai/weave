@props(['props' => [], 'editing' => false])

<div
    class="not-prose font-sans text-base leading-relaxed text-ink"
    data-weave-text
    @if ($textStyle = \Estouai\Weave\Support\StyleBuilder::text($props)) style="{{ $textStyle }}" @endif
>{!! \Estouai\Weave\Support\RichText::render($props['content'] ?? '') !!}</div>
