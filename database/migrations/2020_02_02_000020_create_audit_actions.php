<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAuditActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('audit.connection'))->create('audit_actions', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('target');
            $table->string('action', 10);
            $table->json('data');
            $table->timestamps();

            $table->index('user_id');
            $table->index('target');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('audit.connection'))->dropIfExists('audit_actions');
    }
}
