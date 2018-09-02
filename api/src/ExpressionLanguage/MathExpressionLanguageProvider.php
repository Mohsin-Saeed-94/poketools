<?php


namespace App\ExpressionLanguage;


use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Expose PHP Math functions to the expression language.
 */
class MathExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $phpFunctions = [
            'abs',
            'acos',
            'acosh',
            'asin',
            'asinh',
            'atan2',
            'atan',
            'atanh',
            'base_convert',
            'bindec',
            'ceil',
            'cos',
            'cosh',
            'decbin',
            'dechex',
            'decoct',
            'deg2rad',
            'exp',
            'expm1',
            'floor',
            'fmod',
            'getrandmax',
            'hexdec',
            'hypot',
            'intdiv',
            'is_finite',
            'is_infinite',
            'is_nan',
            'lcg_value',
            'log10',
            'log1p',
            'log',
            'max',
            'min',
            'mt_getrandmax',
            'mt_rand',
            'mt_srand',
            'octdec',
            'pi',
            'pow',
            'rad2deg',
            'rand',
            'round',
            'sin',
            'sinh',
            'sqrt',
            'srand',
            'tan',
            'tanh',
        ];

        foreach ($phpFunctions as $phpFunction) {
            yield ExpressionFunction::fromPhp($phpFunction);
        }
    }
}
