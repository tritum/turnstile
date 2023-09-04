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

namespace TRITUM\Turnstile\Tests\Functional;

use TRITUM\Turnstile\Tests\Functional\Form\DataExtractor;
use TRITUM\Turnstile\Tests\Functional\Form\DataPusher;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

class TurnstileValidationTest extends FunctionalTestCase
{
    public function validationFailsOnMultiStepFormIfTurnstileParametersAreMissingDataProvider(): \Generator
    {
        yield 'missing turnstile, cf-turnstile-response parameters' => [
            'formData' => [
                'name' => 'some name',
                'subject' => 'some subject',
                'email' => 'sender@waldhacker.dev',
                'message' => 'some message',
            ],
            'formDataNoPrefix' => [],
            'removeFormData' => [
                'turnstile-1',
            ],
            'removeFormDataNoPrefix' => [
                'cf-turnstile-response',
            ],
            'privateKey' => self::PRIVATE_KEY_ALWAYS_PASS,
        ];

        yield 'missing cf-turnstile-response parameters' => [
            'formData' => [
                'name' => 'some name',
                'subject' => 'some subject',
                'email' => 'sender@waldhacker.dev',
                'message' => 'some message',
                'turnstile-1' => '1',
            ],
            'formDataNoPrefix' => [],
            'removeFormData' => [],
            'removeFormDataNoPrefix' => [
                'cf-turnstile-response',
            ],
            'privateKey' => self::PRIVATE_KEY_ALWAYS_PASS,
        ];
    }

