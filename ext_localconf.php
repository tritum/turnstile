<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die();

(static function (): void {
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

    ExtensionManagementUtility::addTypoScriptSetup('
        module.tx_form.settings.yamlConfigurations.1692719161 = EXT:turnstile/Configuration/Form/Yaml/BaseSetup.yaml
        plugin.tx_form.settings.yamlConfigurations.1692719161 = EXT:turnstile/Configuration/Form/Yaml/BaseSetup.yaml
    ');
})();
