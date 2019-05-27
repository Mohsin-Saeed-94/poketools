<?php
/**
 * @file ElasticaToModelTransformer.php
 */

namespace App\Elastica;


use Doctrine\ORM\EntityManagerInterface;
use Elastica\Result;
use FOS\ElasticaBundle\Index\IndexManager;

/**
 * Transform Elastica search results to entities
 *
 * Unlike the default transformer, this works across indexes.
 */
class ElasticaToModelTransformer
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ElasticaToModelTransformer constructor.
     *
     * @param IndexManager $indexManager
     * @param EntityManagerInterface $em
     */
    public function __construct(IndexManager $indexManager, EntityManagerInterface $em)
    {
        $this->indexManager = $indexManager;
        $this->em = $em;
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository.
     *
     * @param Result[] $elasticaObjects
     *   An array of Elastica result objects
     *
     * @return object[]
     *   An array of model objects
     **/
    public function transform(array $elasticaObjects)
    {
        $results = [];
        $load = [];

        // Sort out the objects by class to reduce database queries
        foreach ($elasticaObjects as $elasticaObject) {
            $class = $this->getClassForElasticaObject($elasticaObject);
            $id = $elasticaObject->getId();
            // Current value is used to get the hydrated entity from the database later.
            $results[] = [$class, $id];
            $load[$class][] = $id;
        }

        // Load the entities, grouped by class to reduce database queries.
        $this->loadEntities($load);

        // Put the entities back where they belong.
        foreach ($results as &$result) {
            [$class, $id] = $result;
            $result = $load[$class][$id];
        }
        unset($result);

        return $results;
    }

    /**
     * @param Result $elasticaObject
     *
     * @return string
     */
    private function getClassForElasticaObject(Result $elasticaObject): string
    {
        static $map = [];

        $indexName = $elasticaObject->getIndex();
        $typeName = $elasticaObject->getType();
        if (!isset($map[$indexName][$typeName])) {
            $index = $this->indexManager->getIndex($indexName);
            $type = $index->getType($typeName);
            $mapping = $type->getMapping();
            $class = $mapping[$typeName]['_meta']['model'];

            $map[$indexName][$typeName] = $class;
        }

        return $map[$indexName][$typeName];
    }

    /**
     * Load entities from a map of class names to ids
     *
     * @param array $load
     *
     * @return void
     */
    private function loadEntities(array &$load)
    {
        foreach ($load as $class => &$entities) {
            $idField = 'id';
            $qb = $this->em->createQueryBuilder();
            $qb->from($class, 'class', 'class.'.$idField)
                ->select('class')
                ->where('class.'.$idField.' in (:ids)')
                ->setParameter('ids', $entities);
            $q = $qb->getQuery();
            $q->execute();
            $entities = $q->getResult();
        }
    }
}
