<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('process_dict_tree', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // material / process / surface_process
            $table->string('name'); // 对应前端 area
            $table->string('code')->nullable(); // 业务编码，可选
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // nestedset 必须字段
            $table->unsignedInteger('_lft');
            $table->unsignedInteger('_rgt');

            $table->index(['type', 'parent_id']);
            $table->foreign('parent_id')
                ->references('id')
                ->on('process_dict_tree')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_dict_tree');
    }
};
