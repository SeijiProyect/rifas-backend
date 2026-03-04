<?php

namespace App\Controller;

use App\Entity\Aereopuerto;
use App\Entity\CamposTipotransporte;
use App\Entity\DatosTipoTransporte;
use App\Entity\Transporte;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class DatosTipoTransporteController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_id/{datoTipoId}", methods={"GET"}, name="datos_tipo_transporte_getDatoTransporteById")
     * @param int $ciudadCamposId
     * @return JsonResponse
     */
    public function getDatoTransporteById(int $datoTipoId): JsonResponse
    {
        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);
        $datoTipoTransporte = $datosTipoTransporteRepository->find($datoTipoId);

        if (!$datoTipoTransporte) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el registro de ciudad campo'], 400);
        }

        $transporte = $datoTipoTransporte->getTransporte();
        $campoTipoTransporte = $datoTipoTransporte->getCamposTipoTransporte();
        $aeropuerto = $datoTipoTransporte->getAereopuerto();

        $data = [
            'id' => $datoTipoTransporte->getId(),
            'valor' => $datoTipoTransporte->getValor(),
            'transporte' => ($transporte !== null) ? [
                'id' => $transporte->getId(),
                'trayecto' => $transporte->getTrayecto()->getCiudadInicio()->getNombre() . ' - ' . $transporte->getTrayecto()->getCiudadFin()->getNombre(),
                'tipo' => $transporte->getTransporteTipo()->getNombre(),
                'fecha_inicio' => $transporte->getFechaInicio(),
                'fecha_fin' => $transporte->getFechaFin(),
                'duracion' => $transporte->getDuracion()
            ] : null,
            'campo_tipo_transporte' => ($campoTipoTransporte !== null) ? [
                'id' => $campoTipoTransporte->getId(),
                'nombre' => $campoTipoTransporte->getNombre(),
            ] : null,
            'aeropuerto' => ($aeropuerto !== null) ? [
                'id' => $aeropuerto->getId(),
                'nombre' => $aeropuerto->getNombre(),
                'ciudad' => $aeropuerto->getCiudad()->getNombre()
            ] : null,
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_transporte_id/{transporteId}", methods={"GET"}, name="datos_tipo_transporte_getDatoTransporteByTransporteId")
     * @param int $transporteId
     * @return JsonResponse
     */
    public function getDatoTransporteByTransporteId(int $transporteId): JsonResponse
    {
        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);
        $datoTipoTransporte = $datosTipoTransporteRepository->findByTransporte($transporteId);

        if (!$datoTipoTransporte) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el registro del transporte dato'], 400);
        }

        $data = [];
       
        foreach ($datoTipoTransporte as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        /*$data = [
            'id' => $datoTipoTransporte->getId(),
            'valor' => $datoTipoTransporte->getValor(),
            'transporte' => ($transporte !== null) ? [
                'id' => $transporte->getId(),
                'trayecto' => $transporte->getTrayecto()->getCiudadInicio()->getNombre() . ' - ' . $transporte->getTrayecto()->getCiudadFin()->getNombre(),
                'tipo' => $transporte->getTransporteTipo()->getNombre(),
                'fecha_inicio' => $transporte->getFechaInicio(),
                'fecha_fin' => $transporte->getFechaFin(),
                'duracion' => $transporte->getDuracion()
            ] : null,
            'campo_tipo_transporte' => ($campoTipoTransporte !== null) ? [
                'id' => $campoTipoTransporte->getId(),
                'nombre' => $campoTipoTransporte->getNombre(),
            ] : null,
            'aeropuerto' => ($aeropuerto !== null) ? [
                'id' => $aeropuerto->getId(),
                'nombre' => $aeropuerto->getNombre(),
                'ciudad' => $aeropuerto->getCiudad()->getNombre()
            ] : null,
        ];*/

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/list", methods={"GET"}, name="datos_tipo_transporte_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);
        $datosTipoTransporte = $datosTipoTransporteRepository->findAll();

        if (!$datosTipoTransporte) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro ningun registro de datos tipo transporte'], 400);
        }

        $data = [];

        foreach ($datosTipoTransporte as $datoTipoTransporte) {
            $transporte = $datoTipoTransporte->getTransporte();
            $campoTipoTransporte = $datoTipoTransporte->getCamposTipoTransporte();
            $aeropuerto = $datoTipoTransporte->getAereopuerto();

            $data[] = [
                'id' => $datoTipoTransporte->getId(),
                'valor' => $datoTipoTransporte->getValor(),
                'transporte' => ($transporte !== null) ? [
                    'id' => $transporte->getId(),
                    'trayecto' => $transporte->getTrayecto()->getCiudadInicio()->getNombre() . ' - ' . $transporte->getTrayecto()->getCiudadFin()->getNombre(),
                    'tipo' => $transporte->getTransporteTipo()->getNombre(),
                    'fecha_inicio' => $transporte->getFechaInicio(),
                    'fecha_fin' => $transporte->getFechaFin(),
                    'duracion' => $transporte->getDuracion()
                ] : null,
                'campo_tipo_transporte' => ($campoTipoTransporte !== null) ? [
                    'id' => $campoTipoTransporte->getId(),
                    'nombre' => $campoTipoTransporte->getNombre(),
                ] : null,
                'aeropuerto' => ($aeropuerto !== null) ? [
                    'id' => $aeropuerto->getId(),
                    'nombre' => $aeropuerto->getNombre(),
                    'ciudad' => $aeropuerto->getCiudad()->getNombre()
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/list_web", methods={"POST"}, name="datos_tipo_transporte_list_web")
     * @return JsonResponse
     */
    public function listweb(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);
        
        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporte = $datosTipoTransporteRepository->findBy(
            array(),
            array('id' => 'DESC'),
            $limit,
            $offset
        );

        $data = [];

        foreach ($datosTipoTransporte as $datoTipoTransporte) {
            $transporte = $datoTipoTransporte->getTransporte();
            $campoTipoTransporte = $datoTipoTransporte->getCamposTipoTransporte();
            $aeropuerto = $datoTipoTransporte->getAereopuerto();

            $data[] = [
                'id' => $datoTipoTransporte->getId(),
                'valor' => $datoTipoTransporte->getValor(),
                'transporte' => ($transporte !== null) ? [
                    'id' => $transporte->getId(),
                    'trayecto' => $transporte->getTrayecto()->getCiudadInicio()->getNombre() . ' - ' . $transporte->getTrayecto()->getCiudadFin()->getNombre(),
                    'tipo' => $transporte->getTransporteTipo()->getNombre(),
                    'fecha_inicio' => $transporte->getFechaInicio(),
                    'fecha_fin' => $transporte->getFechaFin(),
                    'duracion' => $transporte->getDuracion()
                ] : null,
                'campo_tipo_transporte' => ($campoTipoTransporte !== null) ? [
                    'id' => $campoTipoTransporte->getId(),
                    'nombre' => $campoTipoTransporte->getNombre(),
                ] : null,
                'aeropuerto' => ($aeropuerto !== null) ? [
                    'id' => $aeropuerto->getId(),
                    'nombre' => $aeropuerto->getNombre(),
                    'ciudad' => $aeropuerto->getCiudad()->getNombre()
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/create", methods={"POST"}, name="datos_tipo_transporte_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $datosTipoTransporte = new DatosTipotransporte();
        $transporteRepository = $this->em->getRepository(Transporte::class);
        $transporte = $transporteRepository->find($data['datos_tipo_transporte']['transporte_id']);
        $datosTipoTransporte->setTransporte($transporte);
        $camposTipoTransporteRepository = $this->em->getRepository(CamposTipotransporte::class);
        $campoTipoTransporte = $camposTipoTransporteRepository->find($data['datos_tipo_transporte']['campo_tipo_transporte_id']);
        $datosTipoTransporte->setCamposTipoTransporte($campoTipoTransporte);
        $aeropuertoRepository = $this->em->getRepository(Aereopuerto::class);
        $aeropuerto = $aeropuertoRepository->find($data['datos_tipo_transporte']['aeropuerto_id']);
        $datosTipoTransporte->setAereopuerto($aeropuerto);
        $datosTipoTransporte->setValor($data['datos_tipo_transporte']['valor']);

        $entityManager->persist($datosTipoTransporte);
        $entityManager->flush();

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El dato tipo trasnporte se creo correctamente'], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/update/{id}", methods={"PUT", "PATCH"}, name="datos_tipo_transporte_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);
        $datosTipoTransporte = $datosTipoTransporteRepository->find($id);

        if (!$datosTipoTransporte) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el registro datos tipo de transporte'], 400);
        }

        $transporteRepository = $this->em->getRepository(Transporte::class);
        $transporte = $transporteRepository->find($data['datos_tipo_transporte']['transporte_id']);
        $datosTipoTransporte->setTransporte($transporte);
        $camposTipoTransporteRepository = $this->em->getRepository(CamposTipotransporte::class);
        $campoTipoTransporte = $camposTipoTransporteRepository->find($data['datos_tipo_transporte']['campo_tipo_transporte_id']);
        $datosTipoTransporte->setCamposTipoTransporte($campoTipoTransporte);
        $aeropuertoRepository = $this->em->getRepository(Aereopuerto::class);
        $aeropuerto = $aeropuertoRepository->find($data['datos_tipo_transporte']['aeropuerto_id']);
        $datosTipoTransporte->setAereopuerto($aeropuerto);
        $datosTipoTransporte->setValor($data['datos_tipo_transporte']['valor']);

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El dato tipo de transporte se actualizo correctamente'], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/delete/{id}", methods={"DELETE"}, name="datos_tipo_transporte_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);
        $datosTipoTransporte = $datosTipoTransporteRepository->find($id);

        if (!$datosTipoTransporte) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro de datos tipo transporte'], 400);
        }

        $this->em->remove($datosTipoTransporte);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El registro de datos tipo transporte se elimino correctamente'], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_campos", methods={"POST"}, name="datos_tipo_transporte_getCamposByTransported")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByTransporte(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $campo = (isset($data['campo'])) ? $data['campo'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByCampo($campo, $offset, $limit);

        $data = [];
       
        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_tipo", methods={"POST"}, name="datos_tipo_transporte_getCamposByTipo")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByTipo(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $tipo = (isset($data['tipo'])) ? $data['tipo'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByTipo($tipo, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_aeropuerto", methods={"POST"}, name="datos_tipo_transporte_getCamposByAeropuerto")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByAeropuerto(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $aeropuerto = (isset($data['aeropuerto'])) ? $data['aeropuerto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByAeropuerto($aeropuerto, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_termino", methods={"POST"}, name="datos_tipo_transporte_getCamposByTermino")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByTermino(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByTermino($termino, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_campoaero", methods={"POST"}, name="datos_tipo_transporte_getCamposByCampoAero")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByCampoAero(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $campo = (isset($data['campo'])) ? $data['campo'] : null;
        $aeropuerto = (isset($data['aeropuerto'])) ? $data['aeropuerto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByCampoAero($campo, $aeropuerto, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_campotipo", methods={"POST"}, name="datos_tipo_transporte_getCamposByCampoTipo")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByCampoTipo(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $campo = (isset($data['campo'])) ? $data['campo'] : null;
        $tipo = (isset($data['tipo'])) ? $data['tipo'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByCampoTipo($campo, $tipo, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_aerotipo", methods={"POST"}, name="datos_tipo_transporte_getCamposByAeroTipo")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByAeroTipo(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $tipo = (isset($data['tipo'])) ? $data['tipo'] : null;
        $aeropuerto = (isset($data['aeropuerto'])) ? $data['aeropuerto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByAeroTipo($tipo, $aeropuerto, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_select", methods={"POST"}, name="datos_tipo_transporte_getCamposBySelect")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposBySelect(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $campo = (isset($data['campo'])) ? $data['campo'] : null;
        $tipo = (isset($data['tipo'])) ? $data['tipo'] : null;
        $aeropuerto = (isset($data['aeropuerto'])) ? $data['aeropuerto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findBySelect($campo, $tipo, $aeropuerto, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_terminocampo", methods={"POST"}, name="datos_tipo_transporte_getCamposByTerminoCampo")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByTerminoCampo(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $campo = (isset($data['campo'])) ? $data['campo'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByTerminoCampo($termino, $campo, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_terminotipo", methods={"POST"}, name="datos_tipo_transporte_getCamposByTerminoTipo")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByTerminoTipo(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $tipo = (isset($data['tipo'])) ? $data['tipo'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByTerminoTipo($termino, $tipo, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_terminoaero", methods={"POST"}, name="datos_tipo_transporte_getCamposByTerminoAero")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByTerminoAero(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $aeropuerto = (isset($data['aeropuerto'])) ? $data['aeropuerto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByTerminoAero($termino, $aeropuerto, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/datos_tipo_transporte/get_by_all", methods={"POST"}, name="datos_tipo_transporte_getCamposByAll")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCamposByAll(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $campo = (isset($data['campo'])) ? $data['campo'] : null;
        $tipo = (isset($data['tipo'])) ? $data['tipo'] : null;
        $aeropuerto = (isset($data['aeropuerto'])) ? $data['aeropuerto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $datosTipoTransporteRepository = $this->em->getRepository(DatosTipotransporte::class);

        $datosTipoTransporteTotal = $datosTipoTransporteRepository->findAll();

        $datosTipoTransporteResult = $datosTipoTransporteRepository->findByAll($termino, $campo, $tipo, $aeropuerto, $offset, $limit);

        $data = [];

        foreach ($datosTipoTransporteResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'valor' => $datosTipo["valor"],
                'transporte' => [
                    'id' => $datosTipo["transporte_id"],
                    'trayecto' => $datosTipo["ciudad_inicio"] . ' - ' . $datosTipo["ciudad_fin"],
                    'tipo' => $datosTipo["transporte_tipo"],
                ],
                'campo_tipo_transporte' => [
                    'id' => $datosTipo["campo_tipo_id"],
                    'nombre' => $datosTipo["campo_tipo_nombre"],
                ],
                'aeropuerto' => [
                    'id' => $datosTipo["aeropuerto_id"],
                    'nombre' => $datosTipo["aeropuerto_nombre"],
                ],
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($datosTipoTransporteTotal), 'data' => $data], 200);
    }
}
