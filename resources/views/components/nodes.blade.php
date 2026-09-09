@props(['nodes' => [], 'editing' => false, 'topLevel' => true])

@foreach ($nodes as $node)
    @php
        $def = \Estouai\Weave\Blocks\BlockRegistry::find($node['type'] ?? null);
    @endphp
    @continue(! $def)

    @php
        $style = \Estouai\Weave\Support\StyleBuilder::build($node['props'] ?? []);

        // Entrance animations only fire on the real site — the editor canvas
        // re-renders on every edit, which would replay them constantly.
        $isAnimated = ! $editing && \Estouai\Weave\Support\AnimationBuilder::isAnimated($node['props'] ?? []);
        if ($isAnimated) {
            $animStyle = \Estouai\Weave\Support\AnimationBuilder::style($node['props'] ?? []);
            $style = trim(($style !== '' ? $style.';' : '').$animStyle, ';');
        }

        // Per-breakpoint show/hide. Literal Tailwind classes (not a computed string)
        // so the build's content scan can actually see them — see @class below.
        $hideMobile = (bool) ($node['props']['hide_mobile'] ?? false);
        $hideTablet = (bool) ($node['props']['hide_tablet'] ?? false);
        $hideDesktop = (bool) ($node['props']['hide_desktop'] ?? false);
        $hasVisibilityToggle = $hideMobile || $hideTablet || $hideDesktop;

        // Columns must size via flex-grow on THIS wrapper (the actual flex item inside
        // the parent `columns` block) — a `flex:` style on column.blade.php's own inner
        // div does nothing because that div isn't itself a flex child.
        if (($node['type'] ?? null) === 'column') {
            $width = (int) ($node['props']['width'] ?? 50);
            $style = trim("flex:{$width} 1 0%;min-width:0" . ($style !== '' ? ";{$style}" : ''), ';');
        } elseif (! $editing && $style === '' && ! $hasVisibilityToggle) {
            // `display:contents` (inline style) would beat the hidden/block classes
            // below (inline style always wins over a stylesheet class), so skip it
            // whenever a visibility toggle needs `display` to actually take effect.
            $style = 'display:contents';
        }
    @endphp

    <div
        @if ($editing)
            data-weave-id="{{ $node['id'] }}" data-weave-type="{{ $node['type'] }}"
            draggable="true" data-weave-draggable
            @if ($def->allowsChildren()) data-weave-container @endif
        @endif
        {{-- @alpinejs/intersect (already loaded site-wide, see resources/js/app.js)
             instead of a hand-rolled IntersectionObserver: Turbo swaps the DOM on
             navigation without a full reload, and Alpine's own MutationObserver
             already re-binds directives on new markup correctly — a bespoke
             observer script would need its own Turbo re-init wiring for that. --}}
        @if ($isAnimated) x-intersect.once="$el.style.opacity='1';$el.style.transform='none'" @endif
        @class([
            'hidden' => $hideMobile,
            'md:hidden' => $hideTablet && ! $hideMobile,
            'md:block' => $hideMobile && ! $hideTablet,
            'lg:hidden' => $hideDesktop && ! $hideTablet,
            'lg:block' => $hideTablet && ! $hideDesktop,
        ])
        @if ($style)
            style="{{ $style }}"
        @endif
    >
        @include($def->view(), ['props' => $node['props'] ?? [], 'children' => $node['children'] ?? [], 'editing' => $editing])
    </div>
@endforeach
