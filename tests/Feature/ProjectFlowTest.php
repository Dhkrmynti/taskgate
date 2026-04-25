<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectBatch;
use App\Models\CommerceRekon;
use App\Models\WarehouseRekon;
use App\Models\User;
use App\Models\ProjectState;
use App\Models\UnifiedSubfase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ProjectFlowTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cleanup();

        $this->admin = User::firstOrCreate(['email' => 'admin@test.com'], [
            'name' => 'Admin Test',
            'password' => bcrypt('password')
        ]);
    }

    protected function cleanup()
    {
        Project::where('id', 'like', 'TGIDOP-TEST-%')->delete();
        ProjectBatch::where('id', 'like', 'TGIDSP-TEST-%')->delete();
        CommerceRekon::where('id', 'like', 'TGIDRC-TEST-%')->delete();
        ProjectState::where('stateable_id', 'like', 'TGID%TEST%')->delete();
        UnifiedSubfase::where('faseable_id', 'like', 'TGID%TEST%')->delete();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_a_project_and_initializes_unified_state()
    {
        $project = Project::create([
            'id' => 'TGIDOP-TEST-0001',
            'project_name' => 'Test Project',
            'customer' => 'Test Customer',
            'fase' => 'planning'
        ]);

        $project->projectState()->create(['current_phase' => 'planning']);

        $this->assertDatabaseHas('project_states', [
            'stateable_id' => 'TGIDOP-TEST-0001',
            'stateable_type' => Project::class,
            'current_phase' => 'planning'
        ]);
        
        $this->assertTrue(true); // Marker for risky test prevention
    }

    /** @test */
    public function it_updates_unified_subfase_status()
    {
        $project = Project::create([
            'id' => 'TGIDOP-TEST-0002',
            'project_name' => 'Test Subfase',
            'customer' => 'Test Customer',
            'fase' => 'procurement'
        ]);
        $project->projectState()->create(['current_phase' => 'procurement']);

        $response = $this->actingAs($this->admin)->post(route('project-data.subfase.update', ['project' => 'TGIDOP-TEST-0002']), [
            'subfase_key' => 'procurement_po',
            'status' => 'selesai'
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('unified_subfases', [
            'faseable_id' => 'TGIDOP-TEST-0002',
            'faseable_type' => Project::class,
            'subfase_key' => 'procurement_po',
            'status' => 'selesai'
        ]);
    }

    /** @test */
    public function it_propagates_phase_updates_through_hierarchy()
    {
        // 1. Hierarchy: Rekon -> Batch -> Project
        $rekon = CommerceRekon::create(['id' => 'TGIDRC-TEST-0001', 'fase' => 'rekon']);
        $rekon->projectState()->create(['current_phase' => 'rekon']);

        $batch = ProjectBatch::create([
            'id' => 'TGIDSP-TEST-0001',
            'project_name' => 'Test Batch',
            'rekon_id' => 'TGIDRC-TEST-0001',
            'fase' => 'rekon'
        ]);
        $batch->projectState()->create(['current_phase' => 'rekon']);

        $project = Project::create([
            'id' => 'TGIDOP-TEST-0003',
            'project_name' => 'Test Project',
            'batch_id' => 'TGIDSP-TEST-0001',
            'fase' => 'rekon'
        ]);
        $project->projectState()->create(['current_phase' => 'rekon']);

        // 2. Mark markers
        $rekon->unifiedSubfases()->create(['subfase_key' => 'rekonsiliasi', 'status' => 'selesai']);
        $rekon->unifiedSubfases()->create(['subfase_key' => 'rekon_number', 'status' => 'selesai']);
        $rekon->unifiedSubfases()->create(['subfase_key' => 'rekon_evidence', 'status' => 'selesai']);
        $rekon->unifiedSubfases()->create(['subfase_key' => 'warehouse_done', 'status' => 'selesai']);

        // 3. Submit Commerce
        $this->actingAs($this->admin)->post(route('project-data.commerce.submit', ['project' => 'TGIDRC-TEST-0001']));

        // 4. Verify Propagation
        $this->assertEquals('finance', $rekon->fresh()->projectState->current_phase);
        $this->assertEquals('finance', $batch->fresh()->projectState->current_phase);
        $this->assertEquals('finance', $project->fresh()->projectState->current_phase);
    }
}
