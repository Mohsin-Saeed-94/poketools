<?php

namespace App\EventSubscriber;

use App\Repository\VersionGroupRepository;
use FOS\ElasticaBundle\Event\TransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Elastica event subscriber
 */
class ElasticaSubscriber implements EventSubscriberInterface
{
    /**
     * @var VersionGroupRepository
     */
    private $versionGroupRepo;

    /**
     * ElasticaSubscriber constructor.
     *
     * @param VersionGroupRepository $versionGroupRepo
     */
    public function __construct(VersionGroupRepository $versionGroupRepo)
    {
        $this->versionGroupRepo = $versionGroupRepo;
    }

    public static function getSubscribedEvents()
    {
        return [
            TransformEvent::POST_TRANSFORM => 'onPostTransform',
        ];
    }

    /**
     * @param TransformEvent $event
     */
    public function onPostTransform(TransformEvent $event)
    {
        $fields = $event->getFields();
        $document = $event->getDocument();
        $suggestField = $this->needsVersionGroupContextFilled($fields);
        if ($suggestField !== false) {
            $suggest = $document->get($suggestField);
            $suggest = [
                'input' => $suggest,
                'contexts' => [
                    'version_group' => $this->getAllVersionGroupSlugs(),
                ],
            ];
            $document->set($suggestField, $suggest);
        }
    }

    /**
     * Checks if version group context data must be set.
     *
     * It must be set if there is a context called "version_group" and is does
     * not define a path.
     *
     * @param array $fields
     *
     * @return string|false
     */
    private function needsVersionGroupContextFilled(array $fields)
    {
        foreach ($fields as $field => $contents) {
            if (!is_array($contents)) {
                continue;
            }
            if (isset($contents['contexts']) && is_array($contents['contexts'])) {
                foreach ($contents['contexts'] as $context) {
                    if ($context['name'] === 'version_group') {
                        if (!isset($context['path'])) {
                            return $field;
                        }

                        return false;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getAllVersionGroupSlugs(): array
    {
        static $versionGroupSlugs = null;
        if ($versionGroupSlugs === null) {
            $versionGroupSlugs = [];
            $versionGroups = $this->versionGroupRepo->findAll();
            foreach ($versionGroups as $versionGroup) {
                $versionGroupSlugs[] = $versionGroup->getSlug();
            }
        }

        return $versionGroupSlugs;
    }
}
