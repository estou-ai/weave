<?php

namespace Estouai\Weave\Blocks;

use Statamic\Facades\Form;

class FormBlock extends Block
{
    public function type(): string
    {
        return 'form';
    }

    public function label(): string
    {
        return 'Form';
    }

    public function category(): string
    {
        return 'sections';
    }

    public function icon(): string
    {
        return 'forms';
    }

    public function view(): string
    {
        return 'weave::blocks.form';
    }

    public function propsSchema(): array
    {
        return [
            ['handle' => 'form', 'field' => [
                'type' => 'select',
                'display' => 'Form',
                'options' => static::formOptions(),
                'placeholder' => 'Select a form',
                'instructions' => 'Which Statamic form to render — its fields and container come from that form\'s own blueprint.',
                'validate' => ['required'],
            ]],
            ['handle' => 'show_title', 'field' => ['type' => 'toggle', 'display' => 'Show title']],
            ['handle' => 'title', 'field' => ['type' => 'text', 'display' => 'Title', 'instructions' => 'Shown above the form when "Show title" is on.']],
        ];
    }

    public function defaultProps(): array
    {
        return ['show_title' => false, 'title' => ''];
    }

    protected static function formOptions(): array
    {
        return Form::all()->mapWithKeys(fn ($form) => [$form->handle() => $form->title()])->all();
    }
}
