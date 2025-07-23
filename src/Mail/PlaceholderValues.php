<?php
declare(strict_types=1);

namespace Mail\Mail;

interface PlaceholderValues
{
	public function asArray(): array;
}