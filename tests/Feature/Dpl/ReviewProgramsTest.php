<?php

namespace Tests\Feature\Dpl;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Enums\Semester;
use App\Livewire\Dpl\ReviewPrograms as ReviewProgramsComponent;
use App\Models\Group;
use App\Models\ParticipantOutput;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewProgramsTest extends TestCase
{
    use RefreshDatabase;

    public function test_inspect_modal_shows_student_identity_and_type_specific_program_sections(): void
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);
        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Melati',
            'village' => 'Desa Melati',
        ]);
        $dpl = User::factory()->dpl()->create();
        $dpl->dplGroups()->attach($group);
        $student = User::factory()->mahasiswa()->create([
            'name' => 'Ayu Pratama',
            'nim' => '123456789',
        ]);

        $multidisiplinProgram = Program::create([
            'group_id' => $group->id,
            'title' => 'Pemberdayaan Literasi Digital',
            'type' => ProgramType::Multidisiplin,
        ]);
        $multidisiplinParticipant = ProgramParticipant::create([
            'program_id' => $multidisiplinProgram->id,
            'student_id' => $student->id,
            'participant_title' => 'Pelatihan Literasi Digital',
            'problem_potential' => 'Akses informasi terbatas',
            'execution_description' => 'Pelatihan berlangsung selama dua hari',
            'achievement' => 'Peserta memahami materi',
            'status' => ProgramStatus::Submitted,
            'lpk_status' => ProgramStatus::Draft,
        ]);
        ParticipantOutput::create([
            'program_participant_id' => $multidisiplinParticipant->id,
            'output_code' => 'LM1M1',
            'name' => 'Panduan Literasi Digital',
            'type' => 'pdf',
            'url' => 'https://example.com/panduan.pdf',
        ]);

        $component = Livewire::actingAs($dpl)
            ->test(ReviewProgramsComponent::class)
            ->call('inspect', $multidisiplinParticipant->id)
            ->assertSee('Ayu Pratama')
            ->assertSee('123456789')
            ->assertSee('Pemberdayaan Literasi Digital')
            ->assertSee('Kelompok Melati')
            ->assertSee('Identitas Program')
            ->assertSee('Rancangan Program')
            ->assertSee('Pelaksanaan Program')
            ->assertSee('Luaran Program')
            ->assertSee('Status Rencana')
            ->assertSee('Status Pelaksanaan')
            ->assertSee('Akses informasi terbatas')
            ->assertSee('Pelatihan berlangsung selama dua hari')
            ->assertSee('Panduan Literasi Digital')
            ->assertDontSee('Peran Anda')
            ->assertDontSee('Deskripsi Tugas dan Tanggung Jawab')
            ->assertSee('Setujui Rencana');

        $sosmasProgram = Program::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'title' => 'Program Sosial',
            'type' => ProgramType::SosialKemasyarakatan,
        ]);
        $sosmasParticipant = ProgramParticipant::create([
            'program_id' => $sosmasProgram->id,
            'student_id' => $student->id,
            'role_in_program' => 'Koordinator',
            'responsibility' => 'Mengatur kegiatan',
            'achievement' => 'Kegiatan terlaksana',
            'status' => ProgramStatus::Submitted,
            'lpk_status' => ProgramStatus::Draft,
        ]);

        $component
            ->call('inspect', $sosmasParticipant->id)
            ->assertSee('Peran Anda')
            ->assertSee('Deskripsi Tugas dan Tanggung Jawab')
            ->assertSee('Koordinator')
            ->assertSee('Hasil')
            ->assertSee('Kegiatan terlaksana');

        Livewire::actingAs($dpl)
            ->test(ReviewProgramsComponent::class)
            ->call('approve', $sosmasParticipant->id)
            ->assertDispatched('toast-show');
        $this->assertSame(ProgramStatus::Approved, $sosmasParticipant->fresh()->status);

        Livewire::actingAs($dpl)
            ->test(ReviewProgramsComponent::class)
            ->call('inspect', $sosmasParticipant->id)
            ->set('revisionNote', 'Mohon lengkapi bukti capaian program.')
            ->call('submitRevision')
            ->assertDispatched('toast-show');
        $this->assertSame(ProgramStatus::NeedsRevision, $sosmasParticipant->fresh()->status);

        Livewire::actingAs($dpl)
            ->test(ReviewProgramsComponent::class)
            ->call('confirmDeleteTheme', $multidisiplinProgram->id)
            ->assertDispatched('toast-show');

        $emptyTheme = Program::create([
            'group_id' => $group->id,
            'title' => 'Tema untuk Dihapus',
            'type' => ProgramType::Multidisiplin,
        ]);

        Livewire::actingAs($dpl)
            ->test(ReviewProgramsComponent::class)
            ->call('confirmDeleteTheme', $emptyTheme->id)
            ->call('confirmDelete')
            ->assertDispatched('toast-show');
        $this->assertDatabaseMissing('programs', ['id' => $emptyTheme->id]);
    }
}
