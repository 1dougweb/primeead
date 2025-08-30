<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parceiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\ParceiroAprovadoMail;
use App\Mail\ParceiroRejeitadoMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Str;

class ParceiroAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:parceiros.index');
    }

    /**
     * Lista todos os parceiros
     */
    public function index(Request $request)
    {
        $query = Parceiro::query();

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome_completo', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefone', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('cidade', 'like', "%{$search}%");
            });
        }

        if ($request->filled('modalidade')) {
            $query->where('modalidade_parceria', $request->modalidade);
        }

        if ($request->filled('estrutura')) {
            $query->where('possui_estrutura', $request->estrutura);
        }

        $parceiros = $query->orderBy('created_at', 'desc')->paginate(15);

        // Estatísticas
        $stats = [
            'total' => Parceiro::count(),
            'pendentes' => Parceiro::where('status', 'pendente')->count(),
            'aprovados' => Parceiro::where('status', 'aprovado')->count(),
            'ativos' => Parceiro::where('status', 'ativo')->count(),
            'rejeitados' => Parceiro::where('status', 'rejeitado')->count(),
        ];

        return view('admin.parceiros.index', compact('parceiros', 'stats'));
    }

    /**
     * Formulário para criar novo parceiro
     */
    public function create()
    {
        return view('admin.parceiros.create');
    }

    /**
     * Criar novo parceiro
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:parceiros,email',
            'telefone' => 'required|string|min:10|max:15',
            'whatsapp' => 'nullable|string|min:10|max:15',
            'documento' => 'required|string|min:11|max:18',
            'tipo_documento' => 'required|in:cpf,cnpj',
            'cep' => 'required|string|size:8',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|size:2',
            'modalidade_parceria' => 'required|in:Polo Presencial,EaD,Híbrido,Representante',
            'disponibilidade' => 'required|in:meio_periodo,integral,fins_semana,flexivel',
            'possui_estrutura' => 'required|boolean',
            'tem_site' => 'required|boolean',
            'site_url' => 'nullable|required_if:tem_site,1|url',
            'tem_experiencia_educacional' => 'required|boolean',
            'plano_negocio' => 'nullable|string|max:2000',
            'experiencia_vendas' => 'nullable|string|max:1000',
            'motivacao' => 'nullable|string|max:1000',
            'banco' => 'nullable|string|max:100',
            'agencia' => 'nullable|string|max:20',
            'conta' => 'nullable|string|max:20',
            'pix' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string|max:2000',
            'status' => 'required|in:pendente,aprovado,rejeitado,ativo,inativo',
            'comissao_percentual' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $parceiro = Parceiro::create($request->all());

            // Se for criado como aprovado, registrar data
            if ($request->status === 'aprovado') {
                $parceiro->update(['data_aprovacao' => now()]);
            }

            // Enviar mensagem de boas-vindas via WhatsApp
            try {
                $whatsappService = app(\App\Services\WhatsAppService::class);
                if ($whatsappService->hasValidSettings()) {
                    $whatsappService->sendParceiroBoasVindas($parceiro);
                    \Log::info('Mensagem de boas-vindas WhatsApp enviada para parceiro: ' . $parceiro->id);
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar mensagem WhatsApp para parceiro ' . $parceiro->id . ': ' . $e->getMessage());
                // Não impedir o fluxo se o WhatsApp falhar
            }

            DB::commit();

            return redirect()->route('admin.parceiros.show', $parceiro)
                           ->with('success', 'Parceiro criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['erro' => 'Erro ao criar parceiro.'])->withInput();
        }
    }

    /**
     * Exibe detalhes de um parceiro
     */
    public function show(Parceiro $parceiro)
    {
        return view('admin.parceiros.show', compact('parceiro'));
    }

    /**
     * Formulário para editar parceiro
     */
    public function edit(Parceiro $parceiro)
    {
        return view('admin.parceiros.edit', compact('parceiro'));
    }

    /**
     * Atualizar dados do parceiro
     */
    public function update(Request $request, Parceiro $parceiro)
    {
        $request->validate([
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:parceiros,email,' . $parceiro->id,
            'telefone' => 'required|string|min:10|max:15',
            'whatsapp' => 'nullable|string|min:10|max:15',
            'documento' => 'required|string|min:11|max:18',
            'tipo_documento' => 'required|in:cpf,cnpj',
            'cep' => 'required|string|size:8',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|size:2',
            'banco' => 'nullable|string|max:100',
            'agencia' => 'nullable|string|max:20',
            'conta' => 'nullable|string|max:20',
            'pix' => 'nullable|string|max:255',
            'experiencia_vendas' => 'nullable|string|max:1000',
            'motivacao' => 'nullable|string|max:1000',
            'disponibilidade' => 'required|in:meio_periodo,integral,fins_semana,flexivel',
            'status' => 'required|in:pendente,aprovado,rejeitado,ativo,inativo',
            'comissao_percentual' => 'required|numeric|min:0|max:100',
            'observacoes' => 'nullable|string|max:2000',
        ]);

        try {
            DB::beginTransaction();

            $parceiro->update($request->all());

            // Se mudou para aprovado, registrar data
            if ($request->status === 'aprovado' && $parceiro->status !== 'aprovado') {
                $parceiro->update(['data_aprovacao' => now()]);
            }

            DB::commit();

            return redirect()->route('admin.parceiros.show', $parceiro)
                           ->with('success', 'Parceiro atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['erro' => 'Erro ao atualizar parceiro.'])->withInput();
        }
    }

    /**
     * Aprovar parceiro
     */
    public function aprovar(Parceiro $parceiro)
    {
        try {
            DB::beginTransaction();

            $parceiro->aprovar();
            
            // Criar usuário para o parceiro se ainda não existir
            if (!$parceiro->usuario) {
                // Gerar senha temporária
                $senha = Str::random(10);
                
                $user = User::create([
                    'name' => $parceiro->nome_completo,
                    'email' => $parceiro->email,
                    'password' => bcrypt($senha),
                    'tipo_usuario' => 'parceiro',
                    'parceiro_id' => $parceiro->id,
                    'ativo' => true,
                    'criado_por' => auth()->user()->name
                ]);

                // Enviar email com as credenciais
                try {
                    Mail::to($parceiro->email)->send(new ParceiroAprovadoMail($parceiro, $user, $senha));
                } catch (\Exception $e) {
                    \Log::error('Erro ao enviar email de aprovação: ' . $e->getMessage());
                }
            } else {
                // Enviar email de aprovação normal
                try {
                    Mail::to($parceiro->email)->send(new ParceiroAprovadoMail($parceiro));
                } catch (\Exception $e) {
                    \Log::error('Erro ao enviar email de aprovação: ' . $e->getMessage());
                }
            }

            DB::commit();
            
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Parceiro aprovado com sucesso!']);
            }
            
            return back()->with('success', 'Parceiro aprovado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro ao aprovar parceiro.'], 422);
            }
            
            return back()->withErrors(['erro' => 'Erro ao aprovar parceiro.']);
        }
    }

    /**
     * Ativar parceiro
     */
    public function ativar(Parceiro $parceiro)
    {
        try {
            $parceiro->ativar();
            
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Parceiro ativado com sucesso!']);
            }
            
            return back()->with('success', 'Parceiro ativado com sucesso!');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro ao ativar parceiro.'], 422);
            }
            
            return back()->withErrors(['erro' => 'Erro ao ativar parceiro.']);
        }
    }

    /**
     * Rejeitar parceiro
     */
    public function rejeitar(Parceiro $parceiro)
    {
        try {
            $parceiro->rejeitar();
            
            // Enviar email de rejeição para o parceiro
            try {
                Mail::to($parceiro->email)->send(new ParceiroRejeitadoMail($parceiro));
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar email de rejeição: ' . $e->getMessage());
            }
            
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Parceiro rejeitado.']);
            }
            
            return back()->with('success', 'Parceiro rejeitado.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro ao rejeitar parceiro.'], 422);
            }
            
            return back()->withErrors(['erro' => 'Erro ao rejeitar parceiro.']);
        }
    }

    /**
     * Inativar parceiro
     */
    public function inativar(Parceiro $parceiro)
    {
        try {
            $parceiro->inativar();
            
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Parceiro inativado.']);
            }
            
            return back()->with('success', 'Parceiro inativado.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro ao inativar parceiro.'], 422);
            }
            
            return back()->withErrors(['erro' => 'Erro ao inativar parceiro.']);
        }
    }

    /**
     * Excluir parceiro
     */
    public function destroy(Parceiro $parceiro)
    {
        try {
            $parceiro->delete();
            return redirect()->route('admin.parceiros.index')
                           ->with('success', 'Parceiro excluído com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['erro' => 'Erro ao excluir parceiro.']);
        }
    }

    /**
     * Atualizar último contato
     */
    public function atualizarContato(Parceiro $parceiro)
    {
        try {
            $parceiro->atualizarUltimoContato();
            
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Último contato atualizado!']);
            }
            
            return back()->with('success', 'Último contato atualizado!');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro ao atualizar contato.'], 422);
            }
            
            return back()->withErrors(['erro' => 'Erro ao atualizar contato.']);
        }
    }

    /**
     * Exportar parceiros (CSV)
     */
    public function exportar(Request $request)
    {
        $query = Parceiro::query();

        // Aplicar mesmos filtros da listagem
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome_completo', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefone', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('cidade', 'like', "%{$search}%");
            });
        }

        $parceiros = $query->orderBy('created_at', 'desc')->get();

        $filename = 'parceiros_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($parceiros) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'ID', 'Nome', 'Email', 'Telefone', 'WhatsApp', 'Documento', 'Tipo Doc',
                'CEP', 'Endereço', 'Número', 'Complemento', 'Bairro', 'Cidade', 'Estado',
                'Banco', 'Agência', 'Conta', 'PIX', 'Disponibilidade', 'Status', 'Comissão %',
                'Data Cadastro', 'Data Aprovação', 'Último Contato'
            ], ';');

            // Dados
            foreach ($parceiros as $parceiro) {
                fputcsv($file, [
                    $parceiro->id,
                    $parceiro->nome_completo,
                    $parceiro->email,
                    $parceiro->telefone_formatado,
                    $parceiro->whatsapp_formatado,
                    $parceiro->documento_formatado,
                    strtoupper($parceiro->tipo_documento),
                    $parceiro->cep_formatado,
                    $parceiro->endereco,
                    $parceiro->numero,
                    $parceiro->complemento,
                    $parceiro->bairro,
                    $parceiro->cidade,
                    $parceiro->estado,
                    $parceiro->banco,
                    $parceiro->agencia,
                    $parceiro->conta,
                    $parceiro->pix,
                    $parceiro->disponibilidade_formatada,
                    ucfirst($parceiro->status),
                    $parceiro->comissao_percentual . '%',
                    $parceiro->created_at->format('d/m/Y H:i'),
                    $parceiro->data_aprovacao ? $parceiro->data_aprovacao->format('d/m/Y H:i') : '',
                    $parceiro->ultimo_contato ? $parceiro->ultimo_contato->format('d/m/Y H:i') : '',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
