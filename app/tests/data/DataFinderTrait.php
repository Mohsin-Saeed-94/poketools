<?php
/**
 * @file DataFinderTrait.php
 */

namespace App\Tests\data;


use Symfony\Component\Finder\Finder;

trait DataFinderTrait
{

    /**
     * Build a proper data provider generator from the given Finder.
     *
     * The contents of the file will be sent as the first parameter.
     *
     * @param Finder $finder
     *
     * @return \Generator
     */
    protected function buildFinderDataProvider(Finder $finder): \Generator
    {
        foreach ($finder as $fileInfo) {
            yield $fileInfo->getFilename() => [$fileInfo->getContents()];
        }
    }

    /**
     * @param string $directory
     *
     * @return Finder
     */
    protected function getFinderForDirectory(string $directory): Finder
    {
        $directory = realpath(__DIR__.'/../../resources/data').'/'.trim($directory, '/');
        $finder = new Finder();
        $finder->in($directory)
            ->sortByName(true);

        return $finder;
    }
}
