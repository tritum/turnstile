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

namespace TRITUM\Turnstile\Service;

use TRITUM\Turnstile\Exception\MissingKeyException;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class ConfigurationService
{
    /**
     * @var array<array-key, mixed>|null
     */
    private ?array $settings = null;

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(ConfigurationManager $configurationManager)
    {
        if ($this->settings === null) {
            $this->settings = $configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'turnstile',
            );
        }
    }

    /**
     * @throws MissingKeyException
     */
    public function getSiteKey(): string
    {
        $siteKey = $this->resolveSetting('siteKey', 'TURNSTILE_SITE_KEY');

        if ($siteKey === '') {
            throw new MissingKeyException(
                'Turnstile site key not defined',
                1603034266,
            );
        }

        return $siteKey;
    }

    /**
     * @throws MissingKeyException
     */
    public function getPrivateKey(): string
    {
        $privateKey = $this->resolveSetting('privateKey', 'TURNSTILE_PRIVATE_KEY');

        if ($privateKey === '') {
            throw new MissingKeyException(
                'Turnstile private key not defined',
                1603034285,
            );
        }

        return $privateKey;
    }

    /**
     * @throws MissingKeyException
     */
    public function getApiScript(): string
    {
        $apiScript = $this->resolveSetting('apiScript', 'TURNSTILE_API_SCRIPT');

        if ($apiScript === '') {
            throw new MissingKeyException(
                'turnstile api script not defined',
                1603034329,
            );
        }

        return $apiScript;
    }

    public function sendUserIpAddress(): bool
    {
        return (bool) $this->resolveSetting('sendIp', 'TURNSTILE_SEND_IP');
    }

    public function getChallengeTimeout(): int
    {
        $challengeTimeout = $this->resolveSetting('challengeTimeout', 'TURNSTILE_CHALLENGE_TIMEOUT');

        return $challengeTimeout === '' ? 300 : (int) $challengeTimeout;
    }

    public function getTheme(): string
    {
        $theme = $this->resolveSetting('theme', 'TURNSTILE_THEME');

        return $theme === '' ? 'light' : $theme;
    }

    /**
     * Resolves a scalar setting from TypoScript, falling back to an environment variable.
     */
    private function resolveSetting(string $settingKey, string $envVar): string
    {
        $value = $this->settings[$settingKey] ?? null;
        if (!is_scalar($value) || empty($value)) {
            $value = \getenv($envVar);
        }

        return trim((string) $value);
    }
}
