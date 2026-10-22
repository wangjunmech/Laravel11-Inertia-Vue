<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crawl_tasks', function (Blueprint $t) {
            $t->string('follow_pattern', 500)->nullable()->after('url_pattern');
            $t->unsignedInteger('fetched')->default(0)->after('failed');
        });
    }

    public function down(): void
    {
        Schema::table('crawl_tasks', function (Blueprint $t) {
            $t->dropColumn(['follow_pattern', 'fetched']);
        });
    }
};
