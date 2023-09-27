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

namespace TRITUM\Turnstile\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use TRITUM\Turnstile\Validation\TurnstileValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @backupGlobals enabled
 * @coversDefaultClass \TRITUM\Turnstile\Validation\TurnstileValidator
 */
class TurnstileValidatorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ServerRequestInterface|ObjectProphecy
     */
    private $typo3request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->typo3request = $this->prophesize(ServerRequestInterface::class);
        $GLOBALS['TYPO3_REQUEST'] = $this->typo3request->reveal();
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     * @covers ::validate
     * @covers ::isValid
     * @covers ::validateTurnstile
     */
    public function validateReturnsErrorIfPostResponseFieldIsEmpty(): void
    {
        $subject = $this->getMockBuilder(TurnstileValidator::class)
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();

        $result = $subject->validate(1);
        $errors = $result->getErrors();

        self::assertCount(1, $errors);
        self::assertSame(1566206403, $errors[0]->getCode());
    }

    public function validateReturnsErrorIfVerificationRequestReturnsErrorDataProvider(): \Generator
    {
        yield 'Unsuccessful response with error codes' => [
            'responseData' => [
                'success' => false,
                'error-codes' => ['invalid-input-secret'],
            ],
            'expectedErrorCode' => 1566206403,
        ];

        yield 'Unsuccessful response with empty error codes' => [
            'responseData' => [
                'success' => false,
                'error-codes' => [],
            ],
            'expectedErrorCode' => 1637268562,
        ];
    }

    /**
     * @test
     * @dataProvider validateReturnsErrorIfVerificationRequestReturnsErrorDataProvider
     * @covers ::validate
     * @covers ::isValid
     * @covers ::validateTurnstile
     * @covers ::getConfigurationService
     * @covers ::getRequestFactory
     */
    public function validateReturnsErrorIfVerificationRequestReturnsError(
        array $responseData,
        int $expectedErrorCode
    ): void {
        $subject = $this->getMockBuilder(TurnstileValidator::class)
            ->onlyMethods(['translateErrorMessage', 'validateTurnstile'])
            ->getMock();

        $subject->method('validateTurnstile')->willReturn($responseData);

        $result = $subject->validate(1);
        $errors = $result->getErrors();

        self::assertCount(1, $errors);
        self::assertSame($expectedErrorCode, $errors[0]->getCode());
    }
}
