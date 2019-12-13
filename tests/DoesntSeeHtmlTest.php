<?php

namespace Rogne\LaravelTagAssertions\Tests;

class DoesntSeeHtmlTest extends TestCase
{
    /** @test */
    public function doesnt_see_marquee_tag()
    {
        $response = $this->get('/');

        $response->assertDontSeeTag('marquee');
    }

    /** @test */
    public function doesnt_see_tag_with_attributes()
    {
        $response = $this->get('/');

        $response->assertDontSeeTag('input[name=first_name]', [
            'type' => 'text',
            'placeholder' => 'Placeholder value',
        ]);
    }

    /** @test */
    public function doesnt_see_tag_with_mix_of_attribute_names_and_values()
    {
        $response = $this->get('/');

        $response->assertDontSeeTag('div#app', [
            'id' => 'cool-app',
            'v-cloak', // Don't care about the value, just that the attr exists.
        ]);
    }

    /** @test */
    public function doesnt_see_textarea_content_via_callback()
    {
        $response = $this->get('/');

        $response->assertDontSeeTag('textarea[name=about]', function ($tag, $attributes, $text) {
            return $text == 'Tell the world who you are! Or don\'t. Whatever. :)';
        });
    }

    /** @test */
    public function doesnt_see_textarea_via_content()
    {
        $response = $this->get('/');

        $response->assertDontSeeTagContent('textarea[name=about]', 'Tell the world who you are! Or don\'t. Whatever. :)');
    }

    /** @test */
    public function doesnt_see_input_when_several_input_tags_exist()
    {
        $response = $this->get('/');

        // Instead of targeting the element via selector (input[name=email]),
        // we want to verify we can find *any* <input> with matching attribute(s).
        $response->assertDontSeeTag('input', [
            'name' => 'email2',
        ]);
    }
}
