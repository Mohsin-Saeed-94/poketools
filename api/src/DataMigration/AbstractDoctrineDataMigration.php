<?php


namespace App\DataMigration;


use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\MigrationReferenceStoreInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class AbstractDoctrineDataMigration extends AbstractDataMigration
{

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccess;

    /**
     * AbstractDoctrineDataMigration constructor.
     *
     * @param MigrationReferenceStoreInterface $referenceStore
     * @param PropertyAccessorInterface        $propertyAccess
     */
    public function __construct(MigrationReferenceStoreInterface $referenceStore, PropertyAccessorInterface $propertyAccess)
    {
        parent::__construct($referenceStore);

        $this->propertyAccess = $propertyAccess;
    }

    /**
     * Merge properties similar to array_merge().
     *
     * @param array    $sourceData
     * @param object   $destinationData
     * @param string[] $properties
     *
     * @return object
     */
    protected function mergeProperties(array $sourceData, object $destinationData, array $properties = [])
    {
        if (empty($properties)) {
            $properties = array_keys($sourceData);
        }

        foreach ($properties as $property) {
            $current = $this->propertyAccess->getValue($destinationData, $property);
            if (isset($sourceData[$property]) && !$current) {
                $this->propertyAccess->setValue($destinationData, $property, $sourceData[$property]);
            }
        }

        return $destinationData;
    }
}
