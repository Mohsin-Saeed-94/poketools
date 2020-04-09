<?php

namespace App\Command;

use App\Repository\PokemonRepository;
use App\Repository\PokemonSpeciesInVersionGroupRepository;
use App\Repository\VersionGroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Fix item descriptions to include links to Pokemon for Mega Stones.
 */
final class DataMegaStonesCommand extends Command
{
    // Version groups with mega evolution
    private const MEGASTONE_VERSION_GROUPS = [
        'x-y',
        'omega-ruby-alpha-sapphire',
        'sun-moon',
        'ultra-sun-ultra-moon',
    ];

    // Map mega pokemon slugs to their mega stones
    private const MEGASTONE_MAP = [
        'abomasnow-mega' => ['slug' => 'abomasite', 'name' => 'Abomasite'],
        'absol-mega' => ['slug' => 'absolite', 'name' => 'Absolite'],
        'aerodactyl-mega' => ['slug' => 'aerodactylite', 'name' => 'Aerodactylite'],
        'aggron-mega' => ['slug' => 'aggronite', 'name' => 'Aggronite'],
        'alakazam-mega' => ['slug' => 'alakazite', 'name' => 'Alakazite'],
        'altaria-mega' => ['slug' => 'altarianite', 'name' => 'Altarianite'],
        'ampharos-mega' => ['slug' => 'ampharosite', 'name' => 'Ampharosite'],
        'audino-mega' => ['slug' => 'audinite', 'name' => 'Audinite'],
        'banette-mega' => ['slug' => 'banettite', 'name' => 'Banettite'],
        'beedrill-mega' => ['slug' => 'beedrillite', 'name' => 'Beedrillite'],
        'blastoise-mega' => ['slug' => 'blastoisinite', 'name' => 'Blastoisinite'],
        'blaziken-mega' => ['slug' => 'blazikenite', 'name' => 'Blazikenite'],
        'camerupt-mega' => ['slug' => 'cameruptite', 'name' => 'Cameruptite'],
        'charizard-mega-x' => ['slug' => 'charizardite-x', 'name' => 'Charizardite X'],
        'charizard-mega-y' => ['slug' => 'charizardite-y', 'name' => 'Charizardite Y'],
        'diancie-mega' => ['slug' => 'diancite', 'name' => 'Diancite'],
        'gallade-mega' => ['slug' => 'galladite', 'name' => 'Galladite'],
        'garchomp-mega' => ['slug' => 'garchompite', 'name' => 'Garchompite'],
        'gardevoir-mega' => ['slug' => 'gardevoirite', 'name' => 'Gardevoirite'],
        'gengar-mega' => ['slug' => 'gengarite', 'name' => 'Gengarite'],
        'glalie-mega' => ['slug' => 'glalitite', 'name' => 'Glalitite'],
        'gyarados-mega' => ['slug' => 'gyaradosite', 'name' => 'Gyaradosite'],
        'heracross-mega' => ['slug' => 'heracronite', 'name' => 'Heracronite'],
        'houndoom-mega' => ['slug' => 'houndoominite', 'name' => 'Houndoominite'],
        'kangaskhan-mega' => ['slug' => 'kangaskhanite', 'name' => 'Kangaskhanite'],
        'latias-mega' => ['slug' => 'latiasite', 'name' => 'Latiasite'],
        'latios-mega' => ['slug' => 'latiosite', 'name' => 'Latiosite'],
        'lopunny-mega' => ['slug' => 'lopunnite', 'name' => 'Lopunnite'],
        'lucario-mega' => ['slug' => 'lucarionite', 'name' => 'Lucarionite'],
        'manectric-mega' => ['slug' => 'manectite', 'name' => 'Manectite'],
        'mawile-mega' => ['slug' => 'mawilite', 'name' => 'Mawilite'],
        'medicham-mega' => ['slug' => 'medichamite', 'name' => 'Medichamite'],
        'metagross-mega' => ['slug' => 'metagrossite', 'name' => 'Metagrossite'],
        'mewtwo-mega-x' => ['slug' => 'mewtwonite-x', 'name' => 'Mewtwonite X'],
        'mewtwo-mega-y' => ['slug' => 'mewtwonite-y', 'name' => 'Mewtwonite Y'],
        'pidgeot-mega' => ['slug' => 'pidgeotite', 'name' => 'Pidgeotite'],
        'pinsir-mega' => ['slug' => 'pinsirite', 'name' => 'Pinsirite'],
        'sableye-mega' => ['slug' => 'sablenite', 'name' => 'Sablenite'],
        'salamence-mega' => ['slug' => 'salamencite', 'name' => 'Salamencite'],
        'sceptile-mega' => ['slug' => 'sceptilite', 'name' => 'Sceptilite'],
        'scizor-mega' => ['slug' => 'scizorite', 'name' => 'Scizorite'],
        'sharpedo-mega' => ['slug' => 'sharpedonite', 'name' => 'Sharpedonite'],
        'slowbro-mega' => ['slug' => 'slowbronite', 'name' => 'Slowbronite'],
        'steelix-mega' => ['slug' => 'steelixite', 'name' => 'Steelixite'],
        'swampert-mega' => ['slug' => 'swampertite', 'name' => 'Swampertite'],
        'tyranitar-mega' => ['slug' => 'tyranitarite', 'name' => 'Tyranitarite'],
        'venusaur-mega' => ['slug' => 'venusaurite', 'name' => 'Venusaurite'],
    ];

