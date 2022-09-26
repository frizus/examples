<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAssetsDerivativeDropOriginalDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_derivative', function (Blueprint $table) {
            $table->dropColumn(['discovered_at', 'price_updated_at', 'created_at', 'details_updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets_derivative', function (Blueprint $table) {
            $table->timestamp('discovered_at')->nullable()->comment('Дата, когда ассет был обнаружен впервые')->after('auto_update_price');
            $table->timestamp('price_updated_at')->nullable()->comment('Дата, когда цена была обновлена')->after('discovered_at');
            $table->timestamp('created_at')->nullable()->comment('Дата, когда ассет был создан - заполнен')->after('price_updated_at');
            $table->timestamp('details_updated_at')->nullable()->comment('Дата, когда ассет был обновлен')->after('created_at');
        });
    }
}
