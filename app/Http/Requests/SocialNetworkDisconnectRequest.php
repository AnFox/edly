<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialNetworkDisconnectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'social_network_name' => 'required|string',
        ];
    }
}
