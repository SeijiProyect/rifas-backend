<?php

namespace App\Controller;

use App\Entity\Hospedaje;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HospedajeRepository;

class HospedajeController extends AbstractController
{
    private $hospedajeRepository;
    private $em;

    public function __construct(HospedajeRepository $hospedajeRepository, EntityManagerInterface $em)
    {
        $this->hospedajeRepository = $hospedajeRepository;
        $this->em = $em;
    }

    /**
     * @Route("/hospedaje", name="app_hospedaje")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/HospedajeController.php',
        ]);
    }

    /**
     * @Route("/hospedaje/get-hospedajes", name="get-hospedajes", methods={"GET"})
     */
    public function getHospedajesList()
    {
        $hospedajes = $this->hospedajeRepository->createQueryBuilder('i')
            ->select(
                'i.id, 
                i.nombre
                '
            )
            ->orderBy('i.nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $hospedajes], 200);
    }
}
