<?php

namespace App\Controller;

use App\Entity\PuntoInteres;
use App\Entity\Ciudad;
use App\Entity\Grupo;
use App\Entity\Itinerario;
use App\Entity\ItinerarioDetalle;
use App\Entity\Lista;
use App\Entity\Notificacion;
use App\Entity\Persona;
use App\Entity\Pasajero;
use App\Entity\PasajeroListaOpcion;
use App\Entity\PasajeroNotificacion;
use App\Entity\PersonaTokenFirebase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;
use App\Security\JwtAuthenticator;

class NotificacionController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/notificacion/get_by_id/{notificacionId}", methods={"GET"}, name="notificacion_getNotificacionById")
     * @param int $notificacionId
     * @return JsonResponse
     */
    public function getNotificacionById(int $notificacionId, Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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
                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $pasajeros = $pasajeroRepository->findBy(['Persona' => $id]);
                    $pasajero_id = $pasajeros[0]->getId();

                    $notificacionRepository = $this->em->getRepository(Notificacion::class);
                    $notificacion = $notificacionRepository->find($notificacionId);

                    if (!$notificacion) {
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro la notificacion.'], 400);
                    }

                    $grupo = $notificacion->getGrupo();
                    $itinerario = $notificacion->getItinerario();
                    $ciudad = $notificacion->getCiudad();
                    $lista = $notificacion->getLista();

                    $pasajeroNotificacionRepository = $this->em->getRepository(PasajeroNotificacion::class);
                    $pasajeroNotificacion = $pasajeroNotificacionRepository->findBy(['notificacion' => $notificacion->getId(), 'pasajero' => $pasajero_id]);
                    $is_read = false;
                    if ($pasajeroNotificacion && count($pasajeroNotificacion) > 0) {
                        if ($pasajeroNotificacion[0]->getFechaVisto() !== null) {
                            $is_read = true;
                        }
                    }

                    $data = [
                        'id' => $notificacion->getId(),
                        'titulo' => $notificacion->getTitulo(),
                        'mensaje' => $notificacion->getMensaje(),
                        'fecha_programada' => ($notificacion->getFechaProgramada() !== null) ? $notificacion->getFechaProgramada()->format('Y-m-d H:i:s') : null,
                        'fecha_enviada' => ($notificacion->getFechaEnviado() !== null) ? $notificacion->getFechaEnviado()->format('Y-m-d H:i:s') : null,
                        'foto' => $notificacion->getFoto(),
                        'is_read' => $is_read,
                        'grupo' => ($grupo !== null) ? [
                            'id' => $grupo->getId(),
                            'nombre' => $grupo->getNombre(),
                        ] : null,
                        'itinerario' => ($itinerario !== null) ? [
                            'id' => $itinerario->getId(),
                            'nombre' => $itinerario->getNombre(),
                        ] : null,
                        'ciudad' => ($ciudad !== null) ? [
                            'id' => $ciudad->getId(),
                            'nombre' => $ciudad->getNombre(),
                        ] : null,
                        'lista' => ($lista !== null) ? [
                            'id' => $lista->getId(),
                            'titulo' => $lista->getTitulo(),
                        ] : null,

                    ];

                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Autorizacion invalida'], 400);
        }
    }

    /**
     * @Route("/notificacion/get_elements_by_group", name="getElementsByGroup", methods={"GET"})
     */
    public function getElementsByGroup(Request $request, JwtAuthenticator $jwtAutheticator)
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
                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $itinerarioId = $pasajeroRepository->findByPersonaItinerario($persona);

                    // Obtengo el grupo para poder luego buscar los itinerarios de ese grupo, las listas de ese grupo y las ciudades de ese grupo
                    $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                    $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                    $grupo = $itinerario_aux->getGrupo();

                    $itinerarios = $itinerarioRepository->findBy(['Grupo' => $grupo->getId()]);

                    $itinerarios_data = [];
                    $ciudades_data = [];

                    foreach ($itinerarios as $itinerario) {
                        $itinerarios_data[] = [
                            'id' => $itinerario->getId(),
                            'nombre' => $itinerario->getNombre(),
                            'fecha_inicio' => ($itinerario->getFechaInicio() !== null) ? $itinerario->getFechaInicio()->format('Y-m-d') : null,
                            'fecha_fin' => ($itinerario->getFechaFin() !== null) ? $itinerario->getFechaFin()->format('Y-m-d') : null,
                            'precio' => $itinerario->getPrecio(),
                            'principal' => $itinerario->getPrincipal(),
                        ];

                        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
                        $itinerariosDetalle = $itinerarioDetalleRepository->findBy(['itinerario' => $itinerario->getId()]);
                        foreach ($itinerariosDetalle as $itinerarioDetalle) {
                            $ciudad = $itinerarioDetalle->getCiudad();
                            if ($ciudad !== null) {
                                $ciudades_data[] = [
                                    'id' => $ciudad->getId(),
                                    'nombre' => $ciudad->getNombre(),
                                ];
                            }
                        }
                    }

                    $listaRepository = $this->em->getRepository(Lista::class);
                    $listas = $listaRepository->findBy(['grupo' => $grupo->getId()]);

                    $listas_data = [];

                    foreach ($listas as $lista) {
                        $listas_data[] = [
                            'id' => $lista->getId(),
                            'nombre' => $lista->getTitulo(),
                            'descripcion' => $lista->getDescripcion(),
                        ];
                    }

                    $data = [
                        'itinerarios' => $itinerarios_data,
                        'ciudades' => $ciudades_data,
                        'listas' => $listas_data,
                    ];
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        }
        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/notificacion/get_by_id_admin/{notificacionId}", methods={"GET"}, name="notificacion_getNotificacionByIdAdmin")
     * @param int $notificacionId
     * @return JsonResponse
     */
    public function getNotificacionByIdAdmin(int $notificacionId, Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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
                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $pasajeros = $pasajeroRepository->findBy(['Persona' => $id]);
                    $pasajero_id = $pasajeros[0]->getId();

                    $notificacionRepository = $this->em->getRepository(Notificacion::class);
                    $notificacion = $notificacionRepository->find($notificacionId);

                    if (!$notificacion) {
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro la notificacion.'], 400);
                    }

                    $grupo = $notificacion->getGrupo();
                    $itinerario = $notificacion->getItinerario();
                    $ciudad = $notificacion->getCiudad();
                    $lista = $notificacion->getLista();

                    $pasajeroNotificacionRepository = $this->em->getRepository(PasajeroNotificacion::class);
                    $pasajeroNotificacion = $pasajeroNotificacionRepository->findBy(['notificacion' => $notificacion->getId(), 'pasajero' => $pasajero_id]);
                    $is_read = false;
                    if ($pasajeroNotificacion && count($pasajeroNotificacion) > 0) {
                        if ($pasajeroNotificacion[0]->getFechaVisto() !== null) {
                            $is_read = true;
                        }
                    }

                    $data = [
                        'id' => $notificacion->getId(),
                        'titulo' => $notificacion->getTitulo(),
                        'mensaje' => $notificacion->getMensaje(),
                        'fecha_programada' => ($notificacion->getFechaProgramada() !== null) ? $notificacion->getFechaProgramada()->format('Y-m-d H:i:s') : null,
                        'fecha_enviado' => ($notificacion->getFechaEnviado() !== null) ? $notificacion->getFechaEnviado()->format('Y-m-d H:i:s') : null,
                        'foto' => $notificacion->getFoto(),
                        'is_read' => $is_read,
                        'grupo' => ($grupo !== null) ? [
                            'id' => $grupo->getId(),
                            'nombre' => $grupo->getNombre(),
                        ] : null,
                        'itinerario' => ($itinerario !== null) ? [
                            'id' => $itinerario->getId(),
                            'nombre' => $itinerario->getNombre(),
                        ] : null,
                        'ciudad' => ($ciudad !== null) ? [
                            'id' => $ciudad->getId(),
                            'nombre' => $ciudad->getNombre(),
                        ] : null,
                        'lista' => ($lista !== null) ? [
                            'id' => $lista->getId(),
                            'titulo' => $lista->getTitulo(),
                        ] : null,

                    ];
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Autorizacion invalida'], 400);
        }
    }

    /**
     * @Route("/notificacion/list_by_group", methods={"GET"}, name="notificacion_listByGroup")
     * @return JsonResponse
     */
    public function listByGroup(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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
                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $pasajeros = $pasajeroRepository->findBy(['Persona' => $id]);
                    $pasajero_id = $pasajeros[0]->getId();
                    $itinerarioId = $pasajeroRepository->findByPersonaItinerario($persona);

                    // Obtengo el grupo para poder luego buscar los itinerarios de ese grupo, las listas de ese grupo y las ciudades de ese grupo
                    $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                    $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                    $grupo = $itinerario_aux->getGrupo();
                    $notificacionRepository = $this->em->getRepository(Notificacion::class);
                    $notificaciones = $notificacionRepository->findBy(['grupo' => $grupo->getId()], ['fecha_programada' => 'DESC', 'fecha_enviado' => 'DESC']);

                    if (!$notificaciones) {
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro ninguna notificacion.'], 400);
                    }

                    $data = [];

                    foreach ($notificaciones as $notificacion) {
                        $grupo = $notificacion->getGrupo();
                        $itinerario = $notificacion->getItinerario();
                        $ciudad = $notificacion->getCiudad();
                        $lista = $notificacion->getLista();

                        $pasajeroNotificacionRepository = $this->em->getRepository(PasajeroNotificacion::class);
                        $pasajerosNotificacion = $pasajeroNotificacionRepository->findBy(['notificacion' => $notificacion->getId()]);
                        $pasajeroNotificacion = $pasajeroNotificacionRepository->findBy(['notificacion' => $notificacion->getId(), 'pasajero' => $pasajero_id]);
                        $is_read = false;
                        if ($pasajeroNotificacion && count($pasajeroNotificacion) > 0) {
                            if ($pasajeroNotificacion[0]->getFechaVisto() !== null) {
                                $is_read = true;
                            }
                        }
                        $usuarios_leido = [];
                        $total_enviados = 0;
                        $total_leidos = 0;
                        $usuarios_no_leido = [];
                        foreach ($pasajerosNotificacion as $pn) {
                            if ($pn->getFechaVisto() == null) {
                                $usuarios_no_leido[] = [
                                    'id_pasajero' => $pn->getPasajero()->getId(),
                                    'nombres_persona' => $pn->getPasajero()->getPersona()->getNombres(),
                                    'apellidos_persona' => $pn->getPasajero()->getPersona()->getApellidos(),
                                ];
                            } else {
                                $usuarios_leido[] = [
                                    'id_pasajero' => $pn->getPasajero()->getId(),
                                    'nombres_persona' => $pn->getPasajero()->getPersona()->getNombres(),
                                    'apellidos_persona' => $pn->getPasajero()->getPersona()->getApellidos(),
                                ];
                                $total_leidos = $total_leidos + 1;
                            }
                            $total_enviados = $total_enviados + 1;
                        }


                        $data[] = [
                            'id' => $notificacion->getId(),
                            'titulo' => $notificacion->getTitulo(),
                            'mensaje' => $notificacion->getMensaje(),
                            'fecha_programada' => ($notificacion->getFechaProgramada() !== null) ? $notificacion->getFechaProgramada()->format('Y-m-d H:i:s') : null,
                            'fecha_enviado' => ($notificacion->getFechaEnviado() !== null) ? $notificacion->getFechaEnviado()->format('Y-m-d H:i:s') : null,
                            'foto' => $notificacion->getFoto(),
                            'is_read' => $is_read,
                            'total_usuarios_enviado' => $total_enviados,
                            'total_usuarios_leido' => $total_leidos,
                            'usuarios_leido' => $usuarios_leido,
                            'usuarios_no_leido' => $usuarios_no_leido,
                            'grupo' => ($grupo !== null) ? [
                                'id' => $grupo->getId(),
                                'nombre' => $grupo->getNombre(),
                            ] : null,
                            'itinerario' => ($itinerario !== null) ? [
                                'id' => $itinerario->getId(),
                                'nombre' => $itinerario->getNombre(),
                            ] : null,
                            'ciudad' => ($ciudad !== null) ? [
                                'id' => $ciudad->getId(),
                                'nombre' => $ciudad->getNombre(),
                            ] : null,
                            'lista' => ($lista !== null) ? [
                                'id' => $lista->getId(),
                                'titulo' => $lista->getTitulo(),
                            ] : null,

                        ];
                    }

                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                }
            }
        }
    }

    /**
     * @Route("/notificacion/list_notifications_by_user", name="getNotificationsByUser", methods={"GET"})
     */
    public function getNotificationsByUser(Request $request, JwtAuthenticator $jwtAutheticator)
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
                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $pasajeros = $pasajeroRepository->findBy(['Persona' => $id]);
                    $pasajero_id = $pasajeros[0]->getId();
                    $itinerarioId = $pasajeroRepository->findByPersonaItinerario($persona);

                    // Obtengo el grupo para poder luego buscar los itinerarios de ese grupo, las listas de ese grupo y las ciudades de ese grupo
                    $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                    $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                    $grupo = $itinerario_aux->getGrupo();
                    $notificacionRepository = $this->em->getRepository(Notificacion::class);
                    $notificaciones = $notificacionRepository->findBy(['grupo' => $grupo->getId()], ['fecha_programada' => 'DESC', 'fecha_enviado' => 'DESC']);

                    $data = [];

                    foreach ($notificaciones as $notificacion) {
                        $grupo = $notificacion->getGrupo();
                        $itinerario = $notificacion->getItinerario();
                        $ciudad = $notificacion->getCiudad();
                        $lista = $notificacion->getLista();

                        $pasajeroNotificacionRepository = $this->em->getRepository(PasajeroNotificacion::class);
                        $pasajeroNotificacion = $pasajeroNotificacionRepository->findBy(['notificacion' => $notificacion->getId(), 'pasajero' => $pasajero_id]);
                        $is_read = false;
                        if ($pasajeroNotificacion && count($pasajeroNotificacion) > 0) {
                            if ($pasajeroNotificacion[0]->getFechaVisto() !== null) {
                                $is_read = true;
                            }

                            $data[] = [
                                'id' => $notificacion->getId(),
                                'titulo' => $notificacion->getTitulo(),
                                'mensaje' => $notificacion->getMensaje(),
                                'fecha_programada' => ($notificacion->getFechaProgramada() !== null) ? $notificacion->getFechaProgramada()->format('Y-m-d H:i:s') : null,
                                'fecha_enviado' => ($notificacion->getFechaEnviado() !== null) ? $notificacion->getFechaEnviado()->format('Y-m-d H:i:s') : null,
                                'foto' => $notificacion->getFoto(),
                                'is_read' => $is_read,
                                'grupo' => ($grupo !== null) ? [
                                    'id' => $grupo->getId(),
                                    'nombre' => $grupo->getNombre(),
                                ] : null,
                                'itinerario' => ($itinerario !== null) ? [
                                    'id' => $itinerario->getId(),
                                    'nombre' => $itinerario->getNombre(),
                                ] : null,
                                'ciudad' => ($ciudad !== null) ? [
                                    'id' => $ciudad->getId(),
                                    'nombre' => $ciudad->getNombre(),
                                ] : null,
                                'lista' => ($lista !== null) ? [
                                    'id' => $lista->getId(),
                                    'titulo' => $lista->getTitulo(),
                                ] : null,
                            ];
                        }
                    }
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        }
    }

    /**
     * @Route("/notificacion/user_view_notification", methods={"POST"}, name="notificacion_user_view_notification")
     * @param Request $request
     * @return JsonResponse
     */
    public function user_view_notification(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $notification_id = $data['notification_id'];
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                $personaRepository = $this->em->getRepository(Persona::class);
                $persona = $personaRepository->getPersonaById($id);
                if ($persona) {
                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $pasajero_id = $pasajeroRepository->createQueryBuilder('p')
                        ->select('p.id')
                        ->where('p.Persona = :val')
                        ->setParameter('val', $id)
                        ->getQuery()
                        ->getOneOrNullResult();

                    if ($pasajero_id && $pasajero_id !== null) {
                        $pasajeroNotificacionRepository = $this->em->getRepository(PasajeroNotificacion::class);
                        $pasajero_notificacion = $pasajeroNotificacionRepository->createQueryBuilder('pn')
                            ->where('pn.pasajero = :val_aux')
                            ->andWhere('pn.notificacion = :notification_id')
                            ->setParameter('val_aux', $pasajero_id)
                            ->setParameter('notification_id', $notification_id)
                            ->getQuery()
                            ->getOneOrNullResult();
                        if ($pasajero_notificacion && $pasajero_notificacion !== null) {
                            $dateAux = date('Y-m-d H:i:s');
                            $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
                            $pasajero_notificacion->setFechaVisto($date_fecha_actual);
                            $this->em->flush();
                            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Visualizacion actualizada correctamente.'], 200);
                        } else {
                            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro en la tabla pasajero notificacion.'], 400);
                        }
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El usuario no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Autorizacion invalida.'], 400);
        }
    }

    /**
     * @Route("/notificacion/create", methods={"POST"}, name="notificacion_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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

                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $itinerarioId = $pasajeroRepository->findByPersonaItinerario($persona);

                    // Obtengo el grupo para poder luego buscar los itinerarios de ese grupo, las listas de ese grupo y las ciudades de ese grupo
                    $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                    $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                    $grupo = $itinerario_aux->getGrupo();
                    $entityManager = $this->em;
                    $bandera_no_programada = false;

                    $data = json_decode($request->getContent(), true);

                    $notificacion = new Notificacion();
                    $notificacion->setTitulo($data['notificacion']['titulo']);
                    $notificacion->setMensaje($data['notificacion']['mensaje']);
                    if ($grupo == null) {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El usuario que esta agregando la notificacion no tiene ningun grupo.'], 400);
                    }
                    $notificacion->setGrupo($grupo);

                    if (isset($data['notificacion']['fecha_programada']) && $data['notificacion']['fecha_programada'] !== null) {
                        $string_fecha_programada = $data['notificacion']['fecha_programada'];
                        $date_fecha_programada = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $string_fecha_programada);
                        $notificacion->setFechaProgramada($date_fecha_programada);
                    } else {
                        $bandera_no_programada = true;
                        $fecha_actual_aux = date("Y-m-d H:i:s");
                        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fecha_actual_aux);
                        $notificacion->setFechaProgramada($date_fecha_actual);
                    }

                    if (isset($data['notificacion']['fecha_enviada']) && $data['notificacion']['fecha_enviada'] !== null) {
                        $string_fecha_enviada = $data['notificacion']['fecha_enviada'];
                        $date_fecha_enviada = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $string_fecha_enviada);
                        $notificacion->setFechaEnviado($date_fecha_enviada);
                    } else {
                        if ($bandera_no_programada) {
                            $fecha_actual_aux = date("Y-m-d H:i:s");
                            $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fecha_actual_aux);
                            $notificacion->setFechaEnviado($date_fecha_actual);
                        } else {
                            $notificacion->setFechaEnviado(null);
                        }
                    }

                    if (isset($data['notificacion']['itinerario_id']) && $data['notificacion']['itinerario_id'] !== null) {
                        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                        $itinerario = $itinerarioRepository->find($data['notificacion']['itinerario_id']);
                        $notificacion->setItinerario($itinerario);
                    } else {
                        $notificacion->setItinerario(null);
                    }
                    if (isset($data['notificacion']['ciudad_id']) && $data['notificacion']['ciudad_id'] !== null) {
                        $ciudadRepository = $this->em->getRepository(Ciudad::class);
                        $ciudad = $ciudadRepository->find($data['notificacion']['ciudad_id']);
                        $notificacion->setCiudad($ciudad);
                    } else {
                        $notificacion->setCiudad(null);
                    }
                    if (isset($data['notificacion']['lista_id']) && $data['notificacion']['lista_id'] !== null) {
                        $listaRepository = $this->em->getRepository(Lista::class);
                        $lista = $listaRepository->find($data['notificacion']['lista_id']);
                        $notificacion->setLista($lista);
                    } else {
                        $notificacion->setLista(null);
                    }


                    $entityManager->persist($notificacion);
                    $entityManager->flush();

                    if (isset($data['notificacion']['foto']) && $data['notificacion']['foto'] !== null) {
                        //foto nueva
                        $id = $notificacion->getId();
                        $image = $data['notificacion']['foto'];

                        $dir_assets = "";
                        $padre = dirname(__DIR__);
                        $dir_assets = str_replace('src', 'assets', $padre);

                        $dir = $dir_assets . "/imgs/notificacion";
                        // $folderPath = $padre . "/upload/";
                        $micarpeta = $dir . "/" . $id;
                        $folderPath = $micarpeta . "/foto/";
                        // si no existe la carpeta con el idPersona se crea
                        if (!file_exists($micarpeta)) {
                            //crea el directorio
                            mkdir($micarpeta, 0777, true);
                            //crea sub-directorio foto y documento
                            $dir_foto = $micarpeta . "/foto";
                            mkdir($dir_foto, 0777, true);
                            mkdir($micarpeta . "/documento", 0777, true);
                        }

                        // GUARDO LA IMAGEN EN SERVIDOR
                        $image_parts = explode(";base64,", $image);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_base64 = base64_decode($image_parts[1]);

                        $file = $folderPath . uniqid() . '.png';
                        $nombre_archivo_aux = explode("foto/", $file);
                        $nombre_archivo = $nombre_archivo_aux[1];
                        if (file_put_contents($file, $image_base64)) {
                            //Guardar Foto en BASE DE DATOS
                            $foto_url = '/assets/imgs/notificacion/' . $id . '/foto/' . $nombre_archivo;
                            $notificacion->setFoto($foto_url);
                        }
                    }

                    $this->em->flush();

                    //SI LA NOTIFICACION NO TIENE FECHA PROGRAMADA SE ENVIA EN EL MOMENTO
                    if ($bandera_no_programada) {
                        $usuarios_a_enviar = [];
                        //enviar solo ciudad
                        if ($notificacion->getCiudad() !== null) {
                            $ciudad = $notificacion->getCiudad();
                            $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
                            $itinerariosDetalle = $itinerarioDetalleRepository->findBy(['ciudad' => $ciudad->getId()]);
                            foreach ($itinerariosDetalle as $itinerarioDetalle) {
                                $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                                $pasajeros = $pasajeroRepository->findBy(['Itinerario' => $itinerarioDetalle->getItinerario()->getId()]);
                                foreach ($pasajeros as $pasajero) {
                                    $persona = $pasajero->getPersona();
                                    $personaTokenFirebaseRepository = $this->em->getRepository(PersonaTokenFirebase::class);
                                    $personaTokenFirebase = $personaTokenFirebaseRepository->findBy(['persona' => $persona->getId()]);
                                    if ($personaTokenFirebase && $personaTokenFirebase !== null && count($personaTokenFirebase) > 0) {
                                        $usuarios_a_enviar[] = [
                                            'token' => $personaTokenFirebase[0]->getToken(),
                                            'pasajero' => $pasajero,
                                            'notificacion' => $notificacion
                                        ];
                                    }
                                }
                            }
                        }
                        //enviar solo lista                        
                        else if ($notificacion->getLista() !== null) {
                            $lista = $notificacion->getLista();
                            $listaOpcionRepository = $this->em->getRepository(ListaOpcion::class);
                            $listaOpciones = $listaOpcionRepository->findBy(['lista' => $lista->getId()]);
                            foreach ($listaOpciones as $listaOpcion) {
                                $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                                $pasajerosListaOpcion = $pasajeroListaOpcionRepository->findBy(['lista_opcion' => $listaOpcion->getId()]);
                                foreach ($pasajerosListaOpcion as $pasajeroListaOpcion) {
                                    $persona = $pasajeroListaOpcion->getPasajero()->getPersona();
                                    $personaTokenFirebaseRepository = $this->em->getRepository(PersonaTokenFirebase::class);
                                    $personaTokenFirebase = $personaTokenFirebaseRepository->findBy(['persona' => $persona->getId()]);
                                    if ($personaTokenFirebase && $personaTokenFirebase !== null && count($personaTokenFirebase) > 0) {
                                        $usuarios_a_enviar[] = [
                                            'token' => $personaTokenFirebase[0]->getToken(),
                                            'pasajero' => $pasajeroListaOpcion->getPasajero(),
                                            'notificacion' => $notificacion
                                        ];
                                    }
                                }
                            }
                        }
                        //enviar solo itinerario
                        else if ($notificacion->getItinerario() !== null) {
                            $itinerario = $notificacion->getItinerario();
                            $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                            $pasajeros = $pasajeroRepository->findBy(['Itinerario' => $itinerario->getId()]);
                            foreach ($pasajeros as $pasajero) {
                                $persona = $pasajero->getPersona();
                                $personaTokenFirebaseRepository = $this->em->getRepository(PersonaTokenFirebase::class);
                                $personaTokenFirebase = $personaTokenFirebaseRepository->findBy(['persona' => $persona->getId()]);
                                if ($personaTokenFirebase && $personaTokenFirebase !== null && count($personaTokenFirebase) > 0) {
                                    $usuarios_a_enviar[] = [
                                        'token' => $personaTokenFirebase[0]->getToken(),
                                        'pasajero' => $pasajero,
                                        'notificacion' => $notificacion
                                    ];
                                }
                            }
                        }
                        //enviar a todo el grupo
                        else {
                            $grupo = $notificacion->getGrupo();
                            $itinerarioDosRepository = $this->em->getRepository(Itinerario::class);
                            $itinerarios = $itinerarioDosRepository->findBy(['Grupo' => $grupo->getId()]);
                            foreach ($itinerarios as $itinerario) {
                                $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                                $pasajeros = $pasajeroRepository->findBy(['Itinerario' => $itinerario->getId()]);
                                foreach ($pasajeros as $pasajero) {
                                    $persona = $pasajero->getPersona();
                                    $personaTokenFirebaseRepository = $this->em->getRepository(PersonaTokenFirebase::class);
                                    $personaTokenFirebase = $personaTokenFirebaseRepository->findBy(['persona' => $persona->getId()]);
                                    if ($personaTokenFirebase && $personaTokenFirebase !== null && count($personaTokenFirebase) > 0) {
                                        $usuarios_a_enviar[] = [
                                            'token' => $personaTokenFirebase[0]->getToken(),
                                            'pasajero' => $pasajero,
                                            'notificacion' => $notificacion
                                        ];
                                    }
                                }
                            }
                        }

                        //recorro los usuarios a enviar y envio una a una la notificacion
                        foreach ($usuarios_a_enviar as $usuario_a_enviar) {
                            $pasajero_notificacion = new PasajeroNotificacion();
                            $pasajero_notificacion->setPasajero($usuario_a_enviar["pasajero"]);
                            $pasajero_notificacion->setNotificacion($usuario_a_enviar["notificacion"]);
                            $entityManager->persist($pasajero_notificacion);
                            $entityManager->flush();

                            $imagen_ruta = 'http://apirifas.detoqueytoque.com/' . $notificacion->getFoto();
                            //ENVIA LA NOTIFICACION
                            $url = 'https://fcm.googleapis.com/fcm/send';
                            $api_key = 'AAAA4-DndCw:APA91bGPkhl-8nlMYPRChXK7P2S973_jBpp2tzhbEEcPLRIuLgYplrZ7ccZJHIR6i2LlQCdTl4Pkn4DSUpLIIRwVmXkAKbugXfOfrEGxsLWDPeg0f-LJw3hzD_zbQ5HIkghBZElSKrFn';
                            $fields = array(
                                // 'registration_ids' => array (
                                //   $token
                                // ),
                                "notification" => array(
                                    "title" => $notificacion->getTitulo(),
                                    "body" => $notificacion->getMensaje(),
                                    "badge" => 1,
                                    "image" => $imagen_ruta,
                                    "sound" => array(
                                        "critical" => 1,
                                        "name" => "default",
                                        "volume" => 1.0
                                    ),
                                ),
                                "android" => array(
                                    "ttl" => "3600s",
                                    "priority" => "high",
                                    "color" => "#00d592",
                                ),
                                "apns" => array(
                                    "headers" => array(
                                        "apns-priority" => "5",
                                    ),
                                    "payload" => array(
                                        "aps" => array(
                                            "badge" => 1,
                                        ),
                                        "mutable_content" => true,
                                        "content_available" => true,
                                    ),
                                ),
                                "data" => array(
                                    "body" => 'https://www.google.com',
                                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                                    "status" => "done",
                                    "notification_id" => $id,
                                ),
                                "to" => $usuario_a_enviar["token"],
                                //
                                'priority' => 'high'
                            );

                            //header includes Content type and api key
                            $headers = array(
                                'Content-Type:application/json',
                                'Authorization:key=' . $api_key
                            );

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                            $result = curl_exec($ch);

                            if ($result === FALSE) {
                                die('FCM Send Error: ' . curl_error($ch));
                            }
                            curl_close($ch);
                        }
                    }
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'La notificacion se creo correctamente'], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Autorizacion invalida'], 400);
        }
    }
}
