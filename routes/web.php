<?php

use App\Http\Controllers\InscricaoController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractTemplateController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\ParceiroController;
use App\Http\Controllers\Admin\ParceiroAdminController;
use App\Http\Controllers\Admin\GoogleDriveFileController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\AiSettingsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\WebhookTestController;

// Webhook routes (must be before CSRF protection)
Route::post('/webhook/mercadopago', [WebhookController::class, 'mercadoPago'])->name('webhook.mercadopago')->withoutMiddleware(['web', 'throttle:api', 'throttle']);
Route::get('/webhook/test', [WebhookTestController::class, 'test'])->name('webhook.test');
Route::post('/webhook/debug', [WebhookTestController::class, 'debug'])->name('webhook.debug');
Route::get('/webhook/mercadopago-test', [WebhookTestController::class, 'testMercadoPago'])->name('webhook.mercadopago.test');

// Rota de teste específica para webhook sem middleware
Route::post('/webhook-test', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Webhook test endpoint working',
        'data' => request()->all(),
        'headers' => request()->headers->all()
    ]);
});

// Rota de webhook sem middleware web
Route::post('/webhook-simple', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Webhook simple endpoint working',
        'data' => request()->all(),
        'timestamp' => now()
    ])->withoutMiddleware(['web']);
});

// Rota de webhook sem nenhum middleware
Route::post('/webhook-raw', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Webhook raw endpoint working',
        'data' => request()->all(),
        'timestamp' => now()
    ])->withoutMiddleware(['web', 'throttle:api', 'throttle']);
});

// Rota de teste simples para debug
Route::get('/test-simple', function() {
    return response()->json(['status' => 'ok', 'message' => 'Test simple working']);
});

// Rota de teste para Blade
Route::get('/test-blade', function() {
    return view('test');
});

// Rotas públicas
Route::get('/', [InscricaoController::class, 'index'])->name('home');
Route::post('/inscricao', [InscricaoController::class, 'store'])->name('inscricao.store');
Route::get('/obrigado', [InscricaoController::class, 'obrigado'])->name('obrigado');

// Rotas de contato
Route::get('/contato', [ContatoController::class, 'index'])->name('contato');
Route::post('/contato', [ContatoController::class, 'store'])->name('contato.store');

// Páginas complementares
Route::get('/politica-privacidade', function() {
    return view('politica-privacidade');
})->name('politica-privacidade');

Route::get('/politica-rembolso', function() {
    return view('politica-rembolso');
})->name('politica-rembolso');

// Rota para download de autorização MEC
Route::get('/mec-authorization', function() {
    $filePath = \App\Models\SystemSetting::get('landing_mec_authorization_file', '');
    
    if (!$filePath || !\Storage::disk('public')->exists($filePath)) {
        abort(404, 'Arquivo não encontrado');
    }
    
    $fullPath = storage_path('app/public/' . $filePath);
    
    return response()->file($fullPath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="autorizacao-mec.pdf"'
    ]);
})->name('mec.authorization');

// Rotas públicas de parceiros
Route::get('/parceiros/cadastro', [ParceiroController::class, 'create'])->name('parceiros.cadastro');
Route::post('/parceiros/cadastro', [ParceiroController::class, 'store'])->name('parceiros.store');
Route::get('/parceiros/sucesso', [ParceiroController::class, 'sucesso'])->name('parceiros.sucesso');
Route::get('/api/cep/{cep}', [ParceiroController::class, 'buscarCep'])->name('api.cep.buscar');

// Rotas de rastreamento de email (públicas)
Route::get('/email/track/open/{trackingCode}', [EmailCampaignController::class, 'trackOpen'])->name('admin.email-campaigns.track-open');
Route::get('/email/track/click/{trackingCode}/{url}', [EmailCampaignController::class, 'trackClick'])->name('admin.email-campaigns.track-click');
Route::get('/email/unsubscribe/{trackingCode}', [EmailCampaignController::class, 'unsubscribe'])->name('admin.email-campaigns.unsubscribe');

