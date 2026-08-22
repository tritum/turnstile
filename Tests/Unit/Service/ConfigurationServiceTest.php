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

namespace TRITUM\Turnstile\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TRITUM\Turnstile\Exception\MissingKeyException;
use TRITUM\Turnstile\Service\ConfigurationService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * @coversDefaultClass \TRITUM\Turnstile\Service\ConfigurationService
 */
class ConfigurationServiceTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ConfigurationManager|ObjectProphecy
     */
    private ObjectProphecy $configurationManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationManager = $this->prophesize(ConfigurationManager::class);
        $this->configurationManager->getConfiguration(Argument::cetera())->willReturn([]);
    }

    /**
     * @covers ::__construct
     * @covers ::getSiteKey
     */
    #[Test]
    public function getSiteKeyThrowsExceptionIfKeyNotSet(): void
    {
        putenv('TURNSTILE_SITE_KEY');

        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getSiteKey();
    }

    /**
     * @covers ::__construct
     * @covers ::getSiteKey
     */
    #[Test]
    public function getSiteKeyReturnsKeyFromSettings(): void
    {
        $expectedKey = 'my_superb_key';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'turnstile')
            ->willReturn(['siteKey' => $expectedKey]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $siteKey = $subject->getSiteKey();

        self::assertSame($expectedKey, $siteKey);
    }

    /**
     * @covers ::__construct
     * @covers ::getSiteKey
     */
    #[Test]
    public function getSiteKeyReturnsKeyFromEnv(): void
    {
        $expectedKey = 'my_superb_key';
        putenv('TURNSTILE_SITE_KEY=' . $expectedKey);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $siteKey = $subject->getSiteKey();

        self::assertSame($expectedKey, $siteKey);
    }

    /**
     * @covers ::__construct
     * @covers ::getPrivateKey
     */
    #[Test]
    public function getPrivateKeyThrowsExceptionIfKeyNotSet(): void
    {
        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getPrivateKey();
    }

    /**
     * @covers ::__construct
     * @covers ::getPrivateKey
     */
    #[Test]
    public function getPrivateKeyReturnsKeyFromSettings(): void
    {
        $expectedKey = 'my_superb_key';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'turnstile')
            ->willReturn(['privateKey' => $expectedKey]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $privateKey = $subject->getPrivateKey();

        self::assertSame($expectedKey, $privateKey);
    }

    /**
     * @covers ::__construct
     * @covers ::getPrivateKey
     */
    #[Test]
    public function getPrivateKeyReturnsKeyFromEnv(): void
    {
        $expectedKey = 'my_superb_key';
        putenv('TURNSTILE_PRIVATE_KEY=' . $expectedKey);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $privateKey = $subject->getPrivateKey();

        self::assertSame($expectedKey, $privateKey);
    }

    /**
     * @covers ::__construct
     * @covers ::getApiScript
     */
    #[Test]
    public function getApiScriptThrowsExceptionIfKeyNotSet(): void
    {
        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getApiScript();
    }

    /**
     * @covers ::__construct
     * @covers ::getApiScript
     * @covers ::appendSiteLanguage
     */
    #[Test]
    public function getApiScriptReturnsKeyFromSettings(): void
    {
        $expectedScript = 'https://turnstile.com/1/api.js';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'turnstile')
            ->willReturn(['apiScript' => $expectedScript]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $apiScript = $subject->getApiScript();

        self::assertSame($expectedScript, $apiScript);
    }

    /**
     * @covers ::__construct
     * @covers ::getApiScript
     * @covers ::appendSiteLanguage
     * @covers ::getServerRequest
     */
    #[Test]
    public function getApiScriptReturnsKeyFromEnv(): void
    {
        $expectedScript = 'https://turnstile.com/1/api.js';
        putenv('TURNSTILE_API_SCRIPT=' . $expectedScript);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $apiScript = $subject->getApiScript();

        self::assertSame($expectedScript, $apiScript);
    }

    /**
     * @covers ::__construct
     * @covers ::sendUserIpAddress
     */
    #[Test]
    public function sendUserIpAddressReturnsKeyFromSettings(): void
    {
        $expected = false;
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'turnstile')
            ->willReturn(['sendIp' => 0]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $sendIp = $subject->sendUserIpAddress();

        self::assertSame($expected, $sendIp);
    }

    /**
     * @covers ::__construct
     * @covers ::sendUserIpAddress
     */
    #[Test]
    public function sendUserIpAddressReturnsKeyFromEnv(): void
    {
        $expected = false;
        putenv('TURNSTILE_SEND_IP=0');

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $sendIp = $subject->sendUserIpAddress();

        self::assertSame($expected, $sendIp);
    }

    /**
     * @covers ::__construct
     * @covers ::getChallengeTimeout
     */
    #[Test]
    public function getChallengeTimeoutReturnsKeyFromSettings(): void
    {
        $expected = 500;
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'turnstile')
            ->willReturn(['challengeTimeout' => $expected]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $challengeTimeout = $subject->getChallengeTimeout();

        self::assertSame($expected, $challengeTimeout);
    }

    /**
     * @covers ::__construct
     * @covers ::getChallengeTimeout
     */
    #[Test]
    public function getChallengeTimeoutReturnsKeyFromEnv(): void
    {
        $expected = 500;
        putenv('TURNSTILE_CHALLENGE_TIMEOUT=' . $expected);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $challengeTimeout = $subject->getChallengeTimeout();

        self::assertSame($expected, $challengeTimeout);
    }

    /**
     * @covers ::__construct
     * @covers ::getTheme
     */
    #[Test]
    public function getThemeReturnsKeyFromSettings(): void
    {
        $expected = 'dark';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'turnstile')
            ->willReturn(['theme' => $expected]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $theme = $subject->getTheme();

        self::assertSame($expected, $theme);
    }

    /**
     * @covers ::__construct
     * @covers ::getTheme
     */
    #[Test]
    public function getThemeReturnsKeyFromEnv(): void
    {
        $expected = 'dark';
        putenv('TURNSTILE_THEME=' . $expected);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $theme = $subject->getTheme();

        self::assertSame($expected, $theme);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('TURNSTILE_SITE_KEY');
        putenv('TURNSTILE_PRIVATE_KEY');
        putenv('TURNSTILE_API_SCRIPT');
        putenv('TURNSTILE_SEND_IP');
        putenv('TURNSTILE_CHALLENGE_TIMEOUT');
        putenv('TURNSTILE_THEME');
    }
}
