<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crawl_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->json('start_urls');                      // 起始 URL 列表
            $t->string('url_pattern');                   // 用户输入的正则（不含分隔符）
            $t->json('fields');                          // [{name, selector, type, attr, multiple}]
            $t->unsignedInteger('max_pages')->default(100);
            $t->unsignedTinyInteger('max_depth')->default(3);
            $t->unsignedInteger('delay_ms')->default(500);
            $t->boolean('respect_robots')->default(true);
            $t->string('status')->default('pending');    // pending/running/done/failed/cancelled
            $t->unsignedInteger('crawled')->default(0);
            $t->unsignedInteger('failed')->default(0);
            $t->string('output_path')->nullable();
            $t->text('error')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crawl_tasks');
    }
};
