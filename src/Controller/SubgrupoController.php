<?php

namespace App\Controller;

use App\Entity\Subgrupo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubgrupoRepository;

class SubgrupoController extends AbstractController
{
    private $subgrupoRepository;
    private $em;

    public function __construct(SubgrupoRepository $subgrupoRepository, EntityManagerInterface $em)
    {
        $this->subgrupoRepository = $subgrupoRepository;
        $this->em = $em;
    }

    /**
     * @Route("/subgrupo", name="app_subgrupo")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SubgrupoController.php',
        ]);
    }

    /**
     * @Route("/subgrupo/get-subgrupos", name="get-subgrupos", methods={"GET"})
     */
    public function getSubgruposList()
    {
        $subgrupos = $this->subgrupoRepository->createQueryBuilder('i')
            ->select(
                'i.id, 
                i.nombre
                '
            )
            ->orderBy('i.nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $subgrupos], 200);
    }
}
