<?php
/**
 * @file AbstractMediaMigration.php
 */

namespace App\DataMigration\Media;


use App\A2B\Drivers\Destination\FileDestinationDriver;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\MigrationReferenceStoreInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for media migrations.
 */
abstract class AbstractMediaMigration extends AbstractDataMigration
{
    /**
     * @var string
     */
    protected $resourcesDir;

    /**
     * @var string
     */
    protected $destinationPath;

    /**
     * AbstractMediaMigration constructor.
     *
     * @param MigrationReferenceStoreInterface $referenceStore
     * @param ContainerInterface $container
     */
    public function __construct(MigrationReferenceStoreInterface $referenceStore, ContainerInterface $container)
    {
        parent::__construct($referenceStore);

        $this->resourcesDir = $container->getParameter('kernel.project_dir').'/resources';
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function configureDestination(DestinationDriverInterface $destinationDriver)
    {
        parent::configureDestination($destinationDriver);

        if ($destinationDriver instanceof FileDestinationDriver) {
            $this->destinationPath = $destinationDriver->getPath();
        }
    }
}