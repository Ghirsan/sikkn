<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Models\ParticipantOutput;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Services\ExternalImagePreviewUrl;
use App\Services\ExternalUrlMetadata;
use App\Services\ProgramOutputUrlResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProgramForm extends Component
{
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
    public string $documentation_image_url = '';

    public ?string $documentation_image_preview_url = null;

    public ?string $documentation_image_error = null;

    public bool $documentation_image_verified = false;

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

        $period = Period::active()->first();
        $this->min_date = now()->format('Y-m-d');

        if ($user->group && $user->group->effective_end_date) {
            $this->max_date = $user->group->effective_end_date->format('Y-m-d');
        } elseif ($period && $period->end_date) {
            $this->max_date = $period->end_date->format('Y-m-d');
        }

        if ($this->action === 'create') {
            $this->type = $this->type ?? 'lainnya';

            if ($this->programType?->code === 'multidisiplin') {
                $this->formMode = 'create_multidisiplin';
                $joinedIds = ProgramParticipant::where('student_id', $user->id)->pluck('program_id');
                $this->availableMultidisiplinPrograms = Program::where('group_id', $user->group_id)
                    ->whereType('multidisiplin')
                    ->whereNotIn('id', $joinedIds)
                    ->get();
            } else {
                $this->formMode = 'create_individual';
            }
        } elseif ($this->action === 'edit' && $this->programId) {
            $program = Program::where('group_id', $user->group_id)->findOrFail($this->programId);
            $this->type = $program->programType?->code;

            $isVideoProfile = $program ? $program->isVideoProfile() : false;

            if ($program->programType?->code === 'sosial_kemasyarakatan' || $program->programType?->code === 'lainnya') {
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
            $this->revision_note = $participant->revision_note;
            $this->isLpkVideoProfile = $participant->program ? $participant->program->isVideoProfile() : false;
            $this->isLpkMultidisiplin = $participant->program->programType?->code === 'multidisiplin' && ! $this->isLpkVideoProfile;

            $this->achievement = $participant->achievement ?? '';
            $this->obstacle = $participant->obstacle ?? '';
            $this->solution = $participant->solution ?? '';
            $this->execution_description = $participant->execution_description ?? '';

            $this->documentation_image_path = $participant->documentation_image_path;
            $this->documentation_image_url = filter_var($participant->documentation_image_path, FILTER_VALIDATE_URL)
                ? $participant->documentation_image_path
                : '';
            $this->documentation_image_preview_url = $participant->documentationImageUrl();
            $this->documentation_image_verified = $this->documentation_image_url !== '';
            $this->documentation_caption = $participant->documentation_caption;

            $this->outputs = $participant->outputs->map(function ($output) {
                return [
                    'id' => $output->id,
                    'name' => $output->name,
                    'type' => $output->type->value,
                    'url' => $output->url ?? '',
                    'metadata' => null,
                    'url_valid' => true,
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
            'type' => 'pdf',
            'url' => '',
            'metadata' => null,
            'url_valid' => null,
        ];
    }

    public function inferOutputType(int $index): void
    {
        $url = trim($this->outputs[$index]['url'] ?? '');
        if ($url === '') {
            $this->outputs[$index]['metadata'] = null;
            $this->outputs[$index]['url_valid'] = false;
            $this->resetValidation("outputs.{$index}.url");

            return;
        }

        $validator = Validator::make(
            ['url' => $url],
            ['url' => 'required|url'],
            ['url.url' => 'Tautan luaran harus berupa URL yang valid, termasuk https://.'],
        );

        if ($validator->fails()) {
            $this->outputs[$index]['metadata'] = null;
            $this->outputs[$index]['url_valid'] = false;
            $this->addError("outputs.{$index}.url", $validator->errors()->first('url'));

            return;
        }

        $this->resetValidation("outputs.{$index}.url");
        $this->outputs[$index]['url_valid'] = true;

        $metadata = app(ExternalUrlMetadata::class)->fetch($url);
        $inferredType = app(ProgramOutputUrlResolver::class)->infer($url, $metadata['title'] ?? null);
        if ($inferredType) {
            $this->outputs[$index]['type'] = $inferredType->value;
        }

        $suggestedTitle = app(ExternalUrlMetadata::class)->suggestedTitle($metadata['title'] ?? null);
        if (empty(trim($this->outputs[$index]['name'] ?? '')) && $suggestedTitle) {
            $this->outputs[$index]['name'] = $suggestedTitle;
        }

        $this->outputs[$index]['metadata'] = $metadata;
    }

    public function updatedOutputs($value, $key): void
    {
        [$index, $field] = array_pad(explode('.', $key, 2), 2, null);
        if (! isset($this->outputs[$index])) {
            return;
        }

        if ($field === 'url') {
            $this->outputs[$index]['metadata'] = null;
            $this->outputs[$index]['url_valid'] = null;

            return;
        }

        if ($field !== 'type') {
            return;
        }

        $url = trim($this->outputs[$index]['url'] ?? '');
        if ($url === '') {
            $this->outputs[$index]['metadata'] = null;
        }
    }

    public function removeOutput($index)
    {
        // If it has an ID, we might want to mark it for deletion or delete it immediately.
        // For simplicity, we'll just remove it from the array and handle deletion on save.
        if (isset($this->outputs[$index]['id']) && $this->outputs[$index]['id']) {
            ParticipantOutput::find($this->outputs[$index]['id'])?->delete();
        }
        unset($this->outputs[$index]);
        $this->outputs = array_values($this->outputs);
    }

    public function updatedProgramId($value)
    {
        if ($this->action === 'create' && $this->programType?->code === 'multidisiplin') {
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
        if (! $user->group_id) {
            return;
        }

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

        DB::transaction(function () use ($user) {
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
                        ->whereType($this->type)
                        ->max('sequence') + 1;

                    $program = Program::create([
                        'student_id' => $user->id,
                        'group_id' => $user->group_id,
                        'title' => $this->title,
                        'program_type_id' => ProgramType::where('code', $this->type)->first()?->id,
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
        $isMultidisiplin = $participant->program->programType?->code === 'multidisiplin' && ! $isVideo;

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
            'documentation_image_url' => 'nullable|string|max:2048',
            'documentation_caption' => 'required|string|max:255',
            'outputs' => 'required|array|min:1',
            'outputs.*.name' => 'required|string|max:255',
            'outputs.*.type' => 'required|in:pdf,video,image,lainnya',
            'outputs.*.url' => 'required|url',
        ], [
            'outputs.required' => 'Minimal harus menambahkan 1 luaran program.',
            'outputs.min' => 'Minimal harus menambahkan 1 luaran program.',
            'outputs.*.name.required' => 'Judul/Nama luaran harus diisi.',
            'outputs.*.type.required' => 'Jenis luaran harus dipilih.',
        ]);

        $documentationImageInput = trim($this->documentation_image_url);
        $documentationImagePath = $this->documentation_image_path;
        if ($documentationImageInput !== '') {
            if (! $this->documentation_image_verified) {
                throw ValidationException::withMessages([
                    'documentation_image_url' => 'Periksa pratinjau gambar sebelum menyimpan.',
                ]);
            }

            try {
                $this->resolveDocumentationImagePreviewUrl($documentationImageInput);
                $documentationImagePath = $documentationImageInput;
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    'documentation_image_url' => $exception->getMessage(),
                ]);
            }
        } elseif (! $documentationImagePath) {
            throw ValidationException::withMessages([
                'documentation_image_url' => 'Tautan gambar dokumentasi wajib diisi.',
            ]);
        }

        $participant->update([
            'lpk_status' => ProgramStatus::Draft,
            'revision_note' => null,
            'execution_description' => $this->execution_description,
            'achievement' => $this->achievement,
            'obstacle' => $this->obstacle,
            'solution' => $this->solution,
            'documentation_image_path' => $documentationImagePath,
            'documentation_caption' => $this->documentation_caption,
        ]);

        $existingOutputs = $participant->outputs()->pluck('id')->toArray();
        $savedOutputIds = [];

        foreach ($this->outputs as $outputData) {
            $outputModel = null;
            if (! empty($outputData['id'])) {
                $outputModel = ParticipantOutput::find($outputData['id']);
            }

            if (! $outputModel) {
                $outputModel = new ParticipantOutput([
                    'program_participant_id' => $participant->id,
                    'output_code' => 'temp', // will be recalculated
                ]);
            }

            $outputModel->name = $outputData['name'];
            $outputModel->type = $outputData['type'];
            $outputModel->url = $outputData['url'];

            $outputModel->save();
            $savedOutputIds[] = $outputModel->id;
        }

        $outputsToDelete = array_diff($existingOutputs, $savedOutputIds);
        if (! empty($outputsToDelete)) {
            ParticipantOutput::whereIn('id', $outputsToDelete)->delete();
        }

        $allOutputs = $participant->outputs()->orderBy('id')->get();
        $totalCount = $allOutputs->count();
        foreach ($allOutputs as $i => $model) {
            $code = ParticipantOutput::generateOutputCode($participant, $i, $totalCount);
            if ($model->output_code !== $code) {
                $model->update(['output_code' => $code]);
            }
        }

        session()->flash('success', 'Laporan LPK Anda berhasil disimpan.');

        return $this->redirect(route('programs.index'), navigate: true);
    }

    public function validateDocumentationImage(): void
    {
        $this->documentation_image_verified = false;

        try {
            $this->documentation_image_preview_url = $this->resolveDocumentationImagePreviewUrl($this->documentation_image_url);
            $this->documentation_image_error = null;
            $this->resetValidation('documentation_image_url');
        } catch (\InvalidArgumentException $exception) {
            $this->documentation_image_preview_url = null;
            $this->documentation_image_error = $exception->getMessage();
            $this->addError('documentation_image_url', $exception->getMessage());
        }
    }

    public function updatedDocumentationImageUrl(): void
    {
        $this->documentation_image_preview_url = null;
        $this->documentation_image_verified = false;
        $this->documentation_image_error = null;
        $this->resetValidation('documentation_image_url');
    }

    private function resolveDocumentationImagePreviewUrl(string $value): string
    {
        return app(ExternalImagePreviewUrl::class)->resolve($value);
    }

    public function render()
    {
        return view('livewire.mahasiswa.program-form');
    }
}
