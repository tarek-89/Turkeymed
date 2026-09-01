<?php

namespace App\Support\Html;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Lays consecutive images in CMS body HTML out side by side.
 *
 * Editors simply add images one after another in the rich editor; any run of
 * two or more adjacent images (bare <img>, linked <a><img></a>, or an image
 * alone in a <p>/<figure>) is wrapped in a `.image-row` flex container that
 * renders the images next to each other and stacks them on small screens.
 * A single image, or images separated by text, are left untouched.
 */
class ImageRowFormatter
{
    public static function apply(?string $html): string
    {
        if (blank($html) || ! str_contains($html, '<img')) {
            return (string) $html;
        }

        $dom = new DOMDocument;

        $loaded = @$dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="__image-row-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR,
        );

        if (! $loaded) {
            return (string) $html;
        }

        $root = $dom->getElementById('__image-row-root');

        if (! $root instanceof DOMElement) {
            return (string) $html;
        }

        static::wrapImageRuns($dom, $root);

        $output = '';

        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }

    /**
     * Walk the element tree and wrap every run of 2+ adjacent image units.
     */
    protected static function wrapImageRuns(DOMDocument $dom, DOMElement $element): void
    {
        // Recurse first so nested containers (e.g. WordPress wrappers) are
        // processed before their parents' child lists are examined.
        foreach (iterator_to_array($element->childNodes) as $child) {
            if ($child instanceof DOMElement && ! static::isImageUnit($child)) {
                static::wrapImageRuns($dom, $child);
            }
        }

        $run = [];

        foreach ([...iterator_to_array($element->childNodes), null] as $child) {
            if ($child !== null && static::isSkippableWhitespace($child)) {
                continue;
            }

            if ($child instanceof DOMElement && static::isImageUnit($child)) {
                $run[] = $child;

                continue;
            }

            static::wrapRun($dom, $element, $run);
            $run = [];
        }
    }

    /**
     * @param  list<DOMElement>  $run
     */
    protected static function wrapRun(DOMDocument $dom, DOMElement $parent, array $run): void
    {
        if (count($run) < 2) {
            return;
        }

        $row = $dom->createElement('div');
        $row->setAttribute('class', 'image-row');

        $parent->insertBefore($row, $run[0]);

        foreach ($run as $unit) {
            foreach (static::extractImages($unit) as $image) {
                $row->appendChild($image);
            }

            // A bare <img>/<a> unit is itself moved into the row above — only
            // remove leftover wrappers (e.g. an emptied <p>) still outside it.
            if ($unit->parentNode !== null && $unit->parentNode !== $row) {
                $unit->parentNode->removeChild($unit);
            }
        }

        $row->setAttribute('data-count', (string) $row->childNodes->length);
    }

    /**
     * An element that represents exactly an image: a bare <img>, a link whose
     * only content is an image, or a <p>/<figure>/<div> containing only images.
     */
    protected static function isImageUnit(DOMElement $element): bool
    {
        $tag = strtolower($element->tagName);

        if ($tag === 'img') {
            return true;
        }

        if (! in_array($tag, ['a', 'p', 'figure', 'div'], true)) {
            return false;
        }

        $containsImage = false;

        foreach ($element->childNodes as $child) {
            if (static::isSkippableWhitespace($child)) {
                continue;
            }

            if ($child instanceof DOMElement && strtolower($child->tagName) === 'br') {
                continue;
            }

            if (! $child instanceof DOMElement || ! static::isImageUnit($child)) {
                return false;
            }

            $containsImage = true;
        }

        return $containsImage;
    }

    /**
     * The images (or linked images) inside a unit, unwrapped from paragraphs.
     *
     * @return list<DOMElement>
     */
    protected static function extractImages(DOMElement $unit): array
    {
        $tag = strtolower($unit->tagName);

        // Keep links intact so a linked image stays clickable.
        if (in_array($tag, ['img', 'a'], true)) {
            return [$unit];
        }

        $images = [];

        foreach (iterator_to_array($unit->childNodes) as $child) {
            if ($child instanceof DOMElement && static::isImageUnit($child)) {
                $images = [...$images, ...static::extractImages($child)];
            }
        }

        return $images;
    }

    protected static function isSkippableWhitespace(DOMNode $node): bool
    {
        return $node->nodeType === XML_TEXT_NODE && trim((string) $node->nodeValue) === '';
    }
}
