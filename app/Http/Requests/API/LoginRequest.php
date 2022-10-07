<?php

namespace App\Http\Requests\API;

use App\Http\Requests\API\APIFormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends APIFormRequest
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
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];

        return $rules;
    }
}
