<?php

namespace App\Controller;

use App\Entity\Universidad;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class UniversidadController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/universidad", name="app_universidad")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UniversidadController.php',
        ]);
    }

    /**
     * @Route("/universidad/get_list", methods={"GET"}, name="universidad_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $universidadRepository = $this->em->getRepository(Universidad::class);
        $universidades = $universidadRepository->findAll();

        $data = [];

        foreach ($universidades as $uni) {
            $data[] = [
                'id' => $uni->getId(),
                'nombre' =>  $uni->getNombre(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }
}