// Rotas autenticadas
// Rotas de perfil (autenticadas)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('profile.edit', ['user' => auth()->user()]);
    })->name('profile.edit');
    
    Route::patch('/profile', function () {
        $user = auth()->user();
        $user->name = request('name');
        $user->save();
        return redirect()->route('profile.edit')->with('success', 'Perfil atualizado com sucesso!');
    })->name('profile.update');
    
    Route::patch('/profile/password', function () {
        request()->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        
        $user = auth()->user();
        $user->password = bcrypt(request('password'));
        $user->save();
        
        return redirect()->route('profile.edit')->with('success', 'Senha atualizada com sucesso!');
    })->name('profile.password.update');
    
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/test-profile', function () {
    return 'Profile test works!';
})->name('test.profile');
// Rota para obter novo token CSRF
Route::get('/admin/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('auth')->name('admin.csrf-token');

// Rota de emergência para forçar saída da impersonation
Route::get('/emergency-stop-impersonation', function () {
    // Limpar todas as variáveis de sessão relacionadas à impersonation
    session()->forget(['is_impersonating', 'impersonating_user_id', 'admin_id', 'admin_name', 'admin_email', 'admin_tipo', 'admin_logged_in']);
    
    // Fazer logout completo
    auth()->logout();
    
    // Invalidar a sessão
    session()->invalidate();
    session()->regenerateToken();
    
    return redirect('/login')->with('success', 'Impersonation forcefully stopped. Please login again.');
})->name('emergency.stop.impersonation');

// Rotas administrativas (protegidas)
Route::middleware(['auth', 'theme', \App\Http\Middleware\CheckMenuPermission::class])->group(function () {
    // Dashboard e páginas administrativas
    Route::prefix('dashboard')->group(function () {
        // Dashboard principal
        Route::get('/', [AdminController::class, 'dashboard'])
            ->name('dashboard');
        
        // Rotas para vendedores e admin
        Route::middleware(['admin'])->group(function () {
            // Inscrições
            Route::get('/inscricoes', [AdminController::class, 'inscricoes'])->name('admin.inscricoes');
            Route::get('/inscricoes/exportar', [AdminController::class, 'exportarCSV'])->name('admin.inscricoes.exportar');
            
            // Importação de Inscrições
            Route::get('/inscricoes/importar', [AdminController::class, 'importarInscricoes'])->name('admin.inscricoes.importar');
            Route::post('/inscricoes/importar', [AdminController::class, 'processarImportacaoInscricoes'])->name('admin.inscricoes.importar.processar');
            Route::get('/inscricoes/importar/template', [AdminController::class, 'downloadTemplateInscricoes'])->name('admin.inscricoes.importar.template');
            
            // Contagem e exclusão em massa
            Route::get('/inscricoes/count', [AdminController::class, 'countInscricoes'])->name('admin.inscricoes.count');
            Route::post('/inscricoes/apagar-todas', [AdminController::class, 'apagarTodasInscricoes'])->name('admin.inscricoes.apagar-todas');
            
            // Detalhes para modal
            Route::get('/inscricoes/{id}/detalhes-modal', [AdminController::class, 'detalhesModal'])->name('admin.inscricoes.detalhes-modal');
            
            Route::get('/inscricoes/{id}/editar', [AdminController::class, 'editarInscricao'])->name('admin.inscricoes.editar');
            Route::get('/inscricoes/{id}/detalhes', [AdminController::class, 'detalhes'])->name('admin.inscricoes.detalhes');
            Route::put('/inscricoes/{id}', [AdminController::class, 'atualizarInscricao'])->name('admin.inscricoes.atualizar');
            Route::delete('/inscricoes/{id}', [AdminController::class, 'deletarInscricao'])->name('admin.inscricoes.deletar');
            Route::post('/inscricoes/{id}/etiqueta', [AdminController::class, 'atualizarEtiqueta'])->name('admin.inscricoes.etiqueta');
            Route::get('/inscricoes/{id}/show', [AdminController::class, 'show'])->name('admin.inscricoes.show');
            Route::post('/inscricoes/{id}/unlock', [AdminController::class, 'unlock'])->name('admin.inscricoes.unlock');
            Route::get('/inscricoes/{id}/edit', [AdminController::class, 'editModal'])->name('admin.inscricoes.edit.modal');
            Route::put('/inscricoes/{id}/status', [AdminController::class, 'updateStatus'])->name('admin.inscricoes.status');
            Route::post('/inscricoes/{id}/nota', [AdminController::class, 'addNote'])->name('admin.inscricoes.nota');
            Route::post('/inscricoes/{id}/marcar-contato', [AdminController::class, 'markContact'])->name('admin.inscricoes.marcar-contato');

            // Importação e Exportação de Matrículas (DEVE vir ANTES da rota resource)
            Route::prefix('matriculas')->group(function () {
                           // Importação
           Route::get('/importar', [\App\Http\Controllers\Admin\MatriculaImportController::class, 'index'])->name('admin.matriculas.importar');
           Route::post('/importar', [\App\Http\Controllers\Admin\MatriculaImportController::class, 'store'])->name('admin.matriculas.importar.store');
           Route::post('/importar/analyze', [\App\Http\Controllers\Admin\MatriculaImportController::class, 'analyzeFile'])->name('admin.matriculas.importar.analyze');
           Route::get('/importar/template', [\App\Http\Controllers\Admin\MatriculaImportController::class, 'downloadTemplate'])->name('admin.matriculas.importar.template');
           Route::get('/importar/status', [\App\Http\Controllers\Admin\MatriculaImportController::class, 'status'])->name('admin.matriculas.importar.status');
           Route::post('/importar/cleanup', [\App\Http\Controllers\Admin\MatriculaImportController::class, 'cleanup'])->name('admin.matriculas.importar.cleanup');
                
                // Exportação
                Route::get('/exportar', [\App\Http\Controllers\Admin\MatriculaExportController::class, 'index'])->name('admin.matriculas.exportar');
                Route::post('/exportar', [\App\Http\Controllers\Admin\MatriculaExportController::class, 'store'])->name('admin.matriculas.exportar.store');
                Route::get('/exportar/status', [\App\Http\Controllers\Admin\MatriculaExportController::class, 'status'])->name('admin.matriculas.exportar.status');
                Route::post('/exportar/cleanup', [\App\Http\Controllers\Admin\MatriculaExportController::class, 'cleanup'])->name('admin.matriculas.exportar.cleanup');
                Route::get('/exportar/download/{file}', [\App\Http\Controllers\Admin\MatriculaExportController::class, 'download'])->name('admin.matriculas.export.download');
            });

            // Matrículas
            Route::resource('matriculas', MatriculaController::class)->names([
                'index' => 'admin.matriculas.index',
                'create' => 'admin.matriculas.create',
                'store' => 'admin.matriculas.store',
                'show' => 'admin.matriculas.show',
                'edit' => 'admin.matriculas.edit',
                'update' => 'admin.matriculas.update',
                'destroy' => 'admin.matriculas.destroy',
            ]);
            Route::post('/matriculas/{matricula}/create-drive-folder', [MatriculaController::class, 'createDriveFolder'])->name('admin.matriculas.create-drive-folder');
            Route::get('/matriculas/{matricula}/drive-files', [MatriculaController::class, 'listDriveFiles'])->name('admin.matriculas.drive-files');
            Route::post('/matriculas/{matricula}/regenerate-payments', [MatriculaController::class, 'regeneratePayments'])->name('admin.matriculas.regenerate-payments');

            // Kanban
            Route::get('/kanban', [KanbanController::class, 'index'])->name('admin.kanban.index');
            Route::post('/kanban/move', [KanbanController::class, 'moveCard'])->name('admin.kanban.move');
            Route::get('/kanban/lead/{id}', [KanbanController::class, 'getLeadData'])->name('admin.kanban.lead');
            Route::put('/kanban/lead/{id}', [KanbanController::class, 'updateLead'])->name('admin.kanban.update');
            Route::post('/kanban/lead/{id}/note', [KanbanController::class, 'addNote'])->name('admin.kanban.note');
            Route::post('/kanban/lead/{id}/contact', [KanbanController::class, 'markContact'])->name('admin.kanban.contact');
            Route::delete('/kanban/lead/{id}/photo', [KanbanController::class, 'deletePhoto'])->name('admin.kanban.photo.delete');
            Route::get('/kanban/followups', [KanbanController::class, 'getFollowUps'])->name('admin.kanban.followups');
            Route::get('/kanban/filter', [KanbanController::class, 'filter'])->name('admin.kanban.filter');
            
            // Gerenciamento de colunas do Kanban
            Route::get('/kanban/columns', [KanbanController::class, 'listColumns'])->name('admin.kanban.columns.list');
            Route::post('/kanban/columns', [KanbanController::class, 'createColumn'])->name('admin.kanban.columns.create');
            Route::put('/kanban/columns/{id}', [KanbanController::class, 'updateColumn'])->name('admin.kanban.columns.update');
            Route::post('/kanban/columns/reorder', [KanbanController::class, 'reorderColumns'])->name('admin.kanban.columns.reorder');
            Route::delete('/kanban/columns/{id}', [KanbanController::class, 'deleteColumn'])->name('admin.kanban.columns.delete');
            
            // Tarefas e histórico para o Kanban
            Route::get('/inscricoes/{id}/tasks', [KanbanController::class, 'getTasks'])->name('admin.inscricoes.tasks');
            Route::post('/inscricoes/{id}/tasks', [KanbanController::class, 'createTask'])->name('admin.inscricoes.tasks.create');
            Route::put('/inscricoes/{id}/tasks/{taskId}', [KanbanController::class, 'updateTask'])->name('admin.inscricoes.tasks.update');
            Route::delete('/inscricoes/{id}/tasks/{taskId}', [KanbanController::class, 'deleteTask'])->name('admin.inscricoes.tasks.delete');
            Route::get('/inscricoes/{id}/history', [KanbanController::class, 'getHistory'])->name('admin.inscricoes.history');

            // Parceiros (admin)
            Route::get('/parceiros', [ParceiroAdminController::class, 'index'])->name('admin.parceiros.index');
            Route::get('/parceiros/exportar', [ParceiroAdminController::class, 'exportar'])->name('admin.parceiros.exportar');
            Route::get('/parceiros/create', [ParceiroAdminController::class, 'create'])->name('admin.parceiros.create');
            Route::post('/parceiros', [ParceiroAdminController::class, 'store'])->name('admin.parceiros.store');
            Route::get('/parceiros/{parceiro}', [ParceiroAdminController::class, 'show'])->name('admin.parceiros.show');
            Route::get('/parceiros/{parceiro}/edit', [ParceiroAdminController::class, 'edit'])->name('admin.parceiros.edit');
            Route::put('/parceiros/{parceiro}', [ParceiroAdminController::class, 'update'])->name('admin.parceiros.update');
            Route::delete('/parceiros/{parceiro}', [ParceiroAdminController::class, 'destroy'])->name('admin.parceiros.destroy');
            Route::post('/parceiros/{parceiro}/aprovar', [ParceiroAdminController::class, 'aprovar'])->name('admin.parceiros.aprovar');
            Route::post('/parceiros/{parceiro}/ativar', [ParceiroAdminController::class, 'ativar'])->name('admin.parceiros.ativar');
            Route::post('/parceiros/{parceiro}/rejeitar', [ParceiroAdminController::class, 'rejeitar'])->name('admin.parceiros.rejeitar');
            Route::post('/parceiros/{parceiro}/inativar', [ParceiroAdminController::class, 'inativar'])->name('admin.parceiros.inativar');

            // Pagamentos (admin)
            Route::get('/pagamentos', [PaymentController::class, 'dashboard'])->name('admin.payments.dashboard');
            Route::get('/pagamentos/lista', [PaymentController::class, 'index'])->name('admin.payments.index');
            Route::get('/pagamentos/criar', [PaymentController::class, 'create'])->name('admin.payments.create');
            Route::post('/pagamentos', [PaymentController::class, 'store'])->name('admin.payments.store');
            Route::get('/pagamentos/{payment}', [PaymentController::class, 'show'])->name('admin.payments.show');
            Route::get('/pagamentos/{payment}/editar', [PaymentController::class, 'edit'])->name('admin.payments.edit');
            Route::put('/pagamentos/{payment}', [PaymentController::class, 'update'])->name('admin.payments.update');
            Route::delete('/pagamentos/{payment}', [PaymentController::class, 'destroy'])->name('admin.payments.destroy');
            Route::post('/pagamentos/{payment}/mark-paid', [PaymentController::class, 'markAsPaid'])->name('admin.payments.mark-paid');
            Route::post('/pagamentos/{payment}/resend-notifications', [PaymentController::class, 'resendNotifications'])->name('admin.payments.resend-notifications');
            Route::post('/pagamentos/test-connection', [PaymentController::class, 'testConnection'])->name('admin.payments.test-connection');
            Route::get('/pagamentos/{payment}/download-boleto', [PaymentController::class, 'downloadBoleto'])->name('admin.payments.download-boleto');

            // Sistema de Ajuda
            Route::prefix('ajuda')->group(function () {
                Route::get('/', [\App\Http\Controllers\HelpController::class, 'index'])->name('admin.help.index');
                Route::get('/mercado-pago-nova-api', [\App\Http\Controllers\HelpController::class, 'mercadoPagoNovaApi'])->name('admin.help.mercado-pago-nova-api');
                Route::get('/mercado-pago', [\App\Http\Controllers\HelpController::class, 'mercadoPago'])->name('admin.help.mercado-pago');
                Route::get('/configuracao-pagamentos', [\App\Http\Controllers\HelpController::class, 'configuracaoPagamentos'])->name('admin.help.configuracao-pagamentos');
                Route::get('/dashboard-pagamentos', [\App\Http\Controllers\HelpController::class, 'dashboardPagamentos'])->name('admin.help.dashboard-pagamentos');
    
                Route::get('/automacao-pagamentos', [\App\Http\Controllers\HelpController::class, 'automacaoPagamentos'])->name('admin.help.automacao-pagamentos');
            });

            // Contatos (baseado em permissões)
            Route::middleware(['permission:contatos.index'])->group(function () {
                Route::resource('contacts', ContactController::class)->names([
                    'index' => 'contacts.index',
                    'create' => 'contacts.create',
                    'store' => 'contacts.store',
                    'show' => 'contacts.show',
                    'edit' => 'contacts.edit',
                    'update' => 'contacts.update',
                    'destroy' => 'contacts.destroy',
                ]);
                Route::get('/contacts/search', [ContactController::class, 'search'])->name('contacts.search');
            });
            
            // Rota para sair da impersonation (disponível para todos os usuários autenticados)
            Route::post('/usuarios/stop-impersonation', [UserController::class, 'stopImpersonation'])->name('admin.usuarios.stop-impersonation');
            
            // Rota de teste para debug
            Route::get('/test-impersonation', function () {
                return response()->json([
                    'authenticated' => auth()->check(),
                    'user_id' => auth()->id(),
                    'user_type' => auth()->user()->tipo_usuario ?? 'not_authenticated',
                    'is_impersonating' => session('is_impersonating'),
                    'impersonating_user_id' => session('impersonating_user_id'),
                    'session_data' => session()->all()
                ]);
            })->name('test.impersonation');
            
            // Rota de teste para arquivos
            Route::get('/test-files', function () {
                return response()->json([
                    'message' => 'Rota de arquivos funcionando',
                    'authenticated' => auth()->check(),
                    'user_id' => auth()->id()
                ]);
            })->name('test.files');
        });

        // Google Drive Files (acessível para usuários com permissão google-drive.index)
        Route::middleware(['admin'])->group(function () {
            Route::get('/files', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'index'])->name('admin.files.index');
            Route::post('/files', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'store'])->name('admin.files.store');
            Route::post('/files/folder', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'createFolder'])->name('admin.files.create-folder');
            Route::get('/files/folders', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'listFolders'])->name('admin.files.list-folders');
            Route::post('/files/parent-folder', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'createParentFolder'])->name('admin.files.create-parent-folder');
            Route::get('/files/find-by-file-id/{fileId}', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'findByFileId'])->name('admin.files.find-by-file-id');
            Route::get('/files/{id}/download', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'download'])->name('admin.files.download');
            Route::delete('/files/{id}', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'destroy'])->name('admin.files.destroy');
            Route::post('/files/{id}/trash', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'trash'])->name('admin.files.trash');
            Route::post('/files/{id}/move-to-trash', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'moveToTrash'])->name('admin.files.move-to-trash');
            Route::post('/files/delete-folder/{id}', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'destroy'])->name('admin.files.delete-folder');
            Route::post('/files/delete-recursive/{id}', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'destroyRecursive'])->name('admin.files.delete-recursive');
            Route::post('/files/trash-folder/{id}', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'moveToTrash'])->name('admin.files.trash-folder');
            Route::post('/files/{id}/restore', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'restore'])->name('admin.files.restore');
            Route::put('/files/{id}/rename', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'rename'])->name('admin.files.rename');
            Route::put('/files/{id}/move', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'move'])->name('admin.files.move');
            Route::post('/files/by-file-id/{fileId}', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'accessByFileId'])->name('admin.files.by-file-id');
            Route::post('/files/cleanup', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'cleanupRecords'])->name('admin.files.cleanup');
            Route::post('/files/force-cleanup', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'forceCleanup'])->name('admin.files.force-cleanup');
            Route::post('/files/sync-all', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'syncAll'])->name('admin.files.sync-all');
            
            // Google Drive Sync Routes (movidas da API para web)
            Route::post('/files/sync', [\App\Http\Controllers\Admin\GoogleDriveSyncController::class, 'sync'])->name('admin.files.sync');
            Route::get('/files/check-changes', [\App\Http\Controllers\Admin\GoogleDriveSyncController::class, 'checkChanges'])->name('admin.files.check-changes');
            
            // Sharing and Permissions
            Route::get('/files/{id}/permissions', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'getPermissions'])->name('admin.files.permissions');
            Route::get('/files/{id}/check-permissions', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'checkPermissions'])->name('admin.files.check-permissions');
            Route::post('/files/{id}/share-user', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'shareWithUser'])->name('admin.files.share-user');
            Route::post('/files/{id}/public-link', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'createPublicLink'])->name('admin.files.public-link');
            Route::post('/files/{id}/embed', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'generateEmbed'])->name('admin.files.embed');
            Route::delete('/files/{id}/permissions', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'removePermission'])->name('admin.files.remove-permission');
            Route::put('/files/{id}/permissions', [\App\Http\Controllers\Admin\GoogleDriveFileController::class, 'updatePermission'])->name('admin.files.update-permission');
        });

        // Rotas específicas para admin
        Route::middleware(['admin_only'])->group(function () {
            // Monitoramento
            Route::get('/monitoramento', [AdminController::class, 'monitoramento'])->name('admin.monitoramento');

            // Configurações (apenas para admins)
            Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
            Route::post('/admin/settings/update', [SettingsController::class, 'update'])->name('admin.settings.update');
            Route::post('/admin/settings/reset', [SettingsController::class, 'reset'])->name('admin.settings.reset');
            Route::post('/admin/settings/execute-migration', [SettingsController::class, 'executeMigration'])
                ->name('admin.settings.execute-migration')
                ->middleware(['web', 'auth', 'json_response']);
            Route::post('/admin/settings/test-email', [SettingsController::class, 'testEmailConnection'])->name('admin.settings.test-email');
            Route::post('/admin/settings/upload-logo', [SettingsController::class, 'uploadLogo'])->name('admin.settings.upload-logo');
            Route::post('/admin/settings/reset-logo', [SettingsController::class, 'resetLogo'])->name('admin.settings.reset-logo');

            // Configurações do ChatGPT (apenas para admins)
            Route::get('/settings/ai', [AiSettingsController::class, 'index'])->name('admin.settings.ai');
            Route::put('/settings/ai', [AiSettingsController::class, 'update'])->name('admin.settings.ai.update');
            Route::post('/settings/ai/test', [AiSettingsController::class, 'testConnection'])->name('admin.settings.ai.test');

            // Templates de email
            Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('admin.email-templates.index');
            Route::get('/email-templates/{template}/edit', [EmailTemplateController::class, 'edit'])->name('admin.email-templates.edit');
            Route::put('/email-templates/{template}', [EmailTemplateController::class, 'update'])->name('admin.email-templates.update');
            Route::post('/email-templates/{template}/restore', [EmailTemplateController::class, 'restore'])->name('admin.email-templates.restore');
            Route::post('/email-templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('admin.email-templates.preview');
            Route::post('/email-templates/{template}/send-test', [EmailTemplateController::class, 'sendTest'])->name('admin.email-templates.send-test');

            // Usuários (apenas para admins)
            Route::get('/usuarios', [UserController::class, 'index'])->name('admin.usuarios.index');
            Route::get('/usuarios/criar', [UserController::class, 'create'])->name('admin.usuarios.create');
            Route::post('/usuarios', [UserController::class, 'store'])->name('admin.usuarios.store');
            Route::get('/usuarios/{id}', [UserController::class, 'show'])->name('admin.usuarios.show');
            Route::get('/usuarios/{id}/editar', [UserController::class, 'edit'])->name('admin.usuarios.edit');
            Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('admin.usuarios.update');
            Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('admin.usuarios.destroy');
            Route::post('/usuarios/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.usuarios.toggle-status');
            Route::post('/usuarios/{id}/impersonate', [UserController::class, 'impersonate'])->name('admin.usuarios.impersonate');


                

        });

        // WhatsApp (baseado em permissões)
        Route::middleware(['permission:whatsapp.index'])->group(function () {
            Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('admin.settings.whatsapp');
            Route::put('/whatsapp', [WhatsAppController::class, 'update'])->name('admin.settings.whatsapp.update');
            Route::get('/whatsapp/qr-code', [WhatsAppController::class, 'qrCode'])->name('admin.settings.whatsapp.qr-code');
            Route::post('/whatsapp/create-instance', [WhatsAppController::class, 'createInstance'])->name('admin.settings.whatsapp.create-instance');
            Route::post('/whatsapp/force-recreate', [WhatsAppController::class, 'forceRecreate'])->name('admin.settings.whatsapp.force-recreate');
            Route::get('/whatsapp/status', [WhatsAppController::class, 'status'])->name('admin.settings.whatsapp.status');
            Route::get('/whatsapp/check-instance', [WhatsAppController::class, 'checkInstance'])->name('admin.settings.whatsapp.check-instance');
            Route::get('/whatsapp/check-connection-change', [WhatsAppController::class, 'checkConnectionChange'])->name('admin.settings.whatsapp.check-connection-change');
            Route::post('/whatsapp/disconnect', [WhatsAppController::class, 'disconnect'])->name('admin.settings.whatsapp.disconnect');
            Route::post('/whatsapp/reconnect', [WhatsAppController::class, 'reconnect'])->name('admin.settings.whatsapp.reconnect');
            Route::delete('/whatsapp/delete-instance', [WhatsAppController::class, 'deleteInstance'])->name('admin.settings.whatsapp.delete-instance');
            Route::get('/whatsapp/diagnostic', [WhatsAppController::class, 'diagnostic'])->name('admin.settings.whatsapp.diagnostic');
            Route::post('/whatsapp/test-message', [WhatsAppController::class, 'testMessage'])->name('admin.settings.whatsapp.test-message');
            Route::post('/whatsapp/monitor', [WhatsAppController::class, 'monitor'])->name('admin.settings.whatsapp.monitor');
            
            // Templates do WhatsApp
            Route::resource('/whatsapp/templates', \App\Http\Controllers\Admin\WhatsAppTemplateController::class)->names([
                'index' => 'admin.whatsapp.templates.index',
                'create' => 'admin.whatsapp.templates.create',
                'store' => 'admin.whatsapp.templates.store',
                'show' => 'admin.whatsapp.templates.show',
                'edit' => 'admin.whatsapp.templates.edit',
                'update' => 'admin.whatsapp.templates.update',
                'destroy' => 'admin.whatsapp.templates.destroy',
            ]);
            Route::post('/whatsapp/templates/{template}/test', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'test'])
                ->name('admin.whatsapp.templates.test');
            Route::post('/whatsapp/templates/generate-ai', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'generateWithAi'])
                ->name('admin.whatsapp.templates.generate-ai');
        });

        // Campanhas de email (admin e mídia)
        Route::middleware(['admin_or_media'])->group(function () {
            Route::get('/email-campaigns', [EmailCampaignController::class, 'index'])->name('admin.email-campaigns.index');
            Route::get('/email-campaigns/create', [EmailCampaignController::class, 'create'])->name('admin.email-campaigns.create');
            
            // Templates de campanhas
            Route::get('/email-campaigns/templates', [EmailCampaignController::class, 'templates'])->name('admin.email-campaigns.templates');
            Route::get('/email-campaigns/templates/create', [EmailCampaignController::class, 'createTemplate'])->name('admin.email-campaigns.templates.create');
            Route::post('/email-campaigns/templates', [EmailCampaignController::class, 'storeTemplate'])->name('admin.email-campaigns.templates.store');
            Route::get('/email-campaigns/templates/{id}', [EmailCampaignController::class, 'getTemplate'])->name('admin.email-campaigns.templates.get');
            Route::get('/email-campaigns/templates/{id}/edit', [EmailCampaignController::class, 'editTemplate'])->name('admin.email-campaigns.templates.edit');
            Route::put('/email-campaigns/templates/{id}', [EmailCampaignController::class, 'updateTemplate'])->name('admin.email-campaigns.templates.update');
            Route::delete('/email-campaigns/templates/{id}', [EmailCampaignController::class, 'destroyTemplate'])->name('admin.email-campaigns.templates.destroy');
            Route::post('/email-campaigns/templates/generate-ai', [EmailCampaignController::class, 'generateTemplateWithAi'])->name('admin.email-campaigns.templates.generate-ai');
            Route::post('/email-campaigns/generate-ai-template', [EmailCampaignController::class, 'generateAiTemplate'])->name('admin.email-campaigns.generate-ai-template');
            Route::post('/email-campaigns/create-step2', [EmailCampaignController::class, 'createStep2'])->name('admin.email-campaigns.create-step2');
            Route::post('/email-campaigns/create-step3', [EmailCampaignController::class, 'createStep3'])->name('admin.email-campaigns.create-step3');
            Route::post('/email-campaigns/create-finish', [EmailCampaignController::class, 'createFinish'])->name('admin.email-campaigns.create-finish');
            Route::post('/email-campaigns', [EmailCampaignController::class, 'store'])->name('admin.email-campaigns.store');
            Route::get('/email-campaigns/{id}', [EmailCampaignController::class, 'show'])->name('admin.email-campaigns.show');
            Route::get('/email-campaigns/{id}/edit', [EmailCampaignController::class, 'edit'])->name('admin.email-campaigns.edit');
            Route::put('/email-campaigns/{id}', [EmailCampaignController::class, 'update'])->name('admin.email-campaigns.update');
            Route::delete('/email-campaigns/{id}', [EmailCampaignController::class, 'destroy'])->name('admin.email-campaigns.destroy');
            Route::post('/email-campaigns/{id}/recipients', [EmailCampaignController::class, 'addRecipients'])->name('admin.email-campaigns.recipients.add');
            Route::post('/email-campaigns/{id}/send', [EmailCampaignController::class, 'send'])->name('admin.email-campaigns.send');
            Route::post('/email-campaigns/{id}/test', [EmailCampaignController::class, 'sendTest'])->name('admin.email-campaigns.test');
            Route::post('/email-campaigns/{id}/cancel', [EmailCampaignController::class, 'cancel'])->name('admin.email-campaigns.cancel');
        });
    });

    // Rotas do Kanban
    Route::prefix('dashboard/kanban')->group(function () {
        Route::get('/columns', [KanbanController::class, 'listColumns']);
        Route::post('/columns', [KanbanController::class, 'createColumn']);
        Route::put('/columns/{id}', [KanbanController::class, 'updateColumn']);
        Route::delete('/columns/{id}', [KanbanController::class, 'deleteColumn']);
        Route::post('/move', [KanbanController::class, 'moveCard']);
    });

    // Rotas de API para configurações
    Route::get('/api/settings', [SettingsController::class, 'getSettings'])->name('api.settings');
    Route::get('/api/cooldown', [SettingsController::class, 'checkCooldown'])->name('api.cooldown');
    Route::get('/api/tracking/test', [SettingsController::class, 'testTracking'])->name('api.tracking.test');
    Route::post('/api/tracking/validate-gtm', [SettingsController::class, 'validateGTM'])->name('api.tracking.validate-gtm');
    Route::post('/api/tracking/validate-pixel', [SettingsController::class, 'validatePixel'])->name('api.tracking.validate-pixel');
    Route::post('/settings/test-email-connection', [SettingsController::class, 'testEmailConnection'])->name('settings.test-email');
    Route::post('/settings/test-gtm', [SettingsController::class, 'validateGTM'])->name('settings.test-gtm');
    Route::post('/settings/test-pixel', [SettingsController::class, 'validatePixel'])->name('settings.test-pixel');
    Route::post('/admin/settings/test-ai', [SettingsController::class, 'testAiConnection'])->name('admin.settings.test-ai');
});

