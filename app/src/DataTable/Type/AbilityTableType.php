<?php
/**
 * @file AbilityTableType.php
 */

namespace App\DataTable\Type;


use App\DataTable\Column\SummaryColumn;
use App\Entity\AbilityInVersionGroup;
use App\Entity\Version;
use App\Repository\AbilityInVersionGroupRepository;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

/**
 * Ability table
 */
class AbilityTableType implements DataTableTypeInterface
{
    /**
     * @var AbilityInVersionGroupRepository
     */
    private $abilityInVersionGroupRepository;

    public function __construct(AbilityInVersionGroupRepository $abilityInVersionGroupRepository)
    {
        $this->abilityInVersionGroupRepository = $abilityInVersionGroupRepository;
    }

    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];

        $dataTable->add('name', TextColumn::class, ['label' => 'Name'])
            ->add('shortDescription', TextColumn::class, ['label' => 'Description', 'orderable' => false])
            ->addOrderBy('name')
            ->createAdapter(
                ORMAdapter::class,
                [
                    'entity' => AbilityInVersionGroup::class,
                    'query' => function (QueryBuilder $qb) use ($version) {
                        $qb->select('PARTIAL ability_in_version_group.{id, name, shortDescription}')
                            ->from(AbilityInVersionGroup::class, 'ability_in_version_group')
                            ->join('ability_in_version_group.versionGroup', 'version_group')
                            ->where(':version MEMBER OF version_group.versions')
                            ->setParameter('version', $version);
                    },
                ]
            );
    }
}
