<?php
/**
 * @file AbilityTableType.php
 */

namespace App\DataTable\Type;


use App\DataTable\Column\LinkColumn;
use App\DataTable\Column\MarkdownColumn;
use App\Entity\AbilityInVersionGroup;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

/**
 * Ability table
 */
class AbilityTableType implements DataTableTypeInterface
{
    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];

        $dataTable->add(
            'name',
            LinkColumn::class,
            [
                'label' => 'Name',
                'route' => 'ability_view',
                'routeParams' => [
                    'versionSlug' => $version->getSlug(),
                    'abilitySlug' => function (AbilityInVersionGroup $context, $value) {
                        return $context->getSlug();
                    },
                ],
                'className' => 'pkt-ability-index-table-name',
            ]
        )
            ->add(
                'shortDescription',
                MarkdownColumn::class,
                [
                    'label' => 'Description',
                    'className' => 'pkt-ability-index-table-description',
                    'orderable' => false,
                ]
            )
            ->addOrderBy('name')
            ->createAdapter(
                ORMAdapter::class,
                [
                    'entity' => AbilityInVersionGroup::class,
                    'query' => function (QueryBuilder $qb) use ($version) {
                        $qb->select('PARTIAL ability_in_version_group.{id, name, slug, shortDescription}')
                            ->from(AbilityInVersionGroup::class, 'ability_in_version_group')
                            ->join('ability_in_version_group.versionGroup', 'version_group')
                            ->where(':version MEMBER OF version_group.versions')
                            ->setParameter('version', $version);
                    },
                ]
            );
    }
}
