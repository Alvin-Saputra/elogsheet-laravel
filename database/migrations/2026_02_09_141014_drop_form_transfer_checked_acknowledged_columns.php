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
        Schema::table('t_form_transfer_header', function (Blueprint $table) {
            // Drop checked_* columns
            $table->dropColumn([
                'checked_status',
                'checked_by',
                'checked_date',
                'checked_status_remarks',
                'checked_role',
            ]);

            // Drop acknowledged_* columns
            $table->dropColumn([
                'acknowledged_status',
                'acknowledged_by',
                'acknowledged_date',
                'acknowledged_status_remarks',
                'acknowledged_role',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('t_form_transfer_header', function (Blueprint $table) {
            // Recreate checked_* columns
            $table->string('checked_status', 20)->nullable()->after('prepared_status_remarks');
            $table->string('checked_by', 50)->nullable()->after('prepared_status_remarks');
            $table->datetime('checked_date')->nullable()->after('prepared_status_remarks');
            $table->string('checked_status_remarks', 100)->nullable()->after('prepared_status_remarks');
            $table->string('checked_role', 50)->nullable()->after('prepared_status_remarks');

            // Recreate acknowledged_* columns
            $table->string('acknowledged_status', 20)->nullable()->after('approved_status_remarks');
            $table->string('acknowledged_by', 50)->nullable()->after('approved_status_remarks');
            $table->datetime('acknowledged_date')->nullable()->after('approved_status_remarks');
            $table->string('acknowledged_status_remarks', 100)->nullable()->after('approved_status_remarks');
            $table->string('acknowledged_role', 50)->nullable()->after('approved_status_remarks');
        });
    }
};
