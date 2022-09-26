<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsDerivativeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets_derivative', function (Blueprint $table) {
            $table->unsignedBigInteger('index', true);
            $table->unsignedBigInteger('domain');
            $table->foreignId('original_index');
            $table->unsignedBigInteger('id');
            $table->string('name1', 2000)->nullable();
            $table->string('name2', 2000)->nullable();
            $table->text('url')->nullable();
            $table->string('crude_price', 2000)->nullable();
            $table->string('crude_old_price', 2000)->nullable();
            $table->integer('price')->nullable();
            $table->integer('old_price')->nullable();
            $table->json('properties')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->nullable();
            $table->timestamp('discovered_at')->nullable()->comment('Дата, когда ассет был обнаружен впервые');
            $table->timestamp('price_updated_at')->nullable()->comment('Дата, когда цена была обновлена');
            $table->timestamp('created_at')->nullable()->comment('Дата, когда ассет был создан - заполнен');
            $table->timestamp('details_updated_at')->nullable()->comment('Дата, когда ассет был обновлен');

            $table->unique(['domain', 'id']);

            $table->foreign('original_index')
                ->references('index')
                ->on('assets_original');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assets_derivative');
    }
}
