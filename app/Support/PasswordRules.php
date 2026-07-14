<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    /**
     * Regra padrão de senha da aplicação: mínimo de 8 caracteres,
     * com letra maiúscula, minúscula e número.
     */
    public static function default(): Password
    {
        return Password::min(8)->letters()->mixedCase()->numbers();
    }
}
