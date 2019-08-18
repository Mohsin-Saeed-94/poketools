<?php
/**
 * @file LocationEncounterPokemonTableType.php
 */

namespace App\DataTable\Type;


use App\Entity\Encounter;
use App\Entity\LocationArea;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTable;

/**
 * Encounter table for Location view
 */
class LocationEncounterPokemonTableType extends EncounterPokemonTableType
{
    /**
     * @inheritDoc
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var LocationArea $locationArea */
        $locationArea = $options['location_area'];
        /** @var Version $version */
        $version = $options['version'];

        parent::configure($dataTable, $options);

        $dataTable->setName(self::class.'__'.$locationArea->getTreePath())->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Encounter::class,
                'query' => function (QueryBuilder $qb) use ($version, $locationArea) {
                    $this->query($qb, $version);
                    $qb->andWhere('encounter.locationArea = :locationArea')
                        ->setParameter('locationArea', $locationArea);
                },
            ]
        );
    }

}
