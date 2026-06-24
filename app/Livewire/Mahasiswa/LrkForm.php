<?php

namespace App\Livewire\Mahasiswa;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class LrkForm extends Component
{
    use WithFileUploads;

    public string $background = '';
    public string $program_multidisiplin_text = '';
    public string $program_sosmas_text = '';
    public string $program_lainnya_text = '';
    public string $storyboard_text = '';
    public string $video_script_text = '';
    public string $survey_documentation_text = '';
    public string $location_map_text = '';
    public $location_map_image; // For file upload
    public ?string $existing_map_path = null;

    public function mount()
    {
        $user = Auth::user();
        $group = $user->group;

        if (!$group || $group->student_leader_id !== $user->id) {
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
            'location_map_image' => 'nullable|image|max:2048',
        ]);

        $group = Auth::user()->group;

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

        if ($this->location_map_image) {
            $path = $this->location_map_image->store('lrk/maps', 'public');
            $data['location_map_path'] = $path;
            $this->existing_map_path = $path;
        }

        $group->update($data);

        \Flux\Flux::toast(variant: 'success', heading: 'Tersimpan', text: 'Data laporan LRK berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.mahasiswa.lrk-form');
    }
}
