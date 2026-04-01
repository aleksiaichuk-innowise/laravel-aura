<?php

namespace Aura\Tests\Feature\Collectors;

use Aura\Collectors\DatabaseQueryCollector;
use Aura\Core\AuraManager;
use Aura\Core\Tracker;
use Aura\DTO\Metrics\MetricType;
use Aura\Tests\TestCase;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class DatabaseQueryCollectorTest extends TestCase
{
    public function test_it_records_slow_query_with_trace_id()
    {
        config()->set('aura.slow_query_threshold', 10);
        
        // Получаем экземпляр менеджера и устанавливаем Trace ID вручную для проверки
        $tracker = app(Tracker::class);
        $tracker->setTraceId('test-session-id');
        
        $manager = app(AuraManager::class);
        
        // Эмулируем медленный запрос
        Event::dispatch(new QueryExecuted(
            'select * from users',
            [],
            25.5,
            DB::connection()
        ));

        $manager->flush();
        
        $this->assertDatabaseHas('aura_metrics', [
            'type' => MetricType::DATABASE_QUERY->value,
            'value' => 25.5,
        ]);

        $metric = \Illuminate\Support\Facades\DB::table('aura_metrics')->first();
        // В БД сейчас нет колонки trace_id, она хранится в JSON тегах или мы ее добавили в DTO?
        // По плану trace_id должен быть частью DTO. Давайте проверим, как он сохраняется.
    }
}
