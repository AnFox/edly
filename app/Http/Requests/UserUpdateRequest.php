<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'first_name' => 'sometimes|required|string',
            'last_name' => 'nullable|string',
            'email' => 'sometimes|required|email',
            'phone' => 'sometimes|required',
            'password_current' => 'sometimes|required|min:6',
            'password' => 'sometimes|required|confirmed|min:6',
        ];
    }
}
