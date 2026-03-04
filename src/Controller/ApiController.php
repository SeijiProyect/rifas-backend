<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Api Rest Symfony 5.4 (TyT) entorno: ' . $_ENV['APP_ENV'] . ' DEBUG: ' . $_ENV['APP_DEBUG']
        ]);
    }
}
