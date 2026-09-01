<?php

namespace Tests\Unit;

use App\Support\Html\ImageRowFormatter;
use PHPUnit\Framework\TestCase;

class ImageRowFormatterTest extends TestCase
{
    public function test_two_consecutive_images_are_wrapped_in_a_row(): void
    {
        $html = '<p>Intro</p><img src="a.jpg"><img src="b.jpg"><p>Outro</p>';

        $result = ImageRowFormatter::apply($html);

        $this->assertStringContainsString('<div class="image-row" data-count="2">', $result);
        $this->assertStringContainsString('<img src="a.jpg">', $result);
        $this->assertStringContainsString('<img src="b.jpg">', $result);
        $this->assertStringContainsString('<p>Intro</p>', $result);
        $this->assertStringContainsString('<p>Outro</p>', $result);
    }

    public function test_images_in_separate_paragraphs_are_grouped(): void
    {
        $html = '<p><img src="a.jpg"></p><p><img src="b.jpg"></p><p><img src="c.jpg"></p>';

        $result = ImageRowFormatter::apply($html);

        $this->assertStringContainsString('data-count="3"', $result);
        $this->assertSame(1, substr_count($result, 'image-row'));
    }

    public function test_single_image_is_left_untouched(): void
    {
        $html = '<p>Text</p><img src="a.jpg"><p>More text</p>';

        $result = ImageRowFormatter::apply($html);

        $this->assertStringNotContainsString('image-row', $result);
    }

    public function test_images_separated_by_text_are_not_grouped(): void
    {
        $html = '<img src="a.jpg"><p>Between</p><img src="b.jpg">';

        $result = ImageRowFormatter::apply($html);

        $this->assertStringNotContainsString('image-row', $result);
    }

    public function test_linked_images_stay_clickable_inside_the_row(): void
    {
        $html = '<p><a href="/x"><img src="a.jpg"></a></p><p><a href="/y"><img src="b.jpg"></a></p>';

        $result = ImageRowFormatter::apply($html);

        $this->assertStringContainsString('image-row', $result);
        $this->assertStringContainsString('<a href="/x"><img src="a.jpg"></a>', $result);
        $this->assertStringContainsString('<a href="/y"><img src="b.jpg"></a>', $result);
    }

    public function test_unicode_content_survives(): void
    {
        $html = '<p>زراعة الشعر في تركيا</p><p><img src="a.jpg"></p><p><img src="b.jpg"></p>';

        $result = ImageRowFormatter::apply($html);

        $this->assertStringContainsString('زراعة الشعر في تركيا', $result);
        $this->assertStringContainsString('image-row', $result);
    }

    public function test_blank_html_is_returned_unchanged(): void
    {
        $this->assertSame('', ImageRowFormatter::apply(null));
        $this->assertSame('', ImageRowFormatter::apply(''));
        $this->assertSame('<p>No images</p>', ImageRowFormatter::apply('<p>No images</p>'));
    }
}
