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

    /** @test */
    public function sees_textarea_via_content()
    {
        $response = $this->get('/');

        $response->assertSeeTagContent('textarea[name=about]', 'Tell the world who you are!');
    }

    /** @test */
    public function sees_input_when_several_input_tags_exist()
    {
        $response = $this->get('/');

        // Instead of targeting the element via selector (input[name=email]),
        // we want to verify we can find *any* <input> with matching attribute(s).
        $response->assertSeeTag('input', [
            'name' => 'email',
        ]);
    }
}
