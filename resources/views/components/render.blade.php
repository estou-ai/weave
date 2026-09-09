@props(['blocks' => [], 'editing' => false])

<x-weave::nodes :nodes="$blocks" :editing="$editing" />

@if ($editing)
    <script>
        // Preview is a canvas, not a live page — kill every link/button/form action
        // (nav toggles, external links, submits) regardless of whether the target
        // sits inside a block wrapper. Capture phase + preventDefault only (no
        // stopPropagation) so it always wins first but the selection/contextmenu
        // listeners below still see the click via normal bubbling.
        document.addEventListener('click', function (e) {
            if (e.target.closest('a, button, input, select, textarea, [onclick]')) e.preventDefault();
        }, true);

        document.addEventListener('submit', function (e) { e.preventDefault(); }, true);

        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-weave-id]');
            if (! el) return;
            if (e.target.closest('a, button')) e.preventDefault();

            var id = el.getAttribute('data-weave-id');

            if (e.target.closest('[data-weave-empty-add]')) {
                parent.postMessage({ type: 'block:add-empty', id: id }, '*');
                return;
            }

            parent.postMessage({ type: 'block:selected', id: id }, '*');
        });

        document.addEventListener('contextmenu', function (e) {
            var el = e.target.closest('[data-weave-id]');
            if (! el) return;
            e.preventDefault();
            parent.postMessage({
                type: 'block:contextmenu',
                id: el.getAttribute('data-weave-id'),
                x: e.clientX,
                y: e.clientY,
            }, '*');
        });

        window.addEventListener('message', function (e) {
            if (e.data?.type !== 'block:patch') return;

            e.data.nodes.forEach(function (n) {
                var el = document.querySelector('[data-weave-id="' + n.id + '"]');
                if (! el) return;
                el.setAttribute('style', n.style);

                // querySelector could reach into a nested child block's own text
                // element (e.g. a Column containing a Text block) — only touch one
                // that actually belongs to this node, not a descendant block.
                var textEl = Array.from(el.querySelectorAll('[data-weave-text]'))
                    .find(function (t) { return t.closest('[data-weave-id]') === el; });
                if (textEl) textEl.setAttribute('style', n.textStyle);
            });
        });

        window.addEventListener('message', function (e) {
            if (e.data?.type !== 'block:focus-text') return;

            var el = document.querySelector('[data-weave-id="' + e.data.id + '"] [data-weave-prop]');
            if (! el) return;

            el.focus();

            // Select all so the first keystroke replaces the placeholder ("Text
            // content") instead of appending to it.
            var range = document.createRange();
            range.selectNodeContents(el);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        });

        window.addEventListener('message', function (e) {
            if (e.data?.type !== 'block:highlight') return;

            document.querySelectorAll('[data-weave-id]').forEach(function (el) {
                el.style.outline = el.getAttribute('data-weave-id') === e.data.id
                    ? '2px solid #5a84d8'
                    : '';
            });
        });

        // Drop indicator is a `position:fixed` overlay, NOT inserted into the page
        // flow. An earlier version inserted a real placeholder box between blocks —
        // that shifted layout (e.g. pushed an image down) on every dragover tick,
        // which shifted the hovered rect, which flipped the computed position, which
        // moved the placeholder again: a feedback loop that looked like the image
        // was "fighting" the drop zone. An overlay never touches layout, so nothing
        // downstream of it can move during drag.
        var indicator = null;

        function ensureIndicator() {
            if (! indicator) {
                indicator = document.createElement('div');
                indicator.style.cssText = 'position:fixed;pointer-events:none;z-index:9999;border:2px dashed #5a84d8;border-radius:8px;background:rgba(90,132,216,0.08);';
                document.body.appendChild(indicator);
            }
            return indicator;
        }

        function showIndicator(rect, position) {
            var el = ensureIndicator();
            var h = 56;

            el.style.left = rect.left + 'px';
            el.style.width = rect.width + 'px';

            if (position === 'inside') {
                el.style.height = Math.max(Math.min(rect.height - 16, h), 32) + 'px';
                el.style.top = (rect.top + 8) + 'px';
            } else {
                el.style.height = h + 'px';
                el.style.top = (position === 'before' ? rect.top - h / 2 - 4 : rect.bottom - h / 2 + 4) + 'px';
            }
        }

        function hideIndicator() {
            if (indicator && indicator.parentNode) indicator.parentNode.removeChild(indicator);
            indicator = null;
        }

        var dragging = null;
        var reorderDrop = null;

        // The gap/gutter between two nested containers (e.g. the flex gap between
        // Columns) carries none of the data-weave-* attributes, so closest()
        // — which only walks UP the tree — lands on the shared ancestor (Columns
        // itself) instead of the specific Column under the pointer. When that
        // happens, snap to whichever nested container's box is closest to (x, y).
        function nearestContainer(root, x, y, exclude) {
            var candidates = Array.from(root.querySelectorAll('[data-weave-container]'))
                .filter(function (c) { return ! exclude || (c !== exclude && ! exclude.contains(c)); });
            var best = null, bestDist = Infinity;
            candidates.forEach(function (c) {
                var r = c.getBoundingClientRect();
                var dx = Math.max(r.left - x, 0, x - r.right);
                var dy = Math.max(r.top - y, 0, y - r.bottom);
                var dist = Math.hypot(dx, dy);
                if (dist < bestDist) { bestDist = dist; best = c; }
            });
            return best;
        }

        // Nearest container that owns `el` as a child (null = top-level canvas).
        // Used to scope sibling-reorder to the real parent instead of the whole
        // document, now that every block at every depth is draggable.
        function containerOf(el) {
            return el.parentElement ? el.parentElement.closest('[data-weave-container]') : null;
        }

        document.addEventListener('dragstart', function (e) {
            if (e.target.isContentEditable) { e.preventDefault(); return; }
            var el = e.target.closest('[data-weave-draggable]');
            if (! el) return;
            dragging = el;
            e.dataTransfer.effectAllowed = 'move';
            el.style.opacity = '0.4';
        });

        document.addEventListener('dragover', function (e) {
            if (! dragging) return;
            var el = e.target.closest('[data-weave-draggable]');
            var container = e.target.closest('[data-weave-container]');

            if (el && el.hasAttribute('data-weave-container') && (! container || container === el)) {
                var nested = nearestContainer(el, e.clientX, e.clientY, dragging);
                if (nested) container = nested;
            }

            // `el` is always the nearest TOP-LEVEL sibling (nested blocks like a
            // Column don't carry data-weave-draggable). `container !== el`
            // means the cursor is actually inside a nested container (e.g. a Column
            // within Columns) rather than just hovering that sibling's own padding —
            // that's the signal to reparent into it instead of reordering siblings.
            if (container && container !== el && ! dragging.contains(container)) {
                e.preventDefault();
                reorderDrop = { parentId: container.getAttribute('data-weave-id') };
                showIndicator(container.getBoundingClientRect(), 'inside');
                return;
            }

            if (! el || el === dragging) return;
            e.preventDefault();

            var rect = el.getBoundingClientRect();
            var ratio = (e.clientY - rect.top) / rect.height;

            // `el` itself can be a top-level container (Columns), which carries BOTH
            // attributes — `container` above resolves to the same node, not a deeper
            // one, so the branch above never fires while hovering el's own surface.
            // Middle band of el's own rect = drop inside it; top/bottom bands = reorder.
            if (el.hasAttribute('data-weave-container') && ratio > 0.25 && ratio < 0.75) {
                reorderDrop = { parentId: el.getAttribute('data-weave-id') };
                showIndicator(rect, 'inside');
                return;
            }

            var position = ratio < 0.5 ? 'before' : 'after';
            reorderDrop = { target: el, position: position };
            showIndicator(rect, position);
        });

        document.addEventListener('dragend', function () {
            if (! dragging) return;
            dragging.style.opacity = '';
            hideIndicator();

            if (reorderDrop && reorderDrop.parentId) {
                parent.postMessage({
                    type: 'block:move',
                    id: dragging.getAttribute('data-weave-id'),
                    parentId: reorderDrop.parentId,
                }, '*');
            } else if (reorderDrop) {
                var draggedId = dragging.getAttribute('data-weave-id');

                // Every block at every depth is draggable now, so a plain document-wide
                // query would mix siblings from unrelated columns/containers into one
                // flat list. Scope to whichever container actually owns the drop target.
                var scope = containerOf(reorderDrop.target);
                var ids = Array.from((scope || document).querySelectorAll('[data-weave-draggable]'))
                    .filter(function (el) { return containerOf(el) === scope; })
                    .map(function (el) { return el.getAttribute('data-weave-id'); })
                    .filter(function (id) { return id !== draggedId; });

                var targetId = reorderDrop.target.getAttribute('data-weave-id');
                var index = ids.indexOf(targetId) + (reorderDrop.position === 'after' ? 1 : 0);
                ids.splice(index, 0, draggedId);

                parent.postMessage({ type: 'block:reorder', ids: ids }, '*');
            }

            dragging = null;
            reorderDrop = null;
        });

        // Dragging a new block in FROM the palette (parent window). That dragstart
        // happened outside this document, so `dragging` above stays null the whole
        // time — this is how we tell "new block from palette" apart from "reordering
        // an existing block", which share the same dragover/drop events in here.
        function isPaletteDrag(e) {
            return ! dragging && e.dataTransfer.types.indexOf('text/plain') !== -1;
        }

        function computeInsertion(e) {
            var el = e.target.closest('[data-weave-draggable]');
            var container = e.target.closest('[data-weave-container]');

            if (el && el.hasAttribute('data-weave-container') && (! container || container === el)) {
                var nested = nearestContainer(el, e.clientX, e.clientY);
                if (nested) container = nested;
            }

            // Same "nested deeper than the top-level sibling" signal used for
            // internal reorder above — drop straight into that container's children.
            if (container && container !== el) {
                return { parentId: container.getAttribute('data-weave-id'), rect: container.getBoundingClientRect(), position: 'inside' };
            }

            var wrappers = Array.from(document.querySelectorAll('[data-weave-draggable]'));
            var canvas = document.querySelector('[data-weave-canvas]');

            if (! wrappers.length) {
                return { index: 0, rect: canvas ? canvas.getBoundingClientRect() : null, position: 'inside' };
            }

            if (! el) {
                var first = wrappers[0], last = wrappers[wrappers.length - 1];
                if (e.clientY < first.getBoundingClientRect().top) {
                    return { index: 0, rect: first.getBoundingClientRect(), position: 'before' };
                }
                return { index: wrappers.length, rect: last.getBoundingClientRect(), position: 'after' };
            }

            var rect = el.getBoundingClientRect();
            var ratio = (e.clientY - rect.top) / rect.height;

            if (el.hasAttribute('data-weave-container') && ratio > 0.25 && ratio < 0.75) {
                return { parentId: el.getAttribute('data-weave-id'), rect: rect, position: 'inside' };
            }

            var before = ratio < 0.5;

            return { index: wrappers.indexOf(el) + (before ? 0 : 1), rect: rect, position: before ? 'before' : 'after' };
        }

        document.addEventListener('dragenter', function (e) {
            if (! isPaletteDrag(e)) return;
            e.preventDefault();
        });

        document.addEventListener('dragover', function (e) {
            if (! isPaletteDrag(e)) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            var result = computeInsertion(e);
            if (result.rect) showIndicator(result.rect, result.position);
        });

        document.addEventListener('drop', function (e) {
            if (! isPaletteDrag(e)) return;
            e.preventDefault();
            var blockType = e.dataTransfer.getData('text/plain');
            var result = computeInsertion(e);
            hideIndicator();
            if (blockType) parent.postMessage({ type: 'block:add', blockType: blockType, index: result.index, parentId: result.parentId || null }, '*');
        });

        document.addEventListener('dragleave', function (e) {
            if (dragging) return;
            if (! e.relatedTarget) hideIndicator();
        });

        // Inline text editing directly on the canvas — click into a heading/paragraph
        // and type. Commits on blur, not per-keystroke, since every prop change
        // debounce-reloads this whole iframe (would yank focus mid-typing otherwise).
        document.addEventListener('blur', function (e) {
            var el = e.target;
            if (! el.hasAttribute || ! el.hasAttribute('data-weave-prop')) return;
            var wrapper = el.closest('[data-weave-id]');
            if (! wrapper) return;
            parent.postMessage({
                type: 'block:prop',
                id: wrapper.getAttribute('data-weave-id'),
                prop: el.getAttribute('data-weave-prop'),
                value: el.innerText.replace(/\n+$/, ''),
            }, '*');
        }, true);

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            var el = e.target.closest('h1, h2, h3, h4, h5, h6, [data-weave-label]');
            if (el && el.hasAttribute('data-weave-prop')) {
                e.preventDefault();
                el.blur();
            }
        });

        // Unlike heading/text (always contenteditable), a button's label sits
        // inside a clickable <a>/<button> — single click has to keep selecting the
        // block, so editing only turns on for the label span, and only on dblclick.
        // The existing blur listener above already posts `block:prop` for any
        // `data-weave-prop` element, so no extra wiring needed there — the
        // label prop change forces a full re-render (see Canvas.vue structuralKey),
        // which naturally re-renders the label back to its plain, non-editable form.
        document.addEventListener('dblclick', function (e) {
            var label = e.target.closest('[data-weave-label]');
            if (! label) return;
            e.preventDefault();

            label.setAttribute('contenteditable', 'true');
            label.setAttribute('data-weave-prop', 'label');
            label.focus();

            var range = document.createRange();
            range.selectNodeContents(label);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        });
    </script>
@endif
