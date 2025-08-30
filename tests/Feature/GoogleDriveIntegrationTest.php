<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Matricula;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GoogleDriveIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $matricula;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'matriculas.edit']);
        Permission::create(['name' => 'arquivos.index']);
        Permission::create(['name' => 'arquivos.create']);

        // Create role and assign permissions
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['matriculas.edit', 'arquivos.index', 'arquivos.create']);

        // Create user and assign role
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Create matricula
        $this->matricula = Matricula::create([
            'nome_completo' => 'João da Silva',
            'cpf' => '123.456.789-00',
            'email' => 'joao@example.com',
            'telefone' => '(11) 99999-9999',
            'curso' => 'Curso Teste',
            'modalidade' => 'EAD',
            'polo' => 'Polo Teste',
            'valor_total' => 1000.00,
            'parcelas' => 10,
            'valor_parcela' => 100.00,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function test_google_drive_service_can_be_instantiated()
    {
        $service = app(GoogleDriveService::class);
        $this->assertInstanceOf(GoogleDriveService::class, $service);
    }

    /** @test */
    public function test_google_drive_configuration_exists()
    {
        $config = config('googledrive');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('client_id', $config);
        $this->assertArrayHasKey('client_secret', $config);
        $this->assertArrayHasKey('refresh_token', $config);
    }

    /** @test */
    public function test_matricula_has_google_drive_folder_id_field()
    {
        $this->assertArrayHasKey('google_drive_folder_id', $this->matricula->getFillable());
    }

    /** @test */
    public function test_matricula_can_store_google_drive_folder_id()
    {
        $folderId = 'test_folder_id_123';
        $this->matricula->update(['google_drive_folder_id' => $folderId]);
        
        $this->assertEquals($folderId, $this->matricula->fresh()->google_drive_folder_id);
    }

    /** @test */
    public function test_google_drive_routes_are_registered()
    {
        $routes = [
            'drive.config',
            'drive.folders.list',
            'drive.folders.create',
            'drive.files.upload',
            'drive.files.delete',
            'drive.student-folder.create',
            'admin.matriculas.create-drive-folder'
        ];

        foreach ($routes as $routeName) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Route::has($routeName),
                "Route {$routeName} not found"
            );
        }
    }

    /** @test */
    public function test_google_drive_permissions_exist()
    {
        $permissions = [
            'arquivos.index',
            'arquivos.create',
            'arquivos.edit',
            'arquivos.delete',
            'arquivos.share'
        ];

        foreach ($permissions as $permission) {
            $this->assertTrue(
                Permission::where('name', $permission)->exists(),
                "Permission {$permission} not found"
            );
        }
    }

    /** @test */
    public function test_user_can_access_matricula_create_drive_folder_endpoint()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(
            route('admin.matriculas.create-drive-folder', $this->matricula),
            ['parent_id' => null]
        );

        // Should not return 403 (forbidden) or 404 (not found)
        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function test_google_drive_config_endpoint_is_accessible()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('drive.config'));

        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(404, $response->status());
    }
} 