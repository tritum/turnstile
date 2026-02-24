<?php

declare(strict_types=1);

/*
 * This file is part of the Turnstile extension for TYPO3
 * - (c) 2023 TRITUM GmbH
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TRITUM\Turnstile\Tests\Functional;

use Symfony\Component\Mailer\SentMessage;
use TRITUM\Turnstile\Tests\Functional\SiteHandling\SiteBasedTestTrait;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\TestingFramework\Core\Functional\Framework\DataHandling\Scenario\DataHandlerFactory;
use TYPO3\TestingFramework\Core\Functional\Framework\DataHandling\Scenario\DataHandlerWriter;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

abstract class FunctionalTestCase extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
    use SiteBasedTestTrait;

    public const MAIL_SPOOL_FOLDER = 'typo3temp/var/transient/spool/';

    protected const ENCRYPTION_KEY = '4408d27a916d51e624b69af3554f516dbab61037a9f7b9fd6f81b4d3bedeccb6';

    protected const TYPO3_CONF_VARS = [
        'SYS' => [
            'encryptionKey' => self::ENCRYPTION_KEY,
        ],
    ];

    protected const LANGUAGE_PRESETS = [
        'DE' => ['id' => 0, 'title' => 'Deutsch', 'locale' => 'de_DE.UTF8', 'iso' => 'de', 'hrefLang' => 'de-DE', 'direction' => ''],
    ];

    protected const ROOT_PAGE_BASE_URI = 'http://localhost';

    protected const VALID_TURNSTILE_RESPONSE = 'XXXX.DUMMY.TOKEN.XXXX';

    protected const PRIVATE_KEY_ALWAYS_PASS = '1x0000000000000000000000000000000AA';

    protected const PRIVATE_KEY_ALWAYS_FAIL = '2x0000000000000000000000000000000AA';

    protected InternalRequestContext $internalRequestContext;

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/turnstile/Tests/Functional/Fixtures/Frontend/AdditionalConfiguration.php' => 'typo3conf/AdditionalConfiguration.php',
    ];

    protected array $coreExtensionsToLoad = [
        'core',
        'backend',
        'frontend',
        'extbase',
        'recordlist',
        'fluid',
        'fluid_styled_content',
        'form',
    ];

    protected array $testExtensionsToLoad = ['typo3conf/ext/turnstile'];

    protected int $rootPageUid = 1;

    protected string $databaseScenarioFile = __DIR__ . '/Fixtures/Frontend/StandardPagesScenario.yaml';

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureLanguageService();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = self::ENCRYPTION_KEY;

        $this->writeSiteConfiguration(
            'acme-com',
            $this->buildSiteConfiguration($this->rootPageUid, self::ROOT_PAGE_BASE_URI . '/'),
            [
                $this->buildDefaultLanguageConfiguration('DE', '/'),
            ],
            $this->buildErrorHandlingConfiguration('Fluid', [404]),
        );

        $this->internalRequestContext = new InternalRequestContext();

        $this->withDatabaseSnapshot(function (): void {
            $this->setUpDatabase();
        });
    }

    protected function tearDown(): void
    {
        putenv('TURNSTILE_SITE_KEY');
        putenv('TURNSTILE_PRIVATE_KEY');
        putenv('TURNSTILE_API_SCRIPT');
        putenv('TURNSTILE_SEND_IP');
        putenv('TURNSTILE_CHALLENGE_TIMEOUT');
        putenv('TURNSTILE_THEME');

        unset($GLOBALS['TYPO3_CONF_VARS']);
        unset($this->internalRequestContext);

        $this->purgeMailSpool();
        parent::tearDown();
    }

    protected function setUpDatabase(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Frontend/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);

        $this->initializeLanguage();

        $factory = DataHandlerFactory::fromYamlFile($this->databaseScenarioFile);
        $writer = DataHandlerWriter::withBackendUser($backendUser);
        $writer->invokeFactory($factory);

        static::failIfArrayIsNotEmpty(
            $writer->getErrors(),
        );
    }

    private function ensureLanguageService(): void
    {
        if (isset($GLOBALS['LANG']) && $GLOBALS['LANG'] instanceof LanguageService) {
            return;
        }

        // for TYPO3 13+: LanguageService needs dependencies -> use factory
        if (class_exists(LanguageServiceFactory::class)) {
            $GLOBALS['LANG'] = $this->getContainer()
                ->get(LanguageServiceFactory::class)
                ->createFromUserPreferences(null);

            // important: caught makeInstance(LanguageService::class) calls
            GeneralUtility::addInstance(LanguageService::class, $GLOBALS['LANG']);
            return;
        }

        // for TYPO3 12
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
        if (method_exists($GLOBALS['LANG'], 'init')) {
            $GLOBALS['LANG']->init('default');
        }
    }

    protected function initializeLanguage(): void
    {
        // TYPO3 <= 12
        if (method_exists(Bootstrap::class, 'initializeLanguageObject')) {
            Bootstrap::initializeLanguageObject();
            return;
        }

        // TYPO3 13+: LanguageService via container (the constructor needs arguments)
        $this->ensureLanguageService();
    }

    protected function getMailSpoolMessages(): array
    {
        $messages = [];

        $files = glob($this->instancePath . '/' . self::MAIL_SPOOL_FOLDER . '*') ?: [];
        foreach (array_filter($files, 'is_file') as $path) {
            $serialized = file_get_contents($path);
            if ($serialized === false) {
                continue;
            }

            /** @var mixed $sent */
            $sent = @unserialize($serialized, ['allowed_classes' => true]);
            if (!($sent instanceof SentMessage)) {
                continue;
            }

            $original = $sent->getOriginalMessage();

            $plaintext = '';
            $subject = '';
            $to = '';
            $date = null;

            if ($original instanceof Email) {
                $plaintext = (string) ($original->getTextBody() ?? '');
                $subject = (string) ($original->getSubject() ?? '');
                $to = implode(', ', array_map(
                    static fn(Address $a): string => $a->toString(),
                    $original->getTo(),
                ));
                $date = $original->getDate();
            } else {
                $raw = method_exists($original, 'toString') ? (string) $original->toString() : '';
                $plaintext = $raw;
            }

            $messages[] = [
                'plaintext' => $plaintext,
                'subject' => $subject,
                'date' => $date instanceof \DateTimeInterface ? \DateTime::createFromInterface($date) : null,
                'to' => $to,
            ];
        }

        return $messages;
    }

    protected function purgeMailSpool(): void
    {
        foreach (glob($this->instancePath . '/' . self::MAIL_SPOOL_FOLDER . '*') as $path) {
            unlink($path);
        }
    }
}
