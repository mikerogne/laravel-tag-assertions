<?php

namespace Rogne\LaravelTagAssertions;

use Illuminate\Foundation\Testing\Assert as PHPUnit;
use Illuminate\Foundation\Testing\TestResponse;
use PHPHtmlParser\Dom;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadViewsFrom(__DIR__.'/views', 'laravel-tag-assertions');

        TestResponse::macro('assertSeeTag', function ($selector, $attributes = []) {
            $dom = new Dom;
            $dom->load($this->getContent());
            $elements = $dom->find($selector);

            PHPUnit::assertTrue(
                count($elements) > 0,
                "Could not find '{$selector}' in response."
            );

            // TODO: Allow for searching for a vague tag like "input" and checking them
            // TODO: ALL to see if at least one matches the criteria instead of just the
            // TODO: first element found ($elements[0]->tag).
            // TODO: What about value for things like textarea?
            // Nice to have:
            // $response->assertSeeTextarea('selector', 'value');
            $elementAttributes = collect($elements[0]->tag->getAttributes())
                ->map(function ($val, $key) {
                    return $val['value'];
                });

            if (is_callable($attributes)) {
                PHPUnit::assertTrue(
                    $attributes(
                        $elements[0]->tag->name(),
                        $elementAttributes->toArray(),
                        $elements[0]->text
                    )
                );

                return $this;
            }

            foreach ($attributes as $attributeName => $attributeValue) {
                if (is_numeric($attributeName) && $attributeValue) {
                    $attributeName = $attributeValue;
                    unset($attributeValue);
                }

                //dump([
                //    'name' => $attributeName,
                //    'value' => isset($attributeValue) ? $attributeValue : 'NOT SET',
                //    'isset($attributeName)' => isset($attributeName),
                //    'isset($attributeValue)' => isset($attributeValue),
                //    'elementAttributes' => $elementAttributes,
                //]);

                PHPUnit::assertTrue(isset($elementAttributes[$attributeName]));

                if (isset($attributeValue)) {
                    PHPUnit::assertEquals(
                        $attributeValue,
                        $elementAttributes[$attributeName],
                        "Did not find expected value for attribute '{$attributeName}'."
                    );
                }
            }

            return $this;
        });
    }
}
