<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Turnstile for EXT:form',
    'description' => 'TYPO3 Extension to add Turnstile to EXT:form',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'dreistrom.land AG',
    'author_email' => 'hello@dreistrom.land',
    'author_company' => 'dreistrom.land AG',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.00-14.9.99',
            'extbase' => '14.0.00-14.9.99',
            'fluid' => '14.0.00-14.9.99',
            'form' => '14.0.00-14.9.99',
        ],
    ],
];
