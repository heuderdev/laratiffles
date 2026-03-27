<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_user', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', [
                'funcionario',    // quem trabalha dentro do tenant
                'cliente',        // usuário que consome o serviço (paciente, cliente, aluno, etc.)
                'fornecedor',     // empresas/pessoas que fornecem serviço/produto
                'parceiro',       // parceiro comercial, integrador, canal
                'owner',          // dono/admin do tenant (pode ser o mesmo que o admin)
                'representante',  // representante de venda, vendedor externo
                'autonomo',       // profissional autônomo ligado ao tenant
            ])->nullable();

            $table->primary(['tenant_id', 'user_id']);

            $table->enum('status', ['ativo', 'inativo', 'pendente'])->default('ativo');
            $table->string('cargo')->nullable();

            $table
                ->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
    }
};
