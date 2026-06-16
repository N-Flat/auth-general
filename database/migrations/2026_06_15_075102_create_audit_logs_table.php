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
        // ai生成
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // いつ
            $table->timestamp('created_at')->useCurrent()->index();
            // 誰が（操作の主体）
            // actor_type: 'admin' | 'client' | 'user' | 'system'
            $table->string('actor_type', 20);
            $table->uuid('actor_id')->nullable();
            // どのクライアント文脈での操作か
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            // 何を（アクション名: 'user.login.success' のような階層構造）
            $table->string('action', 64);
            // 対象（ポリモーフィック的に保持。外部キー制約は張らない）
            $table->string('target_type', 50)->nullable();
            $table->uuid('target_id')->nullable();
            // どこから
            $table->string('ip_address', 45)->nullable(); // IPv6対応で45文字
            $table->string('user_agent', 255)->nullable();
            // 追加情報（操作固有の可変情報をJSONで保持）
            $table->json('metadata')->nullable();
            // 結果: 'success' | 'failure'
            $table->string('result', 10);

            // 検索用の複合インデックス
            $table->index(['actor_type', 'actor_id']);
            $table->index(['target_type', 'target_id']);
            $table->index('action');
            $table->index('result');
            $table->index(['client_id', 'created_at']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
