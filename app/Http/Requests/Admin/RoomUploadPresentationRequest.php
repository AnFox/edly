<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class RoomUploadPresentationRequest
 * @package App\Http\Requests
 */
class RoomUploadPresentationRequest extends FormRequest
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
            'pdf' => 'required|file|max:' . 1024 * config('media-library.max_file_size') . '|mimes:pdf|mimetypes:application/pdf',
            'quality' => [
                'required',
                Rule::in([
                    Room::MEDIA_PDF_QIALITY_STANDARD,
                    Room::MEDIA_PDF_QIALITY_BETTER,
                    Room::MEDIA_PDF_QIALITY_MAXIMUM,
                ]),
            ],
        ];
    }
}
