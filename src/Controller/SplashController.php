<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SplashController extends AbstractController
{
    #[Route('/', name: 'app_splash')]
    public function index(): Response
    {
        return $this->render('splash/index.html.twig');
    }

    #[Route('/offline', name: 'app_offline')]
    public function offline(): Response
    {
        return $this->render('offline.html.twig');
    }
}
