<?php
/**
 * @file AbilityTableType.php
 */

namespace App\DataTable\Type;


use App\DataTable\Column\LinkColumn;
use App\Entity\ContestType;
use App\Entity\Nature;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

/**
 * Nature table
 */
class NatureTableType implements DataTableTypeInterface
{
    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];
        $showContestInfo = $version->getVersionGroup()->hasFeatureString('contests')
            || $version->getVersionGroup()->hasFeatureString('super-contests');

        $dataTable->add(
            'name',
            LinkColumn::class,
            [
                'label' => 'Name',
                'route' => 'nature_view',
                'routeParams' => [
                    'versionSlug' => $version->getSlug(),
                    'natureSlug' => function (Nature $context, $value) {
                        return $context->getSlug();
                    },
                ],
                'className' => 'pkt-nature-index-table-name',
            ]
        )->add(
            'stat_increased',
            TextColumn::class,
            [
                'label' => '+10%',
                'field' => 'stat_increased.name',
                'className' => 'pkt-nature-index-table-statincreased',
                'data' => function (Nature $nature, string $value) {
                    if ($this->affectsSameStat($nature)) {
                        return null;
                    }

                    return $value;
                },
            ]
        )->add(
            'stat_decreased',
            TextColumn::class,
            [
                // This is a minus sign, not a dash!
                'label' => '−10%',
                'field' => 'stat_decreased.name',
                'className' => 'pkt-nature-index-table-statdecreased',
                'data' => function (Nature $nature, string $value) {
                    if ($this->affectsSameStat($nature)) {
                        return null;
                    }

                    return $value;
                },
            ]
        )->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Nature::class,
                'query' => function (QueryBuilder $qb) {
                    $qb->select(
                        'nature'
                    )->addSelect('stat_increased')
                        ->addSelect('stat_decreased')
                        ->addSelect('flavor_likes')
                        ->addSelect('likes_contest_type')
                        ->addSelect('flavor_hates')
                        ->addSelect('hates_contest_type')
                        ->from(Nature::class, 'nature')
                        ->join('nature.statIncreased', 'stat_increased')
                        ->join('nature.statDecreased', 'stat_decreased')
                        ->join('nature.flavorLikes', 'flavor_likes')
                        ->join('nature.flavorHates', 'flavor_hates')
                        ->join('flavor_likes.contestType', 'likes_contest_type')
                        ->join('flavor_hates.contestType', 'hates_contest_type');
                },
            ]
        )->addOrderBy('name');
        $dataTable->add(
            'flavor_likes',
            TextColumn::class,
            [
                'label' => 'Likes',
                'field' => 'flavor_likes.name',
                'className' => 'pkt-nature-index-table-flavorlikes',
                'data' => function (Nature $nature, string $value) {
                    if ($this->affectsSameFlavors($nature)) {
                        return null;
                    }

                    return $value;
                },
            ]
        );
        if ($showContestInfo) {
            $dataTable->add(
                'contest_increased',
                LinkColumn::class,
                [
                    'label' => 'Contest +',
                    // @todo Type link
                    'uri' => '#',
                    'propertyPath' => 'flavor_likes.contest_type',
                    'orderField' => 'likes_contest_type.name',
                    'className' => 'pkt-nature-index-table-contestincreased',
                    'data' => function (Nature $nature, ContestType $value) {
                        if ($this->affectsSameFlavors($nature)) {
                            return null;
                        }

                        return $value;
                    },
                    'linkClassName' => function (Nature $context, string $value) {
                        return $this->linkClassNameContestType($context->getFlavorLikes()->getContestType());
                    },
                ]
            );
        }
        $dataTable->add(
            'flavor_hates',
            TextColumn::class,
            [
                'label' => 'Hates',
                'field' => 'flavor_hates.name',
                'className' => 'pkt-nature-index-table-flavorhates',
                'data' => function (Nature $nature, string $value) {
                    if ($this->affectsSameFlavors($nature)) {
                        return null;
                    }

                    return $value;
                },
            ]
        );
        if ($showContestInfo) {
            $dataTable->add(
                'contest_decreased',
                LinkColumn::class,
                [
                    // This is a minus sign, not a dash!
                    'label' => 'Contest −',
                    // @todo Type link
                    'uri' => '#',
                    'propertyPath' => 'flavor_hates.contest_type',
                    'orderField' => 'hates_contest_type.name',
                    'className' => 'pkt-nature-index-table-contestdecreased',
                    'data' => function (Nature $nature, ContestType $value) {
                        if ($this->affectsSameFlavors($nature)) {
                            return null;
                        }

                        return $value;
                    },
                    'linkClassName' => function (Nature $context, string $value) {
                        return $this->linkClassNameContestType($context->getFlavorHates()->getContestType());
                    },
                ]
            );
        }
    }

    /**
     * @param Nature $nature
     *
     * @return bool
     */
    private function affectsSameStat(Nature $nature): bool
    {
        return $nature->getStatIncreased() === $nature->getStatDecreased();
    }

    /**
     * @param Nature $nature
     *
     * @return bool
     */
    private function affectsSameFlavors(Nature $nature): bool
    {
        return $nature->getFlavorLikes() === $nature->getFlavorHates();
    }

    private function linkClassNameContestType(ContestType $contestType): ?string
    {
        return sprintf('pkt-type-emblem-%s', $contestType->getSlug());
    }
}
