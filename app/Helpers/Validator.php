<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Validator as UseValidator;
use Illuminate\Validation\Rule;

class Validator
{
    static function validatorName($value){
        return trim(strip_tags(
            UseValidator::make(
                [
                    'value' => $value
                ],
                [
                    'value' => ['required', 'string', 'max:255'],
                ]
            )->validated()['value']
        ));
    }
}
