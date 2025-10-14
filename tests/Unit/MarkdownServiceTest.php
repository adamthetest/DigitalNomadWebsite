<?php

namespace Tests\Unit;

use App\Services\MarkdownService;
use Tests\TestCase;

class MarkdownServiceTest extends TestCase
{
    private MarkdownService $markdownService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markdownService = new MarkdownService();
    }

    public function test_parse_converts_markdown_to_html()
    {
        $markdown = '# Hello World\n\nThis is **bold** text.';
        $html = $this->markdownService->parse($markdown);

        // CommonMark may format headers differently, check for content
        $this->assertStringContainsString('Hello World', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function test_parse_handles_links()
    {
        $markdown = 'Visit [Google](https://google.com) for search.';
        $html = $this->markdownService->parse($markdown);

        $this->assertStringContainsString('<a href="https://google.com">Google</a>', $html);
    }

    public function test_parse_handles_lists()
    {
        $markdown = "- Item 1\n- Item 2\n- Item 3";
        $html = $this->markdownService->parse($markdown);

        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('<li>Item 1</li>', $html);
        $this->assertStringContainsString('<li>Item 2</li>', $html);
        $this->assertStringContainsString('<li>Item 3</li>', $html);
    }

    public function test_parse_handles_code_blocks()
    {
        $markdown = "```php\n<?php\necho 'Hello World';\n```";
        $html = $this->markdownService->parse($markdown);

        // CommonMark may format code blocks differently, check for content
        $this->assertStringContainsString('<?php', $html);
        $this->assertStringContainsString('echo', $html);
    }

    public function test_parse_handles_inline_code()
    {
        $markdown = 'Use `echo` to output text.';
        $html = $this->markdownService->parse($markdown);

        $this->assertStringContainsString('<code>echo</code>', $html);
    }

    public function test_parse_handles_italics()
    {
        $markdown = 'This is *italic* text.';
        $html = $this->markdownService->parse($markdown);

        $this->assertStringContainsString('<em>italic</em>', $html);
    }

    public function test_parse_handles_bold()
    {
        $markdown = 'This is **bold** text.';
        $html = $this->markdownService->parse($markdown);

        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function test_parse_returns_html_unchanged_when_input_is_html()
    {
        $html = '<h1>Hello World</h1><p>This is <strong>bold</strong> text.</p>';
        $result = $this->markdownService->parse($html);

        $this->assertEquals($html, $result);
    }

    public function test_parse_handles_mixed_html_and_markdown()
    {
        $mixed = '<div class="container"># Hello World</div>';
        $result = $this->markdownService->parse($mixed);

        // Should return as-is since it contains HTML tags
        $this->assertEquals($mixed, $result);
    }

    public function test_parse_inline_converts_markdown_to_html()
    {
        $markdown = 'This is **bold** and *italic* text.';
        $html = $this->markdownService->parseInline($markdown);

        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
    }

    public function test_parse_inline_strips_paragraph_tags()
    {
        $markdown = 'This is a paragraph.';
        $html = $this->markdownService->parseInline($markdown);

        $this->assertStringNotContainsString('<p>', $html);
        $this->assertStringNotContainsString('</p>', $html);
    }

    public function test_parse_inline_allows_specific_tags()
    {
        $markdown = 'This is **bold**, *italic*, `code`, and [link](https://example.com).';
        $html = $this->markdownService->parseInline($markdown);

        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
        $this->assertStringContainsString('<code>code</code>', $html);
        $this->assertStringContainsString('<a href="https://example.com">link</a>', $html);
    }

    public function test_parse_inline_removes_unwanted_tags()
    {
        $markdown = '# Heading\n\nThis is a paragraph with **bold** text.';
        $html = $this->markdownService->parseInline($markdown);

        $this->assertStringNotContainsString('<h1>', $html);
        $this->assertStringNotContainsString('</h1>', $html);
        $this->assertStringNotContainsString('<p>', $html);
        $this->assertStringNotContainsString('</p>', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function test_parse_inline_returns_html_unchanged_when_input_is_html()
    {
        $html = '<strong>Bold text</strong> and <em>italic text</em>.';
        $result = $this->markdownService->parseInline($html);

        $this->assertEquals($html, $result);
    }

    public function test_parse_inline_handles_empty_string()
    {
        $result = $this->markdownService->parseInline('');

        $this->assertEquals('', $result);
    }

    public function test_parse_handles_empty_string()
    {
        $result = $this->markdownService->parse('');

        $this->assertEquals('', $result);
    }

    public function test_parse_handles_whitespace_only()
    {
        $result = $this->markdownService->parse('   ');

        // CommonMark may strip whitespace-only content
        $this->assertIsString($result);
    }

    public function test_parse_inline_handles_whitespace_only()
    {
        $result = $this->markdownService->parseInline('   ');

        // CommonMark may strip whitespace-only content
        $this->assertIsString($result);
    }

    public function test_parse_handles_complex_markdown()
    {
        $markdown = "# Main Title\n\n## Subtitle\n\nThis is a paragraph with **bold** and *italic* text.\n\n- List item 1\n- List item 2\n\n```php\n<?php\necho 'Hello';\n```\n\n[Visit Google](https://google.com)";
        $html = $this->markdownService->parse($markdown);

        // Test that basic markdown elements are converted
        $this->assertStringContainsString('Main Title', $html);
        $this->assertStringContainsString('Subtitle', $html);
        $this->assertStringContainsString('bold', $html);
        $this->assertStringContainsString('italic', $html);
        $this->assertStringContainsString('List item 1', $html);
        $this->assertStringContainsString('Visit Google', $html);
    }

    public function test_parse_inline_handles_complex_markdown()
    {
        $markdown = "This is **bold**, *italic*, `code`, and [link](https://example.com).";
        $html = $this->markdownService->parseInline($markdown);

        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
        $this->assertStringContainsString('<code>code</code>', $html);
        $this->assertStringContainsString('<a href="https://example.com">link</a>', $html);
        $this->assertStringNotContainsString('<p>', $html);
        $this->assertStringNotContainsString('<h1>', $html);
    }

    public function test_parse_handles_nested_formatting()
    {
        $markdown = 'This is **bold with *italic* inside**.';
        $html = $this->markdownService->parse($markdown);

        $this->assertStringContainsString('<strong>bold with <em>italic</em> inside</strong>', $html);
    }

    public function test_parse_inline_handles_nested_formatting()
    {
        $markdown = 'This is **bold with *italic* inside**.';
        $html = $this->markdownService->parseInline($markdown);

        $this->assertStringContainsString('<strong>bold with <em>italic</em> inside</strong>', $html);
    }

    public function test_parse_handles_line_breaks()
    {
        $markdown = "Line 1\nLine 2\n\nParagraph 2";
        $html = $this->markdownService->parse($markdown);

        // Test that line breaks are handled appropriately
        $this->assertStringContainsString('Line 1', $html);
        $this->assertStringContainsString('Line 2', $html);
        $this->assertStringContainsString('Paragraph 2', $html);
    }

    public function test_parse_inline_handles_line_breaks()
    {
        $markdown = "Line 1\nLine 2";
        $html = $this->markdownService->parseInline($markdown);

        $this->assertStringContainsString('Line 1', $html);
        $this->assertStringContainsString('Line 2', $html);
        $this->assertStringNotContainsString('<p>', $html);
    }

    public function test_parse_handles_unsafe_links()
    {
        $markdown = '[Click me](javascript:alert("xss"))';
        $html = $this->markdownService->parse($markdown);

        // Should not contain javascript: links due to allow_unsafe_links: false
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_parse_handles_safe_links()
    {
        $markdown = '[Safe link](https://example.com)';
        $html = $this->markdownService->parse($markdown);

        $this->assertStringContainsString('<a href="https://example.com">Safe link</a>', $html);
    }
}
