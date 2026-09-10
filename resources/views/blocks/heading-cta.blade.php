@props(['props' => []])

<div class="not-prose flex w-full flex-col items-start gap-lg">
    <div class="flex w-full flex-col items-start gap-sm lg:flex-row">
        <p class="flex-1 font-sans text-sm font-medium leading-none text-ink">{{ $props['eyebrow'] ?? '' }}</p>
        <p class="flex-1 font-display text-h2 leading-none tracking-tight text-ink">{{ $props['heading'] ?? '' }}</p>
    </div>
    @if (! empty($props['button_label']))
        <x-button :variant="$props['button_variant'] ?? 'light'" :href="$props['button_href'] ?? '#'">{{ $props['button_label'] }}</x-button>
    @endif
</div>