// Google Drive Integration Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/google-drive/create-folder', [GoogleDriveController::class, 'createStudentFolder'])->name('google-drive.create-folder');
    Route::post('/google-drive/upload', [GoogleDriveController::class, 'uploadFile'])->name('google-drive.upload');
    Route::get('/google-drive/list-files', [GoogleDriveController::class, 'listFiles'])->name('google-drive.list-files');
    Route::delete('/google-drive/delete/{fileId}', [GoogleDriveController::class, 'deleteFile'])->name('google-drive.delete');
});





// Rotas públicas para contratos (acesso via token)
Route::prefix('contracts')->name('contracts.')->group(function () {
    Route::get('/{token}', [ContractController::class, 'access'])->name('access');
    Route::post('/{token}/verify', [ContractController::class, 'verify'])->name('verify');
    Route::get('/{token}/show', [ContractController::class, 'show'])->name('show');
    Route::post('/{token}/sign', [ContractController::class, 'sign'])->name('sign');
    Route::get('/{token}/pdf', [ContractController::class, 'downloadPublicPdf'])->name('pdf');
});

// Rotas administrativas para contratos
Route::middleware(['auth', 'verified', 'admin', 'theme', \App\Http\Middleware\CheckMenuPermission::class])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::post('/matricula/{matricula}/generate', [ContractController::class, 'generate'])->name('generate');
        Route::post('/{contract}/send', [ContractController::class, 'send'])->name('send');
        Route::post('/{contract}/send-whatsapp', [ContractController::class, 'sendWhatsApp'])->name('send-whatsapp');
        Route::post('/{contract}/cancel', [ContractController::class, 'cancel'])->name('cancel');
        Route::post('/{contract}/regenerate-link', [ContractController::class, 'regenerateLink'])->name('regenerate-link');
        Route::get('/{contract}/signed', [ContractController::class, 'viewSigned'])->name('view-signed');
        Route::get('/{contract}/download-pdf', [ContractController::class, 'downloadPdf'])->name('download-pdf');
        Route::post('/{contract}/update-variables', [ContractController::class, 'updateVariables'])->name('update-variables');
        
        // Rotas para templates
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [ContractTemplateController::class, 'index'])->name('index');
            Route::get('/create', [ContractTemplateController::class, 'create'])->name('create');
            Route::post('/', [ContractTemplateController::class, 'store'])->name('store');
            Route::get('/{template}', [ContractTemplateController::class, 'show'])->name('show');
            Route::get('/{template}/edit', [ContractTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [ContractTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [ContractTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/set-default', [ContractTemplateController::class, 'setDefault'])->name('set-default');
            Route::post('/{template}/toggle-active', [ContractTemplateController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{template}/duplicate', [ContractTemplateController::class, 'duplicate'])->name('duplicate');
            Route::get('/{template}/preview', [ContractTemplateController::class, 'preview'])->name('preview');
        });
        
        // API para variáveis (fora do grupo de templates)
        Route::get('/templates/api/variables', [ContractTemplateController::class, 'getVariables'])->name('templates.variables');
        
        // Geração com ChatGPT
        Route::post('/templates/generate-ai', [ContractTemplateController::class, 'generateWithAi'])->name('templates.generate-ai');
        Route::post('/templates/upload-reference', [ContractTemplateController::class, 'uploadReference'])->name('templates.upload-reference');
    });
});

