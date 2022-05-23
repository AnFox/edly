<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AccountSetPaymentRequest
 * @package App\Http\Requests\Admin
 */
class AccountSetPaymentRequest extends FormRequest
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
            'cashbox_system' => 'required|in:CloudPayments,ATOL',
            'cashbox_inn' => 'required_if:cashbox_system,ATOL',
//            'cashbox_address' => 'required_if:cashbox_system,ATOL',
            'cashbox_login' => 'required_if:cashbox_system,ATOL',
            'cashbox_password' => 'required_if:cashbox_system,ATOL',
            'cashbox_group' => 'required_if:cashbox_system,ATOL',
            'payment_system' => 'required|in:CloudPayments',
            'payment_system_public_key' => 'required_if:payment_system,CloudPayments',
            'payment_system_private_key' => 'required_if:payment_system,CloudPayments',
        ];
    }
}
