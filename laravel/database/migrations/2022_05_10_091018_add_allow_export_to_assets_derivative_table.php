<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowExportToAssetsDerivativeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_derivative', function (Blueprint $table) {
            $table->boolean('allow_export')->default(false)->after('categories');
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
            $table->dropColumn('allow_export');
        });
    }
}
