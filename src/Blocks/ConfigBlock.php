<?php

namespace Estouai\Weave\Blocks;

use Estouai\Weave\Support\BlockIcon;
use Illuminate\Support\Str;

// A block declared entirely from config/weave.php's `custom_blocks` array —
// for the common case (a Blade view + a props schema, no bespoke PHP behaviour).
// Anything that needs real logic (Columns' dynamic children, for example) still
// belongs in its own Block subclass instead.
class ConfigBlock extends Block
{
    public function __construct(protected string $type, protected array $config) {}

    public function type(): string
    {
        return $this->type;
    }

    public function label(): string
    {
        return $this->config['label'] ?? Str::headline($this->type);
    }

    public function category(): string
    {
        return $this->config['category'] ?? 'custom';
    }

    public function icon(): BlockIcon|string
    {
        return $this->config['icon'] ?? parent::icon();
    }

    public function view(): string
    {
        return $this->config['view'];
    }

    public function allowsChildren(): bool
    {
        return $this->config['allows_children'] ?? parent::allowsChildren();
    }

    public function propsSchema(): array
    {
        return $this->config['props'] ?? [];
    }

    public function defaultProps(): array
    {
        return $this->config['defaults'] ?? parent::defaultProps();
    }
}
