@props(['props' => []])

@php
    $form = \Statamic\Facades\Form::find($props['form'] ?? null);
@endphp

@if ($form)
    @php
        $handle = $form->handle();
        $fields = $form->blueprint()->fields()->all();
        // $errors is normally auto-shared by ShareErrorsFromSession, but isn't
        // present when this view renders outside a full HTTP request (e.g. the
        // block editor's own preview render, tinker).
        $fieldErrors = (isset($errors) ? $errors : new \Illuminate\Support\ViewErrorBag())->getBag('form.'.$handle);
        $success = session('form.'.$handle.'.success');
        $inputClass = 'rounded-md border border-black/15 px-4 py-2.5 font-caption text-sm text-ink focus:border-navy focus:outline-none';
    @endphp

    <div class="flex w-full flex-col gap-6">
        @if (($props['show_title'] ?? false) && ($props['title'] ?? ''))
            <p class="font-display text-h3 leading-tight tracking-tight text-ink">{{ $props['title'] }}</p>
        @endif

        @if ($success)
            <p class="rounded-md bg-mint/10 px-4 py-3 font-caption text-sm text-mint">{{ $success }}</p>
        @endif

        <form method="POST" action="{{ route('statamic.forms.submit', $handle) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            {{-- Statamic's silent spam trap: real visitors leave it blank, bots fill it in. --}}
            <input type="text" name="{{ $form->honeypot() }}" class="hidden" tabindex="-1" autocomplete="off">

            @foreach ($fields as $field)
                @php
                    $config = $field->config();
                    $fieldHandle = $field->handle();
                    $type = $config['type'] ?? 'text';
                    $inputId = "form_{$handle}_{$fieldHandle}";
                @endphp

                {{-- `hidden` type, or a CP-only `read_only`/`hidden` visibility (e.g.
                     industry_enquiry's/member_enquiry's context fields, meant to be
                     set by whatever page embeds the form, not filled in by a visitor)
                     — post through as hidden rather than showing an empty box. --}}
                @if ($type === 'hidden' || in_array($config['visibility'] ?? null, ['hidden', 'read_only']))
                    <input type="hidden" name="{{ $fieldHandle }}" value="{{ old($fieldHandle, $config['default'] ?? '') }}">
                    @continue
                @endif

                <div class="flex flex-col gap-1.5 {{ ($config['width'] ?? 100) > 50 ? 'sm:col-span-2' : '' }}">
                    <label for="{{ $inputId }}" class="font-caption text-sm text-ink">
                        {{ $config['display'] ?? $fieldHandle }}@if($config['required'] ?? false)<span aria-hidden="true"> *</span>@endif
                    </label>

                    @switch($type)
                        @case('textarea')
                            <textarea
                                id="{{ $inputId }}"
                                name="{{ $fieldHandle }}"
                                rows="4"
                                placeholder="{{ $config['placeholder'] ?? '' }}"
                                @if($config['required'] ?? false) required @endif
                                class="{{ $inputClass }}"
                            >{{ old($fieldHandle) }}</textarea>
                            @break

                        @case('select')
                            <select
                                id="{{ $inputId }}"
                                name="{{ $fieldHandle }}"
                                @if($config['required'] ?? false) required @endif
                                class="{{ $inputClass }}"
                            >
                                @foreach (($config['options'] ?? []) as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected(old($fieldHandle) === $optionValue)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                            @break

                        @default
                            <input
                                id="{{ $inputId }}"
                                type="{{ $config['input_type'] ?? 'text' }}"
                                name="{{ $fieldHandle }}"
                                value="{{ old($fieldHandle) }}"
                                placeholder="{{ $config['placeholder'] ?? '' }}"
                                @if($config['required'] ?? false) required @endif
                                class="{{ $inputClass }}"
                            >
                    @endswitch

                    @if ($fieldErrors->has($fieldHandle))
                        <p class="font-caption text-xs text-red-600">{{ $fieldErrors->first($fieldHandle) }}</p>
                    @endif
                </div>
            @endforeach

            <div class="sm:col-span-2">
                <x-button variant="dark" type="submit">Submit</x-button>
            </div>
        </form>
    </div>
@endif