    protected static $defaultName = 'app:data:mega-stones';

    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var string
     */
    private $itemDataPath;
    /**
     * @var \App\Repository\PokemonSpeciesInVersionGroupRepository
     */
    private $speciesRepo;
    /**
     * @var \App\Repository\PokemonRepository
     */
    private $pokemonRepo;
    /**
     * @var \App\Repository\VersionGroupRepository
     */
    private $versionGroupRepo;

    /**
     * DataMegaStonesCommand constructor.
     * @param string $dataPath
     * @param \App\Repository\VersionGroupRepository $versionGroupRepo
     * @param \App\Repository\PokemonSpeciesInVersionGroupRepository $speciesRepo
     * @param \App\Repository\PokemonRepository $pokemonRepo
     */
    public function __construct(
        string $dataPath,
        VersionGroupRepository $versionGroupRepo,
        PokemonSpeciesInVersionGroupRepository $speciesRepo,
        PokemonRepository $pokemonRepo
    ) {
        parent::__construct();

        $this->itemDataPath = $dataPath.'/item';
        $this->speciesRepo = $speciesRepo;
        $this->pokemonRepo = $pokemonRepo;
        $this->versionGroupRepo = $versionGroupRepo;
    }

    protected function configure()
    {
        $this->setDescription('Ensure mega stones have links to their Pokémon.')
            ->addOption('items', null, InputOption::VALUE_NONE, 'Adjust items to reference Pokémon.')
            ->addOption('pokemon', null, InputOption::VALUE_NONE, 'Use Pokémon to create/update items.')
            ->setHidden(true);
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('items') && $input->getOption('pokemon')) {
            $this->io->error('Specify only one of --items or --pokemon.');

            return 1;
        }

        if ($input->getOption('items')) {
            return $this->items();
        } elseif ($input->getOption('pokemon')) {
            return $this->pokemon();
        }

