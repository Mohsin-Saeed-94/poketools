<?php


namespace App\Tests\dataschema\Filter;


use App\ExpressionLanguage\ExpressionLanguage;
use Opis\JsonSchema\IFilter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as ExpressionLanguageBase;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * Validate Symfony Expressions
 *
 * Args:
 * - vars
 */
class ExpressionFilter implements IFilter
{

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        $names = $args['vars'] ?? [];
        try {
            $this->exprLang()->parse($data, $names);
        } catch (SyntaxError) {
            return false;
        }

        return true;
    }

    /**
     * @return ExpressionLanguageBase
     */
    private function exprLang(): ExpressionLanguageBase
    {
        $exprLang = null;
        if (!$exprLang) {
            $exprLang = new ExpressionLanguage();
        }

        return $exprLang;
    }

}
