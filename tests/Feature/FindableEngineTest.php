<?php

namespace Tests\Feature;

use Tests\TestCase;
use Findable\FindableEngine;

class FindableEngineTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_without_a_model()
    {
        $engine = app(FindableEngine::class);

        $this->assertInstanceOf(FindableEngine::class, $engine);
    }
}