    /**
     * @test
     * @dataProvider validationFailsOnMultiStepFormIfTurnstileParametersAreMissingDataProvider
     */
    public function validationFailsOnMultiStepFormIfTurnstileParametersAreMissing(
        array $formData,
        array $formDataNoPrefix,
        array $removeFormData,
        array $removeFormDataNoPrefix,
        string $privateKey
    ): void {
        putenv('TURNSTILE_PRIVATE_KEY=' . $privateKey);
        $uri = self::ROOT_PAGE_BASE_URI . '/multistep-test-form';

        $response = $this->executeFrontendSubRequest(new InternalRequest($uri), $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $dataPusher = new DataPusher(new DataExtractor($pageMarkup));
        foreach ($formData as $identifier => $value) {
            $dataPusher->with($identifier, $value);
        }
        foreach ($formDataNoPrefix as $identifier => $value) {
            $dataPusher->withNoPrefix($identifier, $value);
        }
        foreach ($removeFormData as $identifier) {
            $dataPusher->without($identifier);
        }
        foreach ($removeFormDataNoPrefix as $identifier) {
            $dataPusher->withoutNoPrefix($identifier);
        }

        $formPostRequest = $dataPusher->toPostRequest(new InternalRequest($uri));

        $response = $this->executeFrontendSubRequest($formPostRequest, $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $formData = (new DataExtractor($pageMarkup))->getFormData();
        $elementData = $formData['elementData'];

        self::assertEquals(1, (int)$elementData['tx_form_formframework[multistep-test-form-1][__currentPage]']['value']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[multistep-test-form-1][name]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[multistep-test-form-1][subject]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[multistep-test-form-1][email]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[multistep-test-form-1][message]']['class']);
        self::assertStringContainsString('Missing validation value in POST request.', $pageMarkup);
        self::assertStringNotContainsString('Confirmation text', $pageMarkup);
        self::assertCount(0, $this->getMailSpoolMessages());
    }

    public function validationFailsOnMultiStepFormIfTurnstileParametersAreInvalidDataProvider(): \Generator
    {
        yield 'cf-turnstile-response parameter is empty' => [
            'formData' => [
                'name' => 'some name',
                'subject' => 'some subject',
                'email' => 'sender@waldhacker.dev',
                'message' => 'some message',
                'turnstile-1' => '1',
            ],
            'formDataNoPrefix' => [
                'cf-turnstile-response' => '',
            ],
            'removeFormData' => [],
            'removeFormDataNoPrefix' => [],
            'expectedErrorMessage' => 'The response parameter was not passed',
            'privateKey' => self::PRIVATE_KEY_ALWAYS_PASS,
        ];

        yield 'cf-turnstile-response parameter is invalid' => [
            'formData' => [
                'name' => 'some name',
                'subject' => 'some subject',
                'email' => 'sender@waldhacker.dev',
                'message' => 'some message',
                'turnstile-1' => '1',
            ],
            'formDataNoPrefix' => [
                'cf-turnstile-response' => '123456',
            ],
            'removeFormData' => [],
            'removeFormDataNoPrefix' => [],
            'expectedErrorMessage' => 'The response parameter is invalid or has expired',
            'privateKey' => self::PRIVATE_KEY_ALWAYS_FAIL,
        ];
    }

    /**
     * @test
     * @dataProvider validationFailsOnMultiStepFormIfTurnstileParametersAreInvalidDataProvider
     */
    public function validationFailsOnMultiStepFormIfTurnstileParametersAreInvalid(
        array $formData,
        array $formDataNoPrefix,
        array $removeFormData,
        array $removeFormDataNoPrefix,
        string $expectedErrorMessage,
        string $privateKey
    ): void {
        putenv('TURNSTILE_PRIVATE_KEY=' . $privateKey);
        $uri = self::ROOT_PAGE_BASE_URI . '/multistep-test-form';

        $response = $this->executeFrontendSubRequest(new InternalRequest($uri), $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $dataPusher = new DataPusher(new DataExtractor($pageMarkup));
        foreach ($formData as $identifier => $value) {
            $dataPusher->with($identifier, $value);
        }
        foreach ($formDataNoPrefix as $identifier => $value) {
            $dataPusher->withNoPrefix($identifier, $value);
        }
        foreach ($removeFormData as $identifier) {
            $dataPusher->without($identifier);
        }
        foreach ($removeFormDataNoPrefix as $identifier) {
            $dataPusher->withoutNoPrefix($identifier);
        }

        $formPostRequest = $dataPusher->toPostRequest(new InternalRequest($uri));

        $response = $this->executeFrontendSubRequest($formPostRequest, $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $formData = (new DataExtractor($pageMarkup))->getFormData();
        $elementData = $formData['elementData'];

        self::assertEquals(1, (int)$elementData['tx_form_formframework[multistep-test-form-1][__currentPage]']['value']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[multistep-test-form-1][name]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[multistep-test-form-1][subject]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[multistep-test-form-1][email]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[multistep-test-form-1][message]']['class']);
        self::assertStringNotContainsString('Confirmation text', $pageMarkup);
        self::assertStringContainsString($expectedErrorMessage, $pageMarkup);
        self::assertCount(0, $this->getMailSpoolMessages());
    }

    /**
     * @test
     */
    public function validationSuccessfulOnMultiStepFormIfTurnstileParametersAreValid(): void
    {
        putenv('TURNSTILE_PRIVATE_KEY=' . self::PRIVATE_KEY_ALWAYS_PASS);
        $uri = self::ROOT_PAGE_BASE_URI . '/multistep-test-form';

        $response = $this->executeFrontendSubRequest(new InternalRequest($uri), $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $dataPusher = new DataPusher(new DataExtractor($pageMarkup));
        $formPostRequest = $dataPusher
            ->with('name', 'some name')
            ->with('subject', 'some subject')
            ->with('email', 'sender@waldhacker.dev')
            ->with('message', 'some message')
            ->with('turnstile-1', '1')
            ->withNoPrefix('cf-turnstile-response', self::VALID_TURNSTILE_RESPONSE)
            ->toPostRequest(new InternalRequest($uri));

        $response = $this->executeFrontendSubRequest($formPostRequest, $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $formData = (new DataExtractor($pageMarkup))->getFormData();
        $elementData = $formData['elementData'];

        self::assertEquals(2, (int)$elementData['tx_form_formframework[multistep-test-form-1][__currentPage]']['value']);
        self::assertStringContainsString('Summary page', $pageMarkup);
        self::assertStringNotContainsString('Confirmation text', $pageMarkup);
        self::assertCount(0, $this->getMailSpoolMessages());
    }

    public function validationFailsOnSingleStepFormIfTurnstileParametersAreMissingDataProvider(): \Generator
    {
        yield 'missing turnstile, cf-turnstile-response parameters' => [
            'formData' => [
                'name' => 'some name',
                'subject' => 'some subject',
                'email' => 'sender@waldhacker.dev',
                'message' => 'some message',
            ],
            'formDataNoPrefix' => [],
            'removeFormData' => [
                'turnstile-1',
            ],
            'removeFormDataNoPrefix' => [
                'cf-turnstile-response',
            ],
            'privateKey' => self::PRIVATE_KEY_ALWAYS_PASS,
        ];

        yield 'missing cf-turnstile-response parameters' => [
            'formData' => [
                'name' => 'some name',
                'subject' => 'some subject',
                'email' => 'sender@waldhacker.dev',
                'message' => 'some message',
                'turnstile-1' => '1',
            ],
            'formDataNoPrefix' => [],
            'removeFormData' => [],
            'removeFormDataNoPrefix' => [
                'cf-turnstile-response',
            ],
            'privateKey' => self::PRIVATE_KEY_ALWAYS_PASS,
        ];
    }

    /**
     * @test
     * @dataProvider validationFailsOnSingleStepFormIfTurnstileParametersAreMissingDataProvider
     */
    public function validationFailsOnSingleStepFormIfTurnstileParametersAreMissing(
        array $formData,
        array $formDataNoPrefix,
        array $removeFormData,
        array $removeFormDataNoPrefix,
        string $privateKey
    ): void {
        putenv('TURNSTILE_PRIVATE_KEY=' . $privateKey);
        $uri = self::ROOT_PAGE_BASE_URI . '/singlestep-test-form';

        $response = $this->executeFrontendSubRequest(new InternalRequest($uri), $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $dataPusher = new DataPusher(new DataExtractor($pageMarkup));
        foreach ($formData as $identifier => $value) {
            $dataPusher->with($identifier, $value);
        }
        foreach ($formDataNoPrefix as $identifier => $value) {
            $dataPusher->withNoPrefix($identifier, $value);
        }
        foreach ($removeFormData as $identifier) {
            $dataPusher->without($identifier);
        }
        foreach ($removeFormDataNoPrefix as $identifier) {
            $dataPusher->withoutNoPrefix($identifier);
        }

        $formPostRequest = $dataPusher->toPostRequest(new InternalRequest($uri));

        $response = $this->executeFrontendSubRequest($formPostRequest, $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $formData = (new DataExtractor($pageMarkup))->getFormData();
        $elementData = $formData['elementData'];

        self::assertEquals(1, (int)$elementData['tx_form_formframework[singlestep-test-form-2][__currentPage]']['value']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[singlestep-test-form-2][name]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[singlestep-test-form-2][subject]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[singlestep-test-form-2][email]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[singlestep-test-form-2][message]']['class']);
        self::assertStringContainsString('Missing validation value in POST request.', $pageMarkup);
        self::assertStringNotContainsString('Confirmation text', $pageMarkup);
        self::assertCount(0, $this->getMailSpoolMessages());
    }

    public function validationFailsOnSingleStepFormIfTurnstileParametersAreInvalidDataProvider(): \Generator
    {
        yield 'cf-turnstile-response parameter is empty' => [
            'formData' => [
                'name' => 'some name',
                'subject' => 'some subject',
                'email' => 'sender@waldhacker.dev',
                'message' => 'some message',
                'turnstile-1' => '1',
            ],
            'formDataNoPrefix' => [
                'cf-turnstile-response' => '',
            ],
            'removeFormData' => [],
            'removeFormDataNoPrefix' => [],
            'expectedErrorMessage' => 'The response parameter was not passed',
            'privateKey' => self::PRIVATE_KEY_ALWAYS_PASS,
        ];

        yield 'cf-turnstile-response parameter is invalid' => [
            'formData' => [
                'name' => 'some name',
                'subject' => 'some subject',
                'email' => 'sender@waldhacker.dev',
                'message' => 'some message',
                'turnstile-1' => '1',
            ],
            'formDataNoPrefix' => [
                'cf-turnstile-response' => '123456',
            ],
            'removeFormData' => [],
            'removeFormDataNoPrefix' => [],
            'expectedErrorMessage' => 'The response parameter is invalid or has expired',
            'privateKey' => self::PRIVATE_KEY_ALWAYS_FAIL,
        ];
    }

    /**
     * @test
     * @dataProvider validationFailsOnSingleStepFormIfTurnstileParametersAreInvalidDataProvider
     */
    public function validationFailsOnSingleStepFormIfTurnstileParametersAreInvalid(
        array $formData,
        array $formDataNoPrefix,
        array $removeFormData,
        array $removeFormDataNoPrefix,
        string $expectedErrorMessage,
        string $privateKey
    ): void {
        putenv('TURNSTILE_PRIVATE_KEY=' . $privateKey);
        $uri = self::ROOT_PAGE_BASE_URI . '/singlestep-test-form';

        $response = $this->executeFrontendSubRequest(new InternalRequest($uri), $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $dataPusher = new DataPusher(new DataExtractor($pageMarkup));
        foreach ($formData as $identifier => $value) {
            $dataPusher->with($identifier, $value);
        }
        foreach ($formDataNoPrefix as $identifier => $value) {
            $dataPusher->withNoPrefix($identifier, $value);
        }
        foreach ($removeFormData as $identifier) {
            $dataPusher->without($identifier);
        }
        foreach ($removeFormDataNoPrefix as $identifier) {
            $dataPusher->withoutNoPrefix($identifier);
        }

        $formPostRequest = $dataPusher->toPostRequest(new InternalRequest($uri));

        $response = $this->executeFrontendSubRequest($formPostRequest, $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $formData = (new DataExtractor($pageMarkup))->getFormData();
        $elementData = $formData['elementData'];

        self::assertEquals(1, (int)$elementData['tx_form_formframework[singlestep-test-form-2][__currentPage]']['value']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[singlestep-test-form-2][name]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[singlestep-test-form-2][subject]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[singlestep-test-form-2][email]']['class']);
        self::assertStringNotContainsString('error', $elementData['tx_form_formframework[singlestep-test-form-2][message]']['class']);
        self::assertStringNotContainsString('Confirmation text', $pageMarkup);
        self::assertStringContainsString($expectedErrorMessage, $pageMarkup);
        self::assertCount(0, $this->getMailSpoolMessages());
    }

    /**
     * @test
     */
    public function validationSuccessfulOnSingleStepFormIfTurnstileParametersAreValid(): void
    {
        putenv('TURNSTILE_PRIVATE_KEY=' . self::PRIVATE_KEY_ALWAYS_PASS);
        $uri = self::ROOT_PAGE_BASE_URI . '/singlestep-test-form';

        $response = $this->executeFrontendSubRequest(new InternalRequest($uri), $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $dataPusher = new DataPusher(new DataExtractor($pageMarkup));
        $formPostRequest = $dataPusher
            ->with('name', 'some name')
            ->with('subject', 'some subject')
            ->with('email', 'sender@waldhacker.dev')
            ->with('message', 'some message')
            ->with('turnstile-1', '1')
            ->withNoPrefix('cf-turnstile-response', self::VALID_TURNSTILE_RESPONSE)
            ->toPostRequest(new InternalRequest($uri));

        $response = $this->executeFrontendSubRequest($formPostRequest, $this->internalRequestContext, true);
        $pageMarkup = (string)$response->getBody();

        $formData = (new DataExtractor($pageMarkup))->getFormData();
        $elementData = $formData['elementData'];
        $mails = $this->getMailSpoolMessages();

        self::assertStringContainsString('Confirmation text', $pageMarkup);
        self::assertCount(1, $this->getMailSpoolMessages());
        self::assertStringContainsString('Confirmation of your message', $mails[0]['plaintext'] ?? '');
        self::assertStringContainsString('Your message: some subject', $mails[0]['subject'] ?? '');
    }
}
