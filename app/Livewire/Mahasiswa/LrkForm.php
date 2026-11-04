<?php

namespace App\Livewire\Mahasiswa;

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

    public string $video_script_text = '';

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

    public function mount()
    {
        $user = Auth::user();
        $group = $user->group;

        if (! $group || $group->student_leader_id !== $user->id) {
            abort(403);
        }

        $this->background = $group->background ?? '';
        $this->program_multidisiplin_text = $group->program_multidisiplin_text ?? '';
        $this->program_sosmas_text = $group->program_sosmas_text ?? '';
        $this->program_lainnya_text = $group->program_lainnya_text ?? '';
        $this->storyboard_text = $group->storyboard_text ?? '';
        $this->video_script_text = $group->video_script_text ?? '';
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

    public function save()
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
            'video_script_text' => $this->video_script_text,
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

        Flux::toast(variant: 'success', heading: 'Tersimpan', text: 'Data laporan LRK berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.mahasiswa.lrk-form');
    }
}
