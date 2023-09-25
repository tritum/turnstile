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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * @codeCoverageIgnore maybe test with an acceptance test at a later point
 */
class TurnstileViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected $escapeOutput = false;

    /**
     * @var ConfigurationService
     */
    private $configurationService;

    /**
     * @var AssetCollector
     */
    private $assetCollector;

    public function __construct(ConfigurationService $configurationService, AssetCollector $assetCollector)
    {
        $this->configurationService = $configurationService;
        $this->assetCollector = $assetCollector;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        /** @var FormRuntime|null $formRuntime */
        $formRuntime = $this->renderingContext
            ->getViewHelperVariableContainer()
            ->get(RenderRenderableViewHelper::class, 'formRuntime');

        if ($formRuntime instanceof FormRuntime) {
            /**
             * @psalm-suppress InternalMethod
             */
            $renderingOptions = $formRuntime->getRenderingOptions();
            if (isset($renderingOptions['previewMode']) && $renderingOptions['previewMode'] === true) {
                return '';
            }
        }

        $this->assetCollector->addJavaScript(
            'turnstile',
            $this->configurationService->getApiScript(),
            ['async' => '', 'defer' => '']
        );

        return '<div class="cf-turnstile" data-sitekey="' . $this->configurationService->getSiteKey() . '" data-theme="' . $this->configurationService->getTheme() . '"></div>';
    }
}
