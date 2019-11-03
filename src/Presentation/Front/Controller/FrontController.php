<?php

namespace App\Presentation\Front\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/me", name="app_index_user")
     */
    public function indexme(): Response
    {
        return $this->render('home/me.html.twig');
    }

    /**
     * @Route("/oauth2", name="app_oauth2")
     */
    public function oauth2(): Response
    {
        return $this->render('home/oauth2.html.twig');
    }

    /**
     * @Route("/docs", name="app_docs")
     */
    public function docs(): Response
    {
        return $this->render('home/docs.html.twig');
    }

    /**
     * @Route("/examples", name="app_examples")
     */
    public function examples(): Response
    {
        return $this->render('home/examples.html.twig');
    }

}
