<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'turnstile' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:turnstile/Resources/Public/Icons/turnstile.svg',
    ],
];
