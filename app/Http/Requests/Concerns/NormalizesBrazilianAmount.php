<?php

namespace App\Http\Requests\Concerns;

trait NormalizesBrazilianAmount
{
    /**
     * Converte valores no formato pt-BR ("20.000,50") para o formato
     * esperado pela validação ("20000.50").
     *
     * Só toca strings que contêm vírgula: payloads numéricos da API
     * (ex.: 10.999) não podem ser reinterpretados como milhar.
     */
    protected function prepareForValidation(): void
    {
        $amount = $this->input('amount');

        if (is_string($amount) && str_contains($amount, ',')) {
            $this->merge([
                'amount' => str_replace(['.', ','], ['', '.'], $amount),
            ]);
        }
    }
}
