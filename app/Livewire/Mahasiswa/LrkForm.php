<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\DocumentStatus;
use App\Models\Group;
use App\Services\ExternalImagePreviewUrl;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LrkForm extends Component
{
    public string $background = '';

    public string $program_multidisiplin_text = '';

    public string $program_sosmas_text = '';

    public string $program_lainnya_text = '';

    public string $storyboard_text = '';

    public array $storyboard_scenes = [];

    public string $video_script_text = '';

    public array $video_script_scenes = [];

    public array $macro_schedules = [];

    public array $weekKeys = [];

    public string $survey_documentation_text = '';

    public string $location_map_text = '';

    public string $location_map_url = '';

    public ?string $location_map_preview_url = null;

    public bool $location_map_verified = false;

    public ?string $location_map_error = null;

    public ?string $existing_map_path = null;

    public string $survey_document_url = '';

    public ?string $survey_document_preview_url = null;

    public bool $survey_document_verified = false;

    public ?string $survey_document_error = null;

    public ?string $existing_survey_path = null;

    public ?string $previewUrl = null;

    public ?DocumentStatus $lrkStatus = DocumentStatus::Draft;

    public ?string $lrkNotes = null;

    public bool $isReady = false;

    public bool $isFinalized = false;

    public bool $isSubmitted = false;

    public bool $isNeedsRevision = false;

    public bool $canEdit = true;

    public function mount()
    {
        $user = Auth::user();
        $group = $user->group;

        if (! $group || $group->student_leader_id !== $user->id) {
            abort(403);
        }

        $this->isReady = $group->isLrkReady();
        $this->lrkStatus = $group->lrk_status ?? DocumentStatus::Draft;
        $this->lrkNotes = $group->lrk_notes;
        $this->isFinalized = $group->isLrkFinalized();
        $this->isSubmitted = $this->lrkStatus === DocumentStatus::Submitted;
        $this->isNeedsRevision = $this->lrkStatus === DocumentStatus::NeedsRevision;
        $this->canEdit = $group->isLrkEditable();

        $this->background = $group->background ?? '';
        $this->program_multidisiplin_text = $group->program_multidisiplin_text ?? '';
        $this->program_sosmas_text = $group->program_sosmas_text ?? '';
        $this->program_lainnya_text = $group->program_lainnya_text ?? '';
        $this->storyboard_text = $group->storyboard_text ?? '';
        $this->storyboard_scenes = ! empty($group->storyboard_scenes) ? $group->storyboard_scenes : Group::defaultStoryboardScenes();
        foreach ($this->storyboard_scenes as $idx => &$scene) {
            $scene['scene'] = 'Scene '.($idx + 1);
            if (! isset($scene['image_url'])) {
                $scene['image_url'] = '';
            }
            if (! isset($scene['visual'])) {
                $scene['visual'] = '';
            }
        }
        unset($scene);
        $this->video_script_text = $group->video_script_text ?? '';
        $this->video_script_scenes = ! empty($group->video_script_scenes) ? $group->video_script_scenes : Group::defaultVideoScriptScenes();
        $this->weekKeys = $group->getWeekKeys();
        $this->macro_schedules = ! empty($group->macro_schedules) ? $group->macro_schedules : Group::defaultMacroSchedules($group->getWeeksCount());
        foreach ($this->macro_schedules as &$row) {
            foreach ($this->weekKeys as $wKey) {
                if (! isset($row[$wKey])) {
                    $row[$wKey] = false;
                }
            }
        }
        unset($row);
        $this->survey_documentation_text = $group->survey_documentation_text ?? '';
        $this->location_map_text = $group->location_map_text ?? '';

        $this->existing_map_path = $group->location_map_path;
        if ($this->existing_map_path) {
            if (filter_var($this->existing_map_path, FILTER_VALIDATE_URL)) {
                $this->location_map_url = $this->existing_map_path;
                try {
                    $this->location_map_preview_url = app(ExternalImagePreviewUrl::class)->resolve($this->location_map_url);
                    $this->location_map_verified = true;
                } catch (\Throwable) {
                    $this->location_map_preview_url = $this->location_map_url;
                }
            } else {
                $this->location_map_preview_url = asset('storage/'.$this->existing_map_path);
                $this->location_map_verified = true;
            }
        }

        $this->existing_survey_path = $group->survey_document_path;
        if ($this->existing_survey_path) {
            if (filter_var($this->existing_survey_path, FILTER_VALIDATE_URL)) {
                $this->survey_document_url = $this->existing_survey_path;
                try {
                    $this->survey_document_preview_url = app(ExternalImagePreviewUrl::class)->resolve($this->survey_document_url);
                    $this->survey_document_verified = true;
                } catch (\Throwable) {
                    $this->survey_document_preview_url = $this->survey_document_url;
                }
            } else {
                $this->survey_document_preview_url = asset('storage/'.$this->existing_survey_path);
                $this->survey_document_verified = true;
            }
        }
    }

    public function validateLocationMap(): void
    {
        $this->location_map_verified = false;
        $url = trim($this->location_map_url);

        if ($url === '') {
            $this->location_map_preview_url = null;
            $this->location_map_error = null;
            $this->resetValidation('location_map_url');

            return;
        }

        try {
            $this->location_map_preview_url = app(ExternalImagePreviewUrl::class)->resolve($url);
            $this->location_map_error = null;
            $this->resetValidation('location_map_url');
        } catch (\InvalidArgumentException $exception) {
            $this->location_map_preview_url = null;
            $this->location_map_error = $exception->getMessage();
            $this->addError('location_map_url', $exception->getMessage());
        }
    }

    public function updatedLocationMapUrl(): void
    {
        $this->location_map_preview_url = null;
        $this->location_map_verified = false;
        $this->location_map_error = null;
        $this->resetValidation('location_map_url');

        if (trim($this->location_map_url) === '') {
            $this->existing_map_path = null;
        }
    }

    public function validateSurveyDocument(): void
    {
        $this->survey_document_verified = false;
        $url = trim($this->survey_document_url);

        if ($url === '') {
            $this->survey_document_preview_url = null;
            $this->survey_document_error = null;
            $this->resetValidation('survey_document_url');

            return;
        }

        try {
            $this->survey_document_preview_url = app(ExternalImagePreviewUrl::class)->resolve($url);
            $this->survey_document_error = null;
            $this->resetValidation('survey_document_url');
        } catch (\InvalidArgumentException $exception) {
            $this->survey_document_preview_url = null;
            $this->survey_document_error = $exception->getMessage();
            $this->addError('survey_document_url', $exception->getMessage());
        }
    }

    public function updatedSurveyDocumentUrl(): void
    {
        $this->survey_document_preview_url = null;
        $this->survey_document_verified = false;
        $this->survey_document_error = null;
        $this->resetValidation('survey_document_url');

        if (trim($this->survey_document_url) === '') {
            $this->existing_survey_path = null;
        }
    }

    protected function persistData(): void
    {
        $this->validate([
            'background' => 'nullable|string',
            'program_multidisiplin_text' => 'nullable|string',
            'program_sosmas_text' => 'nullable|string',
            'program_lainnya_text' => 'nullable|string',
            'storyboard_text' => 'nullable|string',
            'video_script_text' => 'nullable|string',
            'survey_documentation_text' => 'nullable|string',
            'location_map_text' => 'nullable|string',
            'location_map_url' => 'nullable|string|max:2048',
            'survey_document_url' => 'nullable|string|max:2048',
        ]);

        $group = Auth::user()->group;

        if (! $group || $group->student_leader_id !== Auth::id()) {
            abort(403);
        }

        $data = [
            'background' => $this->background,
            'program_multidisiplin_text' => $this->program_multidisiplin_text,
            'program_sosmas_text' => $this->program_sosmas_text,
            'program_lainnya_text' => $this->program_lainnya_text,
            'storyboard_text' => $this->storyboard_text,
            'storyboard_scenes' => $this->storyboard_scenes,
            'video_script_text' => $this->video_script_text,
            'video_script_scenes' => $this->video_script_scenes,
            'macro_schedules' => $this->macro_schedules,
            'survey_documentation_text' => $this->survey_documentation_text,
            'location_map_text' => $this->location_map_text,
        ];

        // Process location map
        $locationMapInput = trim($this->location_map_url);
        if ($locationMapInput !== '') {
            try {
                app(ExternalImagePreviewUrl::class)->resolve($locationMapInput);
                $data['location_map_path'] = $locationMapInput;
                $this->existing_map_path = $locationMapInput;
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    'location_map_url' => $exception->getMessage(),
                ]);
            }
        } elseif (trim($this->location_map_url) === '' && ! $this->existing_map_path) {
            $data['location_map_path'] = null;
        }

        // Process survey document
        $surveyDocInput = trim($this->survey_document_url);
        if ($surveyDocInput !== '') {
            try {
                app(ExternalImagePreviewUrl::class)->resolve($surveyDocInput);
                $data['survey_document_path'] = $surveyDocInput;
                $this->existing_survey_path = $surveyDocInput;
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    'survey_document_url' => $exception->getMessage(),
                ]);
            }
        } elseif (trim($this->survey_document_url) === '' && ! $this->existing_survey_path) {
            $data['survey_document_path'] = null;
        }

        $group->update($data);
    }

    public function save(): void
    {
        $group = Auth::user()->group;

        if (! $group || ! $group->isLrkEditable()) {
            Flux::toast(variant: 'danger', heading: 'Tidak Dapat Disimpan', text: 'Dokumen sedang ditinjau atau telah disetujui.');

            return;
        }

        $this->persistData();

        Flux::toast(variant: 'success', heading: 'Tersimpan', text: 'Data laporan LRK berhasil disimpan.');
    }

    public function getStoryboardPreviewUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        try {
            return app(ExternalImagePreviewUrl::class)->resolve($url);
        } catch (\Throwable) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }

            return null;
        }
    }

    public function addStoryboardScene(): void
    {
        $count = count($this->storyboard_scenes);
        $this->storyboard_scenes[] = [
            'scene' => 'Scene '.($count + 1),
            'image_url' => '',
            'visual' => '',
        ];
    }

    public function removeStoryboardScene(int $index): void
    {
        unset($this->storyboard_scenes[$index]);
        $this->storyboard_scenes = array_values($this->storyboard_scenes);
        foreach ($this->storyboard_scenes as $idx => &$scene) {
            $scene['scene'] = 'Scene '.($idx + 1);
        }
        unset($scene);
    }

    public function addVideoScriptScene(): void
    {
        $this->video_script_scenes[] = [
            'scene' => (string) (count($this->video_script_scenes) + 1),
            'visual' => '',
            'audio' => '',
        ];
    }

    public function removeVideoScriptScene(int $index): void
    {
        unset($this->video_script_scenes[$index]);
        $this->video_script_scenes = array_values($this->video_script_scenes);
    }

    public function addMacroSchedule(): void
    {
        $newRow = [
            'no' => count($this->macro_schedules) + 1,
            'kegiatan' => '',
        ];
        foreach ($this->weekKeys as $wKey) {
            $newRow[$wKey] = false;
        }
        $this->macro_schedules[] = $newRow;
    }

    public function removeMacroSchedule(int $index): void
    {
        unset($this->macro_schedules[$index]);
        $this->macro_schedules = array_values($this->macro_schedules);
        foreach ($this->macro_schedules as $idx => &$item) {
            $item['no'] = $idx + 1;
        }
    }

    public function preview(): void
    {
        if ($this->canEdit) {
            $this->persistData();
        }

        $group = Auth::user()->group;
        $url = route('lrk.preview', ['group' => $group, 'v' => now()->timestamp]);
        $this->previewUrl = $url;

        $this->js("window.open('{$url}', '_blank')");

        Flux::toast(variant: 'info', heading: 'Pratinjau PDF', text: 'Membuka dokumen di halaman baru.');
    }

    public function submit(): void
    {
        $group = Auth::user()->group;

        if (! $group || $group->student_leader_id !== Auth::id()) {
            abort(403);
        }

        if (! $group->isLrkReady()) {
            Flux::modal('confirm-submit-modal')->close();
            Flux::toast(variant: 'danger', heading: 'Belum Siap', text: 'Dokumen LRK belum dapat diajukan karena belum seluruh rencana program disetujui.');

            return;
        }

        $this->persistData();

        $group->update([
            'lrk_status' => DocumentStatus::Submitted,
            'lrk_submitted_at' => now(),
            'is_lrk_locked' => false,
        ]);

        $this->lrkStatus = DocumentStatus::Submitted;
        $this->isSubmitted = true;
        $this->isNeedsRevision = false;
        $this->canEdit = false;

        Flux::modal('confirm-submit-modal')->close();
        Flux::toast(variant: 'success', heading: 'LRK Berhasil Diajukan', text: 'Dokumen LRK telah diajukan ke DPL untuk diperiksa.');
    }

    public function withdraw(): void
    {
        $group = Auth::user()->group;

        if (! $group || $group->student_leader_id !== Auth::id()) {
            abort(403);
        }

        if ($group->lrk_status !== DocumentStatus::Submitted) {
            return;
        }

        $group->update([
            'lrk_status' => DocumentStatus::Draft,
        ]);

        $this->lrkStatus = DocumentStatus::Draft;
        $this->isSubmitted = false;
        $this->canEdit = true;

        Flux::modal('confirm-withdraw-modal')->close();
        Flux::toast(variant: 'info', heading: 'Pengajuan Dibatalkan', text: 'Dokumen LRK kembali ke status draf untuk dapat diedit.');
    }

    public function finalize(): void
    {
        $this->submit();
    }

    public function unfinalize(): void
    {
        $this->withdraw();
    }

    public function render()
    {
        return view('livewire.mahasiswa.lrk-form');
    }
}
