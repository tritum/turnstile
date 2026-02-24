<?php

declare(strict_types=1);

/*
 * This file is part of the Turnstile extension for TYPO3
 * - (c) 2023 TRITUM GmbH
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TRITUM\Turnstile\ViewHelpers\Forms;

use TRITUM\Turnstile\Service\ConfigurationService;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * @codeCoverageIgnore maybe test with an acceptance test at a later point
 * @psalm-suppress UnusedClass
 */
class TurnstileViewHelper extends AbstractTagBasedViewHelper
{
    public function __construct(
        protected readonly ConfigurationService $configurationService,
        protected readonly AssetCollector $assetCollector,
    ) {
        parent::__construct();
    }

    /**
     * @return string
     */
    #[\Override]
    public function render(): string
    {
        /** @var FormRuntime|null $formRuntime */
        $formRuntime = $this->renderingContext
            ->getViewHelperVariableContainer()
            ->get(RenderRenderableViewHelper::class, 'formRuntime');

        if ($formRuntime instanceof FormRuntime) {
            /** @psalm-suppress InternalMethod */
            $renderingOptions = $formRuntime->getRenderingOptions();
            if (isset($renderingOptions['previewMode']) && $renderingOptions['previewMode'] === true) {
                return '';
            }
        }

        $this->assetCollector->addJavaScript(
            'turnstile',
            $this->configurationService->getApiScript(),
            ['async' => '', 'defer' => ''],
        );

        if (method_exists($this->tag, 'setForceClosingTag')) {
            $this->tag->setForceClosingTag(true);
        }

        // Add the "cf-turnstile" class while preserving any existing classes
        $existingClass = trim((string) $this->tag->getAttribute('class'));
        if (!preg_match('/(?:^|\s)cf-turnstile(?:\s|$)/', $existingClass)) {
            $this->tag->addAttribute('class', trim($existingClass . ' cf-turnstile'));
        }

        // Set data-sitekey / data-theme, but respect values provided by the template
        if (!$this->tag->hasAttribute('data-sitekey')) {
            $this->tag->addAttribute('data-sitekey', $this->configurationService->getSiteKey());
        }
        if (!$this->tag->hasAttribute('data-theme')) {
            $this->tag->addAttribute('data-theme', $this->configurationService->getTheme());
        }

        return $this->tag->render();

    }
}
