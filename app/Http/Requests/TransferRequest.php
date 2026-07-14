<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'email' => [
                'required',
                'email',
                'exists:users,email',
                // não pode transferir para a própria conta
                Rule::notIn([$this->user()?->email]),
            ],
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
            'email.exists' => 'Destinatário não encontrado.',
            'email.not_in' => 'Você não pode transferir para si mesmo.',
            'description.max' => 'A descrição deve ter no máximo 255 caracteres.',
        ];
    }
}
