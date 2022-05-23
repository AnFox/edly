<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AuthNetworkRequest
 * @package App\Http\Requests
 */
class AuthNetworkRequest extends FormRequest
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

            'token'    => 'required|string',
            'remember_me' => 'boolean',
            'social_network_name' => 'required|string',
        ];
    }
}
