<?php

namespace Rogne\LaravelTagAssertions;

use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;
use PHPHtmlParser\Dom;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /** @var Dom */
    private $domCache;

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadViewsFrom(__DIR__ . '/views', 'laravel-tag-assertions');

        TestResponse::macro('assertSeeTag', function ($selector, $attributes = []) {
            if (!isset($this->domCache)) {
                $this->domCache = new Dom;
                $this->domCache->load($this->getContent());
            }

            $elements = collect($this->domCache->find($selector));

            PHPUnit::assertTrue(
                $elements->count() > 0,
                "Could not find '{$selector}' in response."
            );

            $found = $elements->first(function (Dom\HtmlNode $element) use ($attributes) {
                $elementAttributes = collect($element->tag->getAttributes())
                    ->map(function ($val, $key) {
                        return $val['value'];
                    });

                // If callback was provided, use it.
                if (is_callable($attributes)) {
                    return $attributes(
                        $element->tag->name(),
                        $elementAttributes->toArray(),
                        $element->innerHtml()
                    );
                }

                // Otherwise, treat as array.
                foreach ($attributes as $attributeName => $attributeValue) {
                    // If this is a numeric index and we have a value, the user
                    // specified an attribute to search for without a value.
                    // Treat the "value" as the attribute name.
                    if (is_numeric($attributeName) && $attributeValue) {
                        $attributeName = $attributeValue;
                        unset($attributeValue);
                    }

                    // If attribute not found on this element, stop here.
                    if (!isset($elementAttributes[$attributeName])) {
                        return false;
                    }

                    // If attribute value is set & does not match, no good.
                    if (isset($attributeValue)) {
                        if ($attributeValue != $elementAttributes[$attributeName]) {
                            return false;
                        }
                    }
                }

                // After the foreach() loop, we've found all attributes.
                return true;
            });

            PHPUnit::assertNotNull(
                $found,
                "Could not find '{$selector}' with specified attributes in response."
            );

            return $this;
        });

        TestResponse::macro('assertSeeTagContent', function ($selector, $content) {
            return $this->assertSeeTag($selector, function ($tag, $tagAttributes, $text) use ($content) {
                return $text == $content;
            });
        });

        TestResponse::macro('assertDontSeeTag', function ($selector, $attributes = []) {
            if (!isset($this->domCache)) {
                $this->domCache = new Dom;
                $this->domCache->load($this->getContent());
            }
            $elements = collect($this->domCache->find($selector));

            if ($elements->count() == 0) {
                PHPUnit::assertTrue(true, "Did not find '{$selector}' in response.");

                return $this;
            }

            $found = $elements->first(function (Dom\HtmlNode $element) use ($attributes) {
                $elementAttributes = collect($element->tag->getAttributes())
                    ->map(function ($val, $key) {
                        return $val['value'];
                    });

                // If callback was provided, use it.
                if (is_callable($attributes)) {
                    return $attributes(
                        $element->tag->name(),
                        $elementAttributes->toArray(),
                        $element->innerHtml()
                    );
                }

                // Otherwise, treat as array.
                foreach ($attributes as $attributeName => $attributeValue) {
                    // If this is a numeric index and we have a value, the user
                    // specified an attribute to search for without a value.
                    // Treat the "value" as the attribute name.
                    if (is_numeric($attributeName) && $attributeValue) {
                        $attributeName = $attributeValue;
                        unset($attributeValue);
                    }

                    // If attribute not found on this element, stop here.
                    if (!isset($elementAttributes[$attributeName])) {
                        return false;
                    }

                    // If attribute value is set & does not match, no good.
                    if (isset($attributeValue)) {
                        if ($attributeValue != $elementAttributes[$attributeName]) {
                            return false;
                        }
                    }
                }

                // After the foreach() loop, we've found all attributes.
                return true;
            });

            PHPUnit::assertNull(
                $found,
                "Should not find '{$selector}' with specified attributes in response."
            );

            return $this;
        });

        TestResponse::macro('assertDontSeeTagContent', function ($selector, $content) {
            return $this->assertDontSeeTag($selector, function ($tag, $tagAttributes, $text) use ($content) {
                return $text == $content;
            });
        });
    }
}
