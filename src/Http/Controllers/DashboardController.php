<?php

declare(strict_types=1);

namespace Aura\Http\Controllers;

use Aura\Contracts\StorageInterface;
use Aura\DTO\MetricType;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __construct(
        protected StorageInterface $storage
    ) {
    }

    public function index()
    {
        $allDbMetrics = $this->storage->retrieve(MetricType::DATABASE_QUERY);
        
        // Filter insights (metrics that have an 'insight' tag)
        $insights = $allDbMetrics->filter(function ($m) {
            $tags = is_string($m->tags) ? json_decode($m->tags, true) : $m->tags;
            return isset($tags['insight']);
        });

        // Raw queries
        $slowQueries = $allDbMetrics->filter(function ($m) {
            $tags = is_string($m->tags) ? json_decode($m->tags, true) : $m->tags;
            return !isset($tags['insight']) && ($tags['slow'] ?? false);
        });

        $httpRequests = $this->storage->retrieve(MetricType::EXTERNAL_HTTP_REQUEST);
        $slowHttpRequests = $httpRequests->filter(function ($m) {
            $tags = is_string($m->tags) ? json_decode($m->tags, true) : $m->tags;
            return $tags['slow'] ?? false;
        });

        return view('aura::dashboard.index', [
            'slowQueries' => $slowQueries,
            'insights' => $insights,
            'requests' => $this->storage->retrieve(MetricType::REQUEST_DURATION),
            'memory' => $this->storage->retrieve(MetricType::MEMORY_USAGE),
            'slowHttp' => $slowHttpRequests,
            'jobs' => $this->storage->retrieve(MetricType::JOB_EXECUTION),
            'cache' => $this->storage->retrieve(MetricType::CACHE_OPERATION),
        ]);
    }

    public function metrics(string $type)
    {
        $metricType = MetricType::tryFrom($type);
        
        if (!$metricType) {
            return response()->json(['error' => 'Invalid metric type'], 400);
        }
        
        return response()->json([
            'metrics' => $this->storage->retrieve($metricType),
        ]);
    }
}
