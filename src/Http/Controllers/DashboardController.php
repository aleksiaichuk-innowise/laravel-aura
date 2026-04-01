<?php

declare(strict_types=1);

namespace Aura\Http\Controllers;

use Aura\Contracts\StorageInterface;
use Aura\DTO\MetricType;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

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
            return isset($m->tags['insight']);
        });

        // Raw queries
        $slowQueries = $allDbMetrics->filter(function ($m) {
            return !isset($m->tags['insight']) && ($m->tags['slow'] ?? false);
        });

        $httpRequests = $this->storage->retrieve(MetricType::EXTERNAL_HTTP_REQUEST);
        $slowHttpRequests = $httpRequests->filter(function ($m) {
            return $m->tags['slow'] ?? false;
        });

        return view('aura::dashboard.index', [
            'slowQueries' => $slowQueries,
            'insights' => $insights,
            'requests' => $this->storage->retrieve(MetricType::REQUEST_DURATION),
            'memory' => $this->storage->retrieve(MetricType::MEMORY_USAGE),
            'slowHttp' => $slowHttpRequests,
            'cache' => $this->storage->retrieve(MetricType::CACHE_OPERATION),
            'jobs' => $this->storage->retrieve(MetricType::JOB_EXECUTION),
        ]);
    }

    public function metrics(string $type): JsonResponse
    {
        $metricType = MetricType::tryFrom($type);
        
        if (!$metricType) {
            return response()->json(['error' => 'Invalid metric type'], Response::HTTP_BAD_REQUEST);
        }
        
        return response()->json([
            'metrics' => $this->storage->retrieve($metricType),
        ]);
    }
}
