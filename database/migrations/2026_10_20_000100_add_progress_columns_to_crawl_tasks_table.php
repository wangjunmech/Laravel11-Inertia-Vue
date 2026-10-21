<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crawl_tasks', function (Blueprint $t) {
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->string('current_url', 1000)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('crawl_tasks', function (Blueprint $t) {
            $t->dropColumn(['started_at', 'finished_at', 'current_url']);
        });
    }
};
