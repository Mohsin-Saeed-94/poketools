<?php

namespace App\Command;

use App\Command\DataClass\Encounter;
use App\Command\DataClass\PokemonMove;
use App\Repository\MoveInVersionGroupRepository;
use App\Repository\MoveLearnMethodRepository;
use App\Repository\PokemonSpeciesInVersionGroupRepository;
use App\Repository\VersionGroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class DataPokemonMoveSortCommand extends Command
{
    protected static $defaultName = 'app:data:pokemon-move:sort';

    /**
     * @var string
     */
    private $dataPath;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var PokemonSpeciesInVersionGroupRepository
     */
    private $speciesRepo;
    /**
     * @var MoveInVersionGroupRepository
     */
    private $moveRepo;
    /**
     * @var MoveLearnMethodRepository
     */
    private $learnMethodRepo;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var VersionGroupRepository
     */
    private $versionGroupRepo;

    /**
     * DataPokemonMoveSortCommand constructor.
     *
     * @param string $dataPath
     * @param SerializerInterface $serializer
     * @param PokemonSpeciesInVersionGroupRepository $speciesRepo
     * @param MoveInVersionGroupRepository $moveRepo
     * @param MoveLearnMethodRepository $learnMethodRepo
     * @param VersionGroupRepository $versionGroupRepo
     */
    public function __construct(
        string $dataPath,
        SerializerInterface $serializer,
        PokemonSpeciesInVersionGroupRepository $speciesRepo,
        MoveInVersionGroupRepository $moveRepo,
        MoveLearnMethodRepository $learnMethodRepo,
        VersionGroupRepository $versionGroupRepo
    ) {
        parent::__construct();

        $this->dataPath = $dataPath;
        $this->speciesRepo = $speciesRepo;
        $this->moveRepo = $moveRepo;
        $this->learnMethodRepo = $learnMethodRepo;
        $this->serializer = $serializer;
        $this->versionGroupRepo = $versionGroupRepo;
    }

    protected function configure()
    {
        $this->setDescription('Sort the pokemon_move table');
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
        $path = $this->dataPath.'/pokemon_move.csv';
        $data = $this->loadData($path);
        $oldCount = count($data);
        $data = $this->cleanData($data);
        $newCount = count($data);
        $data = $this->sortData($data);

        $this->io->text(['Writing new data to '.$path, 'This will take a while...']);
        $success = $this->writeData($data, $path);

        if ($success) {
            $this->io->success(
                [
                    'Finished sorting pokemon move table.',
                    sprintf('Removed %u entries', $oldCount - $newCount),
                ]
            );

            return 0;
        }

        $this->io->error('Error occurred writing output file.');

        return 1;
    }

    /**
     * Load the data from CSV into memory.
     *
     * @param string $path
     *
     * @return PokemonMove[]
     */
    private function loadData(string $path): array
    {
        $dataContents = file_get_contents($path);
        /** @var Encounter[] $data */
        $data = $this->serializer->deserialize(
            $dataContents,
            PokemonMove::class.'[]',
            'csv',
            [
                ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ]
        );

        return $data;
    }

    /**
     * @param PokemonMove[] $data
     *
     * @return PokemonMove[]
     */
    private function cleanData(array $data): array
    {
        $this->io->text('Cleaning data...');

        $count = count($data);
        $progress = $this->io->createProgressBar($count);
        $progress->setFormat('debug');
        $progress->display();
        $it = new \ArrayIterator($data);
        $it->rewind();
        while ($it->valid()) {
            /** @var PokemonMove $pokemonMove */
            $pokemonMove = $it->current();

            if ($pokemonMove->getLearnMethod() === 'level-up'
                && $pokemonMove->getLevel() === null) {
                unset($data[$it->key()]);
                $count--;
                $progress->setMaxSteps($count);
            }

            $progress->advance();
            $it->next();
        }
        $progress->finish();
        $this->io->newLine(2);

        return $data;
    }

    /**
     * @param PokemonMove[] $data
     *
     * @return PokemonMove[]
     */
    private function sortData(array $data): array
    {
        $this->io->text('Sorting data...');

        // Load sorting tables
        $speciesOrder = [];
        $pokemonOrder = [];
        foreach ($this->speciesRepo->findAll() as $species) {
            $versionGroupSlug = $species->getVersionGroup()->getSlug();
            $speciesOrder[$versionGroupSlug][$species->getSlug()] = $species->getPosition();
            foreach ($species->getPokemon() as $pokemon) {
                $pokemonOrder[$versionGroupSlug][$species->getSlug()][$pokemon->getSlug()] = $pokemon->getPosition();
            }
        }
        $versionGroupOrder = [];
        foreach ($this->versionGroupRepo->findAll() as $versionGroup) {
            $versionGroupOrder[$versionGroup->getSlug()] = $versionGroup->getPosition();
        }
        $learnMethodOrder = [];
        foreach ($this->learnMethodRepo->findAll() as $learnMethod) {
            $learnMethodOrder[$learnMethod->getSlug()] = $learnMethod->getPosition();
        }

        $progress = $this->io->createProgressBar();
        $progress->setFormat('debug_nomax');
        $progress->display();
        uasort(
            $data,
            static function (PokemonMove $a, PokemonMove $b) use (
                $progress,
                $speciesOrder,
                $pokemonOrder,
                $versionGroupOrder,
                $learnMethodOrder
            ) {
                $progress->advance();

                $aVersionGroup = $a->getVersionGroup();
                $bVersionGroup = $b->getVersionGroup();
                if ($a->getVersionGroup() !== $b->getVersionGroup()) {
                    return $versionGroupOrder[$a->getVersionGroup()] - $versionGroupOrder[$b->getVersionGroup()];
                }
                if ($a->getSpecies() !== $b->getSpecies()) {
                    return $speciesOrder[$aVersionGroup][$a->getSpecies()]
                        - $speciesOrder[$bVersionGroup][$b->getSpecies()];
                }
                if ($a->getPokemon() !== $b->getPokemon()) {
                    return $pokemonOrder[$aVersionGroup][$a->getSpecies()][$a->getPokemon()]
                        - $pokemonOrder[$bVersionGroup][$b->getSpecies()][$b->getPokemon()];
                }
                if ($a->getLearnMethod() !== $b->getLearnMethod()) {
                    return $learnMethodOrder[$a->getLearnMethod()] - $learnMethodOrder[$b->getLearnMethod()];
                }
                if ($a->getLevel() !== $b->getLevel()) {
                    return $a->getLevel() - $b->getLevel();
                }
                if ($a->getMachine() !== $b->getMachine()) {
                    // This sorts TMs before HMs.
                    $aType = substr($a->getMachine(), 0, 2);
                    $aSort = substr($a->getMachine(), 2);
                    if ($aType === 'hm') {
                        $aSort += 1000;
                    }
                    $bType = substr($b->getMachine(), 0, 2);
                    $bSort = substr($b->getMachine(), 2);
                    if ($bType === 'hm') {
                        $bSort += 1000;
                    }

                    return (int)$aSort - (int)$bSort;
                }

                return strnatcasecmp($a->getMove(), $b->getMove());
            }
        );

        $progress->finish();
        $this->io->newLine(2);

        return $data;
    }

    /**
     * Write new data
     *
     * @param PokemonMove[] $data
     * @param string $path
     *
     * @return bool|int
     */
    protected function writeData(array $data, string $path)
    {
        $write = [];
        foreach ($data as $row) {
            $write[] = [
                'species' => $row->getSpecies(),
                'pokemon' => $row->getPokemon(),
                'version_group' => $row->getVersionGroup(),
                'move' => $row->getMove(),
                'learn_method' => $row->getLearnMethod(),
                'level' => $row->getLevel(),
                'machine' => $row->getMachine(),
            ];
        }

        $newCsv = $this->serializer->serialize($write, 'csv');

        return file_put_contents($path, $newCsv);
    }
}
