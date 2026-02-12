<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('t_form_transfer_header')) {
            return;
        }

        Schema::create('t_form_transfer_header', function (Blueprint $table) {
            $table->string('id', 32)->primary();

            $table->string('company', 10)->nullable();
            $table->string('plant', 20)->nullable();

            $table->date('transaction_date')->nullable();
            $table->string('to_dept', 100)->nullable();
            $table->string('from_dept', 100)->nullable();

            $table->string('form_no', 45)->nullable();
            $table->date('date_issued')->nullable();
            $table->integer('revision_no')->nullable();
            $table->date('revision_date')->nullable();

            $table->char('flag', 1)->nullable();

            $table->string('entry_by', 50)->nullable();
            $table->datetime('entry_date')->nullable();

            $table->string('prepared_by', 50)->nullable();
            $table->datetime('prepared_date')->nullable();
            $table->string('prepared_status', 20)->nullable();
            $table->string('prepared_status_remarks', 100)->nullable();

            $table->string('checked_by', 50)->nullable();
            $table->datetime('checked_date')->nullable();
            $table->string('checked_status', 20)->nullable();
            $table->string('checked_status_remarks', 100)->nullable();

            $table->string('approved_by', 50)->nullable();
            $table->datetime('approved_date')->nullable();
            $table->string('approved_status', 20)->nullable();
            $table->string('approved_status_remarks', 100)->nullable();

            $table->string('acknowledged_by', 50)->nullable();
            $table->datetime('acknowledged_date')->nullable();
            $table->string('acknowledged_status', 20)->nullable();
            $table->string('acknowledged_status_remarks', 100)->nullable();

            $table->string('updated_by', 50)->nullable();
            $table->datetime('updated_date')->nullable();

            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_form_transfer_header');
    }
};
