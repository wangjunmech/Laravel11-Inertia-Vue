<?php
// BOM物料表迁移文件:php artisan make:migration create_bom_items_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父节点ID 0=整机根节点');
            $table->decimal('qty', 12, 4)->default(1)->comment('单台用量');
            $table->decimal('loss_rate', 5, 2)->default(0)->comment('损耗率 %');
            $table->integer('sort')->default(0)->comment('排序权重');
            $table->decimal('subtotal', 12, 4)->default(0)->comment('行成本小计');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};