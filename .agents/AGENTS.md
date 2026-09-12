# SIKKN System Domain Knowledge

## Roles and Responsibilities

### DPL (Dosen Pembimbing Lapangan / Dosen KKN)
- **Role Enum:** `App\Enums\UserRole::Dpl` (value: `'dpl'`).
- **Route Prefix:** `/dpl`
- **Dashboard Component:** `resources/views/dashboard/panels/dpl.blade.php`
- **Primary Responsibilities:**
  1. **Groups (`dpl.groups.index`):** View and manage assigned student groups, including participant lists and their statuses.
  2. **Programs (`dpl.programs.index`):** Review, approve, or request revisions for programs proposed by students.
  3. **Documents (`dpl.documents.index`):** Monitor the readiness of team documents like LRK (Laporan Rencana Kegiatan) and LPK (Laporan Pelaksanaan Kegiatan).
  4. **Logbook (`dpl.logbook.index`):** Monitor, review, and approve students' daily logbook entries.
  5. **Mentoring (`dpl.mentoring.index`):** Conduct mentoring sessions, review mentoring logs, and provide feedback.
  6. **Grades (`dpl.grades.index`):** Provide final evaluations and grades for students after the KKN period ends.

### Mahasiswa (Student)
- **Role Enum:** `App\Enums\UserRole::Mahasiswa` (value: `'mahasiswa'`).
- **Primary Responsibilities:**

  **1. Logbook (`mahasiswa.logbook.index`):**
    - Handled by `App\Models\DailyLog` and Livewire components `Logbook` / `LogbookForm`.
    - **Constraints:**
      - Only one log is permitted per date per student.
      - Log dates must fall strictly within the group's KKN period (`start_date` to `end_date`).
      - If a log's status is `LogStatus::Approved`, it is locked and cannot be edited by the student.
    - **Structure:** A log contains a date, optional image, important notes, and an array of `activities`.
    - **Activities:** Each activity requires a `start_time` and `end_time` (H:i format) and a description. Total working hours are calculated from the cumulative duration of these activities.
    - **Default Status:** `LogStatus::Pending`.

  **2. Buku Pembimbingan / Mentoring Logs (`mahasiswa.mentoring-logs.index`):**
    - Handled by `App\Models\MentoringLog` and Livewire components `MentoringLogs` / `MentoringLogForm`.
    - **Constraints:**
      - Only one log is permitted per date per student (a single log summarizes all mentoring topics for that day).
      - Log dates must fall strictly within the group's KKN period (`start_date` to `end_date`).
      - If a log's status is `LogStatus::Approved`, it is locked and cannot be edited by the student.
    - **Structure:** Contains date, topic, discussion summary, optional linked program (`program_id`), target group, student count, and output.
    - **Default Status:** `LogStatus::Pending`.
