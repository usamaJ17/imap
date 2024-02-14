<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('org_unique', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('email_from_id')->unsigned();
            $table->text('email_ids');
            $table->foreign('email_from_id')->references('id')->on('email_from')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_unique');
    }
};
