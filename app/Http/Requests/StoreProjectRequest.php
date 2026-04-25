<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_name' => ['required', 'string', 'max:255'],
            'pid_input_mode' => ['required', 'in:manual,auto'],
            'pid' => ['nullable', 'string', 'max:255'],
            'wbs_sap' => ['nullable', 'string', 'max:255'],
            'customer' => ['nullable', 'string', 'max:255'],
            'fase' => ['required', 'in:planning,procurement,konstruksi,rekon,warehouse,finance,closed'],
            'portofolio' => ['nullable', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'jenis_eksekusi' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'pm_project' => ['nullable', 'string', 'max:255'],
            'waspang' => ['nullable', 'string', 'max:255'],
            'evidence_dasar_files' => ['nullable', 'array', 'max:3'],
            'evidence_dasar_files.*' => ['file', 'mimes:pdf,xls,xlsx,kml,kmz'],
            'boq' => ['nullable', 'file', 'mimes:xls,xlsx,csv'],
            'boq_items_json' => ['nullable', 'string'],
            'start_project' => ['nullable', 'date'],
            'end_project' => ['nullable', 'date', 'after_or_equal:start_project'],
        ];
    }

    public function messages(): array
    {
        return [
            'evidence_dasar_files.max' => 'File dasar pekerjaan maksimal 3 file.',
            'evidence_dasar_files.*.mimes' => 'Format dasar pekerjaan hanya boleh PDF, Excel, ASP (KML), atau KMZ.',
            'boq.mimes' => 'Format BoQ hanya boleh Excel atau CSV.',
        ];
    }
}
