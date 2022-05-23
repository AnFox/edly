<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class EmployeeCreateRequest
 * @package App\Http\Requests\Admin
 */
class EmployeeCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'email' => 'required|nullable',
            'password' => 'required',
            'role' => 'required|exists:roles,name',
        ];
    }
}
