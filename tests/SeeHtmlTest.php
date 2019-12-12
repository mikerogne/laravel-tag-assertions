<?php

namespace Rogne\LaravelTagAssertions\Tests;

class SeeHtmlTest extends TestCase
{
    /** @test */
    public function sees_body_tag()
    {
        $response = $this->get('/');

        $response->assertSeeTag('body');
    }

    /** @test */
    public function sees_tag_with_attributes()
    {
        $response = $this->get('/');

        $response->assertSeeTag('input[name=first_name]', [
            'type' => 'text',
            'placeholder' => 'First name',
        ]);
    }

    /** @test */
    public function sees_tag_with_mix_of_attribute_names_and_values()
    {
        $response = $this->get('/');

        $response->assertSeeTag('div#app', [
            'id' => 'app',
            'v-cloak', // Don't care about the value, just that the attr exists.
        ]);
    }

    /** @test */
    public function sees_textarea_content_via_callback()
    {
        $response = $this->get('/');

        $response->assertSeeTag('textarea[name=about]', function ($tag, $attributes, $text) {
            return $text == 'Tell the world who you are!';
        });
    }
}
