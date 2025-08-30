<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Inserir template de boas-vindas para parceiros
        DB::table('whatsapp_templates')->insert([
            'name' => 'parceiro_boas_vindas',
            'description' => 'Template de boas-vindas para novos parceiros',
            'category' => 'parceiro',
            'variables' => json_encode(['nome', 'nome_fantasia', 'telefone', 'email']),
            'content' => "🎉 *BEM-VINDO(A) À NOSSA REDE DE PARCEIROS!*\n\n" .
                "Olá *{{nome}}*!\n\n" .
                "Parabéns! Seu cadastro como parceiro foi realizado com sucesso! ✅\n\n" .
                "🏢 *Dados da Parceria:*\n" .
                "📌 *Nome/Empresa:* {{nome_fantasia}}\n" .
                "📞 *Telefone:* {{telefone}}\n" .
                "📧 *Email:* {{email}}\n\n" .
                "🚀 *Próximos passos:*\n" .
                "• Nossa equipe de parcerias entrará em contato em breve\n" .
                "• Você receberá materiais de apoio para divulgação\n" .
                "• Acompanhe seu painel de parceiro para ver oportunidades\n" .
                "• Tire suas dúvidas através deste WhatsApp\n\n" .
                "💰 *Benefícios da Parceria:*\n" .
                "• Comissões atrativas por matrícula\n" .
                "• Suporte completo da nossa equipe\n" .
                "• Materiais de marketing exclusivos\n" .
                "• Treinamentos especializados\n\n" .
                "🤝 Estamos muito felizes em tê-lo(a) conosco!\n\n" .
                "_Atenciosamente,_\n*Equipe de Parcerias - EJA Supletivo*",
            'active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('whatsapp_templates')->where('name', 'parceiro_boas_vindas')->delete();
    }
}; 