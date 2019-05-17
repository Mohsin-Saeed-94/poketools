<?php

namespace App\Command;

use App\Entity\Media\RegionMap;
use App\Repository\LocationMapRepository;
use App\Repository\Media\RegionMapRepository;
use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Export maps to svg, with overlays
 */
final class ExportLocationMapCommand extends Command
{
    protected static $defaultName = 'app:export:location-map';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var string
     */
    private $assetPath;

    /**
     * @var RegionMapRepository
     */
    private $regionMapRepo;

    /**
     * @var LocationMapRepository
     */
    private $locationMapRepo;

    /**
     * @var XmlEncoder
     */
    private $xmlEncoder;

    /**
     * ExportLocationMapCommand constructor.
     *
     * @param string $assetPath
     * @param RegionMapRepository $regionMapRepo
     * @param LocationMapRepository $locationMapRepo
     * @param XmlEncoder $xmlEncoder
     */
    public function __construct(
        string $assetPath,
        RegionMapRepository $regionMapRepo,
        LocationMapRepository $locationMapRepo,
        SerializerInterface $xmlEncoder
    ) {
        parent::__construct();

        $this->assetPath = $assetPath;
        $this->regionMapRepo = $regionMapRepo;
        $this->locationMapRepo = $locationMapRepo;
        $this->xmlEncoder = $xmlEncoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Export map(s) to svg, with overlays')
            ->addArgument(
                'maps',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'A list of maps to output in the format "region_slug/map_slug".  Defaults to all maps.'
            )->addOption(
                'inkscape',
                null,
                InputOption::VALUE_NONE,
                'Add inkscape properties to the generated SVG'
            )->addOption(
                'style',
                null,
                InputOption::VALUE_REQUIRED,
                'Set the style for generated items.  Defaults to a red outline lightly filled.',
                'fill:#212529;fill-opacity:0.2;stroke:red;stroke-width:1px;stroke-opacity:1;'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mapIdentifiers = $input->getArgument('maps');
        $maps = $this->getMaps($mapIdentifiers);

        $progress = $this->io->createProgressBar(count($maps));
        $progress->setFormat('debug');
        $progress->display();
        foreach ($maps as $mapUrl => $mapSet) {
            $mapImagePathInfo = pathinfo($this->getImagePath($mapUrl));
            $svg = $this->buildMapSvg(
                $mapImagePathInfo,
                $mapSet,
                $input->getOption('style'),
                $input->getOption('inkscape')
            );
            $svgPath = $mapImagePathInfo['dirname'].'/'.$mapImagePathInfo['filename'].'.svg';
            file_put_contents($svgPath, $svg);

            $progress->advance();
        }
        $progress->finish();
        $this->io->newLine();
    }

    /**
     * @param $mapIdentifiers
     *
     * @return array[]
     */
    private function getMaps($mapIdentifiers): array
    {
        $maps = [];
        if (!empty($mapIdentifiers)) {
            foreach ($mapIdentifiers as $mapIdentifier) {
                [$regionSlug, $mapSlug] = explode('/', $mapIdentifier);
                $regionMaps = $this->regionMapRepo->findBySlugCombo($regionSlug, $mapSlug);
                if (empty($regionMaps)) {
                    throw new DomainException(sprintf('The regionMaps "%s" does not exist.', $mapIdentifier));
                }
            }
        } else {
            $regionMaps = $this->regionMapRepo->findAll();
        }
        foreach ($regionMaps as $regionMap) {
            $maps[$regionMap->getUrl()][] = $regionMap;
        }

        return $maps;
    }

    /**
     * @param string $mapUrl
     *
     * @return string
     */
    private function getImagePath(string $mapUrl): string
    {
        return implode('/', [$this->assetPath, 'static', 'map', $mapUrl]);
    }

    /**
     * @param array $mapImagePathInfo
     * @param RegionMap[] $mapSet
     * @param string $style
     * @param bool $inkscape
     *   Add inkscape formatting data
     *
     * @return string
     */
    private function buildMapSvg(array $mapImagePathInfo, array $mapSet, string $style, bool $inkscape): string
    {
        $mapImagePath = $mapImagePathInfo['dirname'].'/'.$mapImagePathInfo['basename'];
        [$imageWidth, $imageHeight] = getimagesize($mapImagePath);
        $build = [
            '@width' => $imageWidth,
            '@height' => $imageHeight,
            '@viewBox' => sprintf('0 0 %d %d', $imageWidth, $imageHeight),

            'image' => [
                '@xlink:href' => basename($mapImagePathInfo['basename']),
                '@width' => $imageWidth,
                '@height' => $imageHeight,
                '@style' => 'image-rendering:optimizeSpeed',
            ],
            'g' => [],
        ];
        if ($inkscape) {
            $build['image']['@sodipodi:insensitive'] = 'true';
            $build += [
                'sodipodi:namedview' => [
                    '@gridtolderance' => 10,
                    '@objecttolerance' => 10,
                    '@showgrid' => 'true',
                    '@inkscape:snap-object-midpoints' => 'true',
                    'inkscape:grid' => [
                        '@type' => 'xygrid',
                    ],
                ],
            ];
        }

        // Get a list of all unique locations on this map set.
        $uniqueMaps = [];
        foreach ($mapSet as $regionMap) {
            $locationMaps = $this->locationMapRepo->findByMap($regionMap);
            foreach ($locationMaps as $locationMap) {
                $uniqueMaps[$locationMap->getLocation()->getSlug()] = $locationMap;
            }
        }

        // Add each location overlay
        foreach ($uniqueMaps as $locationMap) {
            $location = $locationMap->getLocation();
            $overlay = '<g>'.$locationMap->getOverlay().'</g>';
            $overlay = $this->xmlEncoder->decode($overlay, 'xml');
            $attrs = [
                '@id' => $location->getSlug(),
                '@style' => $style,
            ];
            if ($inkscape) {
                $attrs += [
                    '@inkscape:groupmode' => 'layer',
                    '@inkscape:label' => $location->getSlug(),
                ];
            }
            $build['g'][] = $overlay + $attrs;
        }

        return $this->xmlEncoder->encode(
            $build,
            'xml',
            [
                XmlEncoder::ROOT_NODE_NAME => 'svg',
                XmlEncoder::FORMAT_OUTPUT => true,
            ]
        );
    }
}
