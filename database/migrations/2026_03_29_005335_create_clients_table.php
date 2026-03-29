<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->string('name', 150);
            $table->string('email', 190);
            $table->string('whatsapp', 20);
            $table->string('document', 20);
            $table->boolean('is_active')->default(true);

            $table->unique(['tenant_id', 'email'], 'clients_tenant_email_unique');
            $table->unique(['tenant_id', 'whatsapp'], 'clients_tenant_whatsapp_unique');
            $table->unique(['tenant_id', 'document'], 'clients_tenant_document_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
