<?php

namespace App\DataMigration\Media;


use App\A2B\Drivers\Source\FileSourceDriver;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Item icon migration.
 *
 * @DataMigration(
 *     name="Item Icon",
 *     group="Media",
 *     source="file:///%kernel.project_dir%/resources/pokedex-media/items",
 *     sourceIds={@IdField(name="id")},
 *     destination="file:///%kernel.project_dir%/assets/static/item",
 *     destinationIds={@IdField(name="id", type="string")}
 * )
 */
class ItemIcon extends AbstractMediaMigration
{
    /**
     * {@inheritdoc}
     *
     * @param FileSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->configureFinder(
            function (Finder $finder) {
                $finder->depth('== 0');
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        /** @var SplFileInfo $fileInfo */
        $fileInfo = $sourceData['file_info'];

        if (!$destinationData) {
            copy($fileInfo->getPathname(), sprintf('%s/%s', $this->destinationPath, $fileInfo->getFilename()));
        }

        return [
            'id' => $fileInfo->getRelativePathname(),
        ];
    }
}