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
        if(Schema::hasTable('m_business_unit')) {
            return ;
        }
        Schema::create('m_business_unit', function (Blueprint $table) {
            $table->string('bu_code', 36)->primary();
            $table->string('bu_name', 100)->nullable();
            $table->string('isactive', 1)->nullable();
            $table->timestamps(); // Hapus baris ini jika tidak ingin kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_units');
    }
};