// Rotas de debug para testar permissões
Route::middleware(['auth'])->group(function () {
    Route::get('/debug-permissions', function () {
        $user = auth()->user();
        
        return response()->json([
            'authenticated' => auth()->check(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tipo_usuario' => $user->tipo_usuario,
                'isAdmin' => $user->isAdmin(),
                'roles_count' => $user->roles->count(),
                'roles' => $user->roles->map(function($role) {
                    return [
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'is_active' => $role->is_active,
                        'permissions_count' => $role->permissions->count()
                    ];
                })
            ],
            'session' => [
                'is_impersonating' => session('is_impersonating'),
                'impersonating_user_id' => session('impersonating_user_id'),
                'admin_id' => session('admin_id'),
                'admin_logged_in' => session('admin_logged_in')
            ],
            'permissions_test' => [
                'dashboard.index' => $user->hasPermission('dashboard.index'),
                'inscricoes.index' => $user->hasPermission('inscricoes.index'),
                'usuarios.index' => $user->hasPermission('usuarios.index'),
                'permissoes.index' => $user->hasPermission('permissoes.index'),
                'configuracoes.index' => $user->hasPermission('configuracoes.index')
            ]
        ]);
    })->name('debug.permissions');
    
    Route::get('/debug-force-login', function () {
        // Force login as Douglas
        $douglas = \App\Models\User::where('email', 'webmaster@ensinocerto.com.br')->first();
        if ($douglas) {
            auth()->login($douglas);
            session()->forget(['is_impersonating', 'impersonating_user_id', 'admin_id', 'admin_name', 'admin_email', 'admin_tipo', 'admin_logged_in']);
            return redirect('/dashboard')->with('success', 'Forced login as Douglas');
        }
        return redirect('/login')->with('error', 'Douglas not found');
    })->name('debug.force.login');
    
    Route::get('/test-permissions', [\App\Http\Controllers\TestController::class, 'testPermissions'])->name('test.permissions');
});

