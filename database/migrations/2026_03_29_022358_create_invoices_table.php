<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // FKs
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->foreign('bank_account_id', 'fk_invoices_banco_febraban')->references('id')->on('banco_febrabans')->nullOnDelete();

            // Dados de negócio
            $table->string('source_system', 60)->nullable();
            $table->string('nosso_numero', 20);
            $table->string('numero_documento', 20)->nullable();
            $table->string('uso_empresa', 40)->nullable();

            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('amount_cents')->default(0);
            $table->string('status', 20)->default('open');
            $table->dateTime('paid_at')->nullable();

            $table->boolean('cnab_registered')->default(false);
            $table->boolean('continue_billing')->default(false);
            $table->boolean('wa_disabled')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
