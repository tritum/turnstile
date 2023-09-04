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

namespace TRITUM\Turnstile\Event;

/**
 * @codeCoverageIgnore
 */
final class TranslateErrorMessageEvent
{
    /**
     * @var string
     */
    private $errorCode = '';

    /**
     * @var string
     */
    private $message = '';

    public function __construct(string $errorCode)
    {
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
