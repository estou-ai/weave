@props(['props' => [], 'children' => [], 'editing' => false])

<div class="flex w-full min-w-0 flex-col gap-4">
    @if ($editing && empty($children))
        <x-weave::empty-slot :editing="$editing" label="Add content" />
    @else
        <x-weave::nodes :nodes="$children" :editing="$editing" :top-level="false" />
    @endif
</div>
