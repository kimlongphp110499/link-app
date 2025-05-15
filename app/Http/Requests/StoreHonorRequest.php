<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHonorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url_name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'date' => 'required|date_format:Y/m/d H:i',
            'duration' => 'integer',
        ];
    }
}
