<?php

namespace App\Twig;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class FormExtension
 */
class FormExtension extends AbstractExtension
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * FormBuildExtension constructor.
     *
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('form_create', [$this, 'formCreate']),
        ];
    }

    /**
     * @param string $type
     * @param mixed|null $data
     * @param array $options
     *
     * @return FormView
     */
    public function formCreate(string $type, $data = null, array $options = []): FormView
    {
        return $this->formFactory->create($type, $data, $options)->createView();
    }
}
