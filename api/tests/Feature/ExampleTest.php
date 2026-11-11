<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_api_information(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Primus API',
            ]);
    }
}
