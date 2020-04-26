<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * Resize icon png files, matting where necessary.
 */
final class IconResizeCommand extends Command
{
    protected static $defaultName = 'app:icon-resize';

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Resize icons to be icon-sized')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to media directory')
            ->addOption('size', 's', InputOption::VALUE_REQUIRED, 'Icon size', 30)
            ->addOption(
                'recursive',
                'r',
                InputOption::VALUE_OPTIONAL,
                'Recurse through directories.  Optionally specify depth, or omit depth for infinite recursion.',
                false
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $finder = new Finder();
        $finder->in($input->getArgument('path'))
            ->files()
            ->name('*.png');
        if ($input->getOption('recursive') !== true && $input->getOption('recursive') !== null) {
            $finder->depth($input->getOption('recursive'));
        } elseif ($input->getOption('recursive') === false) {
            $finder->depth(0);
        }

        $progress = $this->io->createProgressBar($finder->count());
        $resizedCount = 0;
        foreach ($finder as $fileInfo) {
            try {
                $resized = $this->resizeImage($fileInfo->getPathname(), $input->getOption('size'));
            } catch (\Exception $e) {
                $this->io->error(
                    [
                        sprintf('Error processing %s', $fileInfo->getRelativePathname()),
                        $e->getMessage(),
                    ]
                );

                return 1;
            }
            $progress->advance();
            if ($resized) {
                $resizedCount++;
            }
        }
        $progress->finish();
        $this->io->newLine(2);
        $this->io->success(sprintf('Resized %u images.', $resizedCount));

        return 0;
    }

    private function resizeImage(string $path, int $size): bool
    {
        [0 => $origWidth, 1 => $origHeight] = getimagesize($path);
        if ($origWidth >= $size && $origHeight >= $size) {
            // Image does not need to be matted
            return false;
        }

        $original = imagecreatefrompng($path);
        $newWidth = max($origWidth, $size);
        $newHeight = max($origHeight, $size);
        $new = imagecreate($newWidth, $newHeight);
        imagecolorallocatealpha($new, 0, 0, 0, 127);
//        imagepalettecopy($new, $original);
        imagesavealpha($new, true);

        $placeX = ($newWidth - $origWidth) / 2;
        $placeY = ($newHeight - $origHeight) / 2;
        if (!imagecopy(
            $new,
            $original,
            $placeX,
            $placeY,
            0,
            0,
            $origWidth,
            $origHeight
        )) {
            // Failed for some reason
            throw new \Exception('Could not matte icon.');
        }

        if (!imagepng($new, $path)) {
            // Could not write file
            throw new \Exception('Error writing file.');
        }

        imagedestroy($original);
        imagedestroy($new);


        return true;
    }
}
