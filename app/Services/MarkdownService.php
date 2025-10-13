<?php

namespace App\Services;

use League\CommonMark\CommonMarkConverter;

class MarkdownService
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);
    }

    public function parse(string $content): string
    {
        // If content is already HTML (contains HTML tags), return as-is
        if ($this->isHtml($content)) {
            return $content;
        }
        
        // Otherwise, parse as Markdown
        return $this->converter->convert($content)->getContent();
    }

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

    private function isHtml(string $content): bool
    {
        // Check if content contains HTML tags
        return $content !== strip_tags($content);
    }
}
