<?php
// BOM物料表迁移文件:php artisan make:migration create_products_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique()->comment('成品图号/产品编码');
            $table->string('name')->comment('产品名称');
            $table->text('desc')->nullable()->comment('产品描述');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};