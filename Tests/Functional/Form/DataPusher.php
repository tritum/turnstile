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

namespace TRITUM\Turnstile\Tests\Functional\Form;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

class DataPusher
{
    private $formData = [];
    private $with = [];
    private $withNoPrefix = [];
    private $without = [];
    private $withoutNoPrefix = [];
    private $withChash = true;

    public function __construct(DataExtractor $dataExtractor, string $query = '//form')
    {
        $this->formData = $dataExtractor->getFormData($query);
    }

    public function with(string $identifier, string $value): DataPusher
    {
        $this->with[$identifier] = $value;
        return $this;
    }

    public function withNoPrefix(string $identifier, string $value): DataPusher
    {
        $this->withNoPrefix[$identifier] = $value;
        return $this;
    }

    public function without(string $identifier): DataPusher
    {
        $this->without[$identifier] = $identifier;
        return $this;
    }

    public function withoutNoPrefix(string $identifier): DataPusher
    {
        $this->withoutNoPrefix[$identifier] = $identifier;
        return $this;
    }

    public function withChash(bool $withChash): DataPusher
    {
        $this->withChash = $withChash;
        return $this;
    }

    public function toPostRequest(InternalRequest $request): InternalRequest
    {
        $postStructure = $this->getPostStructure();
        $request->getBody()->write(http_build_query($postStructure));
        return $request
                  ->withMethod('POST')
                  ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                  ->withParsedBody($postStructure)
                  ->withQueryParameters($this->getQueryStructure());
    }

    private function getQueryStructure(): array
    {
        $queryStructure = [];
        $actionData = $this->formData['actionData'];
        if ($this->withChash === false) {
            unset($actionData['cHash']);
        }
        $actionQuery = http_build_query($actionData);

        foreach (explode('&', urldecode($actionQuery)) as $queryPart) {
            [$key, $value] = explode('=', $queryPart, 2);
            $queryStructure[$key] = $value;
        }

        return $queryStructure;
    }

    private function getPostStructure(): array
    {
        $dataPrefix = '';
        $postStructure = [];

        foreach ($this->formData['elementData'] ?? [] as $elementData) {
            $nameStruct = [];
            parse_str(sprintf('%s=%s', $elementData['name'], $elementData['value'] ?? ''), $nameStruct);
            $postStructure = array_replace_recursive($postStructure, $nameStruct);

            if ($this->strEndsWith($elementData['name'], '[__state]')) {
                $prefix = key(ArrayUtility::flatten($nameStruct));
                $prefixItems = explode('.', $prefix);
                array_pop($prefixItems);
                $dataPrefix = implode('.', $prefixItems) . '.';
            }
        }

        foreach ($this->with as $identifier => $value) {
            $postStructure = ArrayUtility::setValueByPath($postStructure, $dataPrefix . $identifier, $value, '.');
        }

        foreach ($this->without as $identifier) {
            $postStructure = ArrayUtility::removeByPath($postStructure, $dataPrefix . $identifier, '.');
        }

        $postStructure = array_replace_recursive(
            $postStructure,
            $this->withNoPrefix
        );

        foreach ($this->withoutNoPrefix as $identifier) {
            unset($postStructure[$identifier]);
        }

        return $postStructure;
    }

    private function strEndsWith(string $haystack, string $needle): bool
    {
        $needleLength = strlen($needle);
        return $needleLength === 0 || substr_compare($haystack, $needle, - $needleLength) === 0;
    }
}
