<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoogleDriveFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'file_id',
        'mime_type',
        'web_view_link',
        'web_content_link',
        'thumbnail_link',
        'size',
        'path',
        'created_by',
        'parent_id',
        'is_folder',
        'is_starred',
        'is_trashed',
        'local_path',
        'is_local'
    ];

    protected $casts = [
        'size' => 'integer',
        'is_folder' => 'boolean',
        'is_starred' => 'boolean',
        'is_trashed' => 'boolean',
        'is_local' => 'boolean'
    ];

    // Relacionamento com o usuário que criou o arquivo
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relacionamento com a pasta pai
    public function parent()
    {
        return $this->belongsTo(GoogleDriveFile::class, 'parent_id');
    }

    // Relacionamento com os arquivos/pastas filhos
    public function children()
    {
        return $this->hasMany(GoogleDriveFile::class, 'parent_id');
    }

    // Escopo para arquivos não excluídos
    public function scopeNotTrashed($query)
    {
        return $query->where('is_trashed', false);
    }

    // Escopo para pastas
    public function scopeFolders($query)
    {
        return $query->where('is_folder', true);
    }

    // Escopo para arquivos
    public function scopeFiles($query)
    {
        return $query->where('is_folder', false);
    }

    // Escopo para arquivos favoritos
    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    // Retorna o tamanho formatado
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Retorna todos os ancestrais (caminho até a raiz)
    public function getAncestorsAttribute()
    {
        $ancestors = collect([]);
        $current = $this->parent;
        $visited = collect([$this->id]); // Evitar loops infinitos
        
        while ($current && !$visited->contains($current->id)) {
            $ancestors->push($current);
            $visited->push($current->id);
            $current = $current->parent;
        }
        
        return $ancestors->reverse();
    }

    // Retorna o ícone baseado no mime type
    public function getIconAttribute()
    {
        if ($this->is_folder) {
            return 'fas fa-folder';
        }

        $mimeTypeIcons = [
            'image/' => 'fas fa-image',
            'video/' => 'fas fa-video',
            'audio/' => 'fas fa-music',
            'application/pdf' => 'fas fa-file-pdf',
            'application/msword' => 'fas fa-file-word',
            'application/vnd.ms-excel' => 'fas fa-file-excel',
            'application/vnd.ms-powerpoint' => 'fas fa-file-powerpoint',
            'text/' => 'fas fa-file-alt',
            'application/zip' => 'fas fa-file-archive'
        ];

        foreach ($mimeTypeIcons as $type => $icon) {
            if (str_starts_with($this->mime_type, $type)) {
                return $icon;
            }
        }

        return 'fas fa-file';
    }
}
