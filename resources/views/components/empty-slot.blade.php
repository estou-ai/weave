@props(['editing' => false, 'label' => 'Add content'])

@if ($editing)
    <div
        data-weave-empty-add
        class="flex min-h-24 w-full cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 text-sm text-gray-400 transition hover:border-sky-400 hover:bg-sky-50/50 hover:text-sky-500"
    >
        + {{ $label }}
    </div>
@endif
