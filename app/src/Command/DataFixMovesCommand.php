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
 * Fix common problems with moves.
 */
final class DataFixMovesCommand extends Command
{
    protected static $defaultName = 'app:data:fix-moves';

    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var string
     */
    private $moveDataPath;
    /**
     * @var string
     */
    private $effectDataPath;
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

        $this->moveDataPath = $dataPath.'/move';
        $this->effectDataPath = $dataPath.'/move_effect';
        $this->versionGroupRepo = $versionGroupRepo;
    }

    protected function configure()
    {
        $this->setDescription('Fix common problems with moves.')
            ->addOption('move_effects', null, InputOption::VALUE_NONE, 'Also adjust moves effects.')
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
            ->in($this->moveDataPath)
            ->name('*.yaml');


        $usedEffects = [];
        $progress = $this->io->createProgressBar(count($finder));
        $progress->setFormat('debug');
        foreach ($finder as $fileInfo) {
            $data = Yaml::parseFile($fileInfo->getPathname());

            foreach ($data as $versionGroup => &$versionGroupData) {
                // The effects in gen 1 determine much of the metadata
                if (in_array($versionGroup, ['red-blue', 'yellow'])) {
                    $versionGroupData = $this->adjustGen1Moves($versionGroupData);
                }
                $usedEffects[$versionGroup][$versionGroupData['effect']] = true;
            }

            $file = $fileInfo->openFile('w');
            $file->fwrite(Yaml::dump($data, PHP_INT_MAX, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
            unset($file);
            $progress->advance();
        }
        $progress->finish();
        $this->io->newLine(2);

        if ($input->getOption('move_effects')) {
            // Now that we have a list of used effects, remove non-applicable version groups.
            $finder = new Finder();
            $finder->files()
                ->in($this->effectDataPath)
                ->name('*.yaml');

            $effectMaxIdMap = [
                'red-blue' => 87,
                'yellow' => 87,
                'gold-silver' => 157,
                'crystal' => 157,
            ];
            $progress = $this->io->createProgressBar(count($finder));
            $progress->setFormat('debug');
            foreach ($finder as $fileInfo) {
                $effectId = (int)$fileInfo->getFilenameWithoutExtension();
                $data = Yaml::parseFile($fileInfo->getPathname());
                foreach ($effectMaxIdMap as $versionGroup => $maxId) {
                    // Check if the high-numbered effect is used somewhere, as sometimes it's
                    // necessary to work around the game special-casing specific moves
                    // in an effect's code.
                    if ($effectId > $maxId && !isset($usedEffects[$versionGroup][$effectId])) {
                        unset($data[$versionGroup]);
                    }
                }

                $file = $fileInfo->openFile('w');
                $file->fwrite(Yaml::dump($data, PHP_INT_MAX, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
                unset($file);
                $progress->advance();
            }
            $progress->finish();
            $this->io->newLine(2);
        }

        return 0;
    }

    /**
     * @param $versionGroupData
     * @return mixed
     */
    private function adjustGen1Moves($versionGroupData)
    {
        // This version group's move effects have fixed effect chances.
        unset($versionGroupData['effect_chance']);

        // Set the flinch chance to match the effect
        if ($versionGroupData['effect'] === 32) {
            $versionGroupData['flinch_chance'] = 10;
        } elseif ($versionGroupData['effect'] == 38) {
            $versionGroupData['flinch_chance'] = 30;
        } else {
            unset($versionGroupData['flinch_chance']);
        }

        // Set drain
        if ($versionGroupData['effect'] === 4) {
            $versionGroupData['drain'] = 50;
        } else {
            unset($versionGroupData['drain']);
        }

        // Set ailments and ailment chances
        // Map effect ids to ailment slugs
        $ailmentMap = [
            3 => 'poison',
            5 => 'burn',
            6 => 'freeze',
            7 => 'paralysis',
            33 => 'sleep',
            34 => 'poison',
            35 => 'burn',
            37 => 'paralysis',
            43 => 'trap',
            50 => 'confusion',
            67 => 'poison',
            68 => 'paralysis',
            77 => 'confusion',
            85 => 'leech-seed',
            87 => 'disable',
        ];
        // Effect id => ailment chance
        $ailmentChanceMap = [
            3 => 20,
            5 => 10,
            6 => 10,
            7 => 10,
            34 => 40,
            35 => 30,
            37 => 30,
            77 => 10,
        ];
        if (isset($ailmentMap[$versionGroupData['effect']])) {
            $versionGroupData['ailment'] = $ailmentMap[$versionGroupData['effect']];
            if (isset($ailmentChanceMap[$versionGroupData['effect']])) {
                $versionGroupData['ailment_chance'] = $ailmentChanceMap[$versionGroupData['effect']];
            } else {
                unset($versionGroupData['ailment_chance']);
            }
        } else {
            unset($versionGroupData['ailment'], $versionGroupData['ailment_chance']);
        }

        // Recoil damage
        if ($versionGroupData['effect'] === 49) {
            $versionGroupData['recoil'] = 25;
        } else {
            unset($versionGroupData['recoil']);
        }

        // Healing moves
        if ($versionGroupData['effect'] === 57) {
            $versionGroupData['healing'] = 50;
        } else {
            unset($versionGroupData['healing']);
        }

        // Hit count
        if ($versionGroupData['effect'] === 30) {
            $versionGroupData['hits'] = '2-5';
        } elseif ($versionGroupData['effect'] === 45) {
            $versionGroupData['hits'] = 2;
        } else {
            $versionGroupData['hits'] = 1;
        }

        // Turns
        $turnsMap = [
            27 => '2-3',
            28 => '3-4',
            30 => '2-5',
            40 => 2,
            65 => 5,
            66 => 5,
            87 => '1-8',
        ];
        if (isset($turnsMap[$versionGroupData['effect']])) {
            $versionGroupData['turns'] = $turnsMap[$versionGroupData['effect']];
        } else {
            $versionGroupData['turns'] = 1;
        }

        // Stat changes
        $statChangeMap = [
            11 => ['attack' => 1],
            12 => ['defense' => 1],
            13 => ['speed' => 1],
            14 => ['special' => 1],
            15 => ['accuracy' => 1],
            16 => ['evasion' => 1],
            19 => ['attack' => 1],
            20 => ['defense' => 1],
            21 => ['speed' => 1],
            22 => ['special' => 1],
            23 => ['accuracy' => 1],
            24 => ['evasion' => 1],
            51 => ['attack' => 2],
            52 => ['defense' => 2],
            53 => ['speed' => 2],
            54 => ['special' => 2],
            55 => ['accuracy' => 2],
            56 => ['evasion' => 2],
            59 => ['attack' => -2],
            60 => ['defense' => -2],
            61 => ['speed' => -2],
            62 => ['special' => -2],
            63 => ['accuracy' => -2],
            64 => ['evasion' => -2],
            69 => ['attack' => -1],
            70 => ['defense' => -1],
            71 => ['speed' => -1],
            72 => ['special' => -1],
        ];
        $statChangeChanceMap = [
            69 => 33,
            70 => 33,
            71 => 33,
            72 => 33,
        ];
        if (isset($statChangeMap[$versionGroupData['effect']])) {
            $versionGroupData['stat_changes'] = $statChangeMap[$versionGroupData['effect']];
            if (isset($statChangeChanceMap[$versionGroupData['effect']])) {
                $versionGroupData['stat_change_chance'] = $statChangeChanceMap[$versionGroupData['effect']];
            } else {
                unset($versionGroupData['stat_change_chance']);
            }
        } else {
            unset($versionGroupData['stat_changes'], $versionGroupData['stat_change_chance']);
        }

        return $versionGroupData;
    }
}
