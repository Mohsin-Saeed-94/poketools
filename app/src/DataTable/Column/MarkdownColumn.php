<?php
/**
 * @file SummaryColumn.php
 */

namespace App\DataTable\Column;


use League\CommonMark\CommonMarkConverter;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TextColumn in Markdown format.
 *
 * This will automatically set the text to be raw.
 *
 * Options:
 * - inlinesOnly: Only parse inline markdown elements.  This requires the content
 *   to have only inline tags to work properly.
 */
class MarkdownColumn extends TextColumn
{
    /**
     * @var CommonMarkConverter
     */
    private $standardMarkdown;

    /**
     * @var CommonMarkConverter
     */
    private $inlineMarkdown;

    /**
     * MarkdownColumn constructor.
     *
     * @param CommonMarkConverter $standardMarkdown
     * @param CommonMarkConverter $inlineMarkdown
     */
    public function __construct(CommonMarkConverter $standardMarkdown, CommonMarkConverter $inlineMarkdown)
    {
        $this->standardMarkdown = $standardMarkdown;
        $this->inlineMarkdown = $inlineMarkdown;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value): string
    {
        if ($this->options['inlinesOnly']) {
            $markdown = $this->inlineMarkdown;
        } else {
            $markdown = $this->standardMarkdown;
        }

        return parent::normalize($markdown->convertToHtml($value));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        // CommonMark parser will output HTML and make everything safe.
        $resolver->setDefault('raw', true);

        $resolver->setDefault('inlinesOnly', false)
            ->setAllowedTypes('inlinesOnly', ['bool']);

        return $this;
    }

}
