<?php
/**
 * @file LevelUp.php
 */

namespace App\Mechanic;


use App\Entity\GrowthRate;
use App\ExpressionLanguage\ExpressionLanguage;

/**
 * Manage experience tables
 */
final class LevelUp
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * LevelUp constructor.
     *
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * @param int $level
     * @param GrowthRate $growthRate
     *
     * @return int
     */
    public function experienceRequired(int $level, GrowthRate $growthRate): int
    {
        return $this->expressionLanguage->evaluate($growthRate->getExpression(), ['level' => $level]);
    }
}