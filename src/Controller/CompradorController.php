<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\CompradorRepository;

class CompradorController extends AbstractController
{
    #[Route('/comprador', name: 'comprador')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PersonaController.php',
        ]);
    }

    /**
     * @Route("/comprador/get-datos", name="get-comprador-datos", methods={"POST"})
     */
    public function getCompradorDatos(Request $request, CompradorRepository $compradorRepository)
    {

        $data = json_decode($request->getContent(), true);
        $id = (isset($data['id'])) ? $data['id'] : null;
        if ($id != null) {
            $comprador = $compradorRepository->getCompradorById($id);
            if ($comprador) {

                $res = array(
                    'id' => $comprador->getId(),
                    'nombres' => $comprador->getNombre(),
                    'celular' => $comprador->getCelular(),
                    'departamento' => $comprador->getDepartamento(),
                    'email' => $comprador->getEmail(),
                );

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Comprador encontrado', 'data' => $res], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El comprador no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

}