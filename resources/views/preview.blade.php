@props(['blocks' => [], 'editing' => false])

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{ Vite::fonts() }}
    @vite(config('weave.preview.vite_entries', ['resources/css/app.css']))
</head>
<body class="overflow-x-clip bg-surface font-sans text-ink antialiased">
    <div style="position: relative; background-color: #013983; min-height: 6.5rem;">
        @include(config('weave.preview.nav_partial'))
    </div>

    <div class="prose prose-lg w-full max-w-none px-container py-2xl font-sans text-ink prose-headings:font-display" @if ($editing) data-weave-canvas @endif>
        <x-weave::render :blocks="$blocks" :editing="$editing" />
    </div>

    @include(config('weave.preview.footer_partial'))
</body>
</html>
