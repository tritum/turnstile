<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Turnstile for EXT:form',
    'description' => 'TYPO3 Extension to add Turnstile to EXT:form',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'TRITUM GmbH',
    'author_email' => 'hallo@tritum.de',
    'author_company' => 'TRITUM GmbH',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.00-13.9.9',
            'extbase' => '12.4.00-13.9.9',
            'fluid' => '12.4.00-13.9.9',
            'form' => '12.4.00-13.9.9',
        ],
    ],
];
