<?php

namespace App\Controller;

use App\Entity\CamposTipotransporte;
use App\Entity\Ciudad;
use App\Entity\CiudadCampos;
use App\Entity\DatosTipoTransporte;
use App\Entity\TransporteTipo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CamposTipoTransporteController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/campos_tipo_transporte/get_by_id/{camposTipoTransporteId}", methods={"GET"}, name="campos_tipo_transporte_getCamposTipoTransporteById")
     * @param int $camposTipoTransporteId
     * @return JsonResponse
     */
    public function getCamposTipoTransporteById(int $camposTipoTransporteId): JsonResponse
    {
        $camposTipoTransporteRepository = $this->em->getRepository(CamposTipotransporte::class);
        $camposTipoTransporte = $camposTipoTransporteRepository->find($camposTipoTransporteId);

        if (!$camposTipoTransporte) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el registro de campos tipo transporte'], 400);
        }

        $transporteTipo = $camposTipoTransporte->getTransporteTipo();

        $data = [
            'id' => $camposTipoTransporte->getId(),
            'nombre' => $camposTipoTransporte->getNombre(),
            'obligatorio' => $camposTipoTransporte->isObligatorio(),
            'aeropuerto' => $camposTipoTransporte->isAeropuerto(),
            'transporte_tipo' => ($transporteTipo !== null) ? [
                'id' => $transporteTipo->getId(),
                'nombre' => $transporteTipo->getNombre(),
            ] : null,
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/campos_tipo_transporte/get_by_transporte_tipo_id/{transporteTipoId}", methods={"GET"}, name="campos_tipo_transporte_getCamposTipoTransporteByTransporteTipoId")
     * @param int $transporteTipoId
     * @return JsonResponse
     */
    public function getCamposTipoTransporteByTransporteTipoId(int $transporteTipoId): JsonResponse
    {
        $camposTipoTransporteRepository = $this->em->getRepository(CamposTipotransporte::class);
        $camposTipoTransporte = $camposTipoTransporteRepository->findBy(['transporte_tipo' => $transporteTipoId]);

        if (!$camposTipoTransporte) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontraron registros para este tipo de transporte'], 400);
        }

        $data = [];

        foreach ($camposTipoTransporte as $campoTipoTransporte) {
            $transporteTipo = $campoTipoTransporte->getTransporteTipo();

            $data[] = [
                'id' => $campoTipoTransporte->getId(),
                'nombre' => $campoTipoTransporte->getNombre(),
                'obligatorio' => $campoTipoTransporte->isObligatorio(),
                'aeropuerto' => $campoTipoTransporte->isAeropuerto(),
                'transporte_tipo' => ($transporteTipo !== null) ? [
                    'id' => $transporteTipo->getId(),
                    'nombre' => $transporteTipo->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/campos_tipo_transporte/list", methods={"GET"}, name="campos_tipo_transporte_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $camposTipoTransporteRepository = $this->em->getRepository(CamposTipotransporte::class);
        $camposTipoTransporte = $camposTipoTransporteRepository->findAll();

        if (!$camposTipoTransporte) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro ningun registro de campos tipo transporte'], 400);
        }

        $data = [];

        foreach ($camposTipoTransporte as $campoTipoTransporte) {
            $transporteTipo = $campoTipoTransporte->getTransporteTipo();

            $data[] = [
                'id' => $campoTipoTransporte->getId(),
                'nombre' => $campoTipoTransporte->getNombre(),
                'obligatorio' => $campoTipoTransporte->isObligatorio(),
                'aeropuerto' => $campoTipoTransporte->isAeropuerto(),
                'transporte_tipo' => ($transporteTipo !== null) ? [
                    'id' => $transporteTipo->getId(),
                    'nombre' => $transporteTipo->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/campos_tipo_transporte/create", methods={"POST"}, name="campos_tipo_transporte_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $camposTipoTransporte = new CamposTipotransporte();
        $camposTipoTransporte->setNombre($data['campos_tipo_transporte']['nombre']);
        $camposTipoTransporte->setObligatorio($data['campos_tipo_transporte']['obligatorio']);
        $camposTipoTransporte->setAeropuerto($data['campos_tipo_transporte']['aeropuerto']);
        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);
        $transporteTipo = $transporteTipoRepository->find($data['campos_tipo_transporte']['transporte_tipo_id']);
        $camposTipoTransporte->setTransporteTipo($transporteTipo);

        $entityManager->persist($camposTipoTransporte);
        $entityManager->flush();

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El campo tipo trasnporte se creo correctamente'], 200);
    }

    /**
     * @Route("/campos_tipo_transporte/update/{id}", methods={"PUT", "PATCH"}, name="campos_tipo_transporte_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $camposTipoTransporteRepository = $this->em->getRepository(CamposTipotransporte::class);
        $camposTipoTransporte = $camposTipoTransporteRepository->find($id);

        if (!$camposTipoTransporte) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el registro campos tipo de transporte'], 400);
        }

        $camposTipoTransporte->setNombre($data['campos_tipo_transporte']['nombre']);
        $camposTipoTransporte->setObligatorio($data['campos_tipo_transporte']['obligatorio']);
        $camposTipoTransporte->setAeropuerto($data['campos_tipo_transporte']['aeropuerto']);
        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);
        $transporteTipo = $transporteTipoRepository->find($data['campos_tipo_transporte']['transporte_tipo_id']);
        $camposTipoTransporte->setTransporteTipo($transporteTipo);

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El campo tipo de transporte se actualizo correctamente'], 200);
    }

    /**
     * @Route("/campos_tipo_transporte/delete/{id}", methods={"DELETE"}, name="campos_tipo_transporte_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $camposTipoTransporteRepository = $this->em->getRepository(CamposTipotransporte::class);
        $camposTipoTransporte = $camposTipoTransporteRepository->find($id);

        if (!$camposTipoTransporte) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro de campos tipo transporte'], 400);
        }

        //CHECK CIUDAD CAMPOS
        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipoTransporte::class);

        $query = $datosTipoTransporteRepository->createQueryBuilder('dtt')
            ->where('dtt.campos_tipo_transporte = :campos_tipo_transporte_id')
            ->setParameter('campos_tipo_transporte_id', $id)
            ->getQuery();

        $datosTipoTransporte = $query->getResult();

        if ($datosTipoTransporte && count($datosTipoTransporte) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'El campo tipo de transporte se encuentra asignado a datos tipo de transporte, no es posible eliminarla.'], 400);
        }

        $this->em->remove($camposTipoTransporte);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El registro de ciudad campo se elimino correctamente'], 200);
    }
}
