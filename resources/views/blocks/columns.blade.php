@props(['props' => [], 'children' => [], 'editing' => false])

@php
$gaps = ['sm' => 'gap-4', 'md' => 'gap-8', 'lg' => 'gap-12'];
$direction = ($props['direction'] ?? 'horizontal') === 'vertical' ? 'flex-col' : 'flex-col lg:flex-row';
@endphp

<div class="flex w-full {{ $direction }} {{ $gaps[$props['gap'] ?? 'md'] ?? 'gap-8' }}">
    @if ($editing && empty($children))
        <x-weave::empty-slot :editing="$editing" label="Add columns" />
    @else
        <x-weave::nodes :nodes="$children" :editing="$editing" :top-level="false" />
    @endif
</div>
