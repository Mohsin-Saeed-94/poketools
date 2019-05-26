<?php
/**
 * @file PokemonEncounterTableType.php
 */

namespace App\DataTable\Type;


use App\Entity\Encounter;
use App\Entity\LocationArea;
use App\Entity\Pokemon;
use App\Entity\Version;
use Doctrine\ORM\QueryBuilder;
use League\CommonMark\CommonMarkConverter;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PokemonEncounterTableType implements DataTableTypeInterface
{
    use EncounterTableTrait;

    /**
     * @var CommonMarkConverter
     */
    protected $markdown;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * PokemonEncounterTableType constructor.
     *
     * @param CommonMarkConverter $markdown
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(CommonMarkConverter $markdown, UrlGeneratorInterface $urlGenerator)
    {
        $this->markdown = $markdown;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        /** @var Version $version */
        $version = $options['version'];
        /** @var Pokemon $pokemon */
        $pokemon = $options['pokemon'];

        $dataTable->add(
            'location',
            TextColumn::class,
            [
                'label' => 'Location',
                'field' => 'encounter.locationArea',
                'orderable' => false,
                'visible' => false,
                'raw' => true,
                'data' => function (Encounter $context, LocationArea $locationArea) use ($version) {
                    $location = $locationArea->getLocation();
                    $locationUrl = $this->urlGenerator->generate(
                        'location_view',
                        [
                            'locationSlug' => $location->getSlug(),
                            'versionSlug' => $version->getSlug(),
                        ]
                    );

                    return sprintf(
                        '<a href="%s">%s</a>: %s',
                        $locationUrl,
                        $location->getName(),
                        $locationArea->getName()
                    );
                },
            ]
        );

        // Merge in normal encounter columns
        $this->addEncounterColumns($dataTable);
        $dataTable->getColumnByName('method')->setOption('visible', true);

        $dataTable->setName(self::class)->createAdapter(
            ORMAdapter::class,
            [
                'entity' => Encounter::class,
                'query' => function (QueryBuilder $qb) use ($version, $pokemon) {
                    $qb->from(Encounter::class, 'encounter')
                        ->addSelect('encounter')
                        ->addSelect('method')
                        ->addSelect('location')
                        ->addSelect('location_area')
                        ->join('encounter.method', 'method')
                        ->join('encounter.locationArea', 'location_area')
                        ->join('location_area.location', 'location')
                        ->andWhere('encounter.version = :version')
                        ->andWhere('encounter.pokemon = :pokemon')
                        ->addOrderBy('location.name')
                        ->addOrderBy('location_area.position')
                        ->addOrderBy('method.position')
                        ->addOrderBy('encounter.position')
                        ->setParameter('version', $version)
                        ->setParameter('pokemon', $pokemon);
                },
            ]
        );
    }
}
