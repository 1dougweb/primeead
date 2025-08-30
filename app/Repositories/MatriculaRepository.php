<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Matricula;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MatriculaRepository
{
    public function __construct(
        private readonly Matricula $model
    ) {}

    /**
     * Buscar matrícula por CPF
     */
    public function findByCpf(string $cpf): ?Matricula
    {
        return $this->model
            ->where('cpf', $this->cleanCpf($cpf))
            ->first();
    }

    /**
     * Buscar matrícula por número de matrícula
     */
    public function findByNumeroMatricula(string $numeroMatricula): ?Matricula
    {
        return $this->model
            ->where('numero_matricula', $numeroMatricula)
            ->first();
    }

    /**
     * Criar nova matrícula
     */
    public function create(array $data): Matricula
    {
        return DB::transaction(function () use ($data) {
            $matricula = $this->model->create($data);
            
            // Gerar número de matrícula se não foi fornecido
            if (empty($matricula->numero_matricula)) {
                $matricula->update([
                    'numero_matricula' => $this->generateNumeroMatricula()
                ]);
            }
            
            return $matricula->fresh();
        });
    }

    /**
     * Atualizar matrícula existente
     */
    public function update(Matricula $matricula, array $data): Matricula
    {
        $matricula->update($data);
        return $matricula->fresh();
    }

    /**
     * Verificar se matrícula existe por CPF
     */
    public function existsByCpf(string $cpf): bool
    {
        return $this->model
            ->where('cpf', $this->cleanCpf($cpf))
            ->exists();
    }

    /**
     * Buscar matrículas por parceiro
     */
    public function findByParceiro(int $parceiroId): Collection
    {
        return $this->model
            ->where('parceiro_id', $parceiroId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Buscar matrículas com paginação
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['parceiro'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Gerar número de matrícula único
     */
    private function generateNumeroMatricula(): string
    {
        $currentYear = date('Y');
        $lastNumber = $this->model
            ->whereYear('created_at', $currentYear)
            ->count() + 1;
        
        return $currentYear . str_pad($lastNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Limpar CPF removendo caracteres especiais
     */
    private function cleanCpf(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }

    /**
     * Buscar estatísticas de matrículas
     */
    public function getStats(): array
    {
        return [
            'total' => $this->model->count(),
            'this_month' => $this->model->whereMonth('created_at', now()->month)->count(),
            'this_year' => $this->model->whereYear('created_at', now()->year)->count(),
            'by_status' => $this->model->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
        ];
    }

    /**
     * Obter query builder para exportação
     */
    public function getQueryBuilder()
    {
        return $this->model->newQuery();
    }

    /**
     * Buscar matrículas com filtros para exportação
     */
    public function getForExport(array $filters = [], ?string $sortBy = null, string $sortDirection = 'asc', ?int $limit = null)
    {
        $query = $this->getQueryBuilder();

        // Aplicar filtros
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['parceiro_id']) && !empty($filters['parceiro_id'])) {
            $query->where('parceiro_id', $filters['parceiro_id']);
        }

        if (isset($filters['data_inicio']) && !empty($filters['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filters['data_inicio']);
        }

        if (isset($filters['data_fim']) && !empty($filters['data_fim'])) {
            $query->whereDate('created_at', '<=', $filters['data_fim']);
        }

        if (isset($filters['curso']) && !empty($filters['curso'])) {
            $query->where('curso', 'like', '%' . $filters['curso'] . '%');
        }

        if (isset($filters['modalidade']) && !empty($filters['modalidade'])) {
            $query->where('modalidade', $filters['modalidade']);
        }

        if (isset($filters['valor_min']) && !empty($filters['valor_min'])) {
            $query->where('valor_total_curso', '>=', $filters['valor_min']);
        }

        if (isset($filters['valor_max']) && !empty($filters['valor_max'])) {
            $query->where('valor_total_curso', '<=', $filters['valor_max']);
        }

        // Aplicar ordenação
        if ($sortBy) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Aplicar limite
        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }
}
