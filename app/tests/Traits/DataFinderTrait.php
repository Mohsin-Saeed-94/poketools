<?php

namespace App\Tests\Traits;


use Symfony\Component\Finder\Finder;

trait DataFinderTrait
{

    /**
     * @param string $directory
     *
     * @return Finder
     */
    protected function getFinderForDirectory(string $directory): Finder
    {
        $directory = implode('/', [dirname(__FILE__, 3), 'resources/data', $directory]);
        $finder = new Finder();
        $finder->in($directory);

        return $finder;
    }

}
