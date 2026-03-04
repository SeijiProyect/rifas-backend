<?php

namespace App\Controller;

use App\Entity\Proveedor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProveedorController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/proveedor", name="app_proveedor")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProveedorController.php',
        ]);
    }

    /**
     * @Route("/proveedor/get_list", methods={"GET"}, name="proveedor_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $proveedorRepository = $this->em->getRepository(Proveedor::class);
        $proveedores = $proveedorRepository->findAll();

        $data = [];

        foreach ($proveedores as $proveedor) {
            $data[] = [
                'id' => $proveedor->getId(),
                'nombre' =>  $proveedor->getNombre(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }
}
