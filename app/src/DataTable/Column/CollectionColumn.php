<?php
/**
 * @file CollectionColumn.php
 */

namespace App\DataTable\Column;


use Omines\DataTablesBundle\Column\AbstractColumn;
use Omines\DataTablesBundle\DependencyInjection\Instantiator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig_Environment;

class CollectionColumn extends AbstractColumn
{
    private const TEMPLATE_PATH = '_data_table/column/collection.html.twig';

    /**
     * @var \Twig_Environment
     */
    private $twigEnvironment;

    /**
     * @var Instantiator
     */
    private $instantiator;

    /**
     * CollectionColumn constructor.
     *
     * @param Twig_Environment $twigEnvironment
     * @param Instantiator $instantiator
     */
    public function __construct(Twig_Environment $twigEnvironment, Instantiator $instantiator)
    {
        $this->twigEnvironment = $twigEnvironment;
        $this->instantiator = $instantiator;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Traversable $collection
     */
    public function render($collection, $context)
    {
        $childColumnType = $this->options['childType'];
        // Merge in some sensible defaults for children.
        $childColumnOptions = array_merge(
            [
                'label' => $this->options['label'],
                'orderable' => false,
            ],
            $this->options['childOptions']
        );
        $column = $this->instantiator->getColumn($childColumnType);
        $column->initialize($this->getName(), 1, $childColumnOptions, $this->getDataTable());

        $items = [];
        foreach ($collection as $item) {
            if (is_object($item)) {
                $childContext = $item;
            } else {
                $childContext = $context;
            }
            $items[] = $column->transform($item, $childContext);
        }

        if (is_callable($this->options['render'])) {
            $items = call_user_func($this->options['render'], $items, $context);
        }

        return $this->twigEnvironment->render(
            self::TEMPLATE_PATH,
            [
                'items' => $items,
                'list_tag' => $this->options['list'],
                'list_class' => $this->options['listClassName'],
                'list_item_class' => $this->options['listItemClassName'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('childType')
            ->setDefault('childOptions', [])
            ->setDefault('list', 'ul')
            ->setDefault('listClassName', null)
            ->setDefault('listItemClassName', null)
            ->setAllowedTypes('childType', ['string'])
            ->setAllowedTypes('childOptions', ['array'])
            ->setAllowedTypes('list', ['string'])
            ->setAllowedValues('list', ['ul', 'ol'])
            ->setAllowedTypes('listClassName', ['string', 'null'])
            ->setAllowedTypes('listItemClassName', ['string', 'null']);

        return $this;
    }
}
