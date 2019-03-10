<?php
/**
 * @file FileDestinationDriver.php
 */

namespace App\A2B\Drivers\Destination;


use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\Driver;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\Drivers\AbstractDestinationDriver;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Destination driver for raw files
 *
 * Migrations using this driver must handle copying/writing/etc. files themselves.
 * Return an array with the proper ids to allow the system to track the files.
 *
 * @Driver("file")
 */
class FileDestinationDriver extends AbstractDestinationDriver
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
     * @var string
     */
    protected $path;
    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DataMigration $definition)
    {
        parent::configure($definition);

        $this->path = $this->destUri['path'];
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $this->finder = new Finder();
        $this->finder->files()->in($this->path);
        foreach (self::DEFAULT_EXTS as $ext) {
            $this->finder->name(sprintf('*.%s', $ext));
        }
    }

    /**
     * Read the ids that presently exist in the destination.
     *
     * @return array
     */
    public function getExistingIds(): array
    {
        $ids = [];
        foreach ($this->finder as $fileInfo) {
            $ids[] = $this->buildIdsFromFilePath($fileInfo, $this->destIds);
        }

        return $ids;
    }

    /**
     * Get id field values from the file path.
     *
     * The Id is built as in this example:
     * - Id fields: a, b, c
     * - Path: w/x/y/z.yaml
     * - Result: a=x, b=y, c=z (note that w is ignored because there are only
     *   3 id fields.
     *
     * @param SplFileInfo $fileInfo
     * @param array $ids
     *
     * @return array
     */
    protected function buildIdsFromFilePath(\SplFileInfo $fileInfo, array $ids): array
    {
        $pathParts = explode('/', $fileInfo->getPath());
        $pathParts[] = $fileInfo->getBasename('.'.$fileInfo->getExtension());

        $id = [];
        foreach (array_reverse($ids) as $idField) {
            /** @var IdField $idField */
            $id[$idField->getName()] = $this->resolveIdType($idField, array_pop($pathParts));
        }

        return $id;
    }

    /**
     * Get the entity as last migrated from the destination for updating.
     *
     * @param array $destIds
     *   A list of key-value pairs where the key is the destination id field and
     *   the value is destination id value.
     *
     * @return \SplFileInfo|null
     *   Returns the selected entity, or null if it does not exist in the
     *   destination.
     */
    public function read(array $destIds)
    {
        $entityFiles = $this->findEntities([$destIds]);
        if (empty($entityFiles)) {
            return null;
        }

        return array_pop($entityFiles);
    }

    /**
     * Find entities matching the given Ids
     *
     * @param array $destIdSet
     *
     * @return array
     */
    protected function findEntities(array $destIdSet): array
    {
        $entityFiles = [];
        foreach ($destIdSet as $destIds) {
            $matched = 0;
            foreach (self::DEFAULT_EXTS as $ext) {
                $searchPath = $this->buildFilePathFromIds($destIds, $this->destUri['path'], $ext);
                if (file_exists($searchPath)) {
                    $matched++;
                    $entityFiles[] = new \SplFileInfo($searchPath);
                }
            }
            if ($matched > 1) {
                // The filesystem would normally enforce uniqueness here, however,
                // because both multiple extensions are allowed, it's conceivable
                // that a file could exist with different extensions.
                throw new \RangeException(
                    sprintf("More than one entity matched the ids:\n%s\n", var_export($destIds, true))
                );
            }
        }

        return $entityFiles;
    }

    /**
     * Build the file path an entity with the given ids will be stored at.
     *
     * @param array $ids
     * @param string $path
     * @param string $ext
     *
     * @return string
     */
    protected function buildFilePathFromIds(array $ids, string $path, string $ext): string
    {
        $pathParts = [];
        foreach ($ids as $id => $value) {
            $pathParts[] = $value;
        }

        $fileName = array_pop($pathParts).'.'.$ext;

        return sprintf('%s/%s/%s', $path, implode('/', $pathParts), $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function readMultiple(array $destIdSet)
    {
        return $this->findEntities($destIdSet);
    }

    /**
     * Dummy method.
     *
     * The migration is responsible for writing data!
     */
    public function write($data)
    {
        return $data;
    }
}