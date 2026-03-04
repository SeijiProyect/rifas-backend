<?php

namespace App\Controller;

use App\Entity\Pasajero;
use App\Entity\Servicio;
use App\Entity\Persona;
use App\Entity\Itinerario;
use App\Entity\ItinerarioDetalle;
use App\Entity\PasajeroServicio;
use App\Entity\Universidad;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\PasajeroRepository;
use App\Repository\DepositoRepository;
use App\Repository\PagoPersonalRepository;
use App\Repository\TalonRepository;
use App\Repository\TarjetaRepository;
use App\Repository\CostoExtraRepository;
use App\Repository\ItinerarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

// PRUEBA DE SUBIDA GIT
class PasajeroController extends AbstractController
{

    private $pasajeroRepository;
    private $em;

    public function __construct(PasajeroRepository $pasajeroRepository, EntityManagerInterface $em)
    {
        $this->pasajeroRepository = $pasajeroRepository;
        $this->em = $em;
    }

    /**
     * @Route("/pasajero", name="pasajero")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PasajeroController.php',
        ]);
    }


    /**
     * @Route("api/pasajero/get-pasajero-by-id-post", name="get-pasajero-byIdPasajero", methods={"POST"})
     */
    public function getPasajeroByIdPOST(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pasajeroId = (isset($data['idPasajero'])) ? $data['idPasajero'] : null;

        $pasajero = $pasajeroRepository->find($pasajeroId);

        if ($pasajero) {
            $itinerario = $pasajero->getItinerario();
            $acompanante = "Titular";

            if ($pasajero->getAcompanante()) {
                $nombre = $pasajero->getAcompanante()->getPersona()->getNombres();
                $apellido = $pasajero->getAcompanante()->getPersona()->getApellidos();

                $acompanante = $nombre . " " . $apellido;
            }

            $res = [
                'id' => $pasajero->getId(),
                'estado' => $pasajero->getEstado(),
                'comentarios' => $pasajero->getComentarios(),
                'acompaniante' => $acompanante,
                'universidad' => $pasajero->getUniversidad(),

                'itinerario' => [
                    'idItinerario' => $itinerario->getId(),
                    'precio' => $itinerario->getPrecio(),
                    'nombre' => $itinerario->getNombre(),
                ],
            ];
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pasajero encontrado', 'data' => $res], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
        }
    }

    /**
     * @Route("/pasajero/get_by_id/{pasajeroId}", methods={"GET"}, name="pasajero_getPasajeroById")
     * @param int $pasajeroId
     * @return JsonResponse
     */
    public function getPasajeroById(int $pasajeroId): JsonResponse
    {
        $pasajeroRepository = $this->em->getRepository(Pasajero::class);
        $pasajero = $pasajeroRepository->find($pasajeroId);

        if (!$pasajero) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el pasajero'], 400);
        }

        $persona = $pasajero->getPersona();
        $itinerario = $pasajero->getItinerario();
        $universidad = $pasajero->getUniversidad();
        if ($pasajero->getCreatedAt()) {
            $pasajero_created_at = $pasajero->getCreatedAt()->format('Y-m-d H:i:s');
        } else {
            $pasajero_created_at = null;
        }
        if ($pasajero->getUpdatedAt()) {
            $pasajero_updated_at = $pasajero->getUpdatedAt()->format('Y-m-d H:i:s');
        } else {
            $pasajero_updated_at = null;
        }
        if ($persona->getFechaNacimiento()) {
            $persona_fecha_nacimiento = $persona->getFechaNacimiento()->format('Y-m-d');
        } else {
            $persona_fecha_nacimiento = null;
        }
        if ($itinerario->getFechaInicio()) {
            $itinerario_fecha_inicio = $itinerario->getFechaInicio()->format('Y-m-d H:i:s');
        } else {
            $itinerario_fecha_inicio = null;
        }
        if ($itinerario->getFechaFin()) {
            $itinerario_fecha_fin = $itinerario->getFechaFin()->format('Y-m-d H:i:s');
        } else {
            $itinerario_fecha_fin = null;
        }

