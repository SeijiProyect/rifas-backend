<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Grupo;


class GrupoController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/grupo/list", methods={"GET"}, name="grupo_list")
     *
     */
    public function list(): JsonResponse
    {
        $grupoRepository = $this->em->getRepository(Grupo::class);
        $grupos = $grupoRepository->findAll();

        $data = [];

        foreach ($grupos as $grupo) {
            $data[] = [
                'id' => $grupo->getId(),
                'nombre' => $grupo->getNombre(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }
}
