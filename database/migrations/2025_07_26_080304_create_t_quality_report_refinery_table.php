<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('t_quality_report_refinery')) {
            return ;
        }
        Schema::create('t_quality_report_refinery', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('report_date')->nullable();
            $table->time('time')->nullable();

            $table->string('p_cat')->nullable();
            $table->decimal('p_tank_source', 10)->nullable();
            $table->decimal('p_flowrate', 10)->nullable();
            $table->decimal('p_ffa', 10, 2)->nullable();
            $table->decimal('p_iv', 10, 2)->nullable();
            $table->decimal('p_pv', 10, 2)->nullable();
            $table->decimal('p_anv', 10, 2)->nullable();
            $table->decimal('p_dobi', 10, 2)->nullable();
            $table->decimal('p_carotene', 10, 2)->nullable();
            $table->decimal('p_m&i', 10, 2)->nullable();
            $table->string('p_color')->nullable();

            $table->string('c_cat')->nullable();
            $table->decimal('c_pa', 10, 2)->nullable();
            $table->decimal('c_be', 10, 2)->nullable();

            $table->string('b_cat')->nullable();
            $table->decimal('b_color_r', 10)->nullable();
            $table->decimal('b_color_y', 10)->nullable();
            $table->string('b_break_test')->nullable();

            $table->string('r_cat')->nullable();
            $table->decimal('r_ffa', 10, 2)->nullable();
            $table->decimal('r_color_r', 10)->nullable();
            $table->decimal('r_color_y', 10)->nullable();
            $table->decimal('r_color_b', 10)->nullable();
            $table->decimal('r_pv', 10)->nullable();
            $table->decimal('r_m&i', 10)->nullable();
            $table->decimal('r_product_tank_no', 10)->nullable();

            $table->string('fp_cat')->nullable();
            $table->decimal('fp_purity', 10, 2)->nullable();
            $table->decimal('fp_product_tank_no', 10)->nullable();

            $table->decimal('spent_earth_oic', 10, 2)->nullable();
            $table->string('pic')->nullable();
            $table->text('remarks')->nullable();

            $table->string('checked_by')->nullable();
            $table->date('checked_date')->nullable();
            $table->time('checked_time')->nullable();

            $table->string('approved_by')->nullable();
            $table->date('approved_date')->nullable();
            $table->time('approved_time')->nullable();

            $table->char('flag', 1)->nullable();
            $table->string('company')->nullable();
            $table->string('plant')->nullable();
            $table->string('entry_by')->nullable();
            $table->timestamp('entry_date')->nullable();

            // Tidak perlu created_at & updated_at
            // $table->timestamps(); ← jangan tambahkan ini
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_quality_report_refinery');
    }
};
