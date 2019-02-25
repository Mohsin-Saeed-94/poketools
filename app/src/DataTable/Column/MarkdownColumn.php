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
 */
class MarkdownColumn extends TextColumn
{
    /**
     * @var CommonMarkConverter
     */
    private $markdown;

    /**
     * MarkdownColumn constructor.
     *
     * @param CommonMarkConverter $markdown
     */
    public function __construct(CommonMarkConverter $markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value): string
    {
        return parent::normalize($this->markdown->convertToHtml($value));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        // CommonMark parser will output HTML and make everything safe.
        $resolver->setDefault('raw', true);

        return $this;
    }

}
