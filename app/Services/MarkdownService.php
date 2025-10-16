<?php

namespace App\Services;

use League\CommonMark\CommonMarkConverter;

/**
 * Markdown Service
 *
 * Handles parsing of Markdown content to HTML with support for inline parsing
 * and automatic detection of existing HTML content.
 */
class MarkdownService
{
    /**
     * The CommonMark converter instance.
     */
    private CommonMarkConverter $converter;

    /**
     * Create a new MarkdownService instance.
     */
    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Parse Markdown content to HTML.
     *
     * @param  string  $content  The Markdown content to parse
     * @return string The parsed HTML content
     */
    public function parse(string $content): string
    {
        // If content is already HTML (contains HTML tags), return as-is
        if ($this->isHtml($content)) {
            return $content;
        }

        // Otherwise, parse as Markdown
        return $this->converter->convert($content)->getContent();
    }

    /**
     * Parse Markdown content to inline HTML (without paragraph tags).
     *
     * @param  string  $content  The Markdown content to parse
     * @return string The parsed inline HTML content
     */
    public function parseInline(string $content): string
    {
        // If content is already HTML (contains HTML tags), return as-is
        if ($this->isHtml($content)) {
            return $content;
        }

        // Otherwise, parse as Markdown and strip paragraph tags for inline use
        $html = $this->converter->convert($content)->getContent();

        return trim(strip_tags($html, '<strong><em><code><a>'));
    }

    /**
     * Check if content contains HTML tags.
     *
     * @param  string  $content  The content to check
     * @return bool True if content contains HTML tags, false otherwise
     */
    private function isHtml(string $content): bool
    {
        // Check if content contains HTML tags
        return $content !== strip_tags($content);
    }
}
