<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

class ImportKhsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'khs_file' => ['required', 'file', 'mimes:xlsx,xls,xlsm,csv', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'khs_file.required' => 'File KHS wajib dipilih.',
            'khs_file.uploaded' => 'File gagal ter-upload ke server. Coba tutup file di Excel lalu upload ulang, atau cek konfigurasi PHP temporary upload.',
            'khs_file.mimes' => 'Format file KHS harus .xlsx / .xls / .xlsm.',
            'khs_file.max' => 'Ukuran file KHS maksimal 20MB.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $file = $this->file('khs_file');
        $rawFile = $_FILES['khs_file'] ?? null;

        Log::error('KHS upload validation failed', [
            'errors' => $validator->errors()->toArray(),
            'has_file' => $this->hasFile('khs_file'),
            'file_original_name' => $file?->getClientOriginalName(),
            'file_size' => $file?->getSize(),
            'file_mime' => $file?->getClientMimeType(),
            'file_real_path' => $file?->getRealPath(),
            'php_file_error_code' => is_array($rawFile) ? ($rawFile['error'] ?? null) : null,
            'php_file_error_desc' => $this->uploadErrorMessage((int) (is_array($rawFile) ? ($rawFile['error'] ?? -1) : -1)),
            'php_upload_max_filesize' => ini_get('upload_max_filesize'),
            'php_post_max_size' => ini_get('post_max_size'),
            'php_upload_tmp_dir' => ini_get('upload_tmp_dir'),
            'php_sys_temp_dir' => ini_get('sys_temp_dir'),
        ]);

        parent::failedValidation($validator);
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_OK => 'UPLOAD_ERR_OK',
            UPLOAD_ERR_INI_SIZE => 'UPLOAD_ERR_INI_SIZE',
            UPLOAD_ERR_FORM_SIZE => 'UPLOAD_ERR_FORM_SIZE',
            UPLOAD_ERR_PARTIAL => 'UPLOAD_ERR_PARTIAL',
            UPLOAD_ERR_NO_FILE => 'UPLOAD_ERR_NO_FILE',
            UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
            UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
            UPLOAD_ERR_EXTENSION => 'UPLOAD_ERR_EXTENSION',
            default => 'UNKNOWN_UPLOAD_ERROR',
        };
    }
}
