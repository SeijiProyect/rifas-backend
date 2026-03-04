<?php

namespace App\Controller;

use App\Entity\DatosTipoTransporte;
use App\Entity\Itinerario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\JwtAuthenticator;
use App\Entity\Persona;
use App\Entity\Pasajero;
use App\Entity\ItinerarioDetalle;
use App\Entity\Transporte;
use App\Entity\Ciudad;
use App\Entity\PasajeroServicio;
use App\Entity\Servicio;
use App\Entity\Trayecto;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use DateTimeInterface;
use DateTimeImmutable;

class ItinerarioDetalleController extends AbstractController
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/itinerario_detalle/get_detalle", name="getItinerarioDetalle", methods={"GET"})
     */
    public function getItinerarioDetalle(Request $request, JwtAuthenticator $jwtAutheticator)
    {
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                $personaRepository = $this->em->getRepository(Persona::class);
                $persona = $personaRepository->getPersonaById($id);
                if ($persona) {
                    // Obtener el itinerario
                    $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
                    $itinerarioId = $pasajeroRepository->findByPersonaItinerario($persona);
                    $itinerarioDetalles = $itinerarioDetalleRepository->findBy(['itinerario' => $itinerarioId], ['orden' => 'ASC']);

                    $res = [];

                    foreach ($itinerarioDetalles as $itinerarioDetalle) {
                        $ciudad = $itinerarioDetalle->getCiudad();
                        $trayecto = $itinerarioDetalle->getTrayecto();
                        if ($trayecto !== null) {
                            $trayectoId = $trayecto->getId();
                            $transporteRepository = $this->em->getRepository(Transporte::class);
                            $transportes = $transporteRepository->findBy(['trayecto_padre' => $trayectoId], ['orden' => 'ASC']);
                            $dataTransporte = [];
                            if ($transportes !== null) {
                                foreach ($transportes as $t) {
                                    //si hay transporte, busco los servicios
                                    $transporteId = $t->getId();
                                    $servicioRepository = $this->em->getRepository(Servicio::class);
                                    $servicio = $servicioRepository->findBy(['transporte' => $transporteId]);

                                    if ($servicio !== null && count($servicio) > 0) {
                                        $pasajeroServicioRepository = $this->em->getRepository(PasajeroServicio::class);
                                        $pasajeroServicio = $pasajeroServicioRepository->findBy([
                                            'pasajero' => $pasajeroId,
                                            'servicio' => $servicio[0]->getId()
                                        ]);

                                        if ($pasajeroServicio !== null && count($pasajeroServicio) > 0) {
                                            //si hay servicios devuelvo eso
                                            $datosTransporteTipoRepository = $this->em->getRepository(DatosTipoTransporte::class);
                                            $datosTransporteTipo = $datosTransporteTipoRepository->getTransporteTipoByTransporteId($transporteId);

                                            $dataDatosTransporteTipo = [];
                                            $aeropuerto_inicio = '';
                                            $aeropuerto_fin = '';
                                            $estado_texto = '';
                                            $estado_color = '';
                                            if ($datosTransporteTipo !== null) {
                                                foreach ($datosTransporteTipo as $dtt) {
                                                    $campoTipoTransporte = $dtt->getCamposTipoTransporte();
                                                    if ($campoTipoTransporte->getNombre() == 'Aeropuerto de Llegada') {
                                                        $aeropuerto_fin = $dtt->getAereopuerto()->getNombre();
                                                    } else if ($campoTipoTransporte->getNombre() == 'Aeropuerto de Salida') {
                                                        $aeropuerto_inicio = $dtt->getAereopuerto()->getNombre();
                                                    } else if ($campoTipoTransporte->getNombre() == 'Estado Texto') {
                                                        $aeropuerto_inicio = $dtt->getValor();
                                                    } else if ($campoTipoTransporte->getNombre() == 'Estado Color') {
                                                        $aeropuerto_inicio = $dtt->getValor();
                                                    } else {
                                                        $dataDatosTransporteTipo[] = [
                                                            'id' => $dtt->getId(),
                                                            'nombre_campo' => $campoTipoTransporte->getNombre(),
                                                            'valor_campo' => $dtt->getValor(),
                                                            'aereopuerto' => $campoTipoTransporte->isAeropuerto()
                                                        ];
                                                    }
                                                }
                                            }
                                            $dataTransporte[] = [
                                                'id' => $t->getId(),
                                                'fecha_inicio' => ($t->getFechaInicio() !== null) ? $t->getFechaInicio()->format('Y-m-d H:i:s') : null,
                                                'fecha_fin' => ($t->getFechaFin() !== null) ? $t->getFechaFin()->format('Y-m-d H:i:s') : null,
                                                'aeropuerto_inicio' => $aeropuerto_inicio,
                                                'aeropuerto_fin' => $aeropuerto_fin,
                                                'duracion' => $t->getDuracion(),
                                                'orden' => $t->getOrden(),
                                                'trayecto' => [
                                                    'id' => $t->getTrayecto()->getId(),
                                                    'ciudad_inicio' => $t->getTrayecto()->getCiudadInicio()->getNombre(),
                                                    'pais_inicio' => $t->getTrayecto()->getCiudadInicio()->getPais()->getNombre(),
                                                    'ciudad_fin' => $t->getTrayecto()->getCiudadFin()->getNombre(),
                                                    'pais_fin' => $t->getTrayecto()->getCiudadFin()->getPais()->getNombre(),
                                                    'estado_texto' => $estado_texto,
                                                    'estado_color' => $estado_color
                                                ],
                                                'tipo_transporte' => [
                                                    'id' => $t->getTransporteTipo()->getId(),
                                                    'nombre' => $t->getTransporteTipo()->getNombre(),
                                                ],
                                                'datos_transporte_tipo' => $dataDatosTransporteTipo
                                            ];
                                        }
                                    }
                                }
                            }

                            $trayecto = [
                                'id' => $trayectoId,
                                'ciudad_inicio' => $trayecto->getCiudadInicio()->getNombre(),
                                'pais_inicio' => $trayecto->getCiudadInicio()->getPais()->getNombre(),
                                'ciudad_fin' => $trayecto->getCiudadFin()->getNombre(),
                                'pais_fin' => $trayecto->getCiudadFin()->getPais()->getNombre(),
                                'transportes' => $dataTransporte
                            ];
                        }
                        $res[] = [
                            'itinerario_detalle' => [
                                'id' => $itinerarioDetalle->getId(),
                                'fecha_inicio' => ($itinerarioDetalle->getFechaInicio() !== null) ? $itinerarioDetalle->getFechaInicio()->format('Y-m-d H:i:s') : null,
                                'fecha_fin' => ($itinerarioDetalle->getFechaFin() !== null) ? $itinerarioDetalle->getFechaFin()->format('Y-m-d H:i:s') : null,
                                'orden' => $itinerarioDetalle->getOrden(),
                            ],
                            'ciudad' => ($ciudad !== null) ? [
                                'id' => $ciudad->getId(),
                                'nombre' => $ciudad->getNombre(),
                                'pais_nombre' => $ciudad->getPais()->getNombre(),
                                'nombre_ingles' => $ciudad->getNombreIngles(),
                                'image_src' => $ciudad->getImageSrc()
                            ] : null,
                            'trayecto' => $trayecto
                        ];
                    }
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Itinerario detalle encontrado', 'data' => $res], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Ha ocurrido un error de autenticación.'], 400);
        }
    }

    /**
     * @Route("/itinerario_detalle/get_by_id/{itinerarioDetalleId}", methods={"GET"}, name="itinerario_detalle_getItinerarioDetalleById")
     * @param int $itinerarioDetalleId
     * @return JsonResponse
     */
    public function getItinerarioDetalleById(int $itinerarioDetalleId): JsonResponse
    {
        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
        $itinerarioDetalle = $itinerarioDetalleRepository->find($itinerarioDetalleId);

        if (!$itinerarioDetalle) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el registro de itinerario detalle'], 400);
        }

        $itinerario = $itinerarioDetalle->getItinerario();
        $ciudad = $itinerarioDetalle->getCiudad();
        $trayecto = $itinerarioDetalle->getTrayecto();

        $data = [
            'id' => $itinerarioDetalle->getId(),
            'fecha_inicio' => ($itinerarioDetalle->getFechaInicio() !== null) ? $itinerarioDetalle->getFechaInicio()->format('Y-m-d') : null,
            'fecha_fin' => ($itinerarioDetalle->getFechaFin() !== null) ? $itinerarioDetalle->getFechaFin()->format('Y-m-d') : null,
            'orden' => $itinerarioDetalle->getOrden(),
            'itinerario' => ($itinerario !== null) ? [
                'id' => $itinerario->getId(),
                'nombre' => $itinerario->getNombre(),
            ] : null,
            'ciudad' => ($ciudad !== null) ? [
                'id' => $ciudad->getId(),
                'nombre' => $ciudad->getNombre(),
            ] : null,
            'trayecto' => ($trayecto !== null) ? [
                'id' => $trayecto->getId(),
                'ciudad_inicio' => $trayecto->getCiudadInicio()->getNombre(),
                'ciudad_fin' => $trayecto->getCiudadFin()->getNombre(),
            ] : null,
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_by_itinerario/{itinerarioId}", methods={"GET"}, name="itinerario_detalle_getItinerarioDetalleByItinerario")
     * @param int $itinerarioId
     * @return JsonResponse
     */
    public function getItinerarioDetalleByItinerario(int $itinerarioId): JsonResponse
    {
        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
        $itinerariosDetalle = $itinerarioDetalleRepository->findBy(['itinerario' => $itinerarioId], ['orden' => 'ASC']);

        if (!$itinerariosDetalle) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontraron registros de itinerario detalle'], 400);
        }

        $data = [];

        foreach ($itinerariosDetalle as $itinerarioDetalle) {
            $itinerario = $itinerarioDetalle->getItinerario();
            $ciudad = $itinerarioDetalle->getCiudad();
            $trayecto = $itinerarioDetalle->getTrayecto();
            $data[] = [
                'id' => $itinerarioDetalle->getId(),
                'fecha_inicio' => ($itinerarioDetalle->getFechaInicio() !== null) ? $itinerarioDetalle->getFechaInicio()->format('Y-m-d') : null,
                'fecha_fin' => ($itinerarioDetalle->getFechaFin() !== null) ? $itinerarioDetalle->getFechaFin()->format('Y-m-d') : null,
                'orden' => $itinerarioDetalle->getOrden(),
                'itinerario' => ($itinerario !== null) ? [
                    'id' => $itinerario->getId(),
                    'nombre' => $itinerario->getNombre(),
                ] : null,
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                ] : null,
                'trayecto' => ($trayecto !== null) ? [
                    'id' => $trayecto->getId(),
                    'ciudad_inicio' => $trayecto->getCiudadInicio()->getNombre(),
                    'ciudad_fin' => $trayecto->getCiudadFin()->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/list_web", methods={"POST"}, name="itinerario_detalle_list_web")
     * @return JsonResponse
     */
    public function listweb(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalle = $itinerarioDetalleRepository->findBy(
            array(),
            array('orden' => 'ASC'),
            $limit,
            $offset
        );

        $data = [];

        foreach ($itinerariosDetalle as $itinerarioDetalle) {
            $itinerario = $itinerarioDetalle->getItinerario();
            $ciudad = $itinerarioDetalle->getCiudad();
            $trayecto = $itinerarioDetalle->getTrayecto();
            $data[] = [
                'id' => $itinerarioDetalle->getId(),
                'fecha_inicio' => ($itinerarioDetalle->getFechaInicio() !== null) ? $itinerarioDetalle->getFechaInicio()->format('Y-m-d') : null,
                'fecha_fin' => ($itinerarioDetalle->getFechaFin() !== null) ? $itinerarioDetalle->getFechaFin()->format('Y-m-d') : null,
                'orden' => $itinerarioDetalle->getOrden(),
                'itinerario' => ($itinerario !== null) ? [
                    'id' => $itinerario->getId(),
                    'nombre' => $itinerario->getNombre(),
                ] : null,
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                ] : null,
                'trayecto' => ($trayecto !== null) ? [
                    'id' => $trayecto->getId(),
                    'ciudad_inicio' => $trayecto->getCiudadInicio()->getNombre(),
                    'ciudad_fin' => $trayecto->getCiudadFin()->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/create", methods={"POST"}, name="itinerario_detalle_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $itinerarioDetalle = new ItinerarioDetalle();
        if (isset($data['itinerario_detalle']['fecha_inicio'])) {
            $string_fecha_inicio = $data['itinerario_detalle']['fecha_inicio'];
            $date_fecha_inicio = DateTimeImmutable::createFromFormat('Y-m-d', $string_fecha_inicio);
            $itinerarioDetalle->setFechaInicio($date_fecha_inicio);
        }
        if (isset($data['itinerario_detalle']['fecha_fin'])) {
            $string_fecha_fin = $data['itinerario_detalle']['fecha_fin'];
            $date_fecha_fin = DateTimeImmutable::createFromFormat('Y-m-d', $string_fecha_fin);
            $itinerarioDetalle->setFechaFin($date_fecha_fin);
        }
        if (isset($data['itinerario_detalle']['orden'])) {
            $itinerarioDetalle->setOrden($data['itinerario_detalle']['orden']);
        }
        if (isset($data['itinerario_detalle']['itinerario_id'])) {
            $itinerarioRepository = $this->em->getRepository(Itinerario::class);
            $itinerario = $itinerarioRepository->find($data['itinerario_detalle']['itinerario_id']);
            $itinerarioDetalle->setItinerario($itinerario);
        }

        if (isset($data['itinerario_detalle']['ciudad_id'])) {
            $ciudadRepository = $this->em->getRepository(Ciudad::class);
            $ciudad = $ciudadRepository->find($data['itinerario_detalle']['ciudad_id']);
            $itinerarioDetalle->setCiudad($ciudad);
        }
        if (isset($data['trayecto'])) {
            if (isset($data['trayecto']['ciudad_inicio']) && isset($data['trayecto']['ciudad_fin'])) {
            $trayectoRepository = $this->em->getRepository(Trayecto::class);
                $trayecto = $trayectoRepository->findBy(['ciudad_inicio' => $data['trayecto']['ciudad_inicio'], 'ciudad_fin' => $data['trayecto']['ciudad_fin']]);
    
                if ($trayecto) {
                    $itinerarioDetalle->setTrayecto($trayecto[0]);
                }
                else {
                    $trayecto = new Trayecto();
                    if (isset($data['trayecto']['ciudad_inicio'])) {
                        $ciudadInicioRepository = $this->em->getRepository(Ciudad::class);
                        $ciudadInicio = $ciudadInicioRepository->find($data['trayecto']['ciudad_inicio']);
                        $trayecto->setCiudadInicio($ciudadInicio);
                    }
                    if (isset($data['trayecto']['ciudad_fin'])) {
                        $ciudadFinRepository = $this->em->getRepository(Ciudad::class);
                        $ciudadFin = $ciudadFinRepository->find($data['trayecto']['ciudad_fin']);
                        $trayecto->setCiudadFin($ciudadFin);
                    }
                    $entityManager->persist($trayecto);
                    $entityManager->flush();
                    $itinerarioDetalle->setTrayecto($trayecto);
                }
            }
        }
        $entityManager->persist($itinerarioDetalle);
        $entityManager->flush();

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El itinerario detalle se creo correctamente'], 200);
    }

    /**
     * @Route("/itinerario_detalle/update/{id}", methods={"PUT", "PATCH"}, name="itinerario_detalle_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
        $itinerarioDetalle = $itinerarioDetalleRepository->find($id);

        if (!$itinerarioDetalle) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el itinerario detalle'], 400);
        }

        if (isset($data['itinerario_detalle']['fecha_inicio'])) {
            $string_fecha_inicio = $data['itinerario_detalle']['fecha_inicio'];
            $date_fecha_inicio = DateTimeImmutable::createFromFormat('Y-m-d', $string_fecha_inicio);
            $itinerarioDetalle->setFechaInicio($date_fecha_inicio);
        }
        if (isset($data['itinerario_detalle']['fecha_fin'])) {
            $string_fecha_fin = $data['itinerario_detalle']['fecha_fin'];
            $date_fecha_fin = DateTimeImmutable::createFromFormat('Y-m-d', $string_fecha_fin);
            $itinerarioDetalle->setFechaFin($date_fecha_fin);
        }
        if (isset($data['itinerario_detalle']['orden'])) {
            $itinerarioDetalle->setOrden($data['itinerario_detalle']['orden']);
        } else {
            $itinerarioDetalle->setOrden(null);
        }
        if (isset($data['itinerario_detalle']['itinerario_id'])) {
            $itinerarioRepository = $this->em->getRepository(Itinerario::class);
            $itinerario = $itinerarioRepository->find($data['itinerario_detalle']['itinerario_id']);
            $itinerarioDetalle->setItinerario($itinerario);
        }

        if (isset($data['itinerario_detalle']['ciudad_id']) && isset($data['itinerario_detalle']['trayecto_id'])) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'no puede enviar a la vez Trayecto y Ciudad, elija uno solo'], 400);
        }

        if (isset($data['itinerario_detalle']['ciudad_id'])) {
            $ciudadRepository = $this->em->getRepository(Ciudad::class);
            $ciudad = $ciudadRepository->find($data['itinerario_detalle']['ciudad_id']);
            $itinerarioDetalle->setCiudad($ciudad);
        } else {
            $itinerarioDetalle->setCiudad(null);
        }

        if (isset($data['itinerario_detalle']['trayecto_id'])) {
            $trayectoRepository = $this->em->getRepository(Trayecto::class);
            $trayecto = $trayectoRepository->find($data['itinerario_detalle']['trayecto_id']);
            $itinerarioDetalle->setTrayecto($trayecto);
        } else {
            $itinerarioDetalle->setTrayecto(null);
        }

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El itinerario detalle se actualizo correctamente'], 200);
    }

    /**
     * @Route("/itinerario_detalle/delete/{id}", methods={"DELETE"}, name="itinerario_detalle_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
        $itinerarioDetalle = $itinerarioDetalleRepository->find($id);

        if (!$itinerarioDetalle) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el itinerario detalle'], 400);
        }

        $this->em->remove($itinerarioDetalle);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El itinerario detalle se elimino correctamente.'], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_itinerario", methods={"POST"}, name="itinerario_detalle_getFilterByItinerario")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByItinerario(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $itinerario = (isset($data['itinerario'])) ? $data['itinerario'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByItinerario($itinerario, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_ciudad", methods={"POST"}, name="itinerario_detalle_getFilterByCiudad")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByCiudad(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $ciudad = (isset($data['ciudad'])) ? $data['ciudad'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByCiudad($ciudad, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_trayecto", methods={"POST"}, name="itinerario_detalle_getFilterByTrayecto")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByTrayecto(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $trayecto = (isset($data['trayecto'])) ? $data['trayecto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByTrayecto($trayecto, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_termino", methods={"POST"}, name="itinerario_detalle_getFilterByTermino")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByTermino(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByTermino($termino, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_itinerario_ciudad", methods={"POST"}, name="itinerario_detalle_getFilterByItinerarioCiudad")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByItinerarioCiudad(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $itinerario = (isset($data['itinerario'])) ? $data['itinerario'] : null;
        $ciudad = (isset($data['ciudad'])) ? $data['ciudad'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByItinerarioCiudad($itinerario, $ciudad, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_itinerario_trayecto", methods={"POST"}, name="itinerario_detalle_getFilterByItinerarioTrayecto")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByItinerarioTrayecto(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $itinerario = (isset($data['itinerario'])) ? $data['itinerario'] : null;
        $trayecto = (isset($data['trayecto'])) ? $data['trayecto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByItinerarioTrayecto($itinerario, $trayecto, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_termino_itinerario", methods={"POST"}, name="itinerario_detalle_getFilterByTerminoItinerario")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByTerminoItinerario(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $itinerario = (isset($data['itinerario'])) ? $data['itinerario'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByItinerarioTermino($itinerario, $termino, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_termino_ciudad", methods={"POST"}, name="itinerario_detalle_getFilterByTerminoCiudad")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByTerminoCiudad(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $ciudad = (isset($data['ciudad'])) ? $data['ciudad'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByCiudadTermino($ciudad, $termino, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_termino_trayecto", methods={"POST"}, name="itinerario_detalle_getFilterByTerminoTrayecto")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByTerminoTrayecto(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $trayecto = (isset($data['trayecto'])) ? $data['trayecto'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByTrayectoTermino($trayecto, $termino, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_termino_itinerario_ciudad", methods={"POST"}, name="itinerario_detalle_getFilterByTerminoItinerarioCiudad")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByTerminoItinerarioCiudad(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $itinerario = (isset($data['itinerario'])) ? $data['itinerario'] : null;
        $ciudad = (isset($data['ciudad'])) ? $data['ciudad'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByTerminoItinerarioCiudad($termino, $itinerario, $ciudad, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario_detalle/get_filter_by_termino_itinerario_trayecto", methods={"POST"}, name="itinerario_detalle_getFilterByTerminoItinerarioTrayecto")
     * @param Request $request
     * @return JsonResponse
     */
    public function getFilterByTerminoItinerarioTrayecto(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $itinerario = (isset($data['itinerario'])) ? $data['itinerario'] : null;
        $trayecto = (isset($data['trayecto'])) ? $data['trayecto'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $itinerariosDetalleTotal = $itinerarioDetalleRepository->findAll();

        $itinerariosDetalleResult = $itinerarioDetalleRepository->findByTerminoItinerarioTrayecto($termino, $itinerario, $trayecto, $offset, $limit);

        $data = [];

        foreach ($itinerariosDetalleResult as $datosTipo) {
            $data[] = [
                'id' => $datosTipo["id"],
                'fecha_inicio' => $datosTipo["fecha_inicio"]->format('Y-m-d'),
                'fecha_fin' => $datosTipo["fecha_fin"]->format('Y-m-d'),
                'orden' => $datosTipo["orden"],
                'itinerario' => [
                    'id' => $datosTipo["itinerario_id"],
                    'nombre' => $datosTipo["itinerario_nombre"],
                ],
                'ciudad' => ($datosTipo["ciudad_id"] !== null) ? [
                    'id' => $datosTipo["ciudad_id"],
                    'nombre' => $datosTipo["ciudad_nombre"],
                ] : null,
                'trayecto' => ($datosTipo["trayecto_id"] !== null) ? [
                    'id' => $datosTipo["trayecto_id"],
                    'ciudad_inicio' => $datosTipo["ciudad_inicio"],
                    'ciudad_fin' => $datosTipo["ciudad_fin"],
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosDetalleTotal), 'data' => $data], 200);
    }
}
