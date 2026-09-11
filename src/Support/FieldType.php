<?php

namespace Estouai\Weave\Support;

// Fieldtype names usable in a props schema (`field.type`). Curated to what
// the addon's blocks use plus common Statamic ones — any other fieldtype can
// still be passed as a plain string.
enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Integer = 'integer';
    case Select = 'select';
    case Toggle = 'toggle';
    case Range = 'range';
    case Color = 'color';
    case Assets = 'assets';
    case ArrayField = 'array';
    case Grid = 'grid';
    case Entries = 'entries';
    case Terms = 'terms';
}
