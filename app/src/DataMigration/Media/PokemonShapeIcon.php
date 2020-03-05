<?php

namespace App\DataMigration\Media;


use App\A2B\Drivers\Source\FileSourceDriver;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Pokemon shape icon migration.
 *
 * @DataMigration(
 *     name="Pokemon Shape Icon",
 *     group="Media",
 *     source="file:///%kernel.project_dir%/resources/pokedex-media/shapes",
 *     sourceDriver="App\A2B\Drivers\Source\FileSourceDriver",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/assets/static/shape",
 *     destinationDriver="App\A2B\Drivers\Destination\FileDestinationDriver",
 *     destinationIds={@IdField(name="id", type="string")}
 * )
 */
class PokemonShapeIcon extends AbstractMediaMigration
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
