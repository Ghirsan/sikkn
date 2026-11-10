<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Period;
use App\Models\User;
use App\Services\GroupAllocationExcelService;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Groups extends Component
{
    use WithFileUploads, WithPagination;

    public ?int $periodId = null;

    public string $search = '';

    public $importFile = null;

    public bool $showImportModal = false;

    public array $importErrors = [];

    public bool $showCreateModal = false;

    public ?int $create_period_id = null;

    public string $create_name = '';

    public ?int $create_group_type_id = null;

    public string $create_village = '';

    public string $create_district = '';

    public string $create_regency = '';

    public string $create_province = '';

    public string $create_partner_name = '';

    public string $create_village_head = '';

    public ?string $create_start_date = null;

    public ?string $create_end_date = null;

    public bool $isEditing = false;

    public ?int $editingGroupId = null;

    public function mount()
    {
        $this->periodId = Period::active()->first()?->id ?? Period::latest('start_date')->first()?->id;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodId(): void
    {
        $this->resetPage();
    }

    public bool $showAssignModal = false;

    public ?Group $assigningGroup = null;

    public array $selectedDpls = [];

    public ?int $leadDplId = null;

    public function openAssignModal(Group $group)
    {
        $this->assigningGroup = $group;
        $this->selectedDpls = $group->dpls()->pluck('users.id')->map(fn ($id) => (string) $id)->toArray();
        $this->leadDplId = $group->lead_dpl_id;
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assigningGroup = null;
        $this->selectedDpls = [];
        $this->leadDplId = null;
    }

    public function saveAssignments()
    {
        if (! $this->assigningGroup) {
            return;
        }

        $this->validate([
            'selectedDpls' => 'array',
            'leadDplId' => 'nullable|integer',
        ]);

        // Sync pivot table
        $this->assigningGroup->dpls()->sync($this->selectedDpls);

        // Ensure lead DPL is one of the assigned DPLs
        if ($this->leadDplId && ! in_array((string) $this->leadDplId, $this->selectedDpls)) {
            $this->leadDplId = null; // Unset if they are not in the assigned list
        }

        // Update group's lead_dpl_id
        $this->assigningGroup->update(['lead_dpl_id' => $this->leadDplId]);

        $this->closeAssignModal();
        Flux::toast('DPL berhasil ditugaskan ke kelompok.');
    }

    public bool $showDatesModal = false;

    public ?Group $editingGroup = null;

    public $startDate = null;

    public $endDate = null;

    public function openDatesModal(Group $group)
    {
        $this->editingGroup = $group;
        $this->startDate = $group->start_date?->format('Y-m-d');
        $this->endDate = $group->end_date?->format('Y-m-d');
        $this->showDatesModal = true;
    }

    public function closeDatesModal()
    {
        $this->showDatesModal = false;
        $this->editingGroup = null;
        $this->startDate = null;
        $this->endDate = null;
    }

    public function saveDates()
    {
        if (! $this->editingGroup) {
            return;
        }

        $period = $this->editingGroup->period;

        $this->validate([
            'startDate' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($period) {
                    if ($value && $period && Carbon::parse($value)->lt($period->start_date)) {
                        $fail('Tanggal mulai tidak boleh sebelum tanggal mulai periode ('.$period->start_date->format('d/m/Y').').');
                    }
                },
            ],
            'endDate' => [
                'nullable',
                'date',
                'after_or_equal:startDate',
                function ($attribute, $value, $fail) use ($period) {
                    if ($value && $period && Carbon::parse($value)->gt($period->end_date)) {
                        $fail('Tanggal selesai tidak boleh setelah tanggal selesai periode ('.$period->end_date->format('d/m/Y').').');
                    }
                },
            ],
        ]);

        $this->editingGroup->update([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $this->closeDatesModal();
        Flux::toast('Waktu KKN berhasil disimpan.');
    }

    public function exportExcel(GroupAllocationExcelService $excelService)
    {
        $period = $this->periodId ? Period::find($this->periodId) : (Period::active()->first() ?? Period::current());

        if (! $period) {
            Flux::toast('Pilih periode KKN terlebih dahulu untuk mengunduh format Excel.', variant: 'danger');

            return null;
        }

        return $excelService->exportGroups($period);
    }

    public function openImportModal(): void
    {
        $this->reset(['importFile', 'importErrors']);
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'importErrors']);
    }

    public function importExcel(GroupAllocationExcelService $excelService)
    {
        $period = $this->periodId ? Period::find($this->periodId) : (Period::active()->first() ?? Period::current());

        if (! $period) {
            $this->importErrors = ['Pilih periode KKN terlebih dahulu sebelum mengimpor data kelompok.'];

            return;
        }

        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ], [
            'importFile.required' => 'Silakan pilih file Excel (.xlsx) untuk diimpor.',
            'importFile.mimes' => 'Format file harus berupa dokumen Excel (.xlsx).',
            'importFile.max' => 'Ukuran file Excel tidak boleh melebihi 10MB.',
        ]);

        $result = $excelService->importGroups($this->importFile, $period);

        try {
            $this->importFile?->delete();
        } catch (\Throwable) {
            // ignore
        }

        if ($result['success']) {
            $this->closeImportModal();
            $msg = "Berhasil memproses kelompok: {$result['imported_count']} baru dibuat, {$result['updated_count']} diperbarui.";
            Flux::toast($msg);
        } else {
            $this->importErrors = $result['errors'];
            Flux::toast('Gagal mengimpor file. Periksa daftar kesalahan.', variant: 'danger');
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->editingGroupId = null;
        $this->create_period_id = $this->periodId ?? Period::active()->first()?->id ?? Period::latest('start_date')->first()?->id;
        $this->create_name = '';
        $this->create_group_type_id = GroupType::first()?->id;
        $this->create_village = '';
        $this->create_district = '';
        $this->create_regency = '';
        $this->create_province = '';
        $this->create_partner_name = '';
        $this->create_village_head = '';
        $this->create_start_date = null;
        $this->create_end_date = null;
        $this->showCreateModal = true;
    }

    public function openEditModal(Group $group): void
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->editingGroupId = $group->id;
        $this->create_period_id = $group->period_id;
        $this->create_name = $group->name;
        $this->create_group_type_id = $group->group_type_id;
        $this->create_village = $group->village ?? '';
        $this->create_district = $group->district ?? '';
        $this->create_regency = $group->regency ?? '';
        $this->create_province = $group->province ?? '';
        $this->create_partner_name = $group->partner_name ?? '';
        $this->create_village_head = $group->village_head ?? '';
        $this->create_start_date = $group->start_date?->format('Y-m-d');
        $this->create_end_date = $group->end_date?->format('Y-m-d');
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->isEditing = false;
        $this->editingGroupId = null;
        $this->resetValidation();
    }

    public function saveGroup(): void
    {
        $period = Period::find($this->create_period_id);

        $nameRule = Rule::unique('groups', 'name')
            ->where(fn ($query) => $query->where('period_id', $this->create_period_id));

        if ($this->isEditing && $this->editingGroupId) {
            $nameRule->ignore($this->editingGroupId);
        }

        $this->validate([
            'create_period_id' => ['required', 'exists:periods,id'],
            'create_name' => [
                'required',
                'string',
                'max:255',
                $nameRule,
            ],
            'create_group_type_id' => ['nullable', 'exists:group_types,id'],
            'create_village' => ['required', 'string', 'max:255'],
            'create_district' => ['required', 'string', 'max:255'],
            'create_regency' => ['nullable', 'string', 'max:255'],
            'create_province' => ['nullable', 'string', 'max:255'],
            'create_partner_name' => ['nullable', 'string', 'max:255'],
            'create_village_head' => ['nullable', 'string', 'max:255'],
            'create_start_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($period) {
                    if ($value && $period && Carbon::parse($value)->lt($period->start_date)) {
                        $fail('Tanggal mulai tidak boleh sebelum tanggal mulai periode ('.$period->start_date->format('d/m/Y').').');
                    }
                },
            ],
            'create_end_date' => [
                'nullable',
                'date',
                'after_or_equal:create_start_date',
                function ($attribute, $value, $fail) use ($period) {
                    if ($value && $period && Carbon::parse($value)->gt($period->end_date)) {
                        $fail('Tanggal selesai tidak boleh setelah tanggal selesai periode ('.$period->end_date->format('d/m/Y').').');
                    }
                },
            ],
        ], [
            'create_period_id.required' => 'Pilih periode KKN.',
            'create_name.required' => 'Nama kelompok wajib diisi.',
            'create_name.unique' => 'Nama kelompok sudah digunakan pada periode ini.',
            'create_village.required' => 'Nama desa wajib diisi.',
            'create_district.required' => 'Nama kecamatan wajib diisi.',
            'create_end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        $data = [
            'period_id' => $this->create_period_id,
            'name' => $this->create_name,
            'group_type_id' => $this->create_group_type_id ?: null,
            'village' => $this->create_village,
            'district' => $this->create_district,
            'regency' => $this->create_regency ?: null,
            'province' => $this->create_province ?: null,
            'partner_name' => $this->create_partner_name ?: null,
            'village_head' => $this->create_village_head ?: null,
            'start_date' => $this->create_start_date ?: null,
            'end_date' => $this->create_end_date ?: null,
        ];

        if ($this->isEditing && $this->editingGroupId) {
            $group = Group::findOrFail($this->editingGroupId);
            $group->update($data);
            $msg = 'Data kelompok berhasil diperbarui.';
        } else {
            Group::create($data);
            $msg = 'Kelompok KKN berhasil dibuat.';
        }

        $this->closeCreateModal();
        Flux::toast($msg, variant: 'success');
    }

    public function createGroup(): void
    {
        $this->saveGroup();
    }

    public function render()
    {
        $query = Group::query();

        if ($this->periodId) {
            $query->where('period_id', $this->periodId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('village', 'like', '%'.$this->search.'%');
            });
        }

        $groups = (clone $query)
            ->with(['period', 'dpls', 'groupType', 'leadDpl'])
            ->withCount('students')
            ->latest()
            ->paginate(10);

        $withDpl = (clone $query)->has('dpls')->count();
        $availableDpls = User::where('role', UserRole::Dpl)->get();

        return view('livewire.admin.groups', [
            'groups' => $groups,
            'availableDpls' => $availableDpls,
            'groupTypes' => GroupType::orderBy('name')->get(),
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'total' => $groups->total(),
                'with_dpl' => $withDpl,
                'without_dpl' => max(0, $groups->total() - $withDpl),
            ],
        ]);
    }
}