        return 1;
    }

    /**
     * @return int
     */
    private function items(): int
    {
        $finder = new Finder();
        $finder->files()
            ->in($this->itemDataPath)
            ->name('*.yaml');

        $progress = $this->io->createProgressBar(count($finder));
        $progress->setFormat('debug');
        foreach ($finder as $fileInfo) {
            $data = Yaml::parseFile($fileInfo->getPathname());
            if ($this->isMegaStone($data)) {
                $data = $this->tryMegaStone($fileInfo->getRelativePathname(), $data);
                $file = $fileInfo->openFile('w');
                $file->fwrite(Yaml::dump($data, PHP_INT_MAX, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
                unset($file);
            }
            $progress->advance();
        }
        $progress->finish();
        $this->io->newLine(2);

        return 0;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function isMegaStone(array $data): bool
    {
        foreach (self::MEGASTONE_VERSION_GROUPS as $versionGroup) {
            if (!isset($data[$versionGroup])) {
                continue;
            }
            if (isset($data[$versionGroup]['category']) && $data[$versionGroup]['category'] === 'mega-stones') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $filename
     * @param array $data
     * @return array
     */
    private function tryMegaStone(string $filename, array $data): array
    {
        foreach (self::MEGASTONE_VERSION_GROUPS as $versionGroupSlug) {
            if (!isset($data[$versionGroupSlug])) {
                continue;
            }

            $versionGroup = $this->versionGroupRepo->findOneBy(['slug' => $versionGroupSlug]);
            foreach (['short_description', 'description'] as $key) {
                if (!preg_match(
                    '`^Held: Allows (?P<species>.+) to Mega Evolve into (?P<mega_pokemon>.+).$`',
                    $data[$versionGroupSlug][$key],
                    $matches
                )) {
                    throw new \DomainException(
                        sprintf(
                            '[%s][%s] The %s does not match expectations.',
                            $filename,
                            $versionGroupSlug,
                            $key
                        )
                    );
                }

                // Try to get the Species and Pokemon for this Mega Evolution
                $species = $this->speciesRepo->findOneBy(
                    ['name' => $matches['species'], 'versionGroup' => $versionGroup]
                );
                if ($species === null) {
                    throw new \DomainException(
                        sprintf(
                            '[%s][%s] The species "%s" does not exist.',
                            $filename,
                            $versionGroupSlug,
                            $matches['species']
                        )
                    );
                }
                $megaPokemon = $this->pokemonRepo->findOneBy(
                    ['species' => $species, 'name' => $matches['mega_pokemon']]
                );
                if ($megaPokemon === null) {
                    throw new \DomainException(
                        sprintf(
                            '[%s][%s] The pokemon "%s" does not exist.',
                            $filename,
                            $versionGroupSlug,
                            $matches['mega_pokemon']
                        )
                    );
                }

                $data[$versionGroupSlug][$key] = sprintf(
                    'Held: Allows []{pokemon:%s} to Mega Evolve into []{pokemon:%s/%s}.',
                    $species->getSlug(),
                    $species->getSlug(),
                    $megaPokemon->getSlug()
                );
            }
        }

        return $data;
    }

    private function pokemon(): int
    {
        $megaPokemon = $this->pokemonRepo->findBy(['mega' => true]);
        $versionGroupSorts = $this->getVersionGroupSorts();
        $processedItems = [];
        $progress = $this->io->createProgressBar(count($megaPokemon));
        $progress->setFormat('debug');
        foreach ($megaPokemon as $pokemon) {
            if (!isset(self::MEGASTONE_MAP[$pokemon->getSlug()])) {
                // This Pokemon has no mega stone (e.g. Rayquaza)
                continue;
            }
            $versionGroup = $pokemon->getSpecies()->getVersionGroup();
            $megaStoneSlug = self::MEGASTONE_MAP[$pokemon->getSlug()]['slug'];
            $megaStoneName = self::MEGASTONE_MAP[$pokemon->getSlug()]['name'];

            // All of these items follow the same basic template, so fill in the blanks.
            $description = sprintf(
                'Held: Allows []{pokemon:%s} to [Mega Evolve]{mechanic:mega-evolution} into []{pokemon:%s/%s}.',
                $pokemon->getSpecies()->getSlug(),
                $pokemon->getSpecies()->getSlug(),
                $pokemon->getSlug(),
            );
            $flavor = sprintf(
                "One variety of the mysterious Mega Stones.\nHave %s hold it, and this stone will\nenable it to Mega Evolve during battle.",
                $pokemon->getSpecies()->getName()
            );
            $versionGroupData = [
                'name' => $megaStoneName,
                'category' => 'mega-stones',
                'pocket' => 'misc',
                'fling_effect' => 'damage',
                'fling_power' => 80,
                'short_description' => $description,
                'description' => $description,
                'flavor_text' => $flavor,
                'icon' => $megaStoneSlug.'.png',
            ];

            $itemFilePath = $this->itemDataPath.'/'.$megaStoneSlug.'.yaml';
            if (in_array($megaStoneSlug, $processedItems)) {
                $data = Yaml::parseFile($itemFilePath);
            } else {
                $data = [];
            }
            $data[$versionGroup->getSlug()] = $versionGroupData;
            // Keep the data sorted by version group.
            uksort(
                $data,
                function (string $a, string $b) use ($versionGroupSorts) {
                    return $versionGroupSorts[$a] - $versionGroupSorts[$b];
                }
            );
            $file = fopen($itemFilePath, 'w');
            fwrite($file, Yaml::dump($data, PHP_INT_MAX, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
            fclose($file);
            $processedItems[] = $megaStoneSlug;
            $progress->advance();
        }
        $progress->finish();
        $this->io->newLine(2);

        return 0;
    }

    /**
     * @return array
     */
    private function getVersionGroupSorts(): array
    {
        $versionGroupSorts = [];
        foreach ($this->versionGroupRepo->findAll() as $versionGroup) {
            $versionGroupSorts[$versionGroup->getSlug()] = $versionGroup->getPosition();
        }

        return $versionGroupSorts;
    }
}
