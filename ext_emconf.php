<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Turnstile for EXT:form',
    'description' => 'TYPO3 Extension to add Turnstile to EXT:form',
    'category' => 'fe',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'author' => 'TRITUM GmbH',
    'author_email' => 'hallo@tritum.de',
    'author_company' => 'TRITUM GmbH',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
            'extbase' => '11.5.0-12.4.99',
            'fluid' => '11.5.0-12.4.99',
            'form' => '11.5.0-12.4.99',
        ],
    ],
];
