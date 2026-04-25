<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectBoqDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'redirect_to' => ['nullable', 'in:project-data.show,rekon.show'],
            'volume_pemenuhan' => ['nullable', 'integer', 'min:0'],
            'volume_aktual' => ['nullable', 'integer', 'min:0'],
            'price_aktual' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
