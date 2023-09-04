<?php

declare(strict_types=1);
require_once __DIR__ . '/../.build/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Command\LintCommand;

(new Application('yaml/lint'))
    ->add(new LintCommand())
    ->getApplication()
    ->setDefaultCommand('lint:yaml', true)
    ->run();
