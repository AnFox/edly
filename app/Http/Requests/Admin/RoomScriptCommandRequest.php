<?php

namespace App\Http\Requests\Admin;

use App\Models\Script;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomScriptCommandRequest extends FormRequest
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
            'timeshift' => 'required|integer',
            'action' => ['required', Rule::in([
                Script::ACTION_START_RECORD,
                Script::ACTION_STOP_RECORD,
                Script::ACTION_START_STREAM,
                Script::ACTION_STOP_STREAM,
                Script::ACTION_WEBINAR_LAYOUT,
                Script::ACTION_SET_PRESENTATION_PAGE,
                Script::ACTION_POST_MESSAGE,
                Script::ACTION_POST_BANNER,
                Script::ACTION_CHAT_BLOCK,
                Script::ACTION_CHAT_UNBLOCK,
            ])],
            'payload' => 'required|json'
        ];
    }
}
