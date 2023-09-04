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

namespace TRITUM\Turnstile\Service;

use TRITUM\Turnstile\Exception\MissingKeyException;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class ConfigurationService
{
    /**
     * @var array|null
     */
    private $settings;

    public function __construct(ConfigurationManager $configurationManager)
    {
        if ($this->settings === null) {
            $this->settings = $configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'turnstile'
            );
        }
    }

    /**
     * @return string
     * @throws MissingKeyException
     */
    public function getSiteKey(): string
    {
        $siteKey = !empty($this->settings['siteKey'])
            ? $this->settings['siteKey']
            : \getenv('TURNSTILE_SITE_KEY');

        if (empty($siteKey)) {
            throw new MissingKeyException(
                'turnstile site key not defined',
                1603034266
            );
        }

        return $siteKey;
    }

    /**
     * @return string
     * @throws MissingKeyException
     */
    public function getPrivateKey(): string
    {
        $privateKey = !empty($this->settings['privateKey'])
            ? $this->settings['privateKey']
            : \getenv('TURNSTILE_PRIVATE_KEY');

        if (empty($privateKey)) {
            throw new MissingKeyException(
                'turnstile private key not defined',
                1603034285
            );
        }

        return $privateKey;
    }

    /**
     * @return string
     * @throws MissingKeyException
     */
    public function getApiScript(): string
    {
        $apiScript = !empty($this->settings['apiScript'])
            ? $this->settings['apiScript']
            : \getenv('TURNSTILE_API_SCRIPT');
        if (empty($apiScript)) {
            throw new MissingKeyException(
                'turnstile api script not defined',
                1603034329
            );
        }

        return $apiScript;
    }

    public function sendUserIpAddress(): bool
    {
        $sendIp = !empty($this->settings['sendIp'])
            ? $this->settings['sendIp']
            : \getenv('TURNSTILE_SEND_IP');

        return (bool)$sendIp;
    }

    public function getChallengeTimeout(): int
    {
        $challengeTimeout = !empty($this->settings['challengeTimeout'])
            ? $this->settings['challengeTimeout']
            : \getenv('TURNSTILE_CHALLENGE_TIMEOUT');

        return (int)(empty($challengeTimeout) ? 300 : $challengeTimeout);
    }

    public function getTheme(): string
    {
        $theme = !empty($this->settings['theme'])
            ? $this->settings['theme']
            : \getenv('TURNSTILE_THEME');

        return empty($theme) ? 'light' : $theme;
    }
}
