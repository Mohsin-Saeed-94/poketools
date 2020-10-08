<?php


namespace App\Handler;


use App\Entity\EntityGroupedInterface;
use App\Entity\EntityHasParentInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\RelativeSlugHandler;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;

class SluggableGroupedHandler extends RelativeSlugHandler
{

    protected const DEFAULT_OPTIONS = [
        'relationField' => null,
        'relationSlugField' => 'slug',
        'separator' => '/',
    ];

    /**
     * {@inheritdoc}
     */
    public static function validate(array $options, ClassMetadata $meta)
    {
        $options = self::buildOptions($meta->getReflectionClass(), $options);
        if ($options) {
            parent::validate($options, $meta);
        }
    }

    /**
     * Build an options array suitable for the RelativeSlugHandler.
     *
     * @param \ReflectionClass $refl
     * @param array            $options
     *
     * @return array
     */
    protected static function buildOptions(\ReflectionClass $refl, array $options = []): array
    {
        $className = $refl->getName();

        static $groupedEntities = [];
        if (!isset($groupedEntities[$className])) {
            $hasParentEntity = ($refl->implementsInterface(EntityHasParentInterface::class));
            $isGrouped = ($refl->implementsInterface(EntityGroupedInterface::class));
            $groupFieldDefined = ($refl->hasMethod('getGroupField'));
            if (($hasParentEntity && $isGrouped) || $groupFieldDefined) {
                $computedOptions = [];
                $computedOptions['relationField'] = call_user_func(
                    $refl->getMethod('getGroupField')
                        ->getClosure()
                );
                $groupedEntities[$className] = array_merge(self::DEFAULT_OPTIONS, $computedOptions, $options);
            } else {
                $groupedEntities[$className] = [];
            }
        }

        return $groupedEntities[$className];
    }

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
        $options = self::buildOptions(new \ReflectionClass($object));
        if ($options) {
            $config['handlers'][get_called_class()] = array_merge($options, $config['handlers'][get_called_class()]);
            parent::onChangeDecision($ea, $config, $object, $slug, $needToChangeSlug);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        $options = self::buildOptions(new \ReflectionClass($object));
        if ($options) {
            $config['handlers'][get_called_class()] = array_merge($options, $config['handlers'][get_called_class()]);
            parent::postSlugBuild($ea, $config, $object, $slug);
        }
    }

}
