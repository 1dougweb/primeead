<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MatriculaImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('matriculas.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'import_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240', // 10MB max
            ],
            'update_existing' => 'boolean',
            'ignore_duplicates' => 'boolean',
            'batch_size' => 'integer|min:10|max:1000',
            'dry_run' => 'boolean',
            'parceiro_id' => [
                'nullable',
                'integer',
                Rule::exists('parceiros', 'id')->where('status', 'ativo'),
            ],
            'notification_email' => 'nullable|email',
            'column_mapping' => 'array',
            'column_mapping.*' => 'integer|min:0',
            'auto_detect_columns' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'import_file.required' => 'Selecione um arquivo para importar',
            'import_file.file' => 'O arquivo enviado é inválido',
            'import_file.mimes' => 'O arquivo deve ser do tipo CSV ou TXT',
            'import_file.max' => 'O arquivo não pode ter mais de 10MB',
            'batch_size.min' => 'O tamanho do lote deve ser pelo menos 10',
            'batch_size.max' => 'O tamanho do lote não pode ser maior que 1000',
            'parceiro_id.exists' => 'O parceiro selecionado não existe ou não está ativo',
            'notification_email.email' => 'O e-mail de notificação deve ser válido',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'import_file' => 'arquivo de importação',
            'update_existing' => 'atualizar existentes',
            'ignore_duplicates' => 'ignorar duplicatas',
            'batch_size' => 'tamanho do lote',
            'dry_run' => 'simulação',
            'parceiro_id' => 'parceiro',
            'notification_email' => 'e-mail de notificação',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'update_existing' => $this->boolean('update_existing'),
            'ignore_duplicates' => $this->boolean('ignore_duplicates', true),
            'dry_run' => $this->boolean('dry_run'),
            'batch_size' => $this->integer('batch_size') ?: 100,
        ]);
    }
}
