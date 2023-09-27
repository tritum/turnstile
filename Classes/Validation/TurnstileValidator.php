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

namespace TRITUM\Turnstile\Validation;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use TRITUM\Turnstile\Event\TranslateErrorMessageEvent;
use TRITUM\Turnstile\Service\ConfigurationService;
use Turnstile\Client\Client;
use Turnstile\Turnstile;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class TurnstileValidator extends AbstractValidator
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var ConfigurationService|null
     */
    private $configurationService;

    /**
     * Validate the Turnstile value from the request and add an error if not valid
     *
     * @param mixed $value The value
     */
    protected function isValid($value): void
    {
        $response = $this->validateTurnstile();

        if ((bool)($response['success'] ?? false) === false) {
            if (empty($response['error-codes'])) {
                $this->addError(
                    $this->translateErrorMessage(
                        'error_turnstile_generic',
                        'turnstile'
                    ),
                    1637268562
                );
            } else {
                foreach ($response['error-codes'] as $errorCode) {
                    $this->addError(
                        $this->translateErrorMessage(
                            'error_turnstile_' . $errorCode,
                            'turnstile'
                        ),
                        1566206403
                    );
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function validateTurnstile(): array
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];

        $parsedBody = $request->getParsedBody();

        $token = $parsedBody['cf-turnstile-response'] ?? null;
        if ($token === null) {
            return ['success' => false, 'error-codes' => ['invalid-post-form']];
        }

        $ip = null;
        if ($this->getConfigurationService()->sendUserIpAddress()) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $normalizedParams = $request->getAttribute('normalizedParams');
            if ($normalizedParams) {
                $ip = $normalizedParams->getRemoteAddress();
            }
        }

        $turnstile = GeneralUtility::makeInstance(
            Turnstile::class,
            GeneralUtility::makeInstance(
                Client::class,
                GeneralUtility::makeInstance(GuzzleHttpClient::class),
                GeneralUtility::makeInstance(HttpFactory::class),
            ),
            $this->getConfigurationService()->getPrivateKey(),
            (string)Uuid::uuid4()
        );

        $response = $turnstile->verify(
            $token,
            $ip,
            $this->getConfigurationService()->getChallengeTimeout()
        );

        return [
            'success' => $response->success,
            'error-codes' => $response->errorCodes,
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    protected function translateErrorMessage($translateKey, $extensionName, $arguments = []): string
    {
        $event = new TranslateErrorMessageEvent($translateKey);
        GeneralUtility::makeInstance(EventDispatcher::class)->dispatch($event);

        $message = $event->getMessage();
        if (!empty($message)) {
            return $message;
        }

        return LocalizationUtility::translate(
            $translateKey,
            $extensionName,
            $arguments
        ) ?? 'Validating Turnstile failed.';
    }

    private function getConfigurationService(): ConfigurationService
    {
        if (!($this->configurationService instanceof ConfigurationService)) {
            /** @var ConfigurationService $configurationService */
            $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
            $this->configurationService = $configurationService;
        }
        return $this->configurationService;
    }
}
