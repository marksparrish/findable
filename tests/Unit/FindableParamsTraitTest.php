<?php

namespace Tests\Unit;

use Tests\TestCase;
use Findable\Traits\FindableGetterTrait;
use Findable\Traits\FindableSetterTrait;
use Findable\Traits\FindableParamsTrait;

class DummyParams
{
    use FindableParamsTrait;
    use FindableSetterTrait;
    use FindableGetterTrait;

    public function boot(): void
    {
        $this->initializeQueryParams();
    }

    public function callGetSize(): int { return $this->getSize(); }
    public function callGetPage(): int { return $this->getPage(); }
    public function callGetFrom(): int { return $this->getFrom(); }
    public function callGetTrackTotalHits(): bool { return $this->getTrackTotalHits(); }
}

class FindableParamsTraitTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('findable.default_size', 15);
        $app['config']->set('findable.default_page', 2);
        $app['config']->set('findable.default_track_total_hits', true);
    }

    /** @test */
    public function it_initializes_query_params_from_config()
    {
        $dummy = new DummyParams();
        $dummy->boot();

        $this->assertEquals(15, $dummy->callGetSize());
        $this->assertEquals(1, $dummy->callGetPage());
        $this->assertEquals(0, $dummy->callGetFrom());
        $this->assertTrue($dummy->callGetTrackTotalHits());
    }
}
