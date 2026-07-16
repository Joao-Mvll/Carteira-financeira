<?php

namespace App\Http\Requests\Concerns;

trait NormalizesBrazilianAmount
{

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
