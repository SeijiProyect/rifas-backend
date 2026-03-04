<?php

namespace App\Controller;

use App\Entity\PuntoInteres;
use App\Entity\Ciudad;
use App\Entity\Grupo;
use App\Entity\Itinerario;
use App\Entity\ItinerarioDetalle;
use App\Entity\Lista;
use App\Entity\ListaEstado;
use App\Entity\ListaOpcion;
use App\Entity\Notificacion;
use App\Entity\Persona;
use App\Entity\Pasajero;
use App\Entity\PasajeroListaOpcion;
use App\Entity\PasajeroNotificacion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;
use App\Security\JwtAuthenticator;

class ListaController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/lista/create", methods={"POST"}, name="lista_create")
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

                    $data = json_decode($request->getContent(), true);

                    $lista = new Lista();
                    if (isset($data['lista']['titulo']) && $data['lista']['titulo'] !== null) {
                        $lista->setTitulo($data['lista']['titulo']);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar un titulo valido.'], 400);
                    }
                    if (isset($data['lista']['descripcion']) && $data['lista']['descripcion'] !== null) {
                        $lista->setDescripcion($data['lista']['descripcion']);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar una descripcion valida.'], 400);
                    }

                    if ($grupo) {
                        $lista->setGrupo($grupo);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro grupo para este usuario.'], 400);
                    }
                    if (isset($data['lista']['ciudad_id']) && $data['lista']['ciudad_id'] !== null) {
                        $ciudadRepository = $this->em->getRepository(Ciudad::class);
                        $ciudad = $ciudadRepository->find($data['lista']['ciudad_id']);
                        $lista->setCiudad($ciudad);
                    } else {
                        $lista->setCiudad(null);
                    }
                    if (isset($data['lista']['limite_opcion']) && $data['lista']['limite_opcion'] !== null) {
                        $lista->setLimiteOpciones($data['lista']['limite_opcion']);
                    } else {
                        $lista->setLimiteOpciones(null);
                    }
                    if (isset($data['lista']['disponible_hasta']) && $data['lista']['disponible_hasta'] !== null) {
                        $string_disponible_hasta = $data['lista']['disponible_hasta'];
                        $date_disponible_hasta = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $string_disponible_hasta);
                        $lista->setDisponibleHasta($date_disponible_hasta);
                    } else {
                        $lista->setDisponibleHasta(null);
                    }
                    if (isset($data['lista']['fecha_programada']) && $data['lista']['fecha_programada'] !== null) {
                        $string_fecha_programada = $data['lista']['fecha_programada'];
                        $date_fecha_programada = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $string_fecha_programada);
                        $lista->setFechaProgramada($date_fecha_programada);
                    } else {
                        $lista->setFechaProgramada(null);
                    }
                    if (isset($data['lista']['lista_estado_id']) && $data['lista']['lista_estado_id'] !== null) {
                        $listaEstadoRepository = $this->em->getRepository(ListaEstado::class);
                        $listaEstado = $listaEstadoRepository->find($data['lista']['lista_estado_id']);
                        $lista->setListaEstado($listaEstado);
                    } else {
                        $lista->setListaEstado(null);
                    }
                    if (isset($data['lista']['itinerario_id']) && $data['lista']['itinerario_id'] !== null) {
                        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                        $itinerario = $itinerarioRepository->find($data['lista']['itinerario_id']);
                        $lista->setItinerario($listaEstado);
                    } else {
                        $lista->setItinerario(null);
                    }

                    if (isset($data['lista']['imagen']) && $data['lista']['imagen'] !== null) {
                        //foto nueva
                        $id = $lista->getId();
                        $image = $data['lista']['imagen'];

                        $dir_assets = "";
                        $padre = dirname(__DIR__);
                        $dir_assets = str_replace('src', 'assets', $padre);

                        $dir = $dir_assets . "/imgs/lista";
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
                            $foto_url = '/assets/imgs/lista/' . $id . '/foto/' . $nombre_archivo;
                            $lista->setImagen($foto_url);
                        }
                    }

                    $entityManager->persist($lista);
                    $entityManager->flush();

                    if (isset($data['lista']['lista_opcion']) && $data['lista']['lista_opcion'] !== null) {
                        foreach ($data['lista']['lista_opcion'] as $lista_opcion_aux) {
                            $lista_opcion = new ListaOpcion();
                            if ($lista->getId() !== null) {
                                $lista_opcion->setLista($lista);
                            } else {
                                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Ocurrio un error al asociar la lista.'], 400);
                            }
                            if (isset($lista_opcion_aux['titulo']) && $lista_opcion_aux['titulo'] !== null) {
                                $lista_opcion->setTitulo($lista_opcion_aux['titulo']);
                            } else {
                                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar un titulo para cada lista opcion.'], 400);
                            }
                            if (isset($lista_opcion_aux['descripcion']) && $lista_opcion_aux['descripcion'] !== null) {
                                $lista_opcion->setDescripcion($lista_opcion_aux['descripcion']);
                            } else {
                                $lista_opcion->setDescripcion(null);
                            }
                            if (isset($lista_opcion_aux['cupo_limite']) && $lista_opcion_aux['cupo_limite'] !== null) {
                                $lista_opcion->setCupoLimite($lista_opcion_aux['cupo_limite']);
                            } else {
                                $lista_opcion->setCupoLimite(null);
                            }
                            if (isset($lista_opcion_aux['precio']) && $lista_opcion_aux['precio'] !== null) {
                                $lista_opcion->setPrecio($lista_opcion_aux['precio']);
                            } else {
                                $lista_opcion->setPrecio(null);
                            }
                            $entityManager->persist($lista_opcion);
                            $entityManager->flush();
                        }
                    }
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'La lista se creo correctamente'], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro la persona.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el usuario.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Error de autorizacion.'], 400);
        }
    }

    /**
     * @Route("/lista/delete/{id}", methods={"DELETE"}, name="lista_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $listaRepository = $this->em->getRepository(Lista::class);
        $lista = $listaRepository->find($id);

        if (!$lista) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro la lista.'], 400);
        }

        //CHECK PASAJERO LISTA OPCION
        $bandera_inscripto = false;
        $listaOpcionRepository = $this->em->getRepository(ListaOpcion::class);

        $query = $listaOpcionRepository->createQueryBuilder('lo')
            ->where('lo.lista = :lista_id')
            ->setParameter('lista_id', $id)
            ->getQuery();

        $listasOpcion = $query->getResult();

        if ($listasOpcion && count($listasOpcion) > 0) {
            foreach ($listasOpcion as $item_lista_opcion) {
                $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                $query = $pasajeroListaOpcionRepository->createQueryBuilder('plo')
                    ->where('plo.lista_opcion = :lista_opcion_id')
                    ->setParameter('lista_opcion_id', $item_lista_opcion->getId())
                    ->getQuery();

                $pasajerosListaOpcion = $query->getResult();

                if ($pasajerosListaOpcion && count($pasajerosListaOpcion) > 0) {
                    $bandera_inscripto = TRUE;
                }
            }
        }

        if ($bandera_inscripto == true) {
            return new JsonResponse(['status' => 'error', 'code' => 200, 'message' => 'No se puede eliminar la lista ya que tiene pasajeros inscriptos a lista opcion.'], 400);
        }

        if ($listasOpcion && count($listasOpcion) > 0) {
            foreach ($listasOpcion as $item_lista_opcion_aux) {
                $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                $query = $pasajeroListaOpcionRepository->createQueryBuilder('plo')
                    ->where('plo.lista_opcion = :lista_opcion_id')
                    ->setParameter('lista_opcion_id', $item_lista_opcion->getId())
                    ->getQuery();

                $pasajerosListaOpcion = $query->getResult();

                if ($pasajerosListaOpcion && count($pasajerosListaOpcion) > 0) {
                    foreach ($pasajerosListaOpcion as $item_pasajero_lista_opcion) {
                        $this->em->remove($item_pasajero_lista_opcion);
                        $this->em->flush();
                    }
                }
                $this->em->remove($item_lista_opcion_aux);
                $this->em->flush();
            }
        }

        $this->em->remove($lista);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'La lista se elimino correctamente'], 200);
    }

    /**
     * @Route("/lista/add_user_option_list", methods={"POST"}, name="lista_add_user_option_list")
     * @param Request $request
     * @return JsonResponse
     */
    public function add_user_option_list(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
    {
        $entityManager = $this->em;
        $data = json_decode($request->getContent(), true);
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
                    $pasajeros = $pasajeroRepository->findBy(['Persona' => $persona->getId()]);
                    $pasajero = $pasajeros[0];
                    if ($pasajero) {
                        if (isset($data['lista_opcion_id']) && $data['lista_opcion_id'] !== null) {
                            $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                            $pasajerosListaOpcionAux = $pasajeroListaOpcionRepository->findBy([
                                'pasajero' => $pasajero->getId(),
                                'lista_opcion' => $data['lista_opcion_id']
                            ]);
                            if ($pasajerosListaOpcionAux && count($pasajerosListaOpcionAux) > 0) {
                                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Este pasajero ya se encuentra inscripto a esta lista opcion.'], 400);
                            }

                            $listaOpcionRepository = $this->em->getRepository(ListaOpcion::class);
                            $listaOpcion = $listaOpcionRepository->find($data['lista_opcion_id']);
                            $pasajeroListaOpcion = new PasajeroListaOpcion();
                            $pasajeroListaOpcion->setListaOpcion($listaOpcion);
                            $pasajeroListaOpcion->setPasajero($pasajero);
                            $dateAux = date('Y-m-d H:i:s');
                            $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
                            $pasajeroListaOpcion->setFechaAnotado($date_fecha_actual);
                            $entityManager->persist($pasajeroListaOpcion);
                            $entityManager->flush();
                            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El usuario se inscribio a la lista opcion.'], 200);
                        } else {
                            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar una lista opcion valida.'], 400);
                        }
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el pasajero.'], 400);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro a la persona.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro al usuario.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Error de autorizacion.'], 400);
        }
    }

    /**
     * @Route("/lista/remove_user_option_list", methods={"POST"}, name="lista_remove_user_option_list")
     * @param Request $request
     * @return JsonResponse
     */
    public function remove_user_option_list(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
    {
        $entityManager = $this->em;
        $data = json_decode($request->getContent(), true);
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
                    $pasajeros = $pasajeroRepository->findBy(['Persona' => $persona->getId()]);
                    $pasajero = $pasajeros[0];
                    if ($pasajero) {
                        if (isset($data['lista_opcion_id']) && $data['lista_opcion_id'] !== null) {
                            $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                            $pasajerosListaOpcionAux = $pasajeroListaOpcionRepository->findBy([
                                'pasajero' => $pasajero->getId(),
                                'lista_opcion' => $data['lista_opcion_id']
                            ]);
                            if ($pasajerosListaOpcionAux && count($pasajerosListaOpcionAux) > 0) {
                                $pasajeroListaOpcion = $pasajerosListaOpcionAux[0];
                                $this->em->remove($pasajeroListaOpcion);
                                $this->em->flush();
                                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El usuario se desinscribio a la lista opcion.'], 200);
                            } else {
                                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro para este pasajero y esa lista opcion.'], 400);
                            }
                        } else {
                            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar una lista opcion valida.'], 400);
                        }
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el pasajero.'], 400);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro a la persona.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro al usuario.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Error de autorizacion.'], 400);
        }
    }

    /**
     * @Route("/lista/list_by_group", methods={"GET"}, name="lista_listByGroup")
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
                    $itinerarioId = $pasajeroRepository->findByPersonaItinerario($persona);

                    // Obtengo el grupo para poder luego buscar los itinerarios de ese grupo, las listas de ese grupo y las ciudades de ese grupo
                    $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                    $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                    $grupo = $itinerario_aux->getGrupo();
                    $listaRepository = $this->em->getRepository(Lista::class);
                    $listas = $listaRepository->findBy(['grupo' => $grupo->getId()]);

                    if (!$listas) {
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro ninguna lista.'], 400);
                    }

                    $data = [];

                    foreach ($listas as $lista) {
                        $listaOpcionRepository = $this->em->getRepository(ListaOpcion::class);
                        $listaOpciones = $listaOpcionRepository->findBy(['lista' => $lista->getId()]);

                        $total_inscriptos = 0;
                        foreach ($listaOpciones as $listaOpcion) {
                            $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                            $pasajeroListaOpciones = $pasajeroListaOpcionRepository->findBy(['lista_opcion' => $listaOpcion->getId()]);
                            foreach ($pasajeroListaOpciones as $pasajeroListaOpcion) {
                                $total_inscriptos = $total_inscriptos + 1;
                            }
                        }
                        $data[] = [
                            'id' => $lista->getId(),
                            'titulo' => $lista->getTitulo(),
                            'imagen' => $lista->getImagen(),
                            'inscriptos' => $total_inscriptos,
                        ];
                    }

                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Debe ingresar autorizacion.'], 400);
        }
    }

    /**
     * @Route("/lista/list_by_user", methods={"GET"}, name="lista_listByUser")
     * @return JsonResponse
     */
    public function listByUser(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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
                    $listaRepository = $this->em->getRepository(Lista::class);
                    $listas = $listaRepository->findBy(['grupo' => $grupo->getId()]);

                    if (!$listas) {
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => [], 'message' => 'No se encontro ninguna lista.'], 400);
                    }

                    $data = [];

                    foreach ($listas as $lista) {
                        $data[] = [
                            'id' => $lista->getId(),
                            'titulo' => $lista->getTitulo(),
                            'imagen' => $lista->getImagen(),
                            'fecha_limite' => ($lista->getDisponibleHasta() !== null) ? $lista->getDisponibleHasta()->format('Y-m-d H:i:s') : null,
                        ];
                    }
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Debe ingresar autorizacion.'], 400);
        }
    }

    /**
     * @Route("/lista/detalle_list_user/{lista_id}", methods={"GET"}, name="lista_detalle_list_user")
     * @param int $lista_id
     * @return JsonResponse
     */
    public function detalle_list_user(int $lista_id, Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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
                    $listaRepository = $this->em->getRepository(Lista::class);
                    $lista = $listaRepository->find($lista_id);

                    if (!$lista || $lista == null) {
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro ninguna lista con ese id.'], 400);
                    }

                    $listaOpcionRepository = $this->em->getRepository(ListaOpcion::class);
                    $listaOpciones = $listaOpcionRepository->findBy(['lista' => $lista->getId()]);

                    $data_lista_opciones = [];

                    foreach ($listaOpciones as $lista_opcion) {
                        $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                        $pasajeroListaOpciones = $pasajeroListaOpcionRepository->findBy(['lista_opcion' => $lista_opcion->getId()]);

                        $inscriptos = [];
                        $total_inscriptos = 0;
                        foreach ($pasajeroListaOpciones as $pasajero_lista_opcion) {
                            $total_inscriptos = $total_inscriptos + 1;
                            $inscriptos[] = [
                                'pasajero_id' => $pasajero_lista_opcion->getPasajero()->getId(),
                                'nombre' => $pasajero_lista_opcion->getPasajero()->getPersona()->getNombres(),
                                'apellido' => $pasajero_lista_opcion->getPasajero()->getPersona()->getApellidos(),
                            ];
                        }

                        $data_lista_opciones[] = [
                            'id' => $lista_opcion->getId(),
                            'titulo' => $lista_opcion->getTitulo(),
                            'descripcion' => $lista_opcion->getDescripcion(),
                            'cupo_limite' => $lista_opcion->getCupoLimite(),
                            'precio' => $lista_opcion->getPrecio(),
                            'total_inscriptos' => $total_inscriptos,
                            'inscriptos' => $inscriptos
                        ];
                    }

                    $grupo = $lista->getGrupo();
                    $ciudad = $lista->getCiudad();
                    $itinerario = $lista->getItinerario();
                    $lista_estado = $lista->getListaEstado();
                    $data = [
                        'id' => $lista->getId(),
                        'titulo' => $lista->getTitulo(),
                        'descripcion' => $lista->getDescripcion(),
                        'limite_opciones' => $lista->getLimiteOpciones(),
                        'disponible_hasta' => ($lista->getDisponibleHasta() !== null) ? $lista->getDisponibleHasta()->format('Y-m-d H:i:s') : null,
                        'fecha_programada' => ($lista->getFechaProgramada() !== null) ? $lista->getFechaProgramada()->format('Y-m-d H:i:s') : null,
                        'imagen' => $lista->getImagen(),
                        'lista_opcion' => $data_lista_opciones,
                        'grupo' => ($grupo !== null) ? [
                            'id' => $grupo->getId(),
                            'nombre' => $grupo->getNombre(),
                        ] : null,
                        'ciudad' => ($ciudad !== null) ? [
                            'id' => $ciudad->getId(),
                            'nombre' => $ciudad->getNombre(),
                        ] : null,
                        'itinerario' => ($itinerario !== null) ? [
                            'id' => $itinerario->getId(),
                            'nombre' => $itinerario->getNombre(),
                        ] : null,
                        'lista_estado' => ($lista_estado !== null) ? [
                            'id' => $lista_estado->getId(),
                            'descripcion' => $lista_estado->getDescripcion(),
                        ] : null,
                    ];
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                }
            }
        }
    }

    /**
     * @Route("/lista/detalle_list_admin/{lista_id}", methods={"GET"}, name="lista_detalle_list_admin")
     * @param int $lista_id
     * @return JsonResponse
     */
    public function detalle_list_admin(int $lista_id, Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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
                    $listaRepository = $this->em->getRepository(Lista::class);
                    $lista = $listaRepository->find($lista_id);

                    if (!$lista || $lista == null) {
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro ninguna lista con ese id.'], 400);
                    }

                    $listaOpcionRepository = $this->em->getRepository(ListaOpcion::class);
                    $listaOpciones = $listaOpcionRepository->findBy(['lista' => $lista->getId()]);

                    $data_lista_opciones = [];

                    foreach ($listaOpciones as $lista_opcion) {
                        $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                        $pasajeroListaOpciones = $pasajeroListaOpcionRepository->findBy(['lista_opcion' => $lista_opcion->getId()]);

                        $inscriptos = [];
                        $total_inscriptos = 0;
                        foreach ($pasajeroListaOpciones as $pasajero_lista_opcion) {
                            $total_inscriptos = $total_inscriptos + 1;
                            $inscriptos[] = [
                                'pasajero_id' => $pasajero_lista_opcion->getPasajero()->getId(),
                                'nombre' => $pasajero_lista_opcion->getPasajero()->getPersona()->getNombres(),
                                'apellido' => $pasajero_lista_opcion->getPasajero()->getPersona()->getApellidos(),
                                'fecha_anotado' => ($pasajero_lista_opcion->getFechaAnotado() !== null) ? $pasajero_lista_opcion->getFechaAnotado()->format('Y-m-d H:i:s') : null,
                                'fecha_pago' => ($pasajero_lista_opcion->getFechaPago() !== null) ? $pasajero_lista_opcion->getFechaPago()->format('Y-m-d H:i:s') : null,
                            ];
                        }

                        $data_lista_opciones[] = [
                            'id' => $lista_opcion->getId(),
                            'titulo' => $lista_opcion->getTitulo(),
                            'descripcion' => $lista_opcion->getDescripcion(),
                            'cupo_limite' => $lista_opcion->getCupoLimite(),
                            'precio' => $lista_opcion->getPrecio(),
                            'total_inscriptos' => $total_inscriptos,
                            'inscriptos' => $inscriptos
                        ];
                    }

                    $grupo = $lista->getGrupo();
                    $ciudad = $lista->getCiudad();
                    $itinerario = $lista->getItinerario();
                    $lista_estado = $lista->getListaEstado();
                    $data = [
                        'id' => $lista->getId(),
                        'titulo' => $lista->getTitulo(),
                        'descripcion' => $lista->getDescripcion(),
                        'limite_opciones' => $lista->getLimiteOpciones(),
                        'disponible_hasta' => ($lista->getDisponibleHasta() !== null) ? $lista->getDisponibleHasta()->format('Y-m-d H:i:s') : null,
                        'fecha_programada' => ($lista->getFechaProgramada() !== null) ? $lista->getFechaProgramada()->format('Y-m-d H:i:s') : null,
                        'imagen' => $lista->getImagen(),
                        'lista_opcion' => $data_lista_opciones,
                        'grupo' => ($grupo !== null) ? [
                            'id' => $grupo->getId(),
                            'nombre' => $grupo->getNombre(),
                        ] : null,
                        'ciudad' => ($ciudad !== null) ? [
                            'id' => $ciudad->getId(),
                            'nombre' => $ciudad->getNombre(),
                        ] : null,
                        'itinerario' => ($itinerario !== null) ? [
                            'id' => $itinerario->getId(),
                            'nombre' => $itinerario->getNombre(),
                        ] : null,
                        'lista_estado' => ($lista_estado !== null) ? [
                            'id' => $lista_estado->getId(),
                            'descripcion' => $lista_estado->getDescripcion(),
                        ] : null,
                    ];
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                }
            }
        }
    }

    /**
     * @Route("/lista/user_payment_option_list", methods={"POST"}, name="lista_user_payment_option_list")
     * @param Request $request
     * @return JsonResponse
     */
    public function user_payment_option_list(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
    {
        $entityManager = $this->em;
        $data = json_decode($request->getContent(), true);
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                $personaRepository = $this->em->getRepository(Persona::class);
                $persona = $personaRepository->getPersonaById($id);
                if ($persona) {
                    if (isset($data['pasajero_id']) && $data['pasajero_id'] !== null) {
                        $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                        $pasajero = $pasajeroRepository->find($data['pasajero_id']);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar un pasajero id valido.'], 400);
                    }

                    if (isset($data['lista_opcion_id']) && $data['lista_opcion_id'] !== null) {
                        $listaOpcionRepository = $this->em->getRepository(ListaOpcion::class);
                        $listaOpcion = $listaOpcionRepository->find($data['lista_opcion_id']);
                        $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                        $pasajerosListaOpcion = $pasajeroListaOpcionRepository->findBy([
                            'pasajero' => $pasajero->getId(),
                            'lista_opcion' => $listaOpcion->getId()
                        ]);
                        if ($pasajerosListaOpcion && count($pasajerosListaOpcion) > 0) {
                            $pasajeroListaOpcion = $pasajerosListaOpcion[0];
                            $dateAux = date('Y-m-d H:i:s');
                            $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
                            $pasajeroListaOpcion->setFechaPago($date_fecha_actual);
                            $entityManager->flush();
                            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El usuario pago la lista opcion.'], 200);
                        } else {
                            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro para ese pasajero en esa lista opcion.'], 400);
                        }
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar una lista opcion valida.'], 400);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro a la persona.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro al usuario.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Error de autorizacion.'], 400);
        }
    }

    /**
     * @Route("/lista/remove_user_payment_option_list", methods={"POST"}, name="lista_remove_user_payment_option_list")
     * @param Request $request
     * @return JsonResponse
     */
    public function remove_user_payment_option_list(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
    {
        $entityManager = $this->em;
        $data = json_decode($request->getContent(), true);
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                $personaRepository = $this->em->getRepository(Persona::class);
                $persona = $personaRepository->getPersonaById($id);
                if ($persona) {
                    if (isset($data['pasajero_id']) && $data['pasajero_id'] !== null) {
                        $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                        $pasajero = $pasajeroRepository->find($data['pasajero_id']);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar un pasajero id valido.'], 400);
                    }

                    if (isset($data['lista_opcion_id']) && $data['lista_opcion_id'] !== null) {
                        $listaOpcionRepository = $this->em->getRepository(ListaOpcion::class);
                        $listaOpcion = $listaOpcionRepository->find($data['lista_opcion_id']);
                        $pasajeroListaOpcionRepository = $this->em->getRepository(PasajeroListaOpcion::class);
                        $pasajerosListaOpcion = $pasajeroListaOpcionRepository->findBy([
                            'pasajero' => $pasajero->getId(),
                            'lista_opcion' => $listaOpcion->getId()
                        ]);
                        if ($pasajerosListaOpcion && count($pasajerosListaOpcion) > 0) {
                            $pasajeroListaOpcion = $pasajerosListaOpcion[0];
                            if ($pasajeroListaOpcion->getFechaPago() !== null) {
                                $pasajeroListaOpcion->setFechaPago(null);
                                $entityManager->flush();
                                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se quito el pago del pasajero a la lista opcion.'], 200);
                            } else {
                                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero esta inscripto a la lista opcion pero no pago.'], 400);
                            }
                        } else {
                            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro para ese pasajero en esa lista opcion.'], 400);
                        }
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Debe enviar una lista opcion valida.'], 400);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro a la persona.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro al usuario.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Error de autorizacion.'], 400);
        }
    }
}
