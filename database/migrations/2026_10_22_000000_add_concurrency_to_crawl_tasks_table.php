<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crawl_tasks', function (Blueprint $t) {
            $t->unsignedTinyInteger('concurrency')->default(1)->after('delay_ms');
        });
    }

    public function down(): void
    {
        Schema::table('crawl_tasks', function (Blueprint $t) {
            $t->dropColumn('concurrency');
        });
    }
};
