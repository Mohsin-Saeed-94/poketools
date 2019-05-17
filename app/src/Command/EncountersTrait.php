<?php
/**
 * @file EncountersTrait.php
 */

namespace App\Command;

use App\Command\DataClass\Encounter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Handle loading and manipulating encounter data.
 */
trait EncountersTrait
{

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Encounter[]|ArrayCollection
     */
    protected $data;

    /**
     * Load the data from CSV into memory.
     *
     * @param string $path
     *
     * @return array
     */
    protected function loadData(string $path): array
    {
        $dataContents = file_get_contents($path);
        /** @var Encounter[] $data */
        $data = $this->serializer->deserialize(
            $dataContents,
            Encounter::class.'[]',
            'csv',
            [
                ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ]
        );

        return $data;
    }

    /**
     * Write new data
     *
     * @param string $path
     *
     * @return bool|int
     */
    protected function writeData(string $path)
    {
        $newCsv = $this->serializer->serialize(
            $this->data->getValues(),
            'csv',
            [CsvEncoder::AS_COLLECTION_KEY => true]
        );

        return file_put_contents($path, $newCsv);
    }
}
