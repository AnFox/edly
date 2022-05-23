<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BannerRequest
 * @package App\Http\Requests
 */
class BannerRequest extends FormRequest
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
            'room_id' => 'required|exists:rooms,id',
            'is_product' => 'required|boolean',
            'product_id' => 'required_if:is_product,true|numeric|exists:products,id',
            'url' => 'required_if:is_product,false|string|nullable',
            'media_id' => 'required_without:image|integer|exists:media,id',
            'image' => 'required_without:media_id|string',
            'title' => 'required|string',
        ];
    }
}
