<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Transporte;
use App\Entity\Servicio;
use App\Entity\Hospedaje;
use App\Entity\Grupo;

class ServicioController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/servicio/get_by_id/{servicioId}", methods={"GET"}, name="servicio_getServicioById")
     * @param int $servicioId
     * @return JsonResponse
     */
    public function getServicioById(int $servicioId): JsonResponse
    {
        $servicioRepository = $this->em->getRepository(Servicio::class);
        $servicio = $servicioRepository->find($servicioId);

        if (!$servicio) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el servicio'], 400);
        }

        $transporte = $servicio->getTransporte();
        $hospedaje = $servicio->getHospedaje();
        $grupo = $servicio->getGrupo();
        $data[] = [
            'id' => $servicio->getId(),
            'cantidad' => $servicio->getCantidad(),
            'numero_booking' => $servicio->getNroBooking(),
            'precio_persona' => $servicio->getPrecioPorPersona(),
            'comentarios' => $servicio->getComentarios(),
            'estado' => $servicio->getEstado(),
            'transporte' => ($transporte !== null) ? [
                'id' => $transporte->getId(),
                'fecha_inicio' => ($transporte->getFechaInicio() !== null) ? $transporte->getFechaInicio()->format('Y-m-d H:i:s') : null,
                'fecha_fin' => ($transporte->getFechaFin() !== null) ? $transporte->getFechaFin()->format('Y-m-d H:i:s') : null,
                'comentarios' => $transporte->getComentarios(),
                'orden' => $transporte->getOrden(),
                'duracion' => $transporte->getDuracion(),
            ] : null,
            'hospedaje' => ($hospedaje !== null) ? [
                'id' => $hospedaje->getId(),
                'nombre' => $hospedaje->getNombre(),
                'fecha_desde' => ($hospedaje->getFechaDesde() !== null) ? $hospedaje->getFechaDesde()->format('Y-m-d H:i:s') : null,
                'fecha_hasta' => ($hospedaje->getFechaHasta() !== null) ? $hospedaje->getFechaHasta()->format('Y-m-d H:i:s') : null,
                'comentarios' => $hospedaje->getComentarios(),
                'habitaciones' => $hospedaje->getHabitaciones(),
                'alojamiento' => $hospedaje->getAlojamiento()->getNombre()
            ] : null,
            'grupo' => ($grupo !== null) ? [
                'id' => $grupo->getId(),
                'nombre' => $grupo->getNombre(),
                'viaje' => $grupo->getViaje()->getNombre()
            ] : null,
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/servicio/list", methods={"GET"}, name="servicio_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $servicioRepository = $this->em->getRepository(Servicio::class);
        $servicios = $servicioRepository->findAll();

        $data = [];

        foreach ($servicios as $servicio) {
            $transporte = $servicio->getTransporte();
            $hospedaje = $servicio->getHospedaje();
            $grupo = $servicio->getGrupo();
            $data[] = [
                'id' => $servicio->getId(),
                'cantidad' => $servicio->getCantidad(),
                'numero_booking' => $servicio->getNroBooking(),
                'precio_persona' => $servicio->getPrecioPorPersona(),
                'comentarios' => $servicio->getComentarios(),
                'estado' => $servicio->getEstado(),
                'transporte' => ($transporte !== null) ? [
                    'id' => $transporte->getId(),
                    'fecha_inicio' => ($transporte->getFechaInicio() !== null) ? $transporte->getFechaInicio()->format('Y-m-d H:i:s') : null,
                    'fecha_fin' => ($transporte->getFechaFin() !== null) ? $transporte->getFechaFin()->format('Y-m-d H:i:s') : null,
                    'comentarios' => $transporte->getComentarios(),
                    'orden' => $transporte->getOrden(),
                    'duracion' => $transporte->getDuracion(),
                ] : null,
                'hospedaje' => ($hospedaje !== null) ? [
                    'id' => $hospedaje->getId(),
                    'nombre' => $hospedaje->getNombre(),
                    'fecha_desde' => ($hospedaje->getFechaDesde() !== null) ? $hospedaje->getFechaDesde()->format('Y-m-d H:i:s') : null,
                    'fecha_hasta' => ($hospedaje->getFechaHasta() !== null) ? $hospedaje->getFechaHasta()->format('Y-m-d H:i:s') : null,
                    'comentarios' => $hospedaje->getComentarios(),
                    'habitaciones' => $hospedaje->getHabitaciones(),
                    'alojamiento' => $hospedaje->getAlojamiento()->getNombre()
                ] : null,
                'grupo' => ($grupo !== null) ? [
                    'id' => $grupo->getId(),
                    'nombre' => $grupo->getNombre(),
                    'viaje' => $grupo->getViaje()->getNombre()
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/servicio/list_web", methods={"POST"}, name="servicio_list_web")
     * @return JsonResponse
     */
    public function list_web(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $servicioRepository = $this->em->getRepository(Servicio::class);

        if(isset($data['numero_booking'])){
            $servicioTotal = $servicioRepository->findBy(['nro_booking' => $data['numero_booking']]);

            $servicios = $servicioRepository->findBy(
                array('nro_booking' => $data['numero_booking']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else if(isset($data['grupo_id'])){
            $servicioTotal = $servicioRepository->findBy(['grupo' => $data['grupo_id']]);

            $servicios = $servicioRepository->findBy(
                array('grupo' => $data['grupo_id']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else if(isset($data['tipo'])){
            if($data['tipo'] == 'hospedaje'){
                $servicioTotal = $servicioRepository->getTodosServiciosByHospedaje();
                $servicios = $servicioRepository->getServiciosByHospedaje($offset, $limit);
            }
            else if($data['tipo'] == 'transporte'){
                $servicioTotal = $servicioRepository->getTodosServiciosByTransporte();
                $servicios = $servicioRepository->getServiciosByTransporte($offset, $limit);
            }
        }
        else{
            $servicioTotal = $servicioRepository->findAll();

            $servicios = $servicioRepository->findBy(
                array(),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        
        $data = [];

        foreach ($servicios as $servicio) {
            $transporte = $servicio->getTransporte();
            $hospedaje = $servicio->getHospedaje();
            $grupo = $servicio->getGrupo();
            if ($transporte !== null) {
                $trayecto = $transporte->getTrayecto();
            } else {
                $trayecto = null;
            }


            $data[] = [
                'id' => $servicio->getId(),
                'cantidad' => $servicio->getCantidad(),
                'numero_booking' => $servicio->getNroBooking(),
                'precio_persona' => $servicio->getPrecioPorPersona(),
                'comentarios' => $servicio->getComentarios(),
                'estado' => $servicio->getEstado(),
                'transporte' => ($transporte !== null) ? [
                    'id' => $transporte->getId(),
                    'fecha_inicio' => ($transporte->getFechaInicio() !== null) ? $transporte->getFechaInicio()->format('Y-m-d H:i:s') : null,
                    'fecha_fin' => ($transporte->getFechaFin() !== null) ? $transporte->getFechaFin()->format('Y-m-d H:i:s') : null,
                    'comentarios' => $transporte->getComentarios(),
                    'orden' => $transporte->getOrden(),
                    'duracion' => $transporte->getDuracion(),
                    'trayecto' => ($trayecto !== null) ? [
                        'id' => $trayecto->getId(),
                        'ciudad_inicio' => $trayecto->getCiudadInicio()->getNombre(),
                        'pais_inicio' => $trayecto->getCiudadInicio()->getPais()->getNombre(),
                        'ciudad_fin' => $trayecto->getCiudadFin()->getNombre(),
                        'pais_fin' => $trayecto->getCiudadFin()->getPais()->getNombre(),
                    ] : null,
                ] : null,
                'hospedaje' => ($hospedaje !== null) ? [
                    'id' => $hospedaje->getId(),
                    'nombre' => $hospedaje->getNombre(),
                    'fecha_desde' => ($hospedaje->getFechaDesde() !== null) ? $hospedaje->getFechaDesde()->format('Y-m-d H:i:s') : null,
                    'fecha_hasta' => ($hospedaje->getFechaHasta() !== null) ? $hospedaje->getFechaHasta()->format('Y-m-d H:i:s') : null,
                    'comentarios' => $hospedaje->getComentarios(),
                    'habitaciones' => $hospedaje->getHabitaciones(),
                    'alojamiento' => $hospedaje->getAlojamiento()->getNombre()
                ] : null,
                'grupo' => ($grupo !== null) ? [
                    'id' => $grupo->getId(),
                    'nombre' => $grupo->getNombre(),
                    'viaje' => $grupo->getViaje()->getNombre()
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($servicioTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/servicio/create", methods={"POST"}, name="servicio_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $servicio = new Servicio();
        $servicio->setCantidad($data['servicio']['cantidad']);
        $servicio->setNroBooking($data['servicio']['numero_booking']);
        $servicio->setPrecioPorPersona($data['servicio']['precio_persona']);
        $servicio->setComentarios($data['servicio']['comentarios']);
        $servicio->setEstado($data['servicio']['estado']);
        if (isset($data['servicio']['transporte_id'])) {
            $transporteRepository = $this->em->getRepository(Transporte::class);
            $transporte = $transporteRepository->find($data['servicio']['transporte_id']);
            $servicio->setTransporte($transporte);
        }
        if (isset($data['servicio']['hospedaje_id'])) {
            $hospedajeRepository = $this->em->getRepository(Hospedaje::class);
            $hospedaje = $hospedajeRepository->find($data['servicio']['hospedaje_id']);
            $servicio->setHospedaje($hospedaje);
        }
        $grupoRepository = $this->em->getRepository(Grupo::class);
        $grupo = $grupoRepository->find($data['servicio']['grupo_id']);
        $servicio->setGrupo($grupo);

        $entityManager->persist($servicio);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El servicio se creo correctamente'], 200);
    }

    /**
     * @Route("/servicio/update/{id}", methods={"PUT", "PATCH"}, name="servicio_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $servicioRepository = $this->em->getRepository(Servicio::class);
        $servicio = $servicioRepository->find($id);

        if (!$servicio) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el servicio'], 400);
        }

        $servicio->setCantidad($data['servicio']['cantidad']);
        $servicio->setNroBooking($data['servicio']['numero_booking']);
        $servicio->setPrecioPorPersona($data['servicio']['precio_persona']);
        $servicio->setComentarios($data['servicio']['comentarios']);
        $servicio->setEstado($data['servicio']['estado']);
        $transporteRepository = $this->em->getRepository(Transporte::class);
        $transporte = $transporteRepository->find($data['servicio']['transporte_id']);
        $servicio->setTransporte($transporte);
        $hospedajeRepository = $this->em->getRepository(Hospedaje::class);
        $hospedaje = $hospedajeRepository->find($data['servicio']['hospedaje_id']);
        $servicio->setHospedaje($hospedaje);
        $grupoRepository = $this->em->getRepository(Grupo::class);
        $grupo = $grupoRepository->find($data['servicio']['grupo_id']);
        $servicio->setGrupo($grupo);

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El servicio se actualizo correctamente'], 200);
    }

    /**
     * @Route("/servicio/delete/{id}", methods={"DELETE"}, name="servicio_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $servicioRepository = $this->em->getRepository(Servicio::class);
        $servicio = $servicioRepository->find($id);

        if (!$servicio) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el servicio'], 400);
        }

        $this->em->remove($servicio);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El servicio se elimino correctamente'], 200);
    }

    /**
     * @Route("/servicio/get_by_transporte_id/{transporteId}", methods={"GET"}, name="servicio_getByTransporteId")
     * @param int $transporteId
     * @return JsonResponse
     */
    public function getServicioByTransporteId(int $transporteId): JsonResponse
    {
        $servicioRepository = $this->em->getRepository(Servicio::class);
        $servicios = $servicioRepository->findBy(['transporte' => $transporteId]);

        if (!$servicios) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el servicio'], 400);
        }


        $data = [];
        foreach($servicios as $servicio){
            $transporte = $servicio->getTransporte();
            $hospedaje = $servicio->getHospedaje();
            $grupo = $servicio->getGrupo();
            $data[] = [
                'id' => $servicio->getId(),
                'cantidad' => $servicio->getCantidad(),
                'numero_booking' => $servicio->getNroBooking(),
                'precio_persona' => $servicio->getPrecioPorPersona(),
                'comentarios' => $servicio->getComentarios(),
                'estado' => $servicio->getEstado(),
                'transporte' => ($transporte !== null) ? [
                    'id' => $transporte->getId(),
                    'fecha_inicio' => ($transporte->getFechaInicio() !== null) ? $transporte->getFechaInicio()->format('Y-m-d') : null,
                    'fecha_fin' => ($transporte->getFechaFin() !== null) ? $transporte->getFechaFin()->format('Y-m-d') : null,
                    'nombre' => $transporte->getNombre(),
                    'hora_inicio' => $transporte->getHoraInicio(),
                    'hora_fin' => $transporte->getHoraFin(),
                    'comentarios' => $transporte->getComentarios(),
                    'orden' => $transporte->getOrden(),
                    'duracion' => $transporte->getDuracion(),
                ] : null,
                'hospedaje' => ($hospedaje !== null) ? [
                    'id' => $hospedaje->getId(),
                    'nombre' => $hospedaje->getNombre(),
                    'fecha_desde' => ($hospedaje->getFechaDesde() !== null) ? $hospedaje->getFechaDesde()->format('Y-m-d H:i:s') : null,
                    'fecha_hasta' => ($hospedaje->getFechaHasta() !== null) ? $hospedaje->getFechaHasta()->format('Y-m-d H:i:s') : null,
                    'comentarios' => $hospedaje->getComentarios(),
                    'habitaciones' => $hospedaje->getHabitaciones(),
                    'alojamiento' => $hospedaje->getAlojamiento()->getNombre()
                ] : null,
                'grupo' => ($grupo !== null) ? [
                    'id' => $grupo->getId(),
                    'nombre' => $grupo->getNombre(),
                    'viaje' => $grupo->getViaje()->getNombre()
                ] : null,
            ];
        }
        
        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }
}
