<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAssetsDerivativeDropColumnsUrlCrudePriceCrudeOldPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_derivative', function (Blueprint $table) {
            $table->dropColumn(['url', 'crude_price', 'crude_old_price']);
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
            $table->text('url')->nullable()->after('name2');
            $table->string('crude_price', 2000)->nullable()->after('url');
            $table->string('crude_old_price', 2000)->nullable()->after('crude_price');
        });
    }
}
