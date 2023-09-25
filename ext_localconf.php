<?php

defined('TYPO3') or die();

call_user_func(static function () {
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'turnstile',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:turnstile/Resources/Public/Icons/turnstile.svg',
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'Turnstile',
        'setup',
        'module.tx_form {
          settings {
            yamlConfigurations {
              1692719161 = EXT:turnstile/Configuration/Form/Yaml/BaseSetup.yaml
            }
          }
        }'
    );
});
