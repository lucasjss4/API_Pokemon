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
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('HP');
            $table->integer('Attack');
            $table->integer('Defense');
            $table->integer('Sp_Attack');
            $table->integer('Sp_Defense');
            $table->integer('Speed');
            $table->string('species');
            $table->string('description');
            $table->string('height');
            $table->string('weight');
            $table->string('gender');
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemon');
    }
};
