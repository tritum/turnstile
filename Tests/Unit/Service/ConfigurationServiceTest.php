<?php

declare(strict_types=1);

/*
 * This file is part of the turnstile extension for TYPO3
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
    private $configurationManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationManager = $this->prophesize(ConfigurationManager::class);
        $this->configurationManager->getConfiguration(Argument::cetera())->willReturn([]);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getSiteKey
     */
    public function getSiteKeyThrowsExceptionIfKeyNotSet(): void
    {
        putenv('TURNSTILE_SITE_KEY');

        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getSiteKey();
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getSiteKey
     */
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
     * @test
     * @covers ::__construct
     * @covers ::getSiteKey
     */
    public function getSiteKeyReturnsKeyFromEnv(): void
    {
        $expectedKey = 'my_superb_key';
        putenv('TURNSTILE_SITE_KEY=' . $expectedKey);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $siteKey = $subject->getSiteKey();

        self::assertSame($expectedKey, $siteKey);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getPrivateKey
     */
    public function getPrivateKeyThrowsExceptionIfKeyNotSet(): void
    {
        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getPrivateKey();
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getPrivateKey
     */
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
     * @test
     * @covers ::__construct
     * @covers ::getPrivateKey
     */
    public function getPrivateKeyReturnsKeyFromEnv(): void
    {
        $expectedKey = 'my_superb_key';
        putenv('TURNSTILE_PRIVATE_KEY=' . $expectedKey);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $privateKey = $subject->getPrivateKey();

        self::assertSame($expectedKey, $privateKey);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getApiScript
     */
    public function getApiScriptThrowsExceptionIfKeyNotSet(): void
    {
        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getApiScript();
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getApiScript
     * @covers ::appendSiteLanguage
     */
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
     * @test
     * @covers ::__construct
     * @covers ::getApiScript
     * @covers ::appendSiteLanguage
     * @covers ::getServerRequest
     */
    public function getApiScriptReturnsKeyFromEnv(): void
    {
        $expectedScript = 'https://turnstile.com/1/api.js';
        putenv('TURNSTILE_API_SCRIPT=' . $expectedScript);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $apiScript = $subject->getApiScript();

        self::assertSame($expectedScript, $apiScript);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::sendUserIpAddress
     */
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
     * @test
     * @covers ::__construct
     * @covers ::sendUserIpAddress
     */
    public function sendUserIpAddressReturnsKeyFromEnv(): void
    {
        $expected = false;
        putenv('TURNSTILE_SEND_IP=0');

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $sendIp = $subject->sendUserIpAddress();

        self::assertSame($expected, $sendIp);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getChallengeTimeout
     */
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
     * @test
     * @covers ::__construct
     * @covers ::getChallengeTimeout
     */
    public function getChallengeTimeoutReturnsKeyFromEnv(): void
    {
        $expected = 500;
        putenv('TURNSTILE_CHALLENGE_TIMEOUT=' . $expected);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $challengeTimeout = $subject->getChallengeTimeout();

        self::assertSame($expected, $challengeTimeout);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getTheme
     */
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
     * @test
     * @covers ::__construct
     * @covers ::getTheme
     */
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
