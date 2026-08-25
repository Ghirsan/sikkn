<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Program;
use App\Models\ProgramParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class ProgramForm extends Component
{
    use WithFileUploads;
    #[Url]
    public string $action = 'create'; // 'create', 'edit', 'lpk'
    
    #[Url]
    public ?string $type = null;

    #[Url]
    public ?string $programId = '';

    #[Url]
    public ?int $participantId = null;

    public string $formMode = 'edit_program';
    
    // For Multidisiplin Join
    public $availableMultidisiplinPrograms = [];

    // Program Fields (Programs Table)
    public string $title = '';
    public string $problem_potential = '';
    public string $location = '';
    public string $target_audience = '';
    public string $output_target = '';
    public string $method = '';
    public ?string $execution_date = null;

    // Participant Fields (Participants Table - LRK Phase)
    public string $participant_title = '';
    public string $role_in_program = '';
    public string $responsibility = '';
    public ?string $sdg_category = '';

    // Participant Fields (Participants Table - LPK Phase)
    public string $achievement = '';
    public string $obstacle = '';
    public string $solution = '';
    public string $execution_description = '';
    
    // Lampiran 1 (Documentation)
    public $documentation_image; // for upload
    public ?string $documentation_image_path = null;
    public ?string $documentation_caption = null;

    // Lampiran 2 (Outputs)
    public array $outputs = []; // Array to hold multiple outputs
    
    public ?string $status = null;
    public ?string $revision_note = null;
    
    public bool $isLpkMultidisiplin = false;
    public bool $isLpkVideoProfile = false;

    public string $min_date = '';
    public ?string $max_date = null;

    public function mount()
    {
        $user = Auth::user();
        
        $period = \App\Models\Period::active()->first();
        $this->min_date = now()->format('Y-m-d');
        if ($period) {
            $this->max_date = $period->end_date->format('Y-m-d');
        }

        if ($this->action === 'create') {
            $this->type = $this->type ?? ProgramType::Lainnya->value;
            
            if ($this->type === ProgramType::Multidisiplin->value) {
                $this->formMode = 'create_multidisiplin';
                $joinedIds = ProgramParticipant::where('student_id', $user->id)->pluck('program_id');
                $this->availableMultidisiplinPrograms = Program::where('group_id', $user->group_id)
                    ->where('type', ProgramType::Multidisiplin)
                    ->whereNotIn('id', $joinedIds)
                    ->get();
            } else {
                $this->formMode = 'create_individual';
            }
        } elseif ($this->action === 'edit' && $this->programId) {
            $program = Program::where('group_id', $user->group_id)->findOrFail($this->programId);
            $this->type = $program->type->value;
            
            $isVideoProfile = $program ? $program->isVideoProfile() : false;

            if ($program->type === ProgramType::SosialKemasyarakatan || $program->type === ProgramType::Lainnya) {
                $this->formMode = 'create_individual';
                $this->title = $program->title;
            } elseif ($isVideoProfile) {
                $this->formMode = 'edit_peran';
                $this->title = $program->title;
            } else {
                $this->formMode = 'edit_program';
                $this->title = $program->title;
            }

            if ($this->participantId) {
                $participant = $program->participants()->where('student_id', $user->id)->findOrFail($this->participantId);
                $this->status = $participant->status->value;
                $this->revision_note = $participant->revision_note;
                $this->participant_title = $participant->participant_title ?? '';
                $this->role_in_program = $participant->role_in_program ?? '';
                $this->responsibility = $participant->responsibility ?? '';
                $this->sdg_category = $participant->sdg_category?->value ? (string) $participant->sdg_category->value : null;
                if ($this->formMode === 'edit_program' || $this->formMode === 'create_individual' || $this->formMode === 'edit_peran') {
                    $this->execution_date = $participant->execution_date?->format('Y-m-d');
                }
                if ($this->formMode === 'edit_program' || $this->formMode === 'create_individual') {
                    $this->problem_potential = $participant->problem_potential ?? '';
                    $this->location = $participant->location ?? '';
                    $this->method = $participant->method ?? '';
                    $this->target_audience = $participant->target_audience ?? '';
                    $this->output_target = $participant->output_target ?? '';
                    $this->execution_date = $participant->execution_date?->format('Y-m-d');
                }
            } else {
                $participant = $program->participants()->where('student_id', $user->id)->first();
                if ($participant) {
                    $this->participantId = $participant->id;
                    $this->status = $participant->status->value;
                    $this->revision_note = $participant->revision_note;
                    $this->participant_title = $participant->participant_title ?? '';
                    $this->role_in_program = $participant->role_in_program ?? '';
                    $this->responsibility = $participant->responsibility ?? '';
                    $this->sdg_category = $participant->sdg_category?->value ? (string) $participant->sdg_category->value : null;
                    if ($this->formMode === 'edit_program' || $this->formMode === 'create_individual' || $this->formMode === 'edit_peran') {
                        $this->execution_date = $participant->execution_date?->format('Y-m-d');
                    }
                    if ($this->formMode === 'edit_program' || $this->formMode === 'create_individual') {
                        $this->problem_potential = $participant->problem_potential ?? '';
                        $this->location = $participant->location ?? '';
                        $this->method = $participant->method ?? '';
                        $this->target_audience = $participant->target_audience ?? '';
                        $this->output_target = $participant->output_target ?? '';
                        $this->execution_date = $participant->execution_date?->format('Y-m-d');
                    }
                }
            }
        } elseif ($this->action === 'lpk' && $this->participantId) {
            $this->formMode = 'lpk';
            $participant = ProgramParticipant::with('program')->where('student_id', $user->id)->findOrFail($this->participantId);
            
            if ($participant->status !== ProgramStatus::Approved) {
                return redirect()->route('lpk.index');
            }

            $this->title = $participant->program->title;
            $this->status = $participant->lpk_status->value;
            $this->revision_note = $participant->lpk_revision_note;
            $this->isLpkVideoProfile = $participant->program ? $participant->program->isVideoProfile() : false;
            $this->isLpkMultidisiplin = $participant->program->type === ProgramType::Multidisiplin && !$this->isLpkVideoProfile;
            
            $this->achievement = $participant->achievement ?? '';
            $this->obstacle = $participant->obstacle ?? '';
            $this->solution = $participant->solution ?? '';
            $this->execution_description = $participant->execution_description ?? '';
            
            $this->documentation_image_path = $participant->documentation_image_path;
            $this->documentation_caption = $participant->documentation_caption;

            $this->outputs = $participant->outputs->map(function ($output) {
                return [
                    'id' => $output->id,
                    'name' => $output->name,
                    'type' => $output->type,
                    'file' => null,
                    'file_path' => $output->file_path,
                    'url' => $output->url ? preg_replace('#^https?://#i', '', $output->url) : '',
                ];
            })->toArray();

            // Initialize with one empty output row if none exist
            if (empty($this->outputs)) {
                $this->addOutput();
            }
        }
    }

    public function addOutput()
    {
        $this->outputs[] = [
            'id' => null,
            'name' => '',
            'type' => 'file',
            'file' => null,
            'file_path' => null,
            'url' => '',
        ];
    }

    public function removeOutput($index)
    {
        // If it has an ID, we might want to mark it for deletion or delete it immediately.
        // For simplicity, we'll just remove it from the array and handle deletion on save.
        if (isset($this->outputs[$index]['id']) && $this->outputs[$index]['id']) {
            \App\Models\ParticipantOutput::find($this->outputs[$index]['id'])?->delete();
        }
        unset($this->outputs[$index]);
        $this->outputs = array_values($this->outputs);
    }



    public function updatedProgramId($value)
    {
        if ($this->action === 'create' && $this->type === ProgramType::Multidisiplin->value) {
            if ($value) {
                $program = Program::find($value);
                $this->title = $program->title;
                if ($program->isVideoProfile()) {
                    $this->formMode = 'edit_peran';
                } else {
                    $this->formMode = 'edit_program';
                }
            } else {
                $this->formMode = 'create_multidisiplin';
                $this->title = '';
            }
        }
    }

    public function save()
    {
        if ($this->action === 'lpk') {
            return $this->saveLpk();
        }

        $user = Auth::user();
        if (!$user->group_id) return;

        // Validation based on mode
        if ($this->formMode === 'create_multidisiplin') {
            $this->validate([
                'programId' => 'required',
            ], [
                'programId.required' => 'Pilih tema program multidisiplin terlebih dahulu.',
            ]);
            return;
        } elseif ($this->formMode === 'edit_peran') {
            $this->validate([
                'role_in_program' => 'required|string',
                'responsibility' => 'required|string',
                'execution_date' => 'required|date',
            ]);
        } elseif ($this->formMode === 'create_individual') {
            $this->validate([
                'title' => 'required|string|max:255',
                'role_in_program' => 'required|string',
                'responsibility' => 'required|string',
                'execution_date' => 'required|date',
                'sdg_category' => 'required|integer|between:1,17',
            ]);
        } else {
            $this->validate([
                'participant_title' => 'required|string|max:255',
                'problem_potential' => 'required|string',
                'location' => 'required|string',
                'method' => 'required|string',
                'target_audience' => 'required|string',
                'output_target' => 'required|string',
                'execution_date' => 'required|date',
                'sdg_category' => 'required|integer|between:1,17',
            ]);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
            // 1. Handle Program Creation/Update
            if ($this->programId) {
                $program = Program::where('group_id', $user->group_id)->findOrFail($this->programId);
                if ($this->formMode === 'create_individual') {
                    $program->update([
                        'title' => $this->title,
                    ]);
                }
            } else {
                if ($this->formMode === 'create_individual') {

                    $nextSequence = Program::where('student_id', $user->id)
                        ->where('type', $this->type)
                        ->max('sequence') + 1;

                    $program = Program::create([
                        'student_id' => $user->id,
                        'group_id' => $user->group_id,
                        'title' => $this->title,
                        'type' => $this->type,
                        'sequence' => $nextSequence,
                    ]);
                    $this->programId = $program->id;
                }
            }

            // 2. Handle Participant Creation/Update
            $participantData = [
                'status' => ProgramStatus::Draft,
                'sdg_category' => $this->sdg_category ?: null,
            ];

            // Ensure we preserve existing role/responsibility for edit_program
            // or update them if provided in create_individual/edit_peran
            $participantData['role_in_program'] = $this->role_in_program ?: null;
            $participantData['responsibility'] = $this->responsibility ?: null;
            
            if ($this->formMode === 'edit_peran' || $this->formMode === 'create_individual') {
                $participantData['execution_date'] = $this->execution_date ?: null;
                $participantData['participant_title'] = null;
                $participantData['problem_potential'] = null;
                $participantData['location'] = null;
                $participantData['method'] = null;
                $participantData['target_audience'] = null;
                $participantData['output_target'] = null;
            } elseif ($this->formMode === 'edit_program') {
                $participantData['participant_title'] = $this->participant_title ?: null;
                $participantData['problem_potential'] = $this->problem_potential ?: null;
                $participantData['location'] = $this->location ?: null;
                $participantData['method'] = $this->method ?: null;
                $participantData['target_audience'] = $this->target_audience ?: null;
                $participantData['output_target'] = $this->output_target ?: null;
                $participantData['execution_date'] = $this->execution_date ?: null;
            }

            if ($this->participantId) {
                $participant = $program->participants()->where('student_id', $user->id)->findOrFail($this->participantId);
                $participantData['revision_note'] = null;
                $participant->update($participantData);
            } else {
                $participantData['student_id'] = $user->id;
                $program->participants()->create($participantData);
            }
        });

        session()->flash('success', 'Data program berhasil disimpan.');
        return $this->redirect(route('programs.index'), navigate: true);
    }

    private function saveLpk()
    {
        $participant = ProgramParticipant::with('program')->where('student_id', Auth::id())->findOrFail($this->participantId);
        $isVideo = $participant->program ? $participant->program->isVideoProfile() : false;
        $isMultidisiplin = $participant->program->type === ProgramType::Multidisiplin && !$isVideo;

        if ($isMultidisiplin) {
            $this->validate([
                'execution_description' => 'required|string',
                'achievement' => 'required|string',
                'obstacle' => 'required|string',
                'solution' => 'required|string',
            ]);
        } else {
            // For Sosmas/Lainnya/Video Profile: they need to fill 'achievement' as the "Hasil"
            $this->validate([
                'achievement' => 'required|string', // Digunakan untuk menampung "Hasil"
            ]);
        }
        
        $this->validate([
            'documentation_image' => $this->documentation_image_path ? 'nullable|image|max:5120' : 'required|image|max:5120',
            'documentation_caption' => 'required|string|max:255',
            'outputs' => 'required|array|min:1',
            'outputs.*.name' => 'required|string|max:255',
            'outputs.*.type' => 'required|in:file,link',
        ], [
            'outputs.required' => 'Minimal harus menambahkan 1 luaran program.',
            'outputs.min' => 'Minimal harus menambahkan 1 luaran program.',
            'outputs.*.name.required' => 'Judul/Nama luaran harus diisi.',
            'outputs.*.type.required' => 'Jenis luaran harus dipilih.',
        ]);

        foreach ($this->outputs as $index => $output) {
            if ($output['type'] === 'file') {
                if (empty($output['file_path'])) {
                    $this->validate([
                        "outputs.{$index}.file" => 'required|file|max:10240',
                    ], ["outputs.{$index}.file.required" => 'File luaran harus diunggah.']);
                } else {
                    $this->validate([
                        "outputs.{$index}.file" => 'nullable|file|max:10240',
                    ]);
                }
            } elseif ($output['type'] === 'link') {
                $url = trim($output['url']);
                if (!empty($url) && !preg_match('#^https?://#i', $url)) {
                    $url = 'https://' . $url;
                    $this->outputs[$index]['url'] = $url;
                }
                
                $this->validate([
                    "outputs.{$index}.url" => 'required|url',
                ], ["outputs.{$index}.url.required" => 'URL tautan luaran harus diisi.']);
            }
        }

        if ($this->documentation_image) {
            if ($participant->documentation_image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($participant->documentation_image_path);
            }
            $participant->documentation_image_path = $this->documentation_image->store('lpk_documentations', 'public');
        }
        
        $participant->update([
            'execution_description' => $this->execution_description,
            'achievement' => $this->achievement,
            'obstacle' => $this->obstacle,
            'solution' => $this->solution,
            'documentation_caption' => $this->documentation_caption,
        ]);

        $existingOutputs = $participant->outputs()->pluck('id')->toArray();
        $savedOutputIds = [];

        foreach ($this->outputs as $outputData) {
            $outputModel = null;
            if (!empty($outputData['id'])) {
                $outputModel = \App\Models\ParticipantOutput::find($outputData['id']);
            }

            if (!$outputModel) {
                $outputModel = new \App\Models\ParticipantOutput([
                    'program_participant_id' => $participant->id,
                    'output_code' => 'temp', // will be recalculated
                ]);
            }

            $outputModel->name = $outputData['name'];
            $outputModel->type = $outputData['type'];

            if ($outputData['type'] === 'file') {
                $outputModel->url = null;
                if (!empty($outputData['file'])) {
                    if ($outputModel->file_path) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($outputModel->file_path);
                    }
                    $outputModel->file_path = $outputData['file']->store('lpk_outputs', 'public');
                }
            } else {
                if ($outputModel->file_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($outputModel->file_path);
                    $outputModel->file_path = null;
                }
                $outputModel->url = $outputData['url'];
            }
            
            $outputModel->save();
            $savedOutputIds[] = $outputModel->id;
        }

        $outputsToDelete = array_diff($existingOutputs, $savedOutputIds);
        if (!empty($outputsToDelete)) {
            $toDelete = \App\Models\ParticipantOutput::whereIn('id', $outputsToDelete)->get();
            foreach ($toDelete as $model) {
                if ($model->file_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($model->file_path);
                }
                $model->delete();
            }
        }
        
        $allOutputs = $participant->outputs()->orderBy('id')->get();
        $totalCount = $allOutputs->count();
        foreach ($allOutputs as $i => $model) {
            $code = \App\Models\ParticipantOutput::generateOutputCode($participant, $i, $totalCount);
            if ($model->output_code !== $code) {
                $model->update(['output_code' => $code]);
            }
        }

        session()->flash('success', 'Laporan LPK Anda berhasil disimpan.');
        return $this->redirect(route('programs.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.mahasiswa.program-form');
    }
}
