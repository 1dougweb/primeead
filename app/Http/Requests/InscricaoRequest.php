<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InscricaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Obter valores válidos das configurações
        $formSettings = \App\Models\SystemSetting::getFormSettings();
        $validCourses = array_keys($formSettings['available_courses'] ?? []);
        $validModalities = array_keys($formSettings['available_modalities'] ?? []);
        
        return [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:inscricaos,email',
            'telefone' => 'required|string|max:20|unique:inscricaos,telefone',
            'curso' => 'required|string|in:' . implode(',', $validCourses),
            'modalidade' => 'required|string|in:' . implode(',', $validModalities),
            'termos' => 'required|accepted',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Por favor, insira um email válido.',
            'email.unique' => 'Você já se inscreveu em breve entraremos em contato',
            'telefone.required' => 'O campo telefone é obrigatório.',
            'telefone.unique' => 'Você já se inscreveu em breve entraremos em contato',
            'curso.required' => 'Por favor, selecione um curso.',
            'curso.in' => 'Por favor, selecione um curso válido.',
            'modalidade.required' => 'Por favor, selecione a modalidade de ensino.',
            'modalidade.in' => 'Por favor, selecione uma modalidade válida.',
            'termos.required' => 'Você deve aceitar os termos e condições.',
            'termos.accepted' => 'Você deve aceitar os termos e condições.',
        ];
    }
}
