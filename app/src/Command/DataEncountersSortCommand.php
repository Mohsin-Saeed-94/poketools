<?php

namespace App\Command;

use App\Command\DataClass\Encounter;
use App\Repository\EncounterMethodRepository;
use App\Repository\PokemonSpeciesInVersionGroupRepository;
use App\Repository\VersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

final class DataEncountersSortCommand extends Command
{
    use EncountersTrait;

    protected static $defaultName = 'app:data:encounters:sort';

    /**
     * @var string
     */
    private $dataPath;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var VersionRepository
     */
    private $versionsRepo;

    /**
     * @var EncounterMethodRepository
     */
    private $encounterMethodRepo;
    /**
     * @var \App\Repository\PokemonSpeciesInVersionGroupRepository
     */
    private $speciesRepo;

    /**
     * DataEncountersSortCommand constructor.
     *
     * @param string $dataPath
     * @param SerializerInterface $serializer
     * @param VersionRepository $versionsRepo
     * @param EncounterMethodRepository $encounterMethodRepo
     * @param \App\Repository\PokemonSpeciesInVersionGroupRepository $pokemonRepo
     */
    public function __construct(
        string $dataPath,
        SerializerInterface $serializer,
        VersionRepository $versionsRepo,
        EncounterMethodRepository $encounterMethodRepo,
        PokemonSpeciesInVersionGroupRepository $pokemonRepo
    ) {
        parent::__construct();

        $this->dataPath = $dataPath;
        $this->serializer = $serializer;
        $this->versionsRepo = $versionsRepo;
        $this->encounterMethodRepo = $encounterMethodRepo;
        $this->speciesRepo = $pokemonRepo;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sort encounters in a useful way.')
            ->addOption(
                'id-spacing',
                null,
                InputOption::VALUE_REQUIRED,
                'The gap in id numbers to allow for adding encounters.',
                5
            );
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text(['Loading data', 'This will take a while...', '']);
        $path = $this->dataPath.'/encounter.csv';
        $this->data = new ArrayCollection($this->loadData($path));

        $this->sortData();
        $this->resetIds($input->getOption('id-spacing'));

        $this->io->text(['Writing new data to '.$path, 'This will take a while...']);
        $success = $this->writeData($path);

        if ($success) {
            $this->io->success('Finished sorting encounters.');

            return 0;
        }

        $this->io->error('Error occurred writing output file.');

        return 1;
    }

    /**
     * Sort the data
     *
     * The sort order is
     * - version (using loaded entities)
     * - location
     * - area
     * - method (using loaded entities)
     * - chance (ascending)
     * - pokemon national dex number
     */
    private function sortData()
    {
        $this->io->text('Loading sorting orders');

        // Load sorting tables
        // Versions
        $this->io->text('Versions');
        $versions = $this->versionsRepo->findAll();
        $progress = $this->io->createProgressBar(count($versions));
        $progress->setFormat('debug_nomax');
        $progress->display();
        $versionsOrder = [];
        foreach ($versions as $version) {
            $versionsOrder[$version->getSlug()] = $version->getPosition();
            $progress->advance();
        }
        unset($versions);
        $progress->finish();
        $this->io->newLine();

        // Encounter methods
        $this->io->text('Encounter methods');
        $encounterMethods = $this->encounterMethodRepo->findAll();
        $progress = $this->io->createProgressBar(count($encounterMethods));
        $progress->setFormat('debug_nomax');
        $progress->display();
        $methodOrder = [];
        foreach ($encounterMethods as $method) {
            $methodOrder[$method->getSlug()] = $method->getPosition();
            $progress->advance();
        }
        unset($encounterMethods);
        $progress->finish();
        $this->io->newLine();

        // Species/Pokemon
        $this->io->text('Species/Pokemon');
        $specieses = $this->speciesRepo->findAll();
        $progress = $this->io->createProgressBar(count($specieses));
        $progress->setFormat('debug_nomax');
        $progress->display();
        $speciesOrder = [];
        $pokemonOrder = [];
        foreach ($specieses as $species) {
            foreach ($species->getVersionGroup()->getVersions() as $version) {
                $speciesOrder[$version->getSlug()][$species->getSlug()] = $species->getPosition();
                foreach ($species->getPokemon() as $pokemon) {
                    $pokemonOrder[$version->getSlug()][$species->getSlug()][$pokemon->getSlug(
                    )] = $pokemon->getPosition();
                }
            }
            $progress->advance();
        }
        unset($specieses);
        $progress->finish();
        $this->io->newLine(2);

        gc_collect_cycles();
        $this->io->text('Sorting data...');
        $progress = $this->io->createProgressBar();
        $progress->setFormat('debug_nomax');
        $progress->display();
        $it = $this->data->getIterator();
        $it->uasort(
            function (Encounter $a, Encounter $b) use (
                $progress,
                $versionsOrder,
                $methodOrder,
                $speciesOrder,
                $pokemonOrder
            ) {
                $progress->advance();
                if ($a->getVersion() !== $b->getVersion()) {
                    return $versionsOrder[$a->getVersion()] - $versionsOrder[$b->getVersion()];
                }
                if ($a->getLocation() !== $b->getLocation()) {
                    return strnatcasecmp($a->getLocation(), $b->getLocation());
                }
                if ($a->getArea() !== $b->getArea()) {
                    return strnatcasecmp($a->getArea(), $b->getArea());
                }
                if ($a->getMethod() !== $b->getMethod()) {
                    return $methodOrder[$a->getMethod()] - $methodOrder[$b->getMethod()];
                }
                if ($a->getChance() !== $b->getChance()) {
                    return $b->getChance() - $a->getChance();
                }
                if ($a->getSpecies() !== $b->getSpecies()) {
                    return $speciesOrder[$a->getVersion()][$a->getSpecies()]
                        - $speciesOrder[$b->getVersion()][$b->getSpecies()];
                }
                if ($a->getPokemon() !== $b->getPokemon()) {
                    return $pokemonOrder[$a->getVersion()][$a->getSpecies()][$a->getPokemon()]
                        - $pokemonOrder[$b->getVersion()][$b->getSpecies()][$b->getPokemon()];
                }

                return 0;
            }
        );

        $progress->finish();
        $this->io->newLine(2);
        $this->data = new ArrayCollection(iterator_to_array($it));
    }

    private function resetIds(int $spacing)
    {
        $this->io->text('Resetting encounter ids...');
        $progress = $this->io->createProgressBar($this->data->count());
        $progress->setFormat('debug');
        $progress->display();
        $id = 1;
        foreach ($this->data as &$encounter) {
            $encounter->setId($id);

            $id += $spacing;
            $progress->advance();
        }
        unset($encounter, $id);
        $progress->finish();
        $this->io->newLine(2);
    }
}
