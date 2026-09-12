<?php

namespace App\Livewire\Dpl;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Program;
use App\Models\ProgramParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReviewPrograms extends Component
{
    public string $filterStatus = '';
    public string $selectedGroupId = '';

    // Theme management
    public string $newThemeTitle = '';
    public ?int $editingThemeId = null;
    public string $editingThemeTitle = '';

    public function approve(int $participantId): void
    {
        $participant = $this->getAuthorizedParticipant($participantId);
        $participant->update(['status' => ProgramStatus::Approved, 'revision_note' => null]);
    }

    public string $revisionNote = '';

    public int $revisingParticipantId = 0;

    public function startRevision(int $participantId): void
    {
        $this->revisingParticipantId = $participantId;
        $this->revisionNote = '';
    }

    public function submitRevision(): void
    {
        $this->validate(['revisionNote' => 'required|min:10']);

        $participant = $this->getAuthorizedParticipant($this->revisingParticipantId);
        $participant->update([
            'status' => ProgramStatus::NeedsRevision,
            'revision_note' => $this->revisionNote,
        ]);

        $this->revisingParticipantId = 0;
        $this->revisionNote = '';
    }

    // ── Theme Management ─────────────────────────────────────────────

    public function addTheme(): void
    {
        $this->validate(['newThemeTitle' => 'required|string|max:255']);

        $groupId = $this->getSelectedGroupIdForThemes();
        if (! $groupId) return;

        $this->authorizeGroup($groupId);

        $nextSequence = Program::where('group_id', $groupId)
            ->where('type', ProgramType::Multidisiplin)
            ->whereNull('student_id')
            ->max('sequence') + 1;

        Program::create([
            'group_id'  => $groupId,
            'student_id' => null,
            'title'     => $this->newThemeTitle,
            'type'      => ProgramType::Multidisiplin,
            'sequence'  => $nextSequence,
        ]);

        $this->newThemeTitle = '';
    }

    public function startEditTheme(int $programId): void
    {
        $program = $this->getAuthorizedTheme($programId);
        $this->editingThemeId = $program->id;
        $this->editingThemeTitle = $program->title;
    }

    public function cancelEditTheme(): void
    {
        $this->editingThemeId = null;
        $this->editingThemeTitle = '';
    }

    public function saveEditTheme(): void
    {
        $this->validate(['editingThemeTitle' => 'required|string|max:255']);

        $program = $this->getAuthorizedTheme($this->editingThemeId);
        $program->update(['title' => $this->editingThemeTitle]);

        $this->editingThemeId = null;
        $this->editingThemeTitle = '';
    }

    public function deleteTheme(int $programId): void
    {
        $program = $this->getAuthorizedTheme($programId);

        if ($program->participants()->count() > 0) {
            return; // Block deletion if students have joined
        }

        $program->delete();
    }

    private function getSelectedGroupIdForThemes(): ?int
    {
        if ($this->selectedGroupId) {
            return (int) $this->selectedGroupId;
        }

        // If DPL only has one group, use that
        $groups = Auth::user()->dplGroups()->pluck('groups.id');
        if ($groups->count() === 1) {
            return $groups->first();
        }

        return null;
    }

    private function authorizeGroup(int $groupId): void
    {
        $groupIds = Auth::user()->dplGroups()->pluck('groups.id');
        abort_unless($groupIds->contains($groupId), 403);
    }

    private function getAuthorizedTheme(int $programId): Program
    {
        $groupIds = Auth::user()->dplGroups()->pluck('groups.id');

        return Program::where('type', ProgramType::Multidisiplin)
            ->whereNull('student_id')
            ->whereIn('group_id', $groupIds)
            ->findOrFail($programId);
    }

    public function render()
    {
        $user = Auth::user();
        $groupIds = $user->dplGroups()->pluck('groups.id');

        if ($this->selectedGroupId) {
            $groupIds = collect([$this->selectedGroupId]);
        }

        $query = ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })->with(['student', 'program.group']);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Multidisiplin themes for the theme management section
        $themeGroupId = $this->getSelectedGroupIdForThemes();
        $multidisiplinThemes = $themeGroupId
            ? Program::where('group_id', $themeGroupId)
                ->where('type', ProgramType::Multidisiplin)
                ->whereNull('student_id')
                ->withCount('participants')
                ->orderBy('sequence')
                ->get()
            : collect();

        return view('livewire.dpl.review-programs', [
            'participants' => $query->latest()->get(),
            'allGroups' => $user->dplGroups()->get(),
            'multidisiplinThemes' => $multidisiplinThemes,
            'canManageThemes' => $themeGroupId !== null,
            'stats' => [
                'pending' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->where('status', ProgramStatus::Submitted)->count(),
                
                'approved' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->where('status', ProgramStatus::Approved)->count(),
                
                'revision' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->where('status', ProgramStatus::NeedsRevision)->count(),
                
                'total' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->count(),
            ],
        ]);
    }

    private function getAuthorizedParticipant(int $participantId): ProgramParticipant
    {
        $groupIds = Auth::user()->dplGroups()->pluck('groups.id');

        return ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })->findOrFail($participantId);
    }
}
