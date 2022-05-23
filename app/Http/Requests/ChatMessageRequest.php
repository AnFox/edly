<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class ChatMessageRequest
 * @package App\Http\Requests
 */
class ChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !Auth::guest();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required_without:banner_id|string',
            'banner_id' => 'nullable|exists:banners,id',
            'recipient_user_id' => 'nullable|integer|exists:users,id',
        ];
    }
}
