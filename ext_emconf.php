<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Turnstile for EXT:form',
    'description' => 'TYPO3 Extension to add Turnstile to EXT:form',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'dreistrom.land AG',
    'author_email' => 'hello@dreistrom.land',
    'author_company' => 'dreistrom.land AG',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.00-13.9.9',
            'extbase' => '12.4.00-13.9.9',
            'fluid' => '12.4.00-13.9.9',
            'form' => '12.4.00-13.9.9',
        ],
    ],
];
