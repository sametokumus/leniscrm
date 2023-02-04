<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_id');
            $table->string('request_id');
            $table->bigInteger('customer_id')->default(0);
            $table->bigInteger('status_id')->default(1);
            $table->decimal('sub_total',10,2, false)->nullable();
            $table->decimal('freight',10,2, false)->nullable();
            $table->decimal('vat',10,2, false)->nullable();
            $table->decimal('vat_rate',10,2, false)->nullable();
            $table->decimal('grand_total',10,2, false)->nullable();
            $table->tinyInteger('active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
