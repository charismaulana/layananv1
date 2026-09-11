<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'nomor_pegawai'      => ['nullable', 'string', 'max:50'],
            'jabatan'            => ['nullable', 'string', 'max:100'],
            'company_name'       => ['nullable', 'string', 'max:150'],
            'company_id'         => ['nullable', 'exists:companies,id'],
            'department_id'      => ['nullable', 'exists:departments,id'],
            'worker_status_id'   => ['nullable', 'exists:worker_statuses,id'],
            'homebase_region_id'    => ['nullable', 'exists:regions,id'],
            'meal_location_id'      => ['nullable', 'exists:meal_locations,id'],
            'breakfast_location_id' => ['nullable', 'exists:meal_locations,id'],
            'lunch_location_id'     => ['nullable', 'exists:meal_locations,id'],
            'dinner_location_id'    => ['nullable', 'exists:meal_locations,id'],
            'supper_location_id'    => ['nullable', 'exists:meal_locations,id'],
            'is_shift'              => ['nullable'],
            'shift_type'            => ['nullable', 'string', 'in:non_shift,shift_pagi,shift_malam'],
            'room_id'               => ['nullable', 'exists:rooms,id'],
        ];
    }
}
