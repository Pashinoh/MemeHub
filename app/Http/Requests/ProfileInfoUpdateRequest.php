<?php

namespace App\Http\Requests;

use App\Support\ReservedAccountNames;
use Illuminate\Foundation\Http\FormRequest;

class ProfileInfoUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (ReservedAccountNames::isBlocked((string) $value)) {
                        $fail('Nama akun tersebut tidak diperbolehkan.');
                    }
                },
            ],
            'bio' => ['nullable', 'string', 'max:1000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cropped_profile_photo' => ['nullable', 'string'],
        ];
    }
}
