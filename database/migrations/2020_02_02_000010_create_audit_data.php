<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAuditData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('audit.connection'))->create('audit_data', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('target');
            $table->string('target_pk');
            $table->string('action', 10);
            $table->json('data');
            $table->timestamps();

            $table->index('user_id');
            $table->index('target', 'target_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('audit.connection'))->dropIfExists('audit_data');
    }
}
