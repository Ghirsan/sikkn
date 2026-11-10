<?php

namespace App\Livewire\Admin;

use App\Enums\DocumentStatus;
use App\Models\Group;
use App\Models\Period;
use Livewire\Component;
use Livewire\WithPagination;

class Documents extends Component
{
    use WithPagination;

    public ?int $periodId = null;

    public string $search = '';

    public string $statusFilter = 'all';

    public function mount(?int $periodId = null): void
    {
        $this->periodId = $periodId ?? Period::active()->first()?->id ?? Period::latest('start_date')->first()?->id;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodId(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $baseGroupQuery = Group::query();
        if ($this->periodId) {
            $baseGroupQuery->where('period_id', $this->periodId);
        }

        $totalGroups = (clone $baseGroupQuery)->count();

        $lrkApprovedCount = (clone $baseGroupQuery)->where(function ($q) {
            $q->where('is_lrk_locked', true)
                ->orWhere('lrk_status', DocumentStatus::Approved);
        })->count();

        $lpkApprovedCount = (clone $baseGroupQuery)->where('is_lpk_locked', true)->count();

        $completedCount = (clone $baseGroupQuery)->where(function ($q) {
            $q->where('is_lrk_locked', true)
                ->orWhere('lrk_status', DocumentStatus::Approved);
        })->where('is_lpk_locked', true)->count();

        $query = Group::query()
            ->with([
                'period',
                'dpls',
                'leadDpl',
                'students' => fn ($q) => $q->select('id', 'name', 'group_id'),
            ]);

        if ($this->periodId) {
            $query->where('period_id', $this->periodId);
        }

        if ($this->search) {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('village', 'like', $term)
                    ->orWhere('district', 'like', $term)
                    ->orWhere('regency', 'like', $term)
                    ->orWhereHas('dpls', function ($dplQuery) use ($term) {
                        $dplQuery->where('name', 'like', $term)
                            ->orWhere('nip', 'like', $term);
                    });
            });
        }

        if ($this->statusFilter === 'lrk_pending') {
            $query->where(function ($q) {
                $q->where('is_lrk_locked', false)
                    ->where(function ($sub) {
                        $sub->whereNull('lrk_status')
                            ->orWhere('lrk_status', '!=', DocumentStatus::Approved);
                    });
            });
        } elseif ($this->statusFilter === 'lpk_pending') {
            $query->where('is_lpk_locked', false);
        } elseif ($this->statusFilter === 'completed') {
            $query->where(function ($q) {
                $q->where('is_lrk_locked', true)
                    ->orWhere('lrk_status', DocumentStatus::Approved);
            })->where('is_lpk_locked', true);
        }

        $groups = $query->orderBy('name')->paginate(10);

        return view('livewire.admin.documents', [
            'groups' => $groups,
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'total' => $totalGroups,
                'lrk_approved' => $lrkApprovedCount,
                'lpk_approved' => $lpkApprovedCount,
                'completed' => $completedCount,
            ],
        ]);
    }
}
