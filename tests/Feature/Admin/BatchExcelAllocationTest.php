<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Enums\UserRole;
use App\Livewire\Admin\Dpls;
use App\Livewire\Admin\Groups;
use App\Livewire\Admin\Students;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Period;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BatchExcelAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Period $period;

    protected GroupType $groupType;

    protected Faculty $faculty;

    protected StudyProgram $studyProgram;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::P2kkn,
        ]);

        $this->period = Period::create([
            'name' => 'KKN Tim 1 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-15',
        ]);

        $this->groupType = GroupType::firstOrCreate(
            ['code' => 'Reguler'],
            ['name' => 'Reguler']
        );

        $this->faculty = Faculty::firstOrCreate(
            ['code' => '11'],
            ['name' => 'Fakultas Sains dan Matematika', 'short_name' => 'FSM']
        );

        $this->studyProgram = StudyProgram::firstOrCreate(
            ['code' => '110001'],
            ['faculty_id' => $this->faculty->id, 'name' => 'Informatika S1']
        );
    }

    /**
     * Helper to create a test Excel file from headers and data rows.
     */
    protected function createExcelFile(array $headers, array $dataRows, string $prefix = 'test_'): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');

        $rowIdx = 2;
        foreach ($dataRows as $row) {
            $sheet->fromArray($row, null, "A{$rowIdx}");
            $rowIdx++;
        }

        $tempPath = tempnam(sys_get_temp_dir(), $prefix).'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $content = file_get_contents($tempPath);
        @unlink($tempPath);

        return UploadedFile::fake()->createWithContent($prefix.'.xlsx', $content);
    }

    public function test_admin_can_export_groups_excel(): void
    {
        Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok 1',
            'village' => 'Desa Karanganyar',
            'group_type_id' => $this->groupType->id,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(Groups::class, ['periodId' => $this->period->id])
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_admin_can_import_groups_excel(): void
    {
        $this->actingAs($this->admin);

        $headers = [
            'No', 'Nama Kelompok', 'Jenis Kelompok', 'Desa', 'Kecamatan',
            'Kabupaten', 'Provinsi', 'Mitra', 'Kepala Desa', 'Tanggal Mulai', 'Tanggal Selesai',
        ];

        $dataRows = [
            [1, 'Kelompok Alpha', 'Reguler', 'Desa Makmur', 'Kec. Barat', 'Semarang', 'Jateng', 'Mitra A', 'Bpk Lurah', '2026-07-01', '2026-08-15'],
            [2, 'Kelompok Beta', 'Reguler', 'Desa Sejahtera', 'Kec. Timur', 'Semarang', 'Jateng', 'Mitra B', 'Ibu Lurah', '2026-07-01', '2026-08-15'],
        ];

        $file = $this->createExcelFile($headers, $dataRows, 'groups_');

        Livewire::test(Groups::class, ['periodId' => $this->period->id])
            ->set('importFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertSet('showImportModal', false);

        $this->assertDatabaseHas('groups', [
            'period_id' => $this->period->id,
            'name' => 'Kelompok Alpha',
            'village' => 'Desa Makmur',
        ]);

        $this->assertDatabaseHas('groups', [
            'period_id' => $this->period->id,
            'name' => 'Kelompok Beta',
            'village' => 'Desa Sejahtera',
        ]);
    }

    public function test_admin_can_export_students_excel(): void
    {
        $group = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok Alpha',
        ]);

        User::factory()->create([
            'name' => 'Budi Mahasiswa',
            'nim' => '24060120120001',
            'email' => 'budi@students.ac.id',
            'role' => UserRole::Mahasiswa,
            'group_id' => $group->id,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(Students::class, ['periodId' => $this->period->id])
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_admin_can_import_students_excel_and_link_by_group_name(): void
    {
        $group = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok Alpha',
        ]);

        $this->actingAs($this->admin);

        $headers = [
            'No', 'NIM', 'Nama Mahasiswa', 'Email', 'Fakultas',
            'Program Studi', 'No HP', 'Nama Kelompok', 'Ketua Kelompok',
        ];

        $dataRows = [
            [1, '24060120120001', 'Budi Mahasiswa', 'budi@students.ac.id', 'FSM', 'Informatika S1', '081234567890', 'Kelompok Alpha', 'Ya'],
            [2, '24060120120002', 'Siti Mahasiswi', 'siti@students.ac.id', 'FSM', 'Informatika S1', '081234567891', 'Kelompok Alpha', 'Tidak'],
        ];

        $file = $this->createExcelFile($headers, $dataRows, 'students_');

        $component = Livewire::test(Students::class, ['periodId' => $this->period->id])
            ->set('importFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertSet('showImportModal', true)
            ->assertSet('generatedCredentialsCount', 2);

        $this->assertDatabaseHas('users', [
            'nim' => '24060120120001',
            'name' => 'Budi Mahasiswa',
            'group_id' => $group->id,
            'role' => UserRole::Mahasiswa->value,
            'must_change_password' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'nim' => '24060120120002',
            'name' => 'Siti Mahasiswi',
            'group_id' => $group->id,
            'role' => UserRole::Mahasiswa->value,
            'must_change_password' => true,
        ]);

        // Verify leader assignment & default password{nim}
        $budi = User::where('nim', '24060120120001')->first();
        $this->assertEquals($budi->id, $group->fresh()->student_leader_id);
        $this->assertTrue(Hash::check('password24060120120001', $budi->password));

        $component->call('downloadGeneratedCredentials')
            ->assertFileDownloaded()
            ->assertSet('showImportModal', false)
            ->assertSet('generatedCredentialsCount', 0);

        $this->assertNull(session('admin_import_credentials.students'));
    }

    public function test_import_students_supports_optional_password_column(): void
    {
        $group = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok Alpha',
        ]);

        $this->actingAs($this->admin);

        $headers = [
            'No', 'NIM', 'Nama Mahasiswa', 'Email', 'Nama Kelompok', 'Password',
        ];

        $dataRows = [
            [1, '24060120120077', 'Ani Mahasiswa', 'ani@students.ac.id', 'Kelompok Alpha', 'CustomInit2026'],
        ];

        $file = $this->createExcelFile($headers, $dataRows, 'students_with_pw_');

        Livewire::test(Students::class, ['periodId' => $this->period->id])
            ->set('importFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertSet('generatedCredentialsCount', 1);

        $ani = User::where('nim', '24060120120077')->firstOrFail();
        $this->assertTrue($ani->must_change_password);
        $this->assertTrue(Hash::check('CustomInit2026', $ani->password));
    }

    public function test_import_students_fails_if_group_name_not_in_period(): void
    {
        $this->actingAs($this->admin);

        $headers = [
            'No', 'NIM', 'Nama Mahasiswa', 'Email', 'Fakultas',
            'Program Studi', 'No HP', 'Nama Kelompok', 'Ketua Kelompok',
        ];

        $dataRows = [
            [1, '24060120120099', 'Mahasiswa Hantu', 'ghost@students.ac.id', 'FSM', 'Informatika S1', '081234567899', 'Kelompok Tidak Ada', 'Tidak'],
        ];

        $file = $this->createExcelFile($headers, $dataRows, 'invalid_students_');

        Livewire::test(Students::class, ['periodId' => $this->period->id])
            ->call('openImportModal')
            ->set('importFile', $file)
            ->call('importExcel')
            ->assertSet('showImportModal', true)
            ->assertCount('importErrors', 1);

        $this->assertDatabaseMissing('users', [
            'nim' => '24060120120099',
        ]);
    }

    public function test_import_students_rejects_non_numeric_nim(): void
    {
        Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok Alpha',
        ]);

        $this->actingAs($this->admin);

        $headers = [
            'No', 'NIM', 'Nama Mahasiswa', 'Email', 'Nama Kelompok',
        ];

        $dataRows = [
            [1, 'NIM-ABC-001', 'Mahasiswa Invalid', 'invalid@students.ac.id', 'Kelompok Alpha'],
        ];

        $file = $this->createExcelFile($headers, $dataRows, 'invalid_nim_students_');

        Livewire::test(Students::class, ['periodId' => $this->period->id])
            ->call('openImportModal')
            ->set('importFile', $file)
            ->call('importExcel')
            ->assertSet('showImportModal', true)
            ->assertCount('importErrors', 1);

        $this->assertDatabaseMissing('users', [
            'nim' => 'NIM-ABC-001',
        ]);
    }

    public function test_bulk_import_students_resolves_study_program_issues_default_password_and_handles_duplicate_nim_in_file(): void
    {
        $group = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok Alpha',
        ]);

        $this->actingAs($this->admin);

        $headers = [
            'No', 'NIM', 'Nama Mahasiswa', 'Nama Kelompok', 'Ketua Kelompok',
        ];

        $dataRows = [
            [1, '11000120120001', 'Mahasiswa Pertama', 'Kelompok Alpha', 'Ya'],
            [2, '11000120120002', 'Mahasiswa Kedua', 'Kelompok Alpha', 'Tidak'],
            [3, '11000120120001', 'Mahasiswa Duplikat', 'Kelompok Alpha', 'Tidak'],
        ];

        $file = $this->createExcelFile($headers, $dataRows, 'bulk_students_');

        Livewire::test(Students::class, ['periodId' => $this->period->id])
            ->set('importFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertSet('generatedCredentialsCount', 2);

        $credentials = session('admin_import_credentials.students');
        $this->assertCount(2, $credentials);
        $this->assertArrayNotHasKey('email', $credentials[0]);

        $first = User::where('nim', '11000120120001')->firstOrFail();
        $this->assertNotNull($first->ulid);
        $this->assertNull($first->email);
        $this->assertSame($this->studyProgram->id, $first->study_program_id);
        $this->assertSame($this->faculty->id, $first->faculty_id);
        $this->assertSame($first->id, $group->fresh()->student_leader_id);
        $this->assertTrue($first->must_change_password);
        $this->assertSame('password11000120120001', $credentials[0]['temporary_password']);
        $this->assertTrue(Hash::check('password11000120120001', $first->password));

        $this->assertDatabaseMissing('users', [
            'name' => 'Mahasiswa Duplikat',
        ]);

        $downloadResponse = Livewire::test(Students::class, ['periodId' => $this->period->id])
            ->call('downloadGeneratedCredentials')
            ->assertFileDownloaded();

        $exportedContent = data_get($downloadResponse->effects, 'download.content');
        $spreadsheet = IOFactory::load(
            tap(tempnam(sys_get_temp_dir(), 'cred_'), fn ($p) => file_put_contents($p, base64_decode((string) $exportedContent)))
        );
        $headerRow = $spreadsheet->getActiveSheet()->toArray()[0] ?? [];
        $this->assertSame(['No', 'NIM', 'Nama', 'Password Sementara'], $headerRow);

        $downloadResponse = Livewire::test(Students::class, ['periodId' => $this->period->id])
            ->call('downloadGeneratedCredentials')
            ->assertFileDownloaded();

        $exportedContent = data_get($downloadResponse->effects, 'download.content');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(
            tap(tempnam(sys_get_temp_dir(), 'cred_'), fn ($p) => file_put_contents($p, base64_decode((string) $exportedContent)))
        );
        $headerRow = $spreadsheet->getActiveSheet()->toArray()[0] ?? [];
        $this->assertSame(['No', 'NIM', 'Nama', 'Password Sementara'], $headerRow);
    }

    public function test_admin_can_export_dpls_excel_with_multi_group(): void
    {
        $group1 = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok 1',
        ]);

        $group2 = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok 2',
        ]);

        $dpl = User::factory()->create([
            'name' => 'Dr. DPL Multi',
            'nip' => '198001012005011001',
            'email' => 'dpl.multi@undip.ac.id',
            'role' => UserRole::Dpl,
        ]);

        $dpl->dplGroups()->attach([$group1->id, $group2->id]);
        $group1->update(['lead_dpl_id' => $dpl->id]);

        $this->actingAs($this->admin);

        Livewire::test(Dpls::class, ['periodId' => $this->period->id])
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_admin_can_import_dpls_excel_supporting_multi_group_and_lead_dpl(): void
    {
        $group1 = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok 1',
        ]);

        $group2 = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok 2',
        ]);

        $this->actingAs($this->admin);

        $headers = [
            'No', 'NIP', 'Nama DPL', 'Email', 'Program Studi',
            'No HP', 'Nama Kelompok', 'DPL Utama',
        ];

        $dataRows = [
            [1, '198001012005011001', 'Dr. DPL Multi', 'dpl.multi@undip.ac.id', 'Informatika S1', '081234567891', 'Kelompok 1', 'Ya'],
            [2, '198001012005011001', 'Dr. DPL Multi', 'dpl.multi@undip.ac.id', 'Informatika S1', '081234567891', 'Kelompok 2', 'Tidak'],
        ];

        $file = $this->createExcelFile($headers, $dataRows, 'dpls_');

        $component = Livewire::test(Dpls::class, ['periodId' => $this->period->id])
            ->set('importFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertSet('showImportModal', true)
            ->assertSet('generatedCredentialsCount', 1);

        $dpl = User::where('nip', '198001012005011001')->first();
        $this->assertNotNull($dpl);
        $this->assertEquals(UserRole::Dpl, $dpl->role);
        $this->assertTrue($dpl->must_change_password);
        $this->assertTrue(Hash::check('password198001012005011001', $dpl->password));

        // Check pivot table
        $this->assertTrue($dpl->dplGroups()->where('groups.id', $group1->id)->exists());
        $this->assertTrue($dpl->dplGroups()->where('groups.id', $group2->id)->exists());

        // Check lead DPL on group 1
        $this->assertEquals($dpl->id, $group1->fresh()->lead_dpl_id);
        $this->assertNull($group2->fresh()->lead_dpl_id);

        $component->call('downloadGeneratedCredentials')
            ->assertFileDownloaded()
            ->assertSet('showImportModal', false)
            ->assertSet('generatedCredentialsCount', 0);

        $this->assertNull(session('admin_import_credentials.dpls'));
    }
}
