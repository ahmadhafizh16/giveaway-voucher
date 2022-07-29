<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                    ->constrained("customers")
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');

            $table->decimal('total_spent',10,2)->default(0);
            $table->decimal('total_saving',10,2)->default(0);
            $table->dateTime('transaction_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index('transaction_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('purchase_transactions');
    }
};
