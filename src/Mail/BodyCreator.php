<?php
declare(strict_types=1);

namespace Mail\Mail;

use Laminas\View\Model\ViewModel;
use Mezzio\Template\TemplateRendererInterface;

readonly class BodyCreator
{
	public function __construct(
		private TemplateRendererInterface $phpRenderer
	)
	{
	}

	public function forMail(Mail $mail): string
	{
		$placeholderValues = $mail->getPlaceholderValues()
			? $mail->getPlaceholderValues()
				->asArray()
			: [];

		$placeholderValues['subject'] = $mail->getSubject();

		$contentModel = new ViewModel(
			$placeholderValues
		);
		$contentModel->setTemplate($mail->getContentTemplate());

		$placeholderValues['content'] = $this->phpRenderer->render(
			$contentModel->getTemplate(),
			$contentModel->getVariables()
		);

		$layoutModel = new ViewModel($placeholderValues);
		$layoutModel->setTemplate($mail->getLayoutTemplate());

		return $this->phpRenderer->render($layoutModel->getTemplate(), $layoutModel->getVariables());
	}
}
