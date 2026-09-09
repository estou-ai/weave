@props(['props' => []])

<div class="grid w-full grid-cols-2 gap-lg lg:grid-cols-4">
    @foreach ($props['stats'] ?? [] as $stat)
        <x-stat :value="$stat['value'] ?? ''" :label="$stat['label'] ?? ''" />
    @endforeach
</div>
