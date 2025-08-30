<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MatriculaExportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('matriculas.index');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'format' => [
                'required',
                'string',
                Rule::in(['csv', 'excel', 'pdf', 'json'])
            ],
            'filters' => 'array',
            'filters.status' => 'nullable|string',
            'filters.parceiro_id' => 'nullable|integer|exists:parceiros,id',
            'filters.data_inicio' => 'nullable|date',
            'filters.data_fim' => 'nullable|date|after_or_equal:filters.data_inicio',
            'filters.curso' => 'nullable|string',
            'filters.modalidade' => 'nullable|string',
            'filters.valor_min' => 'nullable|numeric|min:0',
            'filters.valor_max' => 'nullable|numeric|min:0|gte:filters.valor_min',
            'columns' => 'array',
            'columns.*' => 'string|in:id,inscricao_id,numero_matricula,nome_completo,data_nascimento,cpf,rg,orgao_emissor,sexo,estado_civil,nacionalidade,naturalidade,cep,logradouro,numero,complemento,bairro,cidade,estado,telefone_fixo,telefone_celular,email,nome_pai,nome_mae,modalidade,curso,ultima_serie,ano_conclusao,escola_origem,status,escola_parceira,parceiro_id,forma_pagamento,tipo_boleto,valor_total_curso,valor_matricula,valor_mensalidade,numero_parcelas,dia_vencimento,forma_pagamento_mensalidade,parcelas_ativas,parcelas_geradas,parcelas_pagas,percentual_juros,desconto,doc_rg_cpf,doc_comprovante,doc_historico,doc_certificado,doc_outros,google_drive_folder_id,observacoes,created_at,updated_at,deleted_at,created_by,updated_by',
            'sort_by' => 'nullable|string|in:id,inscricao_id,numero_matricula,nome_completo,data_nascimento,cpf,rg,email,telefone_celular,curso,modalidade,status,valor_total_curso,valor_matricula,valor_mensalidade,parceiro_id,created_at,updated_at',
            'sort_direction' => 'nullable|string|in:asc,desc',
            'limit' => 'nullable|integer|min:1|max:10000',
            'include_headers' => 'boolean',
            'notification_email' => 'nullable|email',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'format.required' => 'O formato de exportação é obrigatório',
            'format.in' => 'Formato de exportação inválido',
            'filters.data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial',
            'filters.valor_max.gte' => 'O valor máximo deve ser maior ou igual ao valor mínimo',
            'columns.*.in' => 'Coluna de exportação inválida',
            'sort_by.in' => 'Campo de ordenação inválido',
            'sort_direction.in' => 'Direção de ordenação inválida',
            'limit.min' => 'O limite mínimo é 1',
            'limit.max' => 'O limite máximo é 10.000',
            'notification_email.email' => 'E-mail de notificação inválido',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'format' => 'formato de exportação',
            'filters' => 'filtros',
            'columns' => 'colunas',
            'sort_by' => 'ordenar por',
            'sort_direction' => 'direção da ordenação',
            'limit' => 'limite',
            'include_headers' => 'incluir cabeçalhos',
            'notification_email' => 'e-mail de notificação',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'include_headers' => $this->boolean('include_headers', true),
            'sort_direction' => $this->input('sort_direction', 'asc'),
            'limit' => $this->integer('limit') ?: 1000,
        ]);
    }
}
