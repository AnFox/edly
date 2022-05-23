<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomUpdateRequest extends FormRequest
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
            'type_id' => ['required', Rule::in([Room::TYPE_LIVE, Room::TYPE_AUTO])],
            'name' => 'required|string',
            'duration_minutes' => 'required|integer',
            'video_src' => 'nullable|string',
            'description' => 'nullable|string',
            'waiting_text' => 'nullable|string',
            'post_webinar_text' => 'nullable|string',
            'is_bot_assign_required' => 'required|bool',
            'bot_url_telegram' => 'exclude_unless:is_bot_assign_required,true|required_without_all:bot_url_whatsapp,bot_url_viber',
            'bot_url_whatsapp' => 'exclude_unless:is_bot_assign_required,true|required_without_all:bot_url_telegram,bot_url_viber',
            'bot_url_viber' => 'exclude_unless:is_bot_assign_required,true|required_without_all:bot_url_telegram,bot_url_whatsapp',
            'scheduled_at' => 'nullable|date',
            'schedule_interval' => 'nullable|in:1D,2D,3D,4D,5D,6D,1W,2W,3W,4W',
            'request_record' => 'nullable|bool',
        ];
    }
}
