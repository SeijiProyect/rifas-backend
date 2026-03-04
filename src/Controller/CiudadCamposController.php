<?php

namespace App\Controller;

use App\Entity\Ciudad;
use App\Entity\CiudadCampos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CiudadCamposController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/ciudad_campos/get_by_id/{ciudadCamposId}", methods={"GET"}, name="ciudad_campos_getCiudadCamposById")
     * @param int $ciudadCamposId
     * @return JsonResponse
     */
    public function getCiudadCamposById(int $ciudadCamposId): JsonResponse
    {
        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);
        $ciudadCampos = $ciudadCamposRepository->find($ciudadCamposId);

        if (!$ciudadCampos) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el registro de ciudad campo'], 400);
        }

        $ciudad = $ciudadCampos->getCiudad();

        $data = [
            'id' => $ciudadCampos->getId(),
            'nombre' => $ciudadCampos->getNombre(),
            'valor' => $ciudadCampos->getValor(),
            'ciudad' => ($ciudad !== null) ? [
                'id' => $ciudad->getId(),
                'nombre' => $ciudad->getNombre(),
                'pais_nombre' => $ciudad->getPais()->getNombre(),
                'nombre_ingles' => $ciudad->getNombreIngles(),
            ] : null,
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad_campos/get_by_ciudad_id/{ciudadId}", methods={"GET"}, name="ciudad_campos_getCiudadCamposByCiudadId")
     * @param int $ciudadCamposId
     * @return JsonResponse
     */
    public function getCiudadCamposByCiudadId(int $ciudadId): JsonResponse
    {
        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);
        $ciudadCampos = $ciudadCamposRepository->findBy(['ciudad' => $ciudadId]);

        if (!$ciudadCampos) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontraron registros para esta ciudad'], 400);
        }

        $data = [];

        foreach ($ciudadCampos as $ciudadCampo) {
            $ciudad = $ciudadCampo->getCiudad();
            $data[] = [
                'id' => $ciudadCampo->getId(),
                'nombre' => $ciudadCampo->getNombre(),
                'valor' => $ciudadCampo->getValor(),
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                    'pais_nombre' => $ciudad->getPais()->getNombre(),
                    'nombre_ingles' => $ciudad->getNombreIngles(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad_campos/list", methods={"GET"}, name="ciudad_campos_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);
        $ciudadCampos = $ciudadCamposRepository->findAll();

        if (!$ciudadCampos) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro ningun registro de ciudad campos'], 400);
        }

        $data = [];

        foreach ($ciudadCampos as $ciudadCampo) {
            $ciudad = $ciudadCampo->getCiudad();
            $data[] = [
                'id' => $ciudadCampo->getId(),
                'nombre' => $ciudadCampo->getNombre(),
                'valor' => $ciudadCampo->getValor(),
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                    'pais_nombre' => $ciudad->getPais()->getNombre(),
                    'nombre_ingles' => $ciudad->getNombreIngles(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad_campos/list_web", methods={"POST"}, name="ciudad_campos_list_web")
     * @return JsonResponse
     */
    public function listweb(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);
        
        $ciudadCamposTotal = $ciudadCamposRepository->findAll();

        $ciudadCampos = $ciudadCamposRepository->findBy(
            array(),
            array('id' => 'DESC'),
            $limit,
            $offset
        );

        $data = [];

        foreach ($ciudadCampos as $ciudadCampo) {
            $ciudad = $ciudadCampo->getCiudad();
            $data[] = [
                'id' => $ciudadCampo->getId(),
                'nombre' => $ciudadCampo->getNombre(),
                'valor' => $ciudadCampo->getValor(),
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                    'pais_nombre' => $ciudad->getPais()->getNombre(),
                    'nombre_ingles' => $ciudad->getNombreIngles(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($ciudadCamposTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad_campos/create", methods={"POST"}, name="ciudad_campos_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $ciudadCampo = new CiudadCampos();
        $ciudadCampo->setNombre($data['ciudad_campos']['nombre']);
        $ciudadCampo->setValor($data['ciudad_campos']['valor']);
        $ciudadRepository = $this->em->getRepository(Ciudad::class);
        $ciudad = $ciudadRepository->find($data['ciudad_campos']['ciudad_id']);
        $ciudadCampo->setCiudad($ciudad);

        $entityManager->persist($ciudadCampo);
        $entityManager->flush();

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'La ciudad campo se creo correctamente'], 200);
    }

    /**
     * @Route("/ciudad_campos/update/{id}", methods={"PUT", "PATCH"}, name="ciudad_campos_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);
        $ciudadCampos = $ciudadCamposRepository->find($id);

        if (!$ciudadCampos) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el registro ciudad campo'], 400);
        }

        $ciudadCampos->setNombre($data['ciudad_campos']['nombre']);
        $ciudadCampos->setValor($data['ciudad_campos']['valor']);
        $ciudadRepository = $this->em->getRepository(Ciudad::class);
        $ciudad = $ciudadRepository->find($data['ciudad_campos']['ciudad_id']);
        $ciudadCampos->setCiudad($ciudad);

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'La ciudad campo se actualizo correctamente'], 200);
    }

    /**
     * @Route("/ciudad_campos/delete/{id}", methods={"DELETE"}, name="ciudad_campos_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);
        $ciudadCampos = $ciudadCamposRepository->find($id);

        if (!$ciudadCampos) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro de ciudad campos'], 400);
        }

        $this->em->remove($ciudadCampos);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El registro de ciudad campo se elimino correctamente'], 200);
    }

    /**
     * @Route("/ciudad_campos/get_by_filter", methods={"POST"}, name="ciudad_campos_getCiudadCamposByFilter")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCiudadCamposByFilter(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $ciudad = (isset($data['ciudad'])) ? $data['ciudad'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);

        $ciudadCamposTotal = $ciudadCamposRepository->findAll();

        $ciudadCamposResult = $ciudadCamposRepository->findByFilter($termino, $ciudad, $offset, $limit);

        $data = [];

        foreach ($ciudadCamposResult as $ciudadCampo) {
            $data[] = [
                'id' => $ciudadCampo["id"],
                'nombre' => $ciudadCampo["nombre"],
                'valor' => $ciudadCampo["valor"],
                'ciudad' => [
                    'id' => $ciudadCampo["ciudad_id"],
                    'nombre' => $ciudadCampo["ciudad_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($ciudadCamposTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad_campos/get_by_termino", methods={"POST"}, name="ciudad_campos_getCiudadCamposByTermino")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCiudadCamposByTermino(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);

        $ciudadCamposTotal = $ciudadCamposRepository->findAll();

        $ciudadCamposResult = $ciudadCamposRepository->findByTermino($termino, $offset, $limit);

        $data = [];

        foreach ($ciudadCamposResult as $ciudadCampo) {
            $data[] = [
                'id' => $ciudadCampo["id"],
                'nombre' => $ciudadCampo["nombre"],
                'valor' => $ciudadCampo["valor"],
                'ciudad' => [
                    'id' => $ciudadCampo["ciudad_id"],
                    'nombre' => $ciudadCampo["ciudad_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($ciudadCamposTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad_campos/get_by_ciudad", methods={"POST"}, name="ciudad_campos_getCiudadCamposByCiudad")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCiudadCamposByCiudad(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $ciudad = (isset($data['ciudad'])) ? $data['ciudad'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);

        $ciudadCamposTotal = $ciudadCamposRepository->findAll();

        $ciudadCamposResult = $ciudadCamposRepository->findByCiudad($ciudad, $offset, $limit);

        $data = [];

        foreach ($ciudadCamposResult as $ciudadCampo) {
            $data[] = [
                'id' => $ciudadCampo["id"],
                'nombre' => $ciudadCampo["nombre"],
                'valor' => $ciudadCampo["valor"],
                'ciudad' => [
                    'id' => $ciudadCampo["ciudad_id"],
                    'nombre' => $ciudadCampo["ciudad_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($ciudadCamposTotal), 'data' => $data], 200);
    }
}
