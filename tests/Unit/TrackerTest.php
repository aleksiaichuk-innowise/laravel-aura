<?php

namespace Aura\Tests\Unit;

use Aura\Core\Tracker;
use Aura\Tests\TestCase;
use Illuminate\Support\Str;

class TrackerTest extends TestCase
{
    public function test_it_generates_and_persists_trace_id()
    {
        $tracker = new Tracker();
        
        $id1 = $tracker->getTraceId();
        $id2 = $tracker->getTraceId();

        $this->assertTrue(Str::isUuid($id1));
        $this->assertEquals($id1, $id2, 'Trace ID must be the same during the same request lifecycle');
    }

    public function test_it_allows_setting_manual_trace_id()
    {
        $tracker = new Tracker();
        $manualId = 'custom-trace-id';
        
        $tracker->setTraceId($manualId);
        
        $this->assertEquals($manualId, $tracker->getTraceId());
    }
}
