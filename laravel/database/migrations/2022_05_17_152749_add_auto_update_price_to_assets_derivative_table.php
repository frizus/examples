<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoUpdatePriceToAssetsDerivativeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_derivative', function (Blueprint $table) {
            $table->boolean('auto_update_price')->default(true)->after('allow_export');
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
            $table->dropColumn('auto_update_price');
        });
    }
}
