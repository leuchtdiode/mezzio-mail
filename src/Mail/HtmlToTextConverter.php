<?php
declare(strict_types=1);

namespace Mail\Mail;

/**
 * Creates the plain text alternative of an html mail body
 */
readonly class HtmlToTextConverter
{
	public function convert(string $html): string
	{
		$text = preg_replace('{<(head|style|script)\b.*?</\1\s*>}is', '', $html);

		$text = preg_replace_callback(
			'{<a\b[^>]*?\bhref\s*=\s*(["\'])(.*?)\1[^>]*>(.*?)</a\s*>}is',
			fn(array $matches) => $this->convertLink($matches[2], $matches[3]),
			$text
		);

		$text = preg_replace('{<br\b[^>]*>}i', "\n", $text);
		$text = preg_replace('{<li\b[^>]*>}i', "\n- ", $text);
		$text = preg_replace('{</(p|div|tr|table|h[1-6]|ul|ol)\s*>}i', "\n", $text);

		$text = strip_tags($text);
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = str_replace("\u{00A0}", ' ', $text);

		$text = preg_replace('{[ \t]+}', ' ', $text);
		$text = preg_replace('{^ +| +$}m', '', $text);
		$text = preg_replace('{\n{3,}}', "\n\n", $text);

		return trim($text);
	}

	private function convertLink(string $href, string $label): string
	{
		$href  = html_entity_decode(trim($href), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$label = trim(strip_tags($label));

		if (
			$href === ''
			|| $href === html_entity_decode($label, ENT_QUOTES | ENT_HTML5, 'UTF-8')
			|| preg_match('{^(mailto:|tel:|#|cid:)}i', $href)
		)
		{
			return $label;
		}

		if ($label === '')
		{
			return $href;
		}

		return $label . ' (' . $href . ')';
	}
}
