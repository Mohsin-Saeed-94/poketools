<?php

namespace App\Command;

use App\Repository\VersionGroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Fix common problems with Pokemon.
 */
final class DataFixPokemonCommand extends Command
{
    private const FORM_SUFFIX_MAP = [
        'sun-moon' => 'alola',
        'ultra-sun-ultra-moon' => 'alola',
        'sword-shield' => 'galar',
    ];
    protected static $defaultName = 'app:data:fix-pokemon';

    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var string
     */
    private $pokemonDataPath;
    /**
     * @var \App\Repository\VersionGroupRepository
     */
    private $versionGroupRepo;

    /**
     * DataFixItemsCommand constructor.
     * @param string $dataPath
     * @param \App\Repository\VersionGroupRepository $versionGroupRepo
     */
    public function __construct(string $dataPath, VersionGroupRepository $versionGroupRepo)
    {
        parent::__construct();

        $this->pokemonDataPath = $dataPath.'/pokemon';
        $this->versionGroupRepo = $versionGroupRepo;
    }

    protected function configure()
    {
        $this->setDescription('Fix common problems with Pokémon.')
            ->addOption('regional-forms', null, InputOption::VALUE_NONE, 'Set default Pokemon to regional forms.')
            ->addOption(
                'forms-switchable',
                null,
                InputOption::VALUE_NONE,
                'Set forms switchable to false where there are no extra forms.'
            )
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
        $finder = new Finder();
        $finder->files()
            ->in($this->pokemonDataPath)
            ->name('*.yaml');

        $progress = $this->io->createProgressBar(count($finder));
        $progress->setFormat('debug');
        foreach ($finder as $fileInfo) {
            $data = Yaml::parseFile($fileInfo->getPathname());
            $changed = false;
            if ($input->getOption('regional-forms')) {
                $data = $this->adjustRegionalForms($fileInfo->getRelativePathname(), $data, $changed);
            }
            if ($input->getOption('forms-switchable')) {
                $data = $this->adjustFormsSwitchable($fileInfo->getRelativePathname(), $data, $changed);
            }
            $data = $this->sortVersionGroups($fileInfo->getRelativePathname(), $data, $changed);

            if ($changed) {
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
     * @param string $filename
     * @param array $data
     * @param bool|null $changed
     * @return array
     */
    private function adjustRegionalForms(string $filename, array $data, ?bool &$changed = null): array
    {
        if ($changed === null) {
            $changed = false;
        }

        foreach ($data as $versionGroupSlug => &$versionGroupData) {
            // Check if this version group has regional forms
            if (!isset(self::FORM_SUFFIX_MAP[$versionGroupSlug])) {
                continue;
            }

            // First pass to find the first regional form listed to use as the default
            $regionalForm = null;
            foreach ($versionGroupData['pokemon'] as $pokemonSlug => $pokemonData) {
                if (preg_match('`^.+-'.self::FORM_SUFFIX_MAP[$versionGroupSlug].'$`', $pokemonSlug)) {
                    $regionalForm = $pokemonSlug;
                    break;
                }
            }
            unset($pokemonData);
            if ($regionalForm === null) {
                // No regional form
                continue;
            }

            // Second pass to set the defaults properly.
            $changed = true;
            foreach ($versionGroupData['pokemon'] as $pokemonSlug => &$pokemonData) {
                if ($pokemonSlug === $regionalForm) {
                    $pokemonData['default'] = true;
                } else {
                    $pokemonData['default'] = false;
                }
            }
            unset($pokemonData);
        }
        unset($versionGroupData);

        return $data;
    }

    /**
     * @param string $filename
     * @param array $data
     * @param bool|null $changed
     * @return array
     */
    private function adjustFormsSwitchable(string $filename, array $data, ?bool &$changed = null): array
    {
        if ($changed === null) {
            $changed = false;
        }

        foreach ($data as $versionGroupSlug => &$versionGroupData) {
            foreach ($versionGroupData['pokemon'] as $pokemonSlug => &$pokemonData) {
                if (isset($pokemonData['forms_switchable']) && $pokemonData['forms_switchable'] === true) {
                    if (count($versionGroupData['pokemon']) === 1 && count($pokemonData['forms']) === 1) {
                        $pokemonData['forms_switchable'] = false;
                        $changed = true;
                    }
                }
            }
            unset($pokemonData);
        }
        unset($versionGroupData);

        return $data;
    }

    /**
     * @param string $filename
     * @param array $data
     * @param bool|null $changed
     * @return array
     */
    private function sortVersionGroups(string $filename, array $data, ?bool &$changed = null): array
    {
        if ($changed === null) {
            $changed = false;
        }

        static $versionGroupSorts = null;
        if ($versionGroupSorts === null) {
            $qb = $this->versionGroupRepo->createQueryBuilder('vg');
            $qb->select('vg.slug')->addSelect('vg.position');
            $q = $qb->getQuery();
            $versionGroupSorts = array_column($q->execute(), 'position', 'slug');
        }

        $previousOrder = array_keys($data);
        uksort(
            $data,
            function (string $a, string $b) use ($versionGroupSorts) {
                return $versionGroupSorts[$a] - $versionGroupSorts[$b];
            }
        );
        if (array_keys($data) !== $previousOrder) {
            $changed = true;
        }

        return $data;
    }

}
