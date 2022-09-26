<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDerivativeCreatedAtAndDerivativeUpdatedAtToAssetsDerivativeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_derivative', function (Blueprint $table) {
            $table->timestamp('derivative_created_at')->nullable();
            $table->timestamp('derivative_updated_at')->nullable();
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
            $this->dropColumn('derivative_created_at', 'derivative_updated_at');
        });
    }
}
