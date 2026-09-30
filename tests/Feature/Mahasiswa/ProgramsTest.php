<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Enums\Semester;
use App\Livewire\Mahasiswa\Programs;
use App\Models\Group;
use App\Models\ParticipantOutput;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramsTest extends TestCase
{
    use RefreshDatabase;

    public function test_multidisiplin_program_modal_groups_planning_and_execution_details(): void
    {
        $student = $this->createStudentWithGroup();
        $program = Program::create([
            'group_id' => $student->group_id,
            'title' => 'Program Literasi Digital',
            'type' => ProgramType::Multidisiplin,
        ]);
        $participant = ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'status' => ProgramStatus::Draft,
            'lpk_status' => ProgramStatus::Draft,
        ]);

        Livewire::actingAs($student)
            ->test(Programs::class)
            ->set('selectedProgramId', $program->id)
            ->set('selectedParticipantId', $participant->id)
            ->assertSee('Identitas Program')
            ->assertSee('Rancangan Program')
            ->assertSee('Pelaksanaan Program')
            ->assertSee('Luaran Program')
            ->assertSee('Potensi / Permasalahan')
            ->assertSee('Pelaksanaan Kegiatan')
            ->assertSee('Ketercapaian')
            ->assertSee('Hambatan')
            ->assertSee('Tindak Lanjut')
            ->assertSee('Belum diisi');
    }

    public function test_alternate_program_types_show_role_and_result_without_multidisiplin_fields(): void
    {
        $student = $this->createStudentWithGroup();
        $component = Livewire::actingAs($student)->test(Programs::class);

        foreach ([
            [ProgramType::Multidisiplin, 'Video Profile KKN'],
            [ProgramType::SosialKemasyarakatan, 'Program Sosial'],
            [ProgramType::Lainnya, 'Program Lainnya'],
        ] as [$type, $title]) {
            $program = Program::create([
                'group_id' => $student->group_id,
                'student_id' => $student->id,
                'title' => $title,
                'type' => $type,
            ]);
            $participant = ProgramParticipant::create([
                'program_id' => $program->id,
                'student_id' => $student->id,
                'role_in_program' => 'Koordinator',
                'responsibility' => 'Mengatur kegiatan',
                'achievement' => 'Kegiatan terlaksana',
                'status' => ProgramStatus::Draft,
                'lpk_status' => ProgramStatus::Draft,
            ]);

            $component
                ->set('selectedProgramId', $program->id)
                ->set('selectedParticipantId', $participant->id);

            if ($type === ProgramType::Multidisiplin) {
                $component
                    ->assertDontSee('Peran Anda')
                    ->assertDontSee('Deskripsi Tugas dan Tanggung Jawab')
                    ->assertDontSee('Koordinator');
            } else {
                $component
                    ->assertSee('Peran Anda')
                    ->assertSee('Deskripsi Tugas dan Tanggung Jawab')
                    ->assertSee('Koordinator');
            }

            $component
                ->assertSee('Hasil')
                ->assertSee('Kegiatan terlaksana')
                ->assertDontSee('Potensi / Permasalahan')
                ->assertDontSee('Pelaksanaan Kegiatan');
        }
    }

    public function test_program_modal_shows_placeholders_without_a_participant(): void
    {
        $student = $this->createStudentWithGroup();
        $program = Program::create([
            'group_id' => $student->group_id,
            'student_id' => $student->id,
            'title' => 'Program Tanpa Partisipan',
            'type' => ProgramType::Lainnya,
        ]);

        Livewire::actingAs($student)
            ->test(Programs::class)
            ->set('selectedProgramId', $program->id)
            ->assertSee('Identitas Program')
            ->assertSee('Status Rencana (LRK)')
            ->assertSee('Status Pelaksanaan (LPK)')
            ->assertSee('Belum diisi');
    }

    public function test_program_modal_keeps_output_link_and_documentation_photo(): void
    {
        $student = $this->createStudentWithGroup();
        $program = Program::create([
            'group_id' => $student->group_id,
            'student_id' => $student->id,
            'title' => 'Program Dokumentasi',
            'type' => ProgramType::Lainnya,
        ]);
        $participant = ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'documentation_image_path' => 'programs/dokumentasi.jpg',
            'documentation_caption' => 'Dokumentasi kegiatan uji',
            'status' => ProgramStatus::Draft,
            'lpk_status' => ProgramStatus::Draft,
        ]);
        ParticipantOutput::create([
            'program_participant_id' => $participant->id,
            'output_code' => 'LM1M1',
            'name' => 'Panduan Digital',
            'type' => 'link',
            'url' => 'https://example.com/panduan',
        ]);

        Livewire::actingAs($student)
            ->test(Programs::class)
            ->set('selectedProgramId', $program->id)
            ->set('selectedParticipantId', $participant->id)
            ->assertSee('Panduan Digital')
            ->assertSee('https://example.com/panduan')
            ->assertSee('storage/programs/dokumentasi.jpg')
            ->assertSee('Dokumentasi kegiatan uji');
    }

    private function createStudentWithGroup(): User
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);
        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Uji',
        ]);
        $student = User::factory()->mahasiswa()->create();
        $student->group_id = $group->id;
        $student->save();

        return $student;
    }
}
