<?php
namespace MailTest\Mail;

use Mail\Mail\HtmlToTextConverter;
use Testing\BaseTestCase;

class HtmlToTextConverterTest extends BaseTestCase
{
	private HtmlToTextConverter $converter;

	public function setUp(): void
	{
		parent::setUp();

		$this->converter = $this->getInstance(HtmlToTextConverter::class);
	}

	public function test_head_style_and_script_are_removed()
	{
		$text = $this->converter->convert(
			'<html><head><title>Title</title><style>p { color: red; }</style></head>'
			. '<body><script>alert(1);</script><p>Hello</p></body></html>'
		);

		$this->assertEquals('Hello', $text);
	}

	public function test_paragraphs_and_line_breaks_become_new_lines()
	{
		$text = $this->converter->convert('<p>First</p><p>Second<br>Third<br />Fourth</p>');

		$this->assertEquals("First\nSecond\nThird\nFourth", $text);
	}

	public function test_link_keeps_its_url()
	{
		$text = $this->converter->convert('<p>Click <a href="https://example.com/a?b=1&amp;c=2">here</a></p>');

		$this->assertEquals('Click here (https://example.com/a?b=1&c=2)', $text);
	}

	public function test_link_url_is_not_repeated_when_it_equals_the_label()
	{
		$text = $this->converter->convert('<a href="https://example.com">https://example.com</a>');

		$this->assertEquals('https://example.com', $text);
	}

	public function test_mailto_and_anchor_links_only_keep_their_label()
	{
		$text = $this->converter->convert(
			'<a href="mailto:info@example.com">Write us</a> or <a href="#top">go up</a>'
		);

		$this->assertEquals('Write us or go up', $text);
	}

	public function test_link_without_label_shows_its_url()
	{
		$text = $this->converter->convert('<a href="https://example.com"><img src="cid:logo"></a>');

		$this->assertEquals('https://example.com', $text);
	}

	public function test_list_items_get_a_dash()
	{
		$text = $this->converter->convert('<p>Items:</p><ul><li>One</li><li>Two</li></ul>');

		$this->assertEquals("Items:\n\n- One\n- Two", $text);
	}

	public function test_entities_are_decoded()
	{
		$text = $this->converter->convert('<p>Tom&nbsp;&amp;&nbsp;Jerry &quot;&uuml;&quot; &#39;x&#39;</p>');

		$this->assertEquals('Tom & Jerry "ü" \'x\'', $text);
	}

	public function test_layout_whitespace_is_collapsed()
	{
		$text = $this->converter->convert(
			"<table>\n\t<tr>\n\t\t<td>\n\t\t\tHello    World\n\t\t</td>\n\t</tr>\n\n\n\n"
			. "\t<tr>\n\t\t<td>Bye</td>\n\t</tr>\n</table>"
		);

		$this->assertEquals("Hello World\n\nBye", $text);
	}

	public function test_empty_html_returns_empty_text()
	{
		$this->assertEquals('', $this->converter->convert(''));
		$this->assertEquals('', $this->converter->convert('<html><head><style>a{}</style></head><body> </body></html>'));
	}
}
