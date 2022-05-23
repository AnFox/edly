<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AuthLoginRequest
 * @package App\Http\Requests
 */
class AuthLoginRequest extends FormRequest
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
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email',
            'password' => 'required|min:6',
            'remember_me' => 'boolean',
        ];
    }
}
