<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'name',
        'description',
        'content',
        'variables',
        'category',
        'active'
    ];

    protected $casts = [
        'variables' => 'array',
        'active' => 'boolean'
    ];

    /**
     * Substituir variáveis no template
     */
    public function replaceVariables(array $data): string
    {
        $content = $this->content;

        // Substituir variáveis no formato {{variavel}}
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Validar se todas as variáveis necessárias foram fornecidas
     */
    public function validateVariables(array $data): bool
    {
        if (!$this->variables) {
            return true;
        }

        // Verificar se é array associativo ou simples
        $isAssoc = array_keys($this->variables) !== range(0, count($this->variables) - 1);
        
        if ($isAssoc) {
            // Se variables é um array associativo (chave => descrição)
            foreach (array_keys($this->variables) as $variable) {
                if (!isset($data[$variable]) || empty($data[$variable])) {
                    return false;
                }
            }
        } else {
            // Se variables é um array simples
            foreach ($this->variables as $variable) {
                if (!isset($data[$variable]) || empty($data[$variable])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Obter templates por categoria
     */
    public static function getByCategory(string $category)
    {
        return static::where('category', $category)
            ->where('active', true)
            ->get();
    }
} 