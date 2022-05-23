<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;

class RoomSetCoverRequest extends FormRequest
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
            'cover' => 'required|file|dimensions:min_width='
                . Room::MEDIA_MIN_WIDTH
                . ',min_height='
                . Room::MEDIA_MIN_HEIGHT
                . '|mimes:jpg,jpeg,png|mimetypes:'
                . implode(',', Room::MEDIA_ACCEPTABLE_MIME),
        ];
    }
}
