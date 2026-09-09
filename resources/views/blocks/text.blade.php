@props(['props' => [], 'editing' => false])

<p
    class="font-sans text-base leading-relaxed text-ink"
    data-weave-text
    @if ($textStyle = \Estouai\Weave\Support\StyleBuilder::text($props)) style="{{ $textStyle }}" @endif
    @if ($editing) contenteditable="true" data-weave-prop="content" @endif
>{!! nl2br(e($props['content'] ?? '')) !!}</p>
