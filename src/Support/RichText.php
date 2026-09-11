<?php

namespace Estouai\Weave\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichText
{
    protected const ALLOWED_TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'ul', 'ol', 'li', 'blockquote', 'a', 'code', 'pre'];
    protected const URI_ATTRIBUTES = ['href'];

    public static function render(mixed $value): string
    {
        if (is_array($value)) {
            return e(static::plainText($value));
        }

        if (! is_string($value) || $value === '') {
            return '';
        }

        if (! preg_match('/^\s*<(p|br|strong|b|em|i|u|s|sub|sup|ul|ol|li|blockquote|a|code|pre)\b/i', $value)) {
            return nl2br(e($value), false);
        }

        return static::sanitize($value);
    }

    public static function plainText(mixed $value): string
    {
        if (is_string($value)) {
            return trim(strip_tags($value));
        }

        if (! is_array($value)) {
            return '';
        }

        return trim(collect($value)->map(function ($item) {
            return is_array($item)
                ? trim(($item['text'] ?? '').' '.static::plainText($item['content'] ?? []))
                : static::plainText($item);
        })->filter()->implode(' '));
    }

    protected static function sanitize(string $html): string
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $document->loadHTML('<!doctype html><html><body><div data-weave-rich-text>'.$html.'</div></body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $wrapper = $document->getElementsByTagName('div')->item(0);
        if (! $wrapper) {
            return '';
        }

        static::sanitizeNode($wrapper);

        return collect(iterator_to_array($wrapper->childNodes))
            ->map(fn (DOMNode $node) => $document->saveHTML($node))
            ->implode('');
    }

    protected static function sanitizeNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (in_array($tag, ['script', 'style'], true)) {
                    $child->parentNode?->removeChild($child);
                    continue;
                }

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    static::sanitizeNode($child);
                    static::unwrap($child);
                    continue;
                }

                static::sanitizeAttributes($child);
            }

            static::sanitizeNode($child);
        }
    }

    protected static function sanitizeAttributes(DOMElement $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);

            if ($element->tagName !== 'a' || ! in_array($name, ['href', 'target', 'rel'], true)) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if (in_array($name, self::URI_ATTRIBUTES, true) && ! static::allowedUrl($attribute->value)) {
                $element->removeAttributeNode($attribute);
            }
        }

        if ($element->tagName === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    protected static function allowedUrl(string $url): bool
    {
        $url = trim(strtolower($url));

        return $url === ''
            || str_starts_with($url, '#')
            || str_starts_with($url, '/')
            || preg_match('/^(https?|mailto|tel):/', $url);
    }

    protected static function unwrap(DOMElement $element): void
    {
        while ($element->firstChild) {
            $element->parentNode?->insertBefore($element->firstChild, $element);
        }

        $element->parentNode?->removeChild($element);
    }
}
