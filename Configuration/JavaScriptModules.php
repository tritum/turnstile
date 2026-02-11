<?php

declare(strict_types=1);

return [
    'dependencies' => ['backend', 'form'],
    'imports' => [
        '@tritum/turnstile/' => 'EXT:turnstile/Resources/Public/JavaScript/',
    ],
];
