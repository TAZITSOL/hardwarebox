<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;

class UpdateCurrencyRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('currency') ? $this->route('currency')->id : $this->id;
        return [
            'code'  => ['required', 'string',  'unique:currencies,code,'.$id.',id,deleted_at,NULL'],
            'symbol' => ['required','string'],
            'no_of_decimal' => ['nullable','min:0'],
            'exchange_rate' => ['nullable','min:0'],
            'symbol_position' => ['required','in:before_price,after_price'],
            'thousands_separator' => ['nullable','in:comma,period,space'],
            'decimal_separator' => ['nullable','in:comma,period,space'],
            'status' => ['required','min:0','max:1'],
        ];
    }

    public function messages()
    {
        return [
            'symbol_position.in' => __('validation.position_before_or_after_price'),
            'thousands_separator.in' => __('validation.thousands_separator'),
            'decimal_separator.in' => __('validation.decimal_separator'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
