<?php
/**
 * @file FileSourceDriver.php
 */

namespace App\A2B\Drivers\Source;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\Driver;
use DragoonBoots\A2B\Drivers\AbstractSourceDriver;
use Symfony\Component\Finder\Finder;

/**
 * Class FileSourceDriver
 *
 * @Driver("file")
 */
class FileSourceDriver extends AbstractSourceDriver
{
    protected const DEFAULT_EXTS = [
        'gif',
        'jpg',
        'png',
        'mp3',
        'm4a',
        'ogg',
    ];

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @inheritDoc
     */
    public function configure(DataMigration $definition)
    {
        parent::configure($definition);

        $this->finder = new Finder();
        $this->finder->files()->in($this->sourceUri['path']);
        foreach (self::DEFAULT_EXTS as $ext) {
            $this->finder->name(sprintf('*.%s', $ext));
        }
    }

    /**
     * Configure the finder
     *
     * @param callable $configure
     *   A callable that will receive the finder and the source uri info array
     *   as arguments.
     *
     * @return self
     */
    public function configureFinder(callable $configure): self
    {
        $configure($this->finder, $this->sourceUri);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        foreach ($this->finder->getIterator() as $fileInfo) {
            $handle = fopen($fileInfo->getPathname(), 'rb');
            $contents = fread($handle, filesize($fileInfo->getPathname()));
            fclose($handle);

            yield [
                'id' => $fileInfo->getRelativePathname(),
                'path' => $fileInfo->getPathname(),
                'file_info' => $fileInfo,
                'contents' => $contents,
            ];
        }
        unset($handle, $contents);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->finder->count();
    }
}