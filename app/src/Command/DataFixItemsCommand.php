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
 * Fix common problems with items.
 */
final class DataFixItemsCommand extends Command
{
    protected static $defaultName = 'app:data:fix-items';

    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var string
     */
    private $itemDataPath;
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

        $this->itemDataPath = $dataPath.'/item';
        $this->versionGroupRepo = $versionGroupRepo;
    }

    protected function configure()
    {
        $this->setDescription('Fix common problems with items.')
            ->addOption('mail', null, InputOption::VALUE_NONE, 'Remove mail from version groups without mail.')
            ->addOption(
                'wonder-launcher',
                null,
                InputOption::VALUE_NONE,
                'Set Wonder Launcher items to appear only in Gen 5.'
            )
            ->addOption('z-crystals', null, InputOption::VALUE_NONE, 'Use long descriptions for z-crystals.')
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
            ->in($this->itemDataPath)
            ->name('*.yaml');

        $progress = $this->io->createProgressBar(count($finder));
        $progress->setFormat('debug');
        foreach ($finder as $fileInfo) {
            $data = Yaml::parseFile($fileInfo->getPathname());
            $changed = false;
            if ($input->getOption('wonder-launcher') && $this->isWonderLauncher(
                    $fileInfo->getRelativePathname(),
                    $data
                )) {
                $data = $this->adjustWonderLauncher($fileInfo->getRelativePathname(), $data);
                $changed = true;
            }
            if ($input->getOption('mail') && $this->isMail($fileInfo->getRelativePathname(), $data)) {
                static $versionGroupsNoMail = null;
                if ($versionGroupsNoMail === null) {
                    // Get list of version groups without mail
                    $qb = $this->versionGroupRepo->createQueryBuilder('vg');
                    $qb->select('vg.slug')
                        ->join('vg.generation', 'g')
                        ->where('g.number >= 6')
                        ->orWhere('g.number < 2');
                    $q = $qb->getQuery();
                    $versionGroupsNoMail = array_column($q->execute(), 'slug');
                }
                $data = $this->adjustMail($fileInfo->getRelativePathname(), $data, $versionGroupsNoMail);
                $changed = true;
            }
            if ($input->getOption('z-crystals') && $this->isZCrystal($fileInfo->getRelativePathname(), $data)) {
                $data = $this->adjustZCrystal($fileInfo->getRelativePathname(), $data);
                $changed = true;
            }

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
     * @return bool
     */
    private function isWonderLauncher(string $filename, array $data): bool
    {
        // This ensures that all version group entries claim they are wonder launcher items
        // before proceeding.
        $wonderLauncherCount = 0;
        foreach ($data as $versionGroupSlug => $versionGroupData) {
            if ($versionGroupData['category'] === 'miracle-shooter') {
                $wonderLauncherCount++;
            }
        }

        if ($wonderLauncherCount === 0) {
            return false;
        } elseif ($wonderLauncherCount !== count($data)) {
            throw new \DomainException(sprintf('[%s] All version group entries must be wonder launcher.', $filename));
        } else {
            return true;
        }
    }

    /**
     * @param string $filename
     * @param array $data
     * @return array
     */
    private function adjustWonderLauncher(string $filename, array $data): array
    {
        // Remove entries for version groups without the wonder launcher
        foreach (array_keys($data) as $versionGroupSlug) {
            if (!in_array($versionGroupSlug, ['black-white', 'black-2-white-2'])) {
                unset($data[$versionGroupSlug]);
            }
        }

        return $data;
    }

    /**
     * @param string $filename
     * @param array $data
     * @return bool
     */
    private function isMail(string $filename, array $data): bool
    {
        // This ensures that all version group entries claim they are mail
        // before proceeding.
        $mailCounts = 0;
        foreach ($data as $versionGroupSlug => $versionGroupData) {
            if ($versionGroupData['category'] === 'all-mail') {
                $mailCounts++;
            }
        }

        if ($mailCounts === 0) {
            return false;
        } elseif ($mailCounts !== count($data)) {
            throw new \DomainException(sprintf('[%s] All version group entries must be mail.', $filename));
        } else {
            return true;
        }
    }

    /**
     * @param string $filename
     * @param array $data
     * @param array $versionGroupsNoMail
     * @return array
     */
    private function adjustMail(string $filename, array $data, array $versionGroupsNoMail): array
    {
        // Remove entries for version groups without mail
        foreach (array_keys($data) as $versionGroupSlug) {
            if (in_array($versionGroupSlug, $versionGroupsNoMail)) {
                unset($data[$versionGroupSlug]);
            }
        }

        return $data;
    }

    /**
     * @param string $filename
     * @param array $data
     * @return bool
     */
    private function isZCrystal(string $filename, array $data): bool
    {
        // This ensures that all version group entries claim they are mail
        // before proceeding.
        $zCrystalsCount = 0;
        foreach ($data as $versionGroupSlug => $versionGroupData) {
            if ($versionGroupData['category'] === 'z-crystals') {
                $zCrystalsCount++;
            }
        }

        if ($zCrystalsCount === 0) {
            return false;
        } elseif ($zCrystalsCount !== count($data)) {
            throw new \DomainException(sprintf('[%s] All version group entries must be Z-Crystals.', $filename));
        } else {
            return true;
        }
    }

    /**
     * @param string $filename
     * @param array $data
     * @return array
     */
    private function adjustZCrystal(string $filename, array $data): array
    {
        foreach (array_keys($data) as $versionGroupSlug) {
            if (!in_array($versionGroupSlug, ['sun-moon', 'ultra-sun-ultra-moon'])) {
                unset($data[$versionGroupSlug]);
            }
        }

        foreach ($data as &$versionGroupData) {
            $versionGroupData['short_description'] = $versionGroupData['description'];
        }

        return $data;
    }
}
