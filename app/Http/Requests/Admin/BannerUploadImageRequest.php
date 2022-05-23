<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;

class BannerUploadImageRequest extends FormRequest
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
            'image' => 'required|file|dimensions:min_width='
                . Room::MEDIA_BANNER_MIN_WIDTH
                . ',min_height='
                . Room::MEDIA_BANNER_MIN_HEIGHT
                . '|mimes:jpg,jpeg,png|mimetypes:'
                . implode(',', Room::MEDIA_ACCEPTABLE_MIME),
        ];
    }
}
