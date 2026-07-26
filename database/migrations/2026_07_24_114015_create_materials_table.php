<?php
// BOM物料表迁移文件:php artisan make:migration create_materials_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('物料编码');
            $table->string('name')->comment('物料名称');
            $table->string('spec')->nullable()->comment('规格型号');
            $table->string('unit')->comment('单位：个、kg、m');
            $table->decimal('price', 12, 4)->default(0)->comment('采购单价');
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};