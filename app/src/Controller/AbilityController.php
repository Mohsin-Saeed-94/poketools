<?php

namespace App\Controller;

use App\DataTable\Type\AbilityTableType;
use App\Entity\Version;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AbilityController extends AbstractDexController
{

    /**
     * @param Request $request
     * @param Version $version
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/dex/{version_slug}/ability", name="ability_index")
     * @ParamConverter("version", options={"mapping": {"version_slug": "slug"}})
     *
     */
    public function index(Request $request, Version $version)
    {
        $table = $this->dataTableFactory->createFromType(
            AbilityTableType::class,
            ['version' => $version]
        )->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render(
            'ability/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('ability_index', ['version_slug' => '__VERSION__']),
                'ability_table' => $table,
            ]
        );
    }
}
