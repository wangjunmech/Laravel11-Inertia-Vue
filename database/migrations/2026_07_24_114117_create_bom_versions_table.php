<?php
// BOM物料表迁移文件:php artisan make:migration create_bom_versions_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('version_no')->comment('版本号 V1.0 / V2.0');
            $table->text('version_note')->nullable()->comment('版本修改备注');
            $table->boolean('is_default')->default(false)->comment('是否生效版本');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_versions');
    }
};
