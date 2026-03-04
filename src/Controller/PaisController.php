<?php

namespace App\Controller;

use App\Entity\Pais;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PaisController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/pais", name="app_pais")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PaisController.php',
        ]);
    }

    /**
     * @Route("/pais/get_list", methods={"GET"}, name="pais_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $paisRepository = $this->em->getRepository(Pais::class);
        //$paises = $paisRepository->findAll();
        $paises = $paisRepository->findBy(array(), array('nombre' => 'ASC'));

        $data = [];

        foreach ($paises as $pais) {
            $data[] = [
                'id' => $pais->getId(),
                'nombre' => $pais->getNombre(),
                'nacionalidad' => $pais->getNacionalidad()
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/pais/create", methods={"POST"}, name="pais_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $pais = new Pais();
        $pais->setNombre($data['pais']['nombre']);
        $pais->setNombre($data['pais']['nacionalidad']);
        $entityManager->persist($pais);
        $entityManager->flush();

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El pais se creo correctamente'], 200);
    }

    /**
     * @Route("/pais/update/{id}", methods={"PUT", "PATCH"}, name="pais_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $paisRepository = $this->em->getRepository(Pais::class);
        $pais = $paisRepository->find($id);

        if (!$pais) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el pais'], 400);
        }

        $pais->setNombre($data['pais']['nombre']);
        $pais->setNacionalidad($data['pais']['nacionalidad']);

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El pais se actualizo correctamente'], 200);
    }

    /**
     * @Route("/pais/delete/{id}", methods={"DELETE"}, name="pais_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $paisRepository = $this->em->getRepository(Pais::class);
        $pais = $paisRepository->find($id);

        if (!$pais) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro de pais'], 400);
        }

        $this->em->remove($pais);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El pais se elimino correctamente'], 200);
    }
}
