<?php

namespace Antares\Audit\Tests\Feature;

use Antares\Audit\Tests\TestCase;

class AliveTest extends TestCase
{
    /** @test */
    public function get_alive()
    {
        $response = $this->get(config('audit.route.prefix.api') . '/alive');
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('package', $json);
        $this->assertArrayHasKey('env', $json);
        $this->assertArrayHasKey('serverDateTime', $json);
    }
}
