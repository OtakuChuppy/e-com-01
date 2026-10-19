<?php

namespace Tests\Feature;

use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerfProbeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    public function test_dump_home_queries(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/')->assertOk();
        $seen = [];
        foreach (DB::getQueryLog() as $q) {
            $sql = preg_replace('/\s+/', ' ', $q['query']);
            $seen[$sql] = ($seen[$sql] ?? 0) + 1;
        }
        arsort($seen);
        foreach ($seen as $sql => $n) {
            fwrite(STDERR, "\n  [{$n}x] {$sql}");
        }
        fwrite(STDERR, "\n");
        $this->assertTrue(true);
    }
}
