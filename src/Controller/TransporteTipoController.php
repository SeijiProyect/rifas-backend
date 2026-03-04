<?php

namespace App\Controller;

use App\Entity\CamposTipotransporte;
use App\Entity\TransporteTipo;
use App\Entity\Transporte;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class TransporteTipoController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/transporte/tipo", name="app_transporte_tipo")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TransporteTipoController.php',
        ]);
    }

    /**
     * @Route("/transporte_tipo/get_by_id/{transporteTipoId}", methods={"GET"}, name="transporte_tipo_getTransporteTipoById")
     * @param int $puntoInteresId
     * @return JsonResponse
     */
    public function getTransporteTipoById(int $transporteTipoId): JsonResponse
    {
        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);
        $transporteTipo = $transporteTipoRepository->find($transporteTipoId);

        if (!$transporteTipo) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el transporte tipo'], 400);
        }

        $data = [
            'id' => $transporteTipo->getId(),
            'nombre' =>  $transporteTipo->getNombre(),
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/transporte_tipo/list", methods={"GET"}, name="transporte_tipo_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);
        $transportesTipos = $transporteTipoRepository->findAll();

        $data = [];

        foreach ($transportesTipos as $tipos) {
            $data[] = [
                'id' => $tipos->getId(),
                'nombre' =>  $tipos->getNombre(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/transporte_tipo/list_web", methods={"POST"}, name="transporte_tipo_list_web")
     * @return JsonResponse
     */
    public function listweb(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);

        $transporteTipoTotal = $transporteTipoRepository->findAll();

        $transportesTipos = $transporteTipoRepository->findBy(
            array(),
            array('id' => 'DESC'),
            $limit,
            $offset
        );

        $data = [];

        foreach ($transportesTipos as $tipos) {
            $data[] = [
                'id' => $tipos->getId(),
                'nombre' =>  $tipos->getNombre(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($transporteTipoTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/transporte_tipo/create_with_campos", methods={"POST"}, name="transporte_tipo_create_with_campos")
     * @param Request $request
     * @return JsonResponse
     */
    public function create_with_campos(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $transporteTipo = new TransporteTipo();
        if (isset($data['transporte_tipo']['nombre']) && $data['transporte_tipo']['nombre'] !== '') {
            $transporteTipo->setNombre($data['transporte_tipo']['nombre']);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe ingresar un nombre para tipo de transporte'], 400);
        }

        $entityManager->persist($transporteTipo);
        $entityManager->flush();

        if (isset($data['campos_tipo_transporte']['items'])) {
            if (count($data['campos_tipo_transporte']['items']) > 0) {
                foreach ($data['campos_tipo_transporte']['items'] as $campo_tipo_transporte_aux) {
                    $camposTipoTransporte = new CamposTipotransporte();
                    $camposTipoTransporte->setNombre($campo_tipo_transporte_aux['nombre']);
                    $camposTipoTransporte->setObligatorio($campo_tipo_transporte_aux['obligatorio']);
                    $camposTipoTransporte->setAeropuerto($campo_tipo_transporte_aux['aeropuerto']);
                    $camposTipoTransporte->setTransporteTipo($transporteTipo);
                    $entityManager->persist($camposTipoTransporte);
                    $entityManager->flush();
                }
            }
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El transporte tipo se creo correctamente'], 200);
    }

    /**
     * @Route("/transporte_tipo/create", methods={"POST"}, name="transporte_tipo_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $transporteTipo = new TransporteTipo();
        $transporteTipo->setNombre($data['transporte_tipo']['nombre']);

        $entityManager->persist($transporteTipo);
        $entityManager->flush();

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El transporte tipo se creo correctamente'], 200);
    }

    /**
     * @Route("/transporte_tipo/update/{id}", methods={"PUT", "PATCH"}, name="transporte_tipo_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);
        $transporteTipo = $transporteTipoRepository->find($id);

        if (!$transporteTipo) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el transporte tipo'], 400);
        }

        $transporteTipo->setNombre($data['transporte_tipo']['nombre']);

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El transporte tipo se actualizo correctamente'], 200);
    }

    /**
     * @Route("/transporte_tipo/delete/{id}", methods={"DELETE"}, name="transporte_tipo_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);
        $transporteTipo = $transporteTipoRepository->find($id);

        if (!$transporteTipo) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro de transporte tipo'], 400);
        }

        //CHECK CAMPOS TIPO DE TRANSPORTE
        $camposTipoTransporteRepository = $this->em->getRepository(CamposTipotransporte::class);

        $query = $camposTipoTransporteRepository->createQueryBuilder('tt')
            ->where('tt.transporte_tipo = :id')
            ->setParameter('id', $id)
            ->getQuery();

        $camposTipoTransporte = $query->getResult();

        if ($camposTipoTransporte && count($camposTipoTransporte) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'El transporte tipo se encuentra asociado a un campo tipo transporte, no es posible eliminarla.'], 400);
        }

        //CHECK TRANSPORTE
        $transporteRepository = $this->em->getRepository(Transporte::class);

        $query = $transporteRepository->createQueryBuilder('t')
            ->where('t.transporte_tipo = :id')
            ->setParameter('id', $id)
            ->getQuery();

        $transporte = $query->getResult();

        if ($transporte && count($transporte) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'El transporte tipo se encuentra asociado a un transporte, no es posible eliminarla.'], 400);
        }

        $this->em->remove($transporteTipo);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El transporte tipo se elimino correctamente'], 200);
    }
}
