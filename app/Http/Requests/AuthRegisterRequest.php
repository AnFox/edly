<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AuthRegisterRequest
 * @package App\Http\Requests
 */
class AuthRegisterRequest extends FormRequest
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
            'email' => 'required_without:phone|nullable',
            'phone' => 'required_without:email|nullable',
            'successTermsOfUse' => 'required|accepted',
            'successOnlyAcc' => 'required|accepted',
            'intendedUrl' => 'nullable|string',
        ];
    }
}
