<?php

namespace T2G\Common\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UseCodeRequest extends FormRequest
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
            'code' => 'required|min:3'
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.required' => 'Hãy nhập Gift Code',
            'code.min'      => 'Gift Code không hợp lệ',
        ];
    }
}
