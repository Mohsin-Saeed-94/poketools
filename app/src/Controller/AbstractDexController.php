<?php
/**
 * @file AbstractDexController.php
 */

namespace App\Controller;


use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class AbstractDexController
 */
abstract class AbstractDexController extends AbstractController
{
    /**
     * @var DataTableFactory
     */
    protected $dataTableFactory;

    /**
     * AbstractDexController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     */
    public function __construct(DataTableFactory $dataTableFactory)
    {
        $this->dataTableFactory = $dataTableFactory;
    }
}
