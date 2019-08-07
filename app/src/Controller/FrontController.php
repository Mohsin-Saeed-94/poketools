<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="front")
     */
    public function index()
    {
        return $this->render(
            'front/index.html.twig',
            [
                'controller_name' => 'FrontController',
            ]
        );
    }

    /**
     * @Route("/about/credits", name="page_credits")
     */
    public function credits()
    {
        return $this->render(
            'front/credits.html.twig'
        );
    }

    /**
     * @Route("/about/docs", name="page_docs")
     */
    public function docs()
    {
        return $this->redirect('/doc/index.html');
    }
}
