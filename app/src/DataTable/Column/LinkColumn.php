<?php
/**
 * @file SummaryColumn.php
 */

namespace App\DataTable\Column;


use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Link to a route.
 *
 * Options:
 * - route: The Symfony route name, or
 * - uri: A uri to link to
 * - routeParams: An map of route parameters.  Pass a callable here to use
 *   a value from the current context.  The callback signature is
 *   `function ($context, $value)`.
 */
class LinkColumn extends TextColumn
{
    private const TEMPLATE_PATH = '_data_table/column/link.html.twig';

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var PropertyAccessorInterface
     */
    private $accessor;

    /**
     * @var \Twig\Environment
     */
    private $twigEnvironment;

    /**
     * LinkColumn constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param PropertyAccessorInterface $accessor
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        PropertyAccessorInterface $accessor,
        Environment $twigEnvironment
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->accessor = $accessor;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * {@inheritdoc}}
     */
    protected function render($value, $context)
    {
        if (empty($value)) {
            return parent::render($value, $context);
        }

        $value = parent::render($value, $context);

        if (isset($this->options['uri'])) {
            $uri = $this->options['uri'];
        } elseif (isset($this->options['route'])) {
            $params = $this->buildRouteParams($this->options['routeParams'], $context, $value);
            $uri = $this->urlGenerator->generate($this->options['route'], $params);
        } else {
            throw new \LogicException('You must define either "route" or "uri" in a LinkColumn, found neither.');
        }

        $linkClassName = $this->options['linkClassName'];
        if (($linkClassName !== null) && is_callable($linkClassName)) {
            $linkClassName = $linkClassName($context, $value);
        }

        return $this->twigEnvironment->render(
            self::TEMPLATE_PATH,
            [
                'uri' => $uri,
                'text' => $value,
                'link_class' => $linkClassName,
            ]
        );
    }

    /**
     * Build the parameters list from options and context.
     *
     * @param array $params
     * @param mixed $context
     * @param mixed $value
     *
     * @return mixed
     */
    private function buildRouteParams(array $params, $context, $value): array
    {
        foreach ($params as $paramName => &$paramValue) {
            if (is_callable($paramValue)) {
                $paramValue = $paramValue($context, $value);
            }
        }

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('route', null)
            ->setDefault('uri', null)
            ->setDefault('routeParams', [])
            ->setDefault('linkClassName', null)
            ->setAllowedTypes('route', ['null', 'string'])
            ->setAllowedTypes('uri', ['null', 'string'])
            ->setAllowedTypes('routeParams', ['null', 'array'])
            ->setAllowedTypes('linkClassName', ['null', 'string', 'callable']);

        return $this;
    }

}