// Test route for CSRF debugging
Route::get('/test-csrf', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token(),
        'has_token' => session()->has('_token'),
        'session_data' => session()->all()
    ]);
})->middleware('web');

// CSRF token refresh route
Route::get('/refresh-csrf', function () {
    session()->regenerateToken();
    return response()->json([
        'token' => csrf_token(),
        'success' => true
    ]);
})->name('refresh.csrf');

// Frontend test page
Route::get('/test-csrf-frontend', function () {
    return view('test-csrf');
})->name('test.csrf.frontend');

// Migration Management Routes (fora do middleware web para evitar CSRF)
Route::prefix('admin/permissions/migration')->name('admin.permissions.migration.')->middleware(['auth'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\PermissionMigrationController::class, 'index'])->name('index');
    Route::post('/migrate', [\App\Http\Controllers\Admin\PermissionMigrationController::class, 'migrate'])->name('migrate');
    Route::get('/status', [\App\Http\Controllers\Admin\PermissionMigrationController::class, 'status'])->name('status');
    Route::post('/clear-cache', [\App\Http\Controllers\Admin\PermissionMigrationController::class, 'clearCache'])->name('clear-cache');
});

// Permissions Management Routes (Admin Only)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    

    
    Route::prefix('permissions')->name('permissions.')->middleware('permission:permissoes.index')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PermissionsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\PermissionsController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\PermissionsController::class, 'store'])->name('store');
        
        // MOVER ESTAS ROTAS ESPECÍFICAS ANTES DA ROTA GENÉRICA
        Route::get('/export', [\App\Http\Controllers\Admin\PermissionsController::class, 'export'])->name('export');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\PermissionsController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/generate-slug', [\App\Http\Controllers\Admin\PermissionsController::class, 'generateSlug'])->name('generate-slug');
        Route::post('/sync', [\App\Http\Controllers\Admin\PermissionsController::class, 'sync'])->name('sync');
        Route::post('/clear-cache', [\App\Http\Controllers\Admin\PermissionsController::class, 'clearCache'])->name('clear-cache');
        
        // Roles Management
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PermissionsController::class, 'roles'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\PermissionsController::class, 'createRole'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\PermissionsController::class, 'storeRole'])->name('store');
            Route::get('/{role}/edit', [\App\Http\Controllers\Admin\PermissionsController::class, 'editRole'])->name('edit');
            Route::put('/{role}', [\App\Http\Controllers\Admin\PermissionsController::class, 'updateRole'])->name('update');
            Route::delete('/{role}', [\App\Http\Controllers\Admin\PermissionsController::class, 'destroyRole'])->name('destroy');
        });
        
        // Users Permissions Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PermissionsController::class, 'users'])->name('index');
            Route::get('/{user}/edit', [\App\Http\Controllers\Admin\PermissionsController::class, 'editUser'])->name('edit');
            Route::get('/{user}/permissions', [\App\Http\Controllers\Admin\PermissionsController::class, 'getUserPermissions'])->name('permissions');
            Route::put('/{user}/roles', [\App\Http\Controllers\Admin\PermissionsController::class, 'updateUserRoles'])->name('update-roles');
        });
        
        // ROTAS GENÉRICAS POR ÚLTIMO (para não capturar rotas específicas)
        Route::get('/{permission}', [\App\Http\Controllers\Admin\PermissionsController::class, 'show'])->name('show');
        Route::get('/{permission}/edit', [\App\Http\Controllers\Admin\PermissionsController::class, 'edit'])->name('edit');
        Route::put('/{permission}', [\App\Http\Controllers\Admin\PermissionsController::class, 'update'])->name('update');
        Route::delete('/{permission}', [\App\Http\Controllers\Admin\PermissionsController::class, 'destroy'])->name('destroy');
    });
});


