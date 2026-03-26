<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banco_febrabans', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nome', 100);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->index('codigo');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banco_febrabans');
    }
};