        $data = [
            'id' => $pasajero->getId(),
            'estado' => $pasajero->getEstado(),
            'comentarios' => $pasajero->getComentarios(),
            'created_at' => $pasajero_created_at,
            'updated_at' => $pasajero_updated_at,
            'persona' => ($persona !== null) ? [
                'id' => $persona->getId(),
                'nombres' => $persona->getNombres(),
                'apellidos' => $persona->getApellidos(),
                'fecha_nacimiento' => $persona_fecha_nacimiento,
                'cedula' => $persona->getCedula(),
                'sexo' => $persona->getSexo(),
                'telefono' => $persona->getCelular(),
            ] : null,
            'itinerario' => ($itinerario !== null) ? [
                'id' => $itinerario->getId(),
                'nombre' => $itinerario->getNombre(),
            ] : null,
            'universidad' => ($universidad !== null) ? [
                'id' => $universidad->getId(),
                'nombre' => $universidad->getNombre(),
            ] : null,
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/pasajero/list_web", methods={"POST"}, name="pasajero_list_web")
     * @return JsonResponse
     */
    public function list_web(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $pasajeroRepository = $this->em->getRepository(Pasajero::class);

        $pasajeroTotal = $pasajeroRepository->findAll();

        $pasajeros = $pasajeroRepository->findBy(
            array(),
            array('id' => 'DESC'),
            $limit,
            $offset
        );

        $data = [];

        foreach ($pasajeros as $pasajero) {
            $persona = $pasajero->getPersona();
            $acompanante = $pasajero->getAcompanante();
            $itinerario = $pasajero->getItinerario();
            $universidad = $pasajero->getUniversidad();
            if ($pasajero->getCreatedAt()) {
                $pasajero_created_at = $pasajero->getCreatedAt()->format('Y-m-d H:i:s');
            } else {
                $pasajero_created_at = null;
            }
            if ($pasajero->getUpdatedAt()) {
                $pasajero_updated_at = $pasajero->getUpdatedAt()->format('Y-m-d H:i:s');
            } else {
                $pasajero_updated_at = null;
            }
            if ($persona->getFechaNacimiento()) {
                $persona_fecha_nacimiento = $persona->getFechaNacimiento()->format('Y-m-d');
            } else {
                $persona_fecha_nacimiento = null;
            }
            if ($itinerario->getFechaInicio()) {
                $itinerario_fecha_inicio = $itinerario->getFechaInicio()->format('Y-m-d H:i:s');
            } else {
                $itinerario_fecha_inicio = null;
            }
            if ($itinerario->getFechaFin()) {
                $itinerario_fecha_fin = $itinerario->getFechaFin()->format('Y-m-d H:i:s');
            } else {
                $itinerario_fecha_fin = null;
            }

            $data[] = [
                'id' => $pasajero->getId(),
                'estado' => $pasajero->getEstado(),
                'comentarios' => $pasajero->getComentarios(),
                'created_at' => $pasajero_created_at,
                'updated_at' => $pasajero_updated_at,
                'persona' => ($persona !== null) ? [
                    'id' => $persona->getId(),
                    'nombres' => $persona->getNombres(),
                    'apellidos' => $persona->getApellidos(),
                    'fecha_nacimiento' => $persona_fecha_nacimiento,
                    'cedula' => $persona->getCedula(),
                    'sexo' => $persona->getSexo(),
                    'telefono' => $persona->getCelular(),
                ] : null,
                'itinerario' => ($itinerario !== null) ? [
                    'id' => $itinerario->getId(),
                    'nombre' => $itinerario->getNombre(),
                ] : null,
                'universidad' => ($universidad !== null) ? [
                    'id' => $universidad->getId(),
                    'nombre' => $universidad->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($pasajeroTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/pasajero/create", methods={"POST"}, name="pasajero_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $pasajeroRepository = $this->em->getRepository(Pasajero::class);

        $query = $pasajeroRepository->createQueryBuilder('p')
            ->where('p.Persona = :persona_id')
            ->setParameter('persona_id', $data['pasajero']['persona_id'])
            ->getQuery();

        $pasajerosAux = $query->getResult();

        if ($pasajerosAux && count($pasajerosAux) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'La persona ya tiene un itinerario asignado.'], 400);
        }

        $pasajero = new Pasajero();
        $personaRepository = $this->em->getRepository(Persona::class);
        $persona = $personaRepository->find($data['pasajero']['persona_id']);
        $pasajero->setPersona($persona);
        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
        $itinerario = $itinerarioRepository->find($data['pasajero']['itinerario_id']);
        $pasajero->setItinerario($itinerario);
        $universidadRepository = $this->em->getRepository(Universidad::class);
        $universidad = $universidadRepository->find($data['pasajero']['universidad_id']);
        $pasajero->setUniversidad($universidad);
        $pasajero->setEstado($data['pasajero']['estado']);
        $pasajero->setComentarios($data['pasajero']['comentarios']);
        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
        $pasajero->setCreatedAt($date_fecha_actual);
        $pasajero->setUpdatedAt($date_fecha_actual);


        $entityManager->persist($pasajero);
        $entityManager->flush();

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El pasajero se creo correctamente'], 200);
    }

    /**
     * @Route("/pasajero/update-itinerario-pasajero", methods={"POST"}, name="itinerario_pasajero_update")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateItinerarioPasajero(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        ItinerarioRepository $itinerarioRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['id'])) ? $data['id'] : null;
        $idItinerario = (isset($data['idItinerario'])) ? $data['idItinerario'] : null;

        $pasajero = $pasajeroRepository->find($id);

        if (!$pasajero) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el pasajero'], 400);
        }

        $itinerario = $itinerarioRepository->find($idItinerario);

        if (!$itinerario) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el itinerario'], 400);
        }

        $pasajero->setItinerario($itinerario);
        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
        $pasajero->setUpdatedAt($date_fecha_actual);
        $pasajero->setDeletedAt(null);

        $res = $pasajeroRepository->update($pasajero);

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $res, 'message' => 'El pasajero se actualizo correctamente'], 200);
    }

    /**
     * @Route("/pasajero/update/{id}", methods={"PUT", "PATCH"}, name="pasajero_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pasajeroRepository = $this->em->getRepository(Pasajero::class);
        $pasajero = $pasajeroRepository->find($id);

        if (!$pasajero) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el pasajero'], 400);
        }

        $personaRepository = $this->em->getRepository(Persona::class);
        $persona = $personaRepository->find($data['pasajero']['persona_id']);
        $pasajero->setPersona($persona);
        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
        $itinerario = $itinerarioRepository->find($data['pasajero']['itinerario_id']);
        $pasajero->setItinerario($itinerario);
        $universidadRepository = $this->em->getRepository(Universidad::class);
        $universidad = $universidadRepository->find($data['pasajero']['universidad_id']);
        $pasajero->setUniversidad($universidad);
        $pasajero->setEstado($data['pasajero']['estado']);
        $pasajero->setComentarios($data['pasajero']['comentarios']);
        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
        $pasajero->setUpdatedAt($date_fecha_actual);
        $pasajero->setDeletedAt(null);

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El pasajero se actualizo correctamente'], 200);
    }

    /**
     * @Route("/pasajero/delete/{id}", methods={"PUT", "PATCH"}, name="pasajero_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $pasajeroRepository = $this->em->getRepository(Pasajero::class);
        $pasajero = $pasajeroRepository->find($id);

        if (!$pasajero) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el pasajero'], 400);
        }

        $pasajero->setEstado("F");
        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
        $pasajero->setDeletedAt($date_fecha_actual);

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El pasajero se elimino correctamente'], 200);
    }

    /**
     * @Route("/pasajero/delete_all/{id}", methods={"DELETE"}, name="pasajero_delete_all")
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAll(int $id): JsonResponse
    {
        $pasajeroRepository = $this->em->getRepository(Pasajero::class);
        $pasajero = $pasajeroRepository->find($id);

        if (!$pasajero) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el pasajero'], 400);
        }

        $this->em->remove($pasajero);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El pasajero se elimino correctamente'], 200);
    }

    /**
     * @Route("/pasajero/add-remove-service", methods={"POST"}, name="pasajero_add_remove_service")
     * @param Request $request
     * @return JsonResponse
     */
    public function addRemoveService(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $pasajeroServicioRepository = $this->em->getRepository(PasajeroServicio::class);

        if (isset($data['quitar']) && count($data['quitar']) > 0) {
            foreach ($data['quitar'] as $pasajero_quitar) {
                $query = $pasajeroServicioRepository->createQueryBuilder('p')
                    ->where('p.servicio = :servicio_id')
                    ->andWhere('p.pasajero = :pasajero_id')
                    ->setParameter('servicio_id', $data['servicio_id'])
                    ->setParameter('pasajero_id', $pasajero_quitar['pasajero_id'])
                    ->getQuery();

                $pasajeroServicioAux = $query->getResult();

                if ($pasajeroServicioAux && count($pasajeroServicioAux) > 0) {
                    $this->em->remove($pasajeroServicioAux[0]);
                    $this->em->flush();
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'No se encontro registro para ese pasajero en ese servicio.'], 400);
                }
            }
        }

        if (isset($data['agregar']) && count($data['agregar']) > 0) {
            $servicioRepository = $this->em->getRepository(Servicio::class);
            $servicio = $servicioRepository->find($data['servicio_id']);
            foreach ($data['agregar'] as $pasajero_agregar) {
                $query = $pasajeroServicioRepository->createQueryBuilder('p')
                    ->where('p.servicio = :servicio_id')
                    ->andWhere('p.pasajero = :pasajero_id')
                    ->setParameter('servicio_id', $data['servicio_id'])
                    ->setParameter('pasajero_id', $pasajero_agregar['pasajero_id'])
                    ->getQuery();

                $pasajeroServicioAux = $query->getResult();

                if ($pasajeroServicioAux && count($pasajeroServicioAux) > 0) {
                } else {
                    $pasajeroServicio = new PasajeroServicio();
                    $pasajeroServicio->setServicio($servicio);
                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $pasajero = $pasajeroRepository->find($pasajero_agregar['pasajero_id']);
                    $pasajeroServicio->setPasajero($pasajero);
                    $entityManager->persist($pasajeroServicio);
                    $entityManager->flush();
                }
            }
        }
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se han agregado y quitado correctamente los pasajeros.'], 200);
    }

    /**
     * @Route("/pasajero/list_asociados_servicio", methods={"POST"}, name="pasajero_list_asociados_servicio")
     * @return JsonResponse
     */
    public function list_asociados_servicio(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pasajeroServicioRepository = $this->em->getRepository(PasajeroServicio::class);

        $parameters = [
            'servicio' => $data['servicio_id']
        ];

        $pasajeroServicio = $pasajeroServicioRepository->findBy(
            $parameters,
        );

        $data = [];

        if ($pasajeroServicio !== null && count($pasajeroServicio) > 0) {
            foreach ($pasajeroServicio as $pasajero_servicio_aux) {
                $pasajero_nombre = $pasajero_servicio_aux->getPasajero()->getPersona()->getNombres() . ' ' . $pasajero_servicio_aux->getPasajero()->getPersona()->getApellidos();
                $data[] = [
                    'pasajero_id' => $pasajero_servicio_aux->getPasajero()->getId(),
                    'pasajero_nombre' => $pasajero_nombre,
                ];
            }

            usort($data, function ($a, $b) {
                return strcmp($a['pasajero_nombre'], $b['pasajero_nombre']);
            });
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/pasajero/list_no_asociados_servicio", methods={"POST"}, name="pasajero_list_no_asociados_servicio")
     * @return JsonResponse
     */
    public function list_no_asociados_servicio(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $servicioRepository = $this->em->getRepository(Servicio::class);
        $servicio = $servicioRepository->find($data['servicio_id']);

        $data_return = [];

        if(isset($data['itinerario_id'])){
            
            $pasajeroServicioRepository = $this->em->getRepository(PasajeroServicio::class);
            $pasajerosAsignados = $pasajeroServicioRepository->findBy(['servicio' => $data['servicio_id']]);
            
            $pasajeroRepository = $this->em->getRepository(Pasajero::class);
            $pasajerosItinerario = $pasajeroRepository->findBy(['Itinerario' => $data['itinerario_id']]);
            
            foreach ($pasajerosItinerario as $pasajero_grupo_aux) {
                $bandera_asignado = false;
                if ($pasajerosAsignados !== null && count($pasajerosAsignados) > 0) {
                    foreach ($pasajerosAsignados as $pasajero_asignado_aux) {
                        if ($pasajero_asignado_aux->getPasajero()->getId() == $pasajero_grupo_aux->getId()) {
                            $bandera_asignado = true;
                        }
                    }
                }
                if ($bandera_asignado == false) {
                    $pasajero_nombre = $pasajero_grupo_aux->getPersona()->getNombres() . ' ' . $pasajero_grupo_aux->getPersona()->getApellidos();
                    $data_return[] = [
                        'pasajero_id' => $pasajero_grupo_aux->getId(),
                        'pasajero_nombre' => $pasajero_nombre,
                    ];
                }
            }
        }
        else if(isset($data['ciudad_id'])){
            $pasajeroServicioRepository = $this->em->getRepository(PasajeroServicio::class);
            $pasajerosAsignados = $pasajeroServicioRepository->findBy(['servicio' => $data['servicio_id']]);
            
            $itinerarioRepository = $this->em->getRepository(Itinerario::class);
            $itinerarios = $itinerarioRepository->findBy(['Grupo' => $servicio->getGrupo()->getId()]);

            $data_return = [];
            if ($itinerarios !== null && count($itinerarios) > 0) {
                foreach ($itinerarios as $itinerario) {
                    $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
                    $itinerariosDetalles = $itinerarioDetalleRepository->findBy(['itinerario' => $itinerario->getId()]);
                    foreach ($itinerariosDetalles as $itinerario_detalle) {
                        if($itinerario_detalle->getCiudad() !== null){
                            if($itinerario_detalle->getCiudad()->getId() === $data['ciudad_id']){
                                $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                                $pasajeros_return = $pasajeroRepository->findBy(['Itinerario' => $itinerario->getId()]);
                                if ($pasajeros_return !== null && count($pasajeros_return) > 0) {
                                    foreach ($pasajeros_return as $pasajero_grupo_aux) {
                                        $bandera_asignado = false;
                                        if ($pasajerosAsignados !== null && count($pasajerosAsignados) > 0) {
                                            foreach ($pasajerosAsignados as $pasajero_asignado_aux) {
                                                if ($pasajero_asignado_aux->getPasajero()->getId() == $pasajero_grupo_aux->getId()) {
                                                    $bandera_asignado = true;
                                                }
                                            }
                                        }
                                        if ($bandera_asignado == false) {
                                            $pasajero_nombre = $pasajero_grupo_aux->getPersona()->getNombres() . ' ' . $pasajero_grupo_aux->getPersona()->getApellidos();
                                            $data_return[] = [
                                                'pasajero_id' => $pasajero_grupo_aux->getId(),
                                                'pasajero_nombre' => $pasajero_nombre,
                                            ];
                                        }
                                    }
                                }
                            }
                        }    
                    }
                }
            }
        }
        else{
            $pasajeroServicioRepository = $this->em->getRepository(PasajeroServicio::class);
            $pasajerosAsignados = $pasajeroServicioRepository->findBy(['servicio' => $data['servicio_id']]);

            $itinerarioRepository = $this->em->getRepository(Itinerario::class);
            $itinerarios = $itinerarioRepository->findBy(['Grupo' => $servicio->getGrupo()->getId()]);

            if ($itinerarios !== null && count($itinerarios) > 0) {
                foreach ($itinerarios as $itinerario) {
                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $pasajerosGrupo = $pasajeroRepository->findBy(['Itinerario' => $itinerario->getId()]);

                    if ($pasajerosGrupo !== null && count($pasajerosGrupo) > 0) {
                        foreach ($pasajerosGrupo as $pasajero_grupo_aux) {
                            $bandera_asignado = false;
                            if ($pasajerosAsignados !== null && count($pasajerosAsignados) > 0) {
                                foreach ($pasajerosAsignados as $pasajero_asignado_aux) {
                                    if ($pasajero_asignado_aux->getPasajero()->getId() == $pasajero_grupo_aux->getId()) {
                                        $bandera_asignado = true;
                                    }
                                }
                            }
                            if ($bandera_asignado == false) {
                                $pasajero_nombre = $pasajero_grupo_aux->getPersona()->getNombres() . ' ' . $pasajero_grupo_aux->getPersona()->getApellidos();
                                $data_return[] = [
                                    'pasajero_id' => $pasajero_grupo_aux->getId(),
                                    'pasajero_nombre' => $pasajero_nombre,
                                ];
                            }
                        }
                    }
                }
            }

            usort($data, function ($a, $b) {
                return strcmp($a['pasajero_nombre'], $b['pasajero_nombre']);
            });
        }

        

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data_return], 200);
    }

    /**
     * @Route("/api/pasajero/get-pasajero-by-user", name="get-pasajero-by-user", methods={"POST"})
     */
    public function getPasajeroByUser(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $token = (isset($data['token'])) ? $data['token'] : null;

        if ($token != null) {
            $pasajero = $pasajeroRepository->getPasajeroByToken($token);
            if ($pasajero) {
                $res = [
                    'persona' => [
                        'name' => $pasajero->getPersona()->getNombres(),
                        'lastName' => $pasajero->getPersona()->getApellidos()
                    ],
                    'pasajeroId' => $pasajero->getId()
                ];

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pasajero encontrado', 'data' => $res], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/pasajero/get-pasajero-itinerario-by-user", name="get-pasajero-itinerario-by-user", methods={"GET"})
     */
    public function getItinerarioPasajeroByUser(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {
            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);
            if ($pasajero) {
                $itinerario = $pasajero->getItinerario();
                $res = [
                    'itinerario' => [
                        'precio' => $itinerario->getPrecio(),
                        'nombre' => $itinerario->getNombre(),
                    ],
                ];
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pasajero encontrado', 'data' => $res], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/pasajero/get-pasajero-itinerario", name="get-pasajero-itinerario", methods={"POST"})
     */
    public function getItinerarioPasajeroByIdPasajero(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pasajeroId = (isset($data['pasajero'])) ? $data['pasajero'] : null;

        $pasajero = $pasajeroRepository->findOneBy(
            array('id' => $pasajeroId)
        );

        if ($pasajero != null) {
            if ($pasajero) {
                $itinerario = $pasajero->getItinerario();
                $res = [
                    'itinerario' => [
                        'id' => $itinerario->getId(),
                        'precio' => $itinerario->getPrecio(),
                        'nombre' => $itinerario->getNombre(),
                        'idViaje' => $itinerario->getViaje()->getId()
                    ],
                ];

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pasajero encontrado', 'data' => $res], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/pasajero/get-pasajero-costos-extras", name="get-pasajero-costosExtras", methods={"POST"})
     */
    public function getCostosExtrasByIdPasajero(Request $request, PasajeroRepository $pasajeroRepository, CostoExtraRepository $costosExtrasRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pasajeroId = (isset($data['pasajero'])) ? $data['pasajero'] : null;

        $pasajero = $pasajeroRepository->findOneBy(
            array('id' => $pasajeroId)
        );
        $data = [];
        if ($pasajero != null) {
            if ($pasajero) {
                $data = $costosExtrasRepository->costosExtrasByPasajero($pasajero);

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pasajero Costos extras', 'data' => $data], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/pasajero/get-pasajero-depositos", name="get-pasajero-depositos", methods={"POST"})
     */
    public function getDepositosByIdPasajero(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        DepositoRepository $depositosRepository,
        TalonRepository $talonRepository,
        PagoPersonalRepository $pagoPersonalRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $pasajeroId = (isset($data['pasajero'])) ? $data['pasajero'] : null;

        $pasajero = $pasajeroRepository->findOneBy(
            array('id' => $pasajeroId)
        );
        if ($pasajero != null) {
            if ($pasajero) {
                $depositos = $depositosRepository->depositosByPasajero($pasajero);

                $data = [];
                foreach ($depositos as $deposito) {
                    $totalTalones = 0;
                    $totalRecaudadoTalones = 0;
                    $talones = [];
                    $talones = $talonRepository->talonesByDeposito($deposito['id']);

                    if ($talones[0]['total_registrado'] != null) {
                        $totalTalones = $talones[0]['total_registrado'];
                        $totalRecaudadoTalones = $talones[0]['total_recaudado'];
                    }

                    $totalpagosPersonales = 0;
                    $pagosPersonales = [];
                    $pagosPersonales = $pagoPersonalRepository->pagosPersonalesByDeposito($deposito['id']);
                    if ($pagosPersonales[0]['total'] != null) {
                        $totalpagosPersonales = $pagosPersonales[0]['total'];
                    }

                    $data[] = [
                        'id' => $deposito['id'],
                        'tipo' => $deposito['Tipo'],
                        'fecha' => $deposito['Fecha'],
                        'monto' => $deposito['Monto'],
                        'pagoPersonal_total' => $totalpagosPersonales,
                        'talones_total' => $totalTalones,
                        'talones_recaudado_total' => $totalRecaudadoTalones
                    ];
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pasajero Depositos', 'data' => $data], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/pasajero/get-itinerario", name="get-itinerario-by-pasajero", methods={"POST"})
     */
    public function getItinerarioByPasajero(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pasajeroId = (isset($data['pasajero'])) ? $data['pasajero'] : null;

        $pasajero = $pasajeroRepository->findOneBy(
            array('id' => $pasajeroId)
        );

        $res = [
            'id' => $pasajero->getId(),
            'estado' => $pasajero->getEstado(),

            'itinerario' => [
                'idItinerario' => $pasajero->getItinerario()->getId(),
                'precio' => $pasajero->getItinerario()->getPrecio(),
                'nombre' => $pasajero->getItinerario()->getNombre(),
            ],
            'viaje' => [
                'idViaje' => $pasajero->getItinerario()->getViaje()->getId(),
                'nombreViaje' => $pasajero->getItinerario()->getViaje()->getNombre(),
            ],

        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Itinerario encontrado', 'data' => $res], 200);
    }

    /**
     * @Route("api/pasajero/get-pasajero-activo-by-persona", name="get-pasajero-activo-byPersona", methods={"POST"})
     */
    public function getPasajeroActivoByPersona(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $idPersona = (isset($data['idPersona'])) ? $data['idPersona'] : null;

        if ($idPersona != null) {
            $pasajero = $pasajeroRepository->getPasajeroActivoByPersona($idPersona);
            if ($pasajero) {
                $itinerario = $pasajero[0]->getItinerario();
                $acompanante = "Titular";

                if ($pasajero[0]->getAcompanante()) {
                    $nombre = $pasajero[0]->getAcompanante()->getPersona()->getNombres();
                    $apellido = $pasajero[0]->getAcompanante()->getPersona()->getApellidos();

                    $acompanante = $nombre . " " . $apellido;
                }

                $res = [
                    'id' => $pasajero[0]->getId(),
                    'estado' => $pasajero[0]->getEstado(),
                    'comentarios' => $pasajero[0]->getComentarios(),
                    'acompaniante' => $acompanante,
                    'universidad' => $pasajero[0]->getUniversidad(),

                    'itinerario' => [
                        'idItinerario' => $itinerario->getId(),
                        'precio' => $itinerario->getPrecio(),
                        'nombre' => $itinerario->getNombre(),
                    ],
                ];
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pasajero encontrado', 'data' => $res], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/pasajero/get-depositos-registros-list-by-user", name="get-depositos-registros-list-by-user", methods={"GET"})
     */
    public function getDepositosRegistrosListByUser(Request $request, PasajeroRepository $pasajeroRepository, DepositoRepository $depositoRepository, TalonRepository $talonRepository, PagoPersonalRepository $pagoPersonalRepository)
    {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                $totalDepositado = 0;
                $totalRegistrado = 0;
                $totalRecaudado = 0.00;

                $totalCreditoDepositado = 0;
                $totalCreditoRegistrado = 0;
                $totalCreditoRecaudado = 0;

                $totalDebitoDepositado = 0;
                $totalDebitoRegistrado = 0;
                $totalDebitoRecaudado = 0;

                $totalContadoDepositado = 0;
                $totalContadoRegistrado = 0;
                $totalContadoRecaudado = 0;

                $totalPagoPersonalCredito = 0;
                $totalPagoPersonalDebito = 0;
                $totalPagoPersonalContado = 0;

                $totalRifasCreditoRegistrado = 0;
                $totalRifasCreditoRecaudado = 0;

                $totalRifasDebitoRegistrado = 0;
                $totalRifasDebitoRecaudado = 0;

                $totalRifasContadoRegistrado = 0;
                $totalRifasContadoRecaudado = 0;

                $depositos = $depositoRepository->findBy(
                    array(
                        "Pasajero" => $pasajero
                    )
                );

                $talones = $talonRepository->findBy(
                    array(
                        "Pasajero" => $pasajero
                    )
                );

                $pagospersonales = $pagoPersonalRepository->findBy(
                    array(
                        "Deposito" => $depositos
                    )
                );

                foreach ($depositos as $dep) {
                    $totalDepositado += $dep->getMonto();

                    if ($dep->getTipo() == 'Credito') {
                        $totalCreditoDepositado += $dep->getMonto();
                    } else if ($dep->getTipo() == 'Debito') {
                        $totalDebitoDepositado += $dep->getMonto();
                    } else if ($dep->getTipo() == 'Contado') {
                        $totalContadoDepositado += $dep->getMonto();
                    }
                }

                foreach ($talones as $tal) {
                    if ($tal->getDeposito() != null) {
                        // $totalRegistrado += 20;
                        //$totalRecaudado += 16;

                        $totalRegistrado += $tal->getValor();
                        $totalRecaudado += $tal->getRecaudacion();

                        if ($tal->getDeposito()->getTipo() == 'Credito') {
                            /*$totalCreditoRegistrado += 20;
                            $totalCreditoRecaudado += 16;
                            $totalRifasCreditoRegistrado += 20;
                            $totalRifasCreditoRecaudado += 16;*/

                            $totalCreditoRegistrado += $tal->getValor();
                            $totalCreditoRecaudado += $tal->getRecaudacion();
                            $totalRifasCreditoRegistrado += $tal->getValor();
                            $totalRifasCreditoRecaudado += $tal->getRecaudacion();
                        } else if ($tal->getDeposito()->getTipo() == 'Debito') {
                            /*  $totalDebitoRegistrado += 20;
                              $totalDebitoRecaudado += 16;
                              $totalRifasDebitoRegistrado += 20;
                              $totalRifasDebitoRecaudado += 16;*/

                            $totalDebitoRegistrado += $tal->getValor();
                            $totalDebitoRecaudado += $tal->getRecaudacion();
                            $totalRifasDebitoRegistrado += $tal->getValor();
                            $totalRifasDebitoRecaudado += $tal->getRecaudacion();
                        } else if ($tal->getDeposito()->getTipo() == 'Contado') {
                            /* $totalContadoRegistrado += 20;
                             $totalContadoRecaudado += 16;
                             $totalRifasContadoRegistrado += 20;
                             $totalRifasContadoRecaudado += 16;*/

                            $totalContadoRegistrado += $tal->getValor();
                            $totalContadoRecaudado += $tal->getRecaudacion();
                            $totalRifasContadoRegistrado += $tal->getValor();
                            $totalRifasContadoRecaudado += $tal->getRecaudacion();
                        }
                    }
                }

                foreach ($pagospersonales as $pag) {
                    $totalRegistrado += $pag->getMonto();
                    $totalRecaudado += $pag->getMonto();

                    if ($pag->getDeposito()->getTipo() == 'Credito') {
                        $totalCreditoRegistrado += $pag->getMonto();
                        $totalCreditoRecaudado += $pag->getMonto();
                        $totalPagoPersonalCredito += $pag->getMonto();
                    } else if ($pag->getDeposito()->getTipo() == 'Debito') {
                        $totalDebitoRegistrado += $pag->getMonto();
                        $totalDebitoRecaudado += $pag->getMonto();
                        $totalPagoPersonalDebito += $pag->getMonto();
                    } else if ($pag->getDeposito()->getTipo() == 'Contado') {
                        $totalContadoRegistrado += $pag->getMonto();
                        $totalContadoRecaudado += $pag->getMonto();
                        $totalPagoPersonalContado += $pag->getMonto();
                    }
                }


                $result = array(
                    "totalDepositado" => $totalDepositado,
                    "totalRegistrado" => $totalRegistrado,
                    "totalRecaudado" => $totalRecaudado,
                    "totalCreditoDepositado" => $totalCreditoDepositado,
                    "totalCreditoRegistrado" => $totalCreditoRegistrado,
                    "totalCreditoRecaudado" => $totalCreditoRecaudado,
                    "totalDebitoDepositado" => $totalDebitoDepositado,
                    "totalDebitoRegistrado" => $totalDebitoRegistrado,
                    "totalDebitoRecaudado" => $totalDebitoRecaudado,
                    "totalContadoDepositado" => $totalContadoDepositado,
                    "totalContadoRegistrado" => $totalContadoRegistrado,
                    "totalContadoRecaudado" => $totalContadoRecaudado,
                    "totalPagoPersonalCredito" => $totalPagoPersonalCredito,
                    "totalPagoPersonalDebito" => $totalPagoPersonalDebito,
                    "totalPagoPersonalContado" => $totalPagoPersonalContado,
                    "totalRifasCreditoRegistrado" => $totalRifasCreditoRegistrado,
                    "totalRifasCreditoRecaudado" => $totalRifasCreditoRecaudado,
                    "totalRifasDebitoRegistrado" => $totalRifasDebitoRegistrado,
                    "totalRifasDebitoRecaudado" => $totalRifasDebitoRecaudado,
                    "totalRifasContadoRegistrado" => $totalRifasContadoRegistrado,
                    "totalRifasContadoRecaudado" => $totalRifasContadoRecaudado
                );

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }

    /**
     * @Route("/api/pasajero/get-depositos-tarjetas-registros-by-user", name="get-depositos-tarjetas-registros-by-user", methods={"GET"})
     */
    public function getDepositosTarjetasRegistros(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        DepositoRepository $depositoRepository,
        TarjetaRepository $tarjetaRepository,
        TalonRepository $talonRepository,
        PagoPersonalRepository $pagoPersonalRepository
    ) {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                $depositos = $depositoRepository->createQueryBuilder('d')
                    ->where("d.Pasajero = :pas")
                    ->setParameter('pas', $pasajero)
                    ->orderBy('d.Fecha', 'DESC')
                    ->getQuery()
                    ->getResult();

                $depositosFinal = array();

                foreach ($depositos as $dep) {
                    $lRegistros = array();
                    $tarjeta = null;

                    if ($dep->getTipo() == 'Debito' || $dep->getTipo() == 'Credito') {
                        $query = $tarjetaRepository->createQueryBuilder('t')
                            ->select('t.id, t.Issuer, t.NombreTarjeta, d.Tipo, t.Cuotas, t.Acquirer, t.NumeroTarjeta, t.CodigoAutorizacion, t.FechaTransaccion')
                            ->join('t.Deposito', 'd')
                            ->where("t.Deposito = :dep")
                            ->setParameter('dep', $dep)
                            ->getQuery();

                        $tarjeta = $query->getResult()[0];
                    }

                    $talones = $talonRepository->createQueryBuilder('t')
                        ->select('t.id, t.Numero, t.FechaSorteo, t.Precio, c.Nombre')
                        ->join('t.Comprador', 'c')
                        ->where("t.Deposito = :dep")
                        ->setParameter('dep', $dep)
                        ->getQuery()
                        ->getResult();

                    $query = $pagoPersonalRepository->createQueryBuilder('pp')
                        ->select('pp.id, pp.Fecha, pp.Monto')
                        ->where("pp.Deposito = :dep")
                        ->setParameter('dep', $dep)
                        ->getQuery();

                    $pagoPersonal = $query->getResult();

                    array_push($lRegistros, $talones);
                    array_push($lRegistros, $pagoPersonal);

                    $auxDep = array(
                        'depositoFecha' => $dep->getFecha(),
                        'depositoTipo' => $dep->getTipo(),
                        'depositoMonto' => $dep->getMonto()
                    );

                    array_push($depositosFinal, array('dep' => $auxDep, 'registros' => $lRegistros, 'tarjeta' => $tarjeta));
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $depositosFinal], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe.'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }

    /**
     * @Route("/api/pasajero/get-depositos-registros", name="get-depositos-registros-by-user", methods={"GET"})
     */
    public function getDepositosRegistrosByUser(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        DepositoRepository $depositoRepository,
        PagoPersonalRepository $pagoPersonalRepository,
        TalonRepository $talonRepository
    ) {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                $depositos = $depositoRepository->findBy(
                    array(
                        "Pasajero" => $pasajero
                    )
                );

                $depositos = $depositoRepository->createQueryBuilder('d')
                    ->where("d.Pasajero = :pas")
                    ->setParameter('pas', $pasajero)
                    ->orderBy('d.Fecha', 'DESC')
                    ->getQuery()
                    ->getResult();


                $depositosFinal = array();

                foreach ($depositos as $dep) {
                    $lRegistros = array();

                    $talones = $talonRepository->createQueryBuilder('t')
                        ->select('t.id, t.Numero, t.FechaSorteo, t.FechaRegistro, t.Precio, c.Nombre')
                        ->join('t.Comprador', 'c')
                        ->where("t.Deposito = :dep")
                        ->setParameter('dep', $dep)
                        ->getQuery()
                        ->getResult();

                    $query = $pagoPersonalRepository->createQueryBuilder('pp')
                        ->select('pp.id, pp.Fecha, pp.Monto')
                        ->where("pp.Deposito = :dep")
                        ->setParameter('dep', $dep)
                        ->getQuery();

                    $pagoPersonal = $query->getResult();

                    array_push($lRegistros, $talones);
                    array_push($lRegistros, $pagoPersonal);

                    $auxDep = array(
                        'id' => $dep->getId(),
                        'Fecha' => $dep->getFecha(),
                        'Tipo' => $dep->getTipo(),
                        'Monto' => $dep->getMonto()
                    );

                    array_push($depositosFinal, array('dep' => $auxDep, 'registros' => $lRegistros));
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $depositosFinal], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe.'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }


    /**
     * @Route("/pasajero/get-pasajeros-lista", name="get-pasajeros-lista", methods={"POST"})
     */

    public function getPasajerosListTermino(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $estado = (isset($data['estado'])) ? $data['estado'] : null;
        $idViaje = (isset($data['idViaje'])) ? $data['idViaje'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;
        $pasajeros = [];

        $desde = $offset;
        $hasta = $offset + $limit;

        $result = "Desde: " . $desde . " Hasta: " . $hasta . "Termino: " . $termino . "Estado: " . $estado . "IdViaje: " . $idViaje;
        if ($idViaje == null) {
            $idViaje = 'todos';
        }

        if ($termino == '' && $estado == 'todos' && $idViaje == 'todos') {
            $valor = 'Vacio';
            $pasajeros = $this->pasajeroRepository->pasajerosNativeQuery();
            //$pasajeros = $this->pasajeroRepository->pasajerosByFilter($desde, $limit, $termino, $estado, $idViaje);
        } else {
            $valor = 'Con dato';
            $pasajeros = $this->pasajeroRepository->pasajerosByFilter($desde, $limit, $termino, $estado, $idViaje);
        }

        $auxPasajeros = [];

        $size = sizeof($pasajeros);

        for ($x = $desde; $x < $hasta; $x++) {
            if ($size > 0) {
                if ($pasajeros[$x]) {
                    array_push($auxPasajeros, $pasajeros[$x]);
                }
            }
        }

        $longitud = count($pasajeros);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'termino' => $valor,
            'data' => $auxPasajeros,
            'totalPasajeros' => $longitud,

        ]);
    }


    /**
     * @Route("/pasajero/get-pasajeros", name="get-pasajeros", methods={"POST"})
     */

    public function getPasajerosList(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;
        $pasajeros = [];

        $desde = $offset;
        $hasta = $offset + $limit;

        $result = "Desde: " . $desde . " Hasta: " . $hasta;
        $pasajerosAll = $this->pasajeroRepository->findAll();
        $pasajeros = $pasajeroRepository->createQueryBuilder('pas')
            ->select(
                '
            pas.id as pasajero_id,
            per.Nombres,
            per.Apellidos,
            per.id as persona_id,
            per.Cedula,
            per.Celular,

            i.Nombre as itinerario_nombre,
            i.Precio as precio_itinerario,

            v.Nombre as ViajeNombre,

            pas.Estado as PasajeroEstado,
            pas.Comentarios as PasajeroComentarios
            '
            )
            ->join('pas.Persona', 'per')
            ->leftJoin('pas.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->groupBy('per.id')
            ->orderBy('per.Apellidos')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $longitud = count($pasajerosAll);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'data' => $pasajeros,
            'totalPasajeros' => $longitud,
            'request' => $result
        ]);
    }

    /**
     * @Route("/pasajero/get-pasajeros-by-termino", name="get-pasajeros-by-termino", methods={"POST"})
     */

    public function getPasajerosByTermino(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;
        $pasajeros = [];

        $desde = $offset;
        $hasta = $offset + $limit;

        $result = "Desde: " . $desde . " Hasta: " . $hasta;
        $pasajerosTotal = $this->pasajeroRepository->findAll();
        $pasajerosAll = $pasajeroRepository->getPasajerosByTermino(0, count($pasajerosTotal), $termino);
        $pasajeros = $pasajeroRepository->getPasajerosByTermino($offset, $limit, $termino);


        $longitud = count($pasajerosAll);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'data' => $pasajeros,
            'totalPasajeros' => $longitud
            //'request' => $result
        ]);
    }

    /**
     * @Route("/pasajero/get-pasajeros-by-viaje", name="get-pasajeros-by-viaje", methods={"POST"})
     */

    public function getPasajerosByViaje(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $idViaje = (isset($data['idViaje'])) ? $data['idViaje'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;
        $pasajeros = [];

        $desde = $offset;
        $hasta = $offset + $limit;

        $result = "Desde: " . $desde . " Hasta: " . $hasta;
        $pasajerosTotal = $this->pasajeroRepository->getPasajerosByViaje($idViaje);
        // $pasajerosAll = $pasajeroRepository->getPasajerosDesdeHastaByViaje(0, count($pasajerosTotal), $idViaje);
        $pasajeros = $pasajeroRepository->getPasajerosDesdeHastaByViaje($offset, $limit, $idViaje);

        $longitud = count($pasajerosTotal);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'data' => $pasajeros,
            'totalPasajeros' => $longitud
            //'request' => $result
        ]);
    }

    /**
     * @Route("/pasajero/get-pasajeros-by-itinerario", name="get-pasajeros-by-itinerario", methods={"POST"})
     */

    public function getPasajerosByItinerario(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $idItinerario = (isset($data['idItinerario'])) ? $data['idItinerario'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;
        $pasajeros = [];

        $desde = $offset;
        $hasta = $offset + $limit;

        $result = "Desde: " . $desde . " Hasta: " . $hasta;
        $pasajerosTotal = $this->pasajeroRepository->findAll();
        $pasajerosAll = $pasajeroRepository->getPasajerosByDesdeHastaItinerario(0, count($pasajerosTotal), $idItinerario);
        $pasajeros = $pasajeroRepository->getPasajerosByDesdeHastaItinerario($offset, $limit, $idItinerario);

        $longitud = count($pasajerosAll);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'data' => $pasajeros,
            'totalPasajeros' => $longitud
            //'request' => $result
        ]);
    }

    /**
     * @Route("/pasajero/get-pasajeros-saldos-by-viaje", name="get-pasajeros-saldos-by-viaje", methods={"POST"})
     */
    public function getPasajerosSaldosByViaje(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        CostoExtraRepository $costosExtrasRepository,
        DepositoRepository $depositosRepository,
        TalonRepository $talonRepository,
        PagoPersonalRepository $pagoPersonalRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $idViaje = (isset($data['idViaje'])) ? $data['idViaje'] : null;
        $pasajeros = [];
        $pasajeros = $this->pasajeroRepository->getPasajerosByViaje($idViaje);

        $arrayItem = array();
        foreach ($pasajeros as $pas) {
            $pasajero = $pasajeroRepository->findOneBy(
                array('id' => $pas['pasajero_id'])
            );
            $costos_extras = [];
            if ($pasajero) {
                $costo_extra_positivo = 0;
                $costo_extra_negativo = 0;
                $pago_personal = 0;
                $recaudado_talones = 0;
                $itinerario_precio = 0;

                if ($pasajero->getItinerario()->getPrecio() != null) {
                    $itinerario_precio = $pasajero->getItinerario()->getPrecio();
                }

                $costos_extras = $costosExtrasRepository->costosExtrasByPasajero($pasajero);
                $depositos = $depositosRepository->depositosByPasajero($pasajero);

                foreach ($costos_extras as $item_extras) {
                    if ($item_extras['Monto'] > 0) {
                        $costo_extra_positivo += $item_extras['Monto'];
                    } else {
                        $costo_extra_negativo += $item_extras['Monto'];
                    }
                }
                $array_depositos = [];
                foreach ($depositos as $deposito) {
                    $totalTalones = 0;
                    $totalRecaudadoTalones = 0;
                    $talones = [];
                    $talones = $talonRepository->talonesByDeposito(array('Deposito' => $deposito));

                    if ($talones[0]['total_registrado'] != null) {
                        $totalTalones = $talones[0]['total_registrado'];
                        $totalRecaudadoTalones = $talones[0]['total_recaudado'];
                    }

                    $totalpagosPersonales = 0;
                    $pagosPersonales = [];
                    $pagosPersonales = $pagoPersonalRepository->pagosPersonalesByDeposito(array('Deposito' => $deposito));
                    if ($pagosPersonales[0]['total'] != null) {
                        $totalpagosPersonales = $pagosPersonales[0]['total'];
                    }

                    $pago_personal += $totalpagosPersonales;
                    $recaudado_talones += $totalRecaudadoTalones;
                }

                $porcentaje_recaudacion = 0;
                $porcentaje_saldo_a_favor = 0;
                $costo_total = $itinerario_precio + $costo_extra_positivo;
                $recaudado_total = $recaudado_talones + $pago_personal + (-1) * $costo_extra_negativo;

                $resto =  $costo_total - $recaudado_total;

                if ($resto < 0) {
                    // SALDO A FAVOR
                    $porcentaje_recaudacion = (-1) * $resto * 100 / $recaudado_total + 100;
                    $porcentaje_saldo_a_favor = (-1) * $resto * 100 / $recaudado_total;
                } else {

                    if ($resto == 0 || $recaudado_total == 0) {
                        $porcentaje_recaudacion = 0;
                    } else {
                        // Calculo porcentaje recaudado 100% (total costos) menos porcentaje de recaudacion
                        // (reto de costo total - recaudado * 100%)
                        $porcentaje_recaudacion = 100 - $resto * 100 / $costo_total;
                    }
                }

                $item = [
                    'id' => $pasajero->getId(),
                    'id_persona' => $pasajero->getPersona()->getId(),
                    'nombre' => $pasajero->getPersona()->getNombres(),
                    'apellido' => $pasajero->getPersona()->getApellidos(),
                    'celular' => $pasajero->getPersona()->getCelular(),
                    'email' => $pasajero->getPersona()->getUser()->getEmail(),
                    'itinerario' => [
                        'idItinerario' => $pasajero->getItinerario()->getId(),
                        'precio' => $itinerario_precio,
                        'nombre' => $pasajero->getItinerario()->getNombre(),
                    ],
                    'costos_extras' => $costos_extras,
                    'itinerario_precio' => $itinerario_precio,
                    'costo_extra_positivo' => $costo_extra_positivo,
                    'costo_extra_negativo' => $costo_extra_negativo,
                    'pago_personal' => $pago_personal,
                    'recaudado_talones' => $recaudado_talones,
                    'costo_total' => $costo_total,
                    'recaudado_total' => $recaudado_total,
                    'diferencia' => $resto,
                    'porcentaje_recaudado' => $porcentaje_recaudacion . " % ",
                    'porcentaje_saldo_a_favor' => $porcentaje_saldo_a_favor . " % ",

                ];

                array_push($arrayItem, $item);
            }
        }

        $longitud = count($pasajeros);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'data' => $arrayItem,
            'totalPasajeros' => $longitud
            //'request' => $result
        ]);
    }

    /**
     * @Route("/pasajero/get-pasajeros-saldos-by-itinerario", name="get-pasajeros-saldos-by-itinerario", methods={"POST"})
     */
    public function getPasajerosSaldosByItinerario(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        CostoExtraRepository $costosExtrasRepository,
        DepositoRepository $depositosRepository,
        TalonRepository $talonRepository,
        PagoPersonalRepository $pagoPersonalRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $idItinerario = (isset($data['idItinerario'])) ? $data['idItinerario'] : null;
        $pasajeros = [];
        $pasajeros = $this->pasajeroRepository->getPasajerosByItinerario(0, 500, $idItinerario);

        $arrayItem = array();
        foreach ($pasajeros as $pas) {
            $pasajero = $pasajeroRepository->findOneBy(
                array('id' => $pas['pasajero_id'])
            );
            $costos_extras = [];
            if ($pasajero) {
                $costo_extra_positivo = 0;
                $costo_extra_negativo = 0;
                $pago_personal = 0;
                $recaudado_talones = 0;
                $itinerario_precio = 0;

                if ($pasajero->getItinerario()->getPrecio() != null) {
                    $itinerario_precio = $pasajero->getItinerario()->getPrecio();
                }

                $costos_extras = $costosExtrasRepository->costosExtrasByPasajero($pasajero);
                $depositos = $depositosRepository->depositosByPasajero($pasajero);

                foreach ($costos_extras as $item_extras) {
                    if ($item_extras['Monto'] > 0) {
                        $costo_extra_positivo += $item_extras['Monto'];
                    } else {
                        $costo_extra_negativo += $item_extras['Monto'];
                    }
                }
                $array_depositos = [];
                foreach ($depositos as $deposito) {
                    $totalTalones = 0;
                    $totalRecaudadoTalones = 0;
                    $talones = [];
                    $talones = $talonRepository->talonesByDeposito(array('Deposito' => $deposito));

                    if ($talones[0]['total_registrado'] != null) {
                        $totalTalones = $talones[0]['total_registrado'];
                        $totalRecaudadoTalones = $talones[0]['total_recaudado'];
                    }

                    $totalpagosPersonales = 0;
                    $pagosPersonales = [];
                    $pagosPersonales = $pagoPersonalRepository->pagosPersonalesByDeposito(array('Deposito' => $deposito));
                    if ($pagosPersonales[0]['total'] != null) {
                        $totalpagosPersonales = $pagosPersonales[0]['total'];
                    }

                    $pago_personal += $totalpagosPersonales;
                    $recaudado_talones += $totalRecaudadoTalones;
                }

                $porcentaje_recaudacion = 0;
                $porcentaje_saldo_a_favor = 0;
                $costo_total = $itinerario_precio + $costo_extra_positivo;
                $recaudado_total = $recaudado_talones + $pago_personal + (-1) * $costo_extra_negativo;

                $resto =  $costo_total - $recaudado_total;

                if ($resto < 0) {
                    // SALDO A FAVOR
                    $porcentaje_recaudacion = (-1) * $resto * 100 / $recaudado_total + 100;
                    $porcentaje_saldo_a_favor = (-1) * $resto * 100 / $recaudado_total;
                } else {

                    if ($resto == 0 || $recaudado_total == 0) {
                        $porcentaje_recaudacion = 0;
                    } else {
                        // Calculo porcentaje recaudado 100% (total costos) menos porcentaje de recaudacion
                        // (reto de costo total - recaudado * 100%)
                        $porcentaje_recaudacion = 100 - $resto * 100 / $costo_total;
                    }
                }

                $item = [
                    'id' => $pasajero->getId(),
                    'id_persona' => $pasajero->getPersona()->getId(),
                    'nombre' => $pasajero->getPersona()->getNombres(),
                    'apellido' => $pasajero->getPersona()->getApellidos(),
                    'celular' => $pasajero->getPersona()->getCelular(),
                    'email' => $pasajero->getPersona()->getUser()->getEmail(),
                    'itinerario' => [
                        'idItinerario' => $pasajero->getItinerario()->getId(),
                        'precio' => $itinerario_precio,
                        'nombre' => $pasajero->getItinerario()->getNombre(),
                    ],
                    'costos_extras' => $costos_extras,
                    'itinerario_precio' => $itinerario_precio,
                    'costo_extra_positivo' => $costo_extra_positivo,
                    'costo_extra_negativo' => $costo_extra_negativo,
                    'pago_personal' => $pago_personal,
                    'recaudado_talones' => $recaudado_talones,
                    'costo_total' => $costo_total,
                    'recaudado_total' => $recaudado_total,
                    'diferencia' => $resto,
                    'porcentaje_recaudado' => $porcentaje_recaudacion . " % ",
                    'porcentaje_saldo_a_favor' => $porcentaje_saldo_a_favor . " % ",
                ];

                array_push($arrayItem, $item);
            }
        }

        $longitud = count($pasajeros);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'data' => $arrayItem,
            'totalPasajeros' => $longitud
            //'request' => $result
        ]);
    }

    /**
     * @Route("/pasajero/get-pasajeros-saldos-by-viaje-porcentaje", name="get-pasajeros-saldos-by-viaje-porcentaje", methods={"POST"})
     */
    public function getPasajerosSaldosByViajeAndPorcentaje(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        CostoExtraRepository $costosExtrasRepository,
        DepositoRepository $depositosRepository,
        TalonRepository $talonRepository,
        PagoPersonalRepository $pagoPersonalRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $idViaje = (isset($data['idViaje'])) ? $data['idViaje'] : null;
        $porcentaje = (isset($data['porcentaje'])) ? $data['porcentaje'] : null;
        $pasajeros = [];
        $pasajeros = $this->pasajeroRepository->getPasajerosByViaje($idViaje);

        $arrayItem = array();
        foreach ($pasajeros as $pas) {
            $pasajero = $pasajeroRepository->findOneBy(
                array('id' => $pas['pasajero_id'])
            );
            $costos_extras = [];
            if ($pasajero) {
                $costo_extra_positivo = 0;
                $costo_extra_negativo = 0;
                $pago_personal = 0;
                $recaudado_talones = 0;
                $itinerario_precio = 0;

                if ($pasajero->getItinerario()->getPrecio() != null) {
                    $itinerario_precio = $pasajero->getItinerario()->getPrecio();
                }

                $costos_extras = $costosExtrasRepository->costosExtrasByPasajero($pasajero);
                $depositos = $depositosRepository->depositosByPasajero($pasajero);

                foreach ($costos_extras as $item_extras) {
                    if ($item_extras['Monto'] > 0) {
                        $costo_extra_positivo += $item_extras['Monto'];
                    } else {
                        $costo_extra_negativo += $item_extras['Monto'];
                    }
                }
                $array_depositos = [];
                foreach ($depositos as $deposito) {
                    $totalTalones = 0;
                    $totalRecaudadoTalones = 0;
                    $talones = [];
                    $talones = $talonRepository->talonesByDeposito(array('Deposito' => $deposito));

                    if ($talones[0]['total_registrado'] != null) {
                        $totalTalones = $talones[0]['total_registrado'];
                        $totalRecaudadoTalones = $talones[0]['total_recaudado'];
                    }

                    $totalpagosPersonales = 0;
                    $pagosPersonales = [];
                    $pagosPersonales = $pagoPersonalRepository->pagosPersonalesByDeposito(array('Deposito' => $deposito));
                    if ($pagosPersonales[0]['total'] != null) {
                        $totalpagosPersonales = $pagosPersonales[0]['total'];
                    }

                    $pago_personal += $totalpagosPersonales;
                    $recaudado_talones += $totalRecaudadoTalones;
                }

                $porcentaje_recaudacion = 0;
                $porcentaje_saldo_a_favor = 0;
                $costo_total = $itinerario_precio + $costo_extra_positivo;
                $recaudado_total = $recaudado_talones + $pago_personal + (-1) * $costo_extra_negativo;

                $resto =  $costo_total - $recaudado_total;

                if ($resto < 0) {
                    // SALDO A FAVOR
                    $porcentaje_recaudacion = (-1) * $resto * 100 / $recaudado_total + 100;
                    $porcentaje_saldo_a_favor = (-1) * $resto * 100 / $recaudado_total;
                } else {

                    if ($resto == 0 || $recaudado_total == 0) {
                        $porcentaje_recaudacion = 0;
                    } else {
                        // Calculo porcentaje recaudado 100% (total costos) menos porcentaje de recaudacion
                        // (reto de costo total - recaudado * 100%)
                        $porcentaje_recaudacion = 100 - $resto * 100 / $costo_total;
                    }
                }

                $entero_porcentaje = 0;
                $entero_porcentaje = floor($porcentaje_recaudacion);

                // Condicion que filtre los pasajeros con porcentaje de recaudacion mayor a al valor establecido y
                // que no supere el 100% (porcentaje a favor)
                if ($entero_porcentaje >= $porcentaje && $entero_porcentaje < 100) {

                    $item = [
                        'id' => $pasajero->getId(),
                        'id_persona' => $pasajero->getPersona()->getId(),
                        'nombre' => $pasajero->getPersona()->getNombres(),
                        'apellido' => $pasajero->getPersona()->getApellidos(),
                        'celular' => $pasajero->getPersona()->getCelular(),
                        'email' => $pasajero->getPersona()->getUser()->getEmail(),
                        'itinerario' => [
                            'idItinerario' => $pasajero->getItinerario()->getId(),
                            'precio' => $itinerario_precio,
                            'nombre' => $pasajero->getItinerario()->getNombre(),
                        ],
                        'costos_extras' => $costos_extras,
                        'itinerario_precio' => $itinerario_precio,
                        'costo_extra_positivo' => $costo_extra_positivo,
                        'costo_extra_negativo' => $costo_extra_negativo,
                        'pago_personal' => $pago_personal,
                        'recaudado_talones' => $recaudado_talones,
                        'costo_total' => $costo_total,
                        'recaudado_total' => $recaudado_total,
                        'diferencia' => $resto,
                        'porcentaje_recaudado' => $porcentaje_recaudacion . " % ",
                        'porcentaje_saldo_a_favor' => $porcentaje_saldo_a_favor . " % ",

                    ];

                    array_push($arrayItem, $item);
                }
            }
        }

        $longitud = count($pasajeros);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'data' => $arrayItem,
            'totalPasajeros' => $longitud
            //'request' => $result
        ]);
    }

    /**
     * @Route("/pasajero/get-pasajeros-saldos-by-itinerario-porcentaje", name="get-pasajeros-saldos-by-itinerario-porcentaje", methods={"POST"})
     */
    public function getPasajerosSaldosByItinerarioAndPorcentaje(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        CostoExtraRepository $costosExtrasRepository,
        DepositoRepository $depositosRepository,
        TalonRepository $talonRepository,
        PagoPersonalRepository $pagoPersonalRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $idItinerario = (isset($data['idItinerario'])) ? $data['idItinerario'] : null;
        $porcentaje = (isset($data['porcentaje'])) ? $data['porcentaje'] : null;
        $pasajeros = [];
        $pasajeros = $this->pasajeroRepository->getPasajerosByItinerario(0, 500, $idItinerario);

        $arrayItem = array();
        foreach ($pasajeros as $pas) {
            $pasajero = $pasajeroRepository->findOneBy(
                array('id' => $pas['pasajero_id'])
            );
            $costos_extras = [];
            if ($pasajero) {
                $costo_extra_positivo = 0;
                $costo_extra_negativo = 0;
                $pago_personal = 0;
                $recaudado_talones = 0;
                $itinerario_precio = 0;

                if ($pasajero->getItinerario()->getPrecio() != null) {
                    $itinerario_precio = $pasajero->getItinerario()->getPrecio();
                }

                $costos_extras = $costosExtrasRepository->costosExtrasByPasajero($pasajero);
                $depositos = $depositosRepository->depositosByPasajero($pasajero);

                foreach ($costos_extras as $item_extras) {
                    if ($item_extras['Monto'] > 0) {
                        $costo_extra_positivo += $item_extras['Monto'];
                    } else {
                        $costo_extra_negativo += $item_extras['Monto'];
                    }
                }
                $array_depositos = [];
                foreach ($depositos as $deposito) {
                    $totalTalones = 0;
                    $totalRecaudadoTalones = 0;
                    $talones = [];
                    $talones = $talonRepository->talonesByDeposito(array('Deposito' => $deposito));

                    if ($talones[0]['total_registrado'] != null) {
                        $totalTalones = $talones[0]['total_registrado'];
                        $totalRecaudadoTalones = $talones[0]['total_recaudado'];
                    }

                    $totalpagosPersonales = 0;
                    $pagosPersonales = [];
                    $pagosPersonales = $pagoPersonalRepository->pagosPersonalesByDeposito(array('Deposito' => $deposito));
                    if ($pagosPersonales[0]['total'] != null) {
                        $totalpagosPersonales = $pagosPersonales[0]['total'];
                    }

                    $pago_personal += $totalpagosPersonales;
                    $recaudado_talones += $totalRecaudadoTalones;
                }

                $porcentaje_recaudacion = 0;
                $porcentaje_saldo_a_favor = 0;
                $costo_total = $itinerario_precio + $costo_extra_positivo;
                $recaudado_total = $recaudado_talones + $pago_personal + (-1) * $costo_extra_negativo;

                $resto =  $costo_total - $recaudado_total;

                if ($resto < 0) {
                    // SALDO A FAVOR
                    $porcentaje_recaudacion = (-1) * $resto * 100 / $recaudado_total + 100;
                    $porcentaje_saldo_a_favor = (-1) * $resto * 100 / $recaudado_total;
                } else {

                    if ($resto == 0 || $recaudado_total == 0) {
                        $porcentaje_recaudacion = 0;
                    } else {
                        // Calculo porcentaje recaudado 100% (total costos) menos porcentaje de recaudacion
                        // (reto de costo total - recaudado * 100%)
                        $porcentaje_recaudacion = 100 - $resto * 100 / $costo_total;
                    }
                }

                $entero_porcentaje = 0;
                $entero_porcentaje = floor($porcentaje_recaudacion);

                // Condicion que filtre los pasajeros con porcentaje de recaudacion mayor a al valor establecido y
                // que no supere el 100% (porcentaje a favor)
                if ($entero_porcentaje >= $porcentaje && $entero_porcentaje < 100) {

                    $item = [
                        'id' => $pasajero->getId(),
                        'id_persona' => $pasajero->getPersona()->getId(),
                        'nombre' => $pasajero->getPersona()->getNombres(),
                        'apellido' => $pasajero->getPersona()->getApellidos(),
                        'celular' => $pasajero->getPersona()->getCelular(),
                        'email' => $pasajero->getPersona()->getUser()->getEmail(),
                        'itinerario' => [
                            'idItinerario' => $pasajero->getItinerario()->getId(),
                            'precio' => $itinerario_precio,
                            'nombre' => $pasajero->getItinerario()->getNombre(),
                        ],
                        'costos_extras' => $costos_extras,
                        'itinerario_precio' => $itinerario_precio,
                        'costo_extra_positivo' => $costo_extra_positivo,
                        'costo_extra_negativo' => $costo_extra_negativo,
                        'pago_personal' => $pago_personal,
                        'recaudado_talones' => $recaudado_talones,
                        'costo_total' => $costo_total,
                        'recaudado_total' => $recaudado_total,
                        'diferencia' => $resto,
                        'porcentaje_recaudado' => $porcentaje_recaudacion . " % ",
                        'porcentaje_saldo_a_favor' => $porcentaje_saldo_a_favor . " % ",

                    ];

                    array_push($arrayItem, $item);
                }
            }
        }

        $longitud = count($pasajeros);
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => "Todo correcto",
            'data' => $arrayItem,
            'totalPasajeros' => $longitud
            //'request' => $result
        ]);
    }
}
