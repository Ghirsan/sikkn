<?php

namespace App\Livewire\Dpl;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Program;
use App\Models\ProgramParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReviewPrograms extends Component
{
    public string $filterStatus = '';
    public string $filterType = '';
    public string $selectedGroupId = '';
    public string $search = '';

    // Theme management
    public string $newThemeTitle = '';
    public ?int $editingThemeId = null;
    public string $editingThemeTitle = '';

    // Inspect modal
    public ?int $inspectingParticipantId = null;
    public string $revisionNote = '';
    public bool $showRevisionForm = false;

    public function inspect(int $participantId): void
    {
        $this->inspectingParticipantId = $participantId;
        $this->revisionNote = '';
        $this->showRevisionForm = false;
        $this->modal('inspect-program')->show();
    }

    public function closeInspect(): void
    {
        $this->inspectingParticipantId = null;
        $this->revisionNote = '';
        $this->showRevisionForm = false;
    }

    #[Computed]
    public function inspectingParticipant(): ?ProgramParticipant
    {
        if (! $this->inspectingParticipantId) {
            return null;
        }

        return ProgramParticipant::with(['student', 'program.group', 'outputs'])
            ->find($this->inspectingParticipantId);
    }

    public function approve(int $participantId): void
    {
        $participant = $this->getAuthorizedParticipant($participantId);
        
        $isLpkPhase = $participant->status === ProgramStatus::Approved && $participant->lpk_status === ProgramStatus::Submitted;
        $statusField = $isLpkPhase ? 'lpk_status' : 'status';

        $participant->update([
            $statusField => ProgramStatus::Approved,
            'revision_note' => null
        ]);

        $this->modal('inspect-program')->close();
        $this->closeInspect();
    }

    public function startRevision(): void
    {
        $this->showRevisionForm = true;
    }

    public function submitRevision(): void
    {
        $this->validate(['revisionNote' => 'required|min:10']);

        $participant = $this->getAuthorizedParticipant($this->inspectingParticipantId);
        
        $isLpkPhase = $participant->status === ProgramStatus::Approved && $participant->lpk_status === ProgramStatus::Submitted;
        $statusField = $isLpkPhase ? 'lpk_status' : 'status';

        $participant->update([
            $statusField => ProgramStatus::NeedsRevision,
            'revision_note' => $this->revisionNote,
        ]);

        $this->modal('inspect-program')->close();
        $this->closeInspect();
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
            $query->where(function ($q) {
                $q->where('status', $this->filterStatus)
                  ->orWhere('lpk_status', $this->filterStatus);
            });
        }

        if ($this->filterType) {
            $query->whereHas('program', function ($q) {
                $q->where('type', $this->filterType);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('student', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('nim', 'like', '%' . $this->search . '%');
                })->orWhereHas('program', function ($q2) {
                    $q2->where('title', 'like', '%' . $this->search . '%');
                });
            });
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
                })->where(function($q) {
                    $q->where('status', ProgramStatus::Submitted)
                      ->orWhere('lpk_status', ProgramStatus::Submitted);
                })->count(),
                
                'approved' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->where('status', ProgramStatus::Approved)
                  ->where('lpk_status', ProgramStatus::Approved)->count(),
                
                'revision' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->where(function($q) {
                    $q->where('status', ProgramStatus::NeedsRevision)
                      ->orWhere('lpk_status', ProgramStatus::NeedsRevision);
                })->count(),
                
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
