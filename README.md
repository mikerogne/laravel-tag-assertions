# Laravel Tag Assertions

[Laravel](https://laravel.com/) ships with a huge number of awesome features, but one of my favorites is how easy it makes [testing your application](https://laravel.com/docs/master/testing).

Laravel Tag Assertions aims to make the incredible [HTTP Tests](https://laravel.com/docs/master/http-tests) functionality that Laravel offers even more powerful by adding useful assertions for HTML tags.

### Motivations

I've often wanted to assert that a response contained certain HTML elements (such as a Vue component with certain props), but didn't want newlines and other whitespace to matter. This made using methods like `$response->assertSee(...)` difficult to use for this particular use-case. Using Laravel Dusk wasn't a desireable option either because they can be slow and sometimes fragile.

# Installation

```
composer require --dev mikerogne/laravel-tag-assertions
```

Once installed, your TestResponse instances now have access to new assertions. See below for usage & examples.

# Usage

### TestResponse::assertSeeTag(string $selector, array $attributes)

**$selector** is the name of a tag you want to match. You can get as specific as you want. **$attributes** is either an array of attributes that the tag must have.

| Simple | More Specific          |
|--------|------------------------|
| button | button.btn.btn-default |
| a      | a[role=tab]            |

### TestResponse::assertSeeTag(string $selector, $callback)

If you specify a callback, three parameters will be passed to it:

1. **$tag**: This is the name of the tag itself, ie: `button` or `a`.
2. **$attributes**: This is an array of attributes for the tag, ie: `["class" => "btn btn-default"]`.
3. **$content**: This is a string representing the content (innerHtml). Whitespace is included.

### TestResponse::assertSeeTagContent(string $selector, string $content)

Sometimes we only care that a tag with specific content is on the page. A common use-case for this is a textarea field.

```
$response->assertSeeTagContent('textarea[name=about]', $user->about);
```

# Examples

## Form Validation

```html
<body>
    <h1>Contrived Example</h1>
    
    <form>
        <p>
            <label>First Name</label>
            <input type="text" name="first_name" value="{{ old('first_name') }}">
        </p>
        <p>
            <label>Last Name</label>
            <input type="text" name="last_name" value="{{ old('last_name') }}">
        </p>
        <p>
            <label>Email</label>
            <input type="text" name="email" value="{{ old('email') }}">
        </p>
        <p>
            <button type="submit">Register</button>
        </p>
    </form>
</body>
```

```php
<?php

namespace Tests\Feature;

class ExampleTest extends TestCase
{
    /** @test */
    public function uses_old_input_when_validation_fails()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => '', // oops!
        ];
        
        $response = $this->post('/register', $data);
        
        $response->assertSeeTag('input[name=first_name]', [
            'value' => $data['first_name'],
        ]);
        
        $response->assertSeeTag('input[name=last_name]', [
            'value' => $data['last_name'],
        ]);
    }
}
```


## Vue Component

```html
<body>
    <h1>Another Contrived Example</h1>
    
    <blog-posts
        :posts="{{ $posts->toJson() }}"
    ></blog-posts>
</body>
```

```php
<?php

namespace Tests\Feature;

class VueTest extends TestCase
{
    /** @test */
    public function lists_blog_posts()
    {
        $posts = factory(\App\Post::class, 5)->create();
        
        $response = $this->get('/', $data);
        
        $response->assertSeeTagContent('h1', 'Another Contrived Example');
        
        $response->assertSeeTag('blog-posts', [
            ':posts' => e($posts->toJson()),
        ]);
    }
}
```

## Callback Example

```html
<body>
    <h1>Callback Example</h1>

    <!-- notice the whitespace in the h2's content -->
    <h2 class="section-title" data-foobar="bazburk">
        Product Review
    </h2>
    <p class="summary">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    
</body>
```

```php
<?php

namespace Tests\Feature;

class CallbackTest extends TestCase
{
    /** @test */
    public function shows_product_review()
    {
        $response = $this->get('/', $data);
        
        $response->assertSeeTag('h2', function($tag, $attributes, $content) {
            // $tag -> "h2"
            // $attributes -> ['class' => 'section-title', 'data-foobar' => 'bazburk']
            // $content -> Product Review (but including the whitespace!)
            
            return \Illuminate\Support\Str::contains($content, 'Product Review');
        });
        
        $response->assertSeeTagContent('p.summary', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
    }
}
```

# License

This code is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).