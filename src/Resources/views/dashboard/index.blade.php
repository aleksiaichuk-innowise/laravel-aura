<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0c0e14;
            color: #e2e8f0;
        }

        .glass {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
        }

        .card-stat {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-stat:hover {
            transform: translateY(-5px);
            border-color: #38bdf8;
            box-shadow: 0 10px 25px -5px rgba(56, 189, 248, 0.1);
        }

        .glow-text {
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.5);
        }
    </style>
</head>
<body class="min-h-screen p-6 md:p-12">
<div class="max-w-7xl mx-auto">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
        <div>
            <h1 class="text-5xl font-bold bg-gradient-to-r from-sky-400 to-indigo-500 bg-clip-text text-transparent">
                Aura</h1>
            <p class="text-slate-500 mt-2 text-lg tracking-wide uppercase font-semibold">Self-Contained Performance
                Monitoring</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3 px-4 py-2 glass text-sm">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-sky-500"></span>
                    </span>
                <span class="font-bold tracking-widest uppercase">Live Monitoring</span>
            </div>
        </div>
    </header>

    <main>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="glass p-6 card-stat">
                <span class="text-slate-500 text-xs font-bold uppercase tracking-widest">Memory</span>
                <div class="text-3xl font-bold mt-2 text-white glow-text">{{ number_format($memory->first()?->value ?? 0, 2) }}
                    <span class="text-lg text-slate-500">MB</span></div>
            </div>
            <div class="glass p-6 card-stat">
                <span class="text-slate-500 text-xs font-bold uppercase tracking-widest">Slow DB Queries</span>
                <div class="text-3xl font-bold mt-2 text-white glow-text">{{ $slowQueries->count() }}</div>
            </div>
            <div class="glass p-6 card-stat">
                <span class="text-slate-500 text-xs font-bold uppercase tracking-widest">Slow HTTP</span>
                <div class="text-3xl font-bold mt-2 text-white glow-text">{{ $slowHttp->count() }}</div>
            </div>
            <div class="glass p-6 card-stat">
                <span class="text-slate-500 text-xs font-bold uppercase tracking-widest">Queue Jobs</span>
                <div class="text-3xl font-bold mt-2 text-white glow-text">{{ $jobs->count() }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- DB Queries -->
            <section class="glass p-1 overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h2 class="text-lg font-bold">Recent Slow Queries</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-white/5">
                        @foreach($slowQueries->take(5) as $query)
                            @php $tags = is_string($query->tags) ? json_decode($query->tags, true) : $query->tags; @endphp
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-rose-400 font-bold">{{ $query->value }}ms</span>
                                </td>
                                <td class="px-6 py-4 font-mono text-slate-300 truncate max-w-xs">
                                    {{ $tags['sql'] ?? 'Unknown' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- HTTP Requests -->
            <section class="glass p-1 overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h2 class="text-lg font-bold">Slow HTTP Requests</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-white/5">
                        @foreach($slowHttp->take(5) as $http)
                            @php $tags = is_string($http->tags) ? json_decode($http->tags, true, 512, JSON_THROW_ON_ERROR) : $http->tags; @endphp
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-amber-400 font-bold">{{ number_format($http->value, 0) }}ms</span>
                                </td>
                                <td class="px-6 py-4 font-mono text-slate-300 truncate max-w-xs">
                                    <span class="text-slate-500 mr-2">{{ $tags['method'] }}</span>{{ $tags['url'] }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        @if($insights->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                    <span class="text-sky-400">●</span> Performance Insights
                </h2>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($insights as $insight)
                        @php $tags = is_string($insight->tags) ? json_decode($insight->tags, true) : $insight->tags; @endphp
                        <div class="glass p-6 border-l-4 {{ ($tags['severity'] ?? '') === 'warning' ? 'border-amber-500' : 'border-sky-500' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-white">{{ $tags['insight'] }}</h3>
                                    <p class="text-slate-400 mt-1 font-mono text-sm">{{ $tags['sql'] ?? ($tags['url'] ?? '') }}</p>
                                </div>
                                <span class="text-xs font-bold uppercase px-2 py-1 rounded {{ ($tags['severity'] ?? '') === 'warning' ? 'bg-amber-500/10 text-amber-500' : 'bg-sky-500/10 text-sky-400' }}">
                                    {{ $tags['severity'] ?? 'info' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Queue Stats -->
            <section class="glass p-8">
                <h2 class="text-xl font-bold mb-4">Queue Activity</h2>
                <div class="flex items-center gap-8">
                    <div>
                        <span class="text-slate-500 text-sm font-bold uppercase">Processed</span>
                        <div class="text-3xl font-bold text-white">{{ $jobs->filter(fn($j) => (is_string($j->tags) ? json_decode($j->tags, true) : $j->tags)['status'] === 'processed')->count() }}</div>
                    </div>
                    <div>
                        <span class="text-slate-500 text-sm font-bold uppercase">Failed</span>
                        <div class="text-3xl font-bold text-rose-500">{{ $jobs->filter(fn($j) => (is_string($j->tags) ? json_decode($j->tags, true) : $j->tags)['status'] === 'failed')->count() }}</div>
                    </div>
                </div>
            </section>

            <!-- Cache Stats -->
            <section class="glass p-8">
                <h2 class="text-xl font-bold mb-4">Cache Efficiency</h2>
                @php
                    $hits = $cache->filter(fn($c) => (is_string($c->tags) ? json_decode($c->tags, true) : $c->tags)['operation'] === 'hit')->count();
                    $misses = $cache->filter(fn($c) => (is_string($c->tags) ? json_decode($c->tags, true) : $c->tags)['operation'] === 'miss')->count();
                    $total = $hits + $misses;
                    $rate = $total > 0 ? ($hits / $total) * 100 : 0;
                @endphp
                <div class="flex items-center gap-8">
                    <div>
                        <span class="text-slate-500 text-sm font-bold uppercase">Hit Rate</span>
                        <div class="text-3xl font-bold text-sky-400">{{ number_format($rate, 1) }}%</div>
                    </div>
                    <div class="flex-1 h-2 bg-white/5 rounded-full overflow-hidden mt-6">
                        <div class="h-full bg-sky-500" style="width: {{ $rate }}%"></div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
