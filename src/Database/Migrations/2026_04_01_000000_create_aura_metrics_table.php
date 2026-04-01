<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('aura.database.table', 'aura_metrics'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('trace_id')->nullable()->index();
            $table->string('type')->index();
            $table->float('value');
            $table->json('tags')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('aura.database.table', 'aura_metrics'));
    }
};
