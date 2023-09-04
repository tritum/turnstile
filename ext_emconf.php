<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'turnstile for EXT:form',
    'description'      => 'TYPO3 Extension to add turnstile to EXT:form.',
    'category'         => 'frontend',
    'author'           => 'TRITUM GmbH',
    'author_email'     => 'hello@tritum.de',
    'author_company'   => 'TRITUM GmbH',
    'state'            => 'stable',
    'uploadfolder'     => '0',
    'clearCacheOnLoad' => 1,
    'version'          => '1.0.0',
    'constraints'      => [
        'depends' => [
            'extbase' => '10.4.0-12.4.99',
            'fluid' => '10.4.0-12.4.99',
            'form' => '10.4.0-12.4.99',
            'typo3' => '10.4.0-12.4.99',
        ],
    ],
];
