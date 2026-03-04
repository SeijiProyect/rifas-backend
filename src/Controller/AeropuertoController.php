<?php

namespace App\Controller;

use App\Entity\Aereopuerto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

class AeropuertoController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/aeropuerto", name="app_aeropuerto")
     */
    public function index(): Response
    {
        return $this->render('aeropuerto/index.html.twig', [
            'controller_name' => 'AeropuertoController',
        ]);
    }

    /**
     * @Route("/aeropuerto/get_list", methods={"GET"}, name="aeropuerto_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $aeropuertoRepository = $this->em->getRepository(Aereopuerto::class);
        $aeropuertos = $aeropuertoRepository->findAll();

        $data = [];

        foreach ($aeropuertos as $aero) {
            $ciudad = $aero->getCiudad();

            $data[] = [
                'id' => $aero->getId(),
                'nombre' =>  $aero->getNombre(),
                'codigo' =>  $aero->getCodigo(),
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }
}
