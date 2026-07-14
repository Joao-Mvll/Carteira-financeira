<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // positivo, numérico e no máximo 2 casas decimais
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'O valor deve ser positivo.',
            'amount.decimal' => 'O valor deve ter no máximo 2 casas decimais.',
            'amount.max' => 'O valor excede o limite permitido.',
            'description.max' => 'A descrição deve ter no máximo 255 caracteres.',
        ];
    }
}
