<?php
/**
 * @file TypeMatchup.php
 */

namespace App\Mechanic;


use App\Entity\Type;
use App\Entity\TypeChart;
use App\Repository\TypeEfficacyRepository;

final class TypeMatchup
{
    /**
     * @var TypeEfficacyRepository
     */
    private $efficacyRepo;

    /**
     * TypeMatchup constructor.
     *
     * @param TypeEfficacyRepository $efficacyRepo
     */
    public function __construct(TypeEfficacyRepository $efficacyRepo)
    {
        $this->efficacyRepo = $efficacyRepo;
    }

    /**
     * @param Type $attackingType
     * @param Type|Type[] $defendingTypes
     * @param TypeChart $typeChart
     *
     * @return int
     */
    public function efficacy(Type $attackingType, $defendingTypes, TypeChart $typeChart): int
    {
        if (!is_iterable($defendingTypes)) {
            $defendingTypes = [$defendingTypes];
        }

        $efficacy = 1.0;
        foreach ($defendingTypes as $defendingType) {
            $efficacy *= $this->efficacyRepo->findForMatchup($attackingType, $defendingType, $typeChart) / 100.0;
        }

        return $efficacy * 100;
    }
}