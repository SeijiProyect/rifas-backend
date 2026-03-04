<?php

namespace App\Controller;

use App\Entity\CiudadCampos;
use App\Entity\Piques;
use App\Entity\PuntoInteres;
use App\Entity\Alojamiento;
use App\Entity\Hospedaje;
use App\Entity\Proveedor;
use App\Entity\Servicio;
use App\Entity\Lista;
use App\Entity\ListaOpcion;
use App\Entity\Notificacion;
use App\Entity\PasajeroNotificacion;
use App\Entity\PasajeroListaOpcion;
use App\Entity\PasajeroServicio;
use App\Entity\Itinerario;
use App\Entity\Transporte;
use App\Entity\DatosTipoTransporte;
use App\Entity\Viaje;
use App\Entity\Grupo;
use App\Entity\Ciudad;
use App\Entity\Documento;
use App\Entity\FotoPersona;
use App\Entity\Pasajero;
use App\Entity\Persona;
use App\Entity\Trayecto;
use App\Entity\Pais;
use App\Entity\ItinerarioDetalle;
use App\Entity\UserRol;
use App\Repository\PasajeroRepository;
use App\Repository\GrupoRepository;
use App\Repository\ViajeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ItinerarioRepository;
use DateTimeImmutable;
use App\Security\JwtAuthenticator;

class ItinerarioController extends AbstractController
{
    #[Route('/itinerario', name: 'itinerario')]

    private $itinerarioRepository;
    private $em;

    public function __construct(ItinerarioRepository $itinerarioRepository, EntityManagerInterface $em)
    {
        $this->itinerarioRepository = $itinerarioRepository;
        $this->em = $em;
    }
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ItinerarioController.php',
        ]);
    }



    /**
     * @Route("/itinerario/get-itinerarios", name="get-itinerarios", methods={"GET"})
     */
    public function getViajesList()
    {
        $itinerarios = $this->itinerarioRepository->createQueryBuilder('i')
            ->select(
                'i.id, 
                i.Nombre
                '
            )
            ->orderBy('i.Nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $itinerarios], 200);
    }

    /**
     * @Route("/itinerario/get_by_id/{itinerarioId}", methods={"GET"}, name="itinerario_getItinerarioById")
     * @param int $itinerarioId
     * @return JsonResponse
     */
    public function getItinerarioById(int $itinerarioId): JsonResponse
    {
        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
        $itinerario = $itinerarioRepository->find($itinerarioId);

        if (!$itinerario) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el itinerario'], 400);
        }

        $viaje = $itinerario->getViaje();
        $grupo = $itinerario->getGrupo();

        $data = [
            'id' => $itinerario->getId(),
            'nombre' => $itinerario->getNombre(),
            'fecha_inicio' => ($itinerario->getFechaInicio() !== null) ? $itinerario->getFechaInicio()->format('Y-m-d') : null,
            'fecha_fin' => ($itinerario->getFechaFin() !== null) ? $itinerario->getFechaFin()->format('Y-m-d') : null,
            'precio' => $itinerario->getPrecio(),
            'principal' => $itinerario->getPrincipal(),
            'grupo' => ($grupo !== null) ? [
                'id' => $grupo->getId(),
                'nombre' => $grupo->getNombre(),

            ] : null,
            'viaje' => ($viaje !== null) ? [
                'id' => $viaje->getId(),
                'nombre' => $viaje->getNombre()
            ] : null
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario/get-itinerarios-by-viaje", name="get-itinerarios-by-viaje", methods={"POST"})
     */
    public function getItinerariosByViaje(Request $request, ViajeRepository $viajeRepository)
    {
        $data = json_decode($request->getContent(), true);
        $viajeId = (isset($data['viaje'])) ? $data['viaje'] : null;


        $viaje = $viajeRepository->findOneBy(
            array('id' => $viajeId)
        );

        $itinerarios = $this->itinerarioRepository->createQueryBuilder('i')
            ->select(
                'i.id, 
                i.Nombre
                '
            )
            ->where('i.Viaje = :v')
            ->setParameter('v', $viaje)
            ->orderBy('i.Nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $itinerarios], 200);
    }

    /**
     * @Route("/itinerario/get-itinerarios-by-grupo", name="get-itinerarios-by-grupo", methods={"POST"})
     */
    public function getItinerariosByGrupo(Request $request, GrupoRepository $grupoRepository)
    {
        $data = json_decode($request->getContent(), true);
        $grupoId = (isset($data['grupo'])) ? $data['grupo'] : null;

        $grupo = $grupoRepository->findOneBy(
            array('id' => $grupoId)
        );

        $itinerarios = $this->itinerarioRepository->createQueryBuilder('i')
            ->select(
                'i.id, 
                i.Nombre
                '
            )
            ->where('i.Grupo = :g')
            ->setParameter('g', $grupo)
            ->orderBy('i.Nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $itinerarios], 200);
    }

    /**
     * @Route("/itinerario/list", methods={"GET"}, name="itinerario_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
        $itinerarios = $itinerarioRepository->findAll();

        $data = [];

        foreach ($itinerarios as $itinerario) {
            $viaje = $itinerario->getViaje();
            $grupo = $itinerario->getGrupo();
            $data[] = [
                'id' => $itinerario->getId(),
                'nombre' => $itinerario->getNombre(),
                'fecha_inicio' => ($itinerario->getFechaInicio() !== null) ? $itinerario->getFechaInicio()->format('Y-m-d H:i:s') : null,
                'fecha_fin' => ($itinerario->getFechaFin() !== null) ? $itinerario->getFechaFin()->format('Y-m-d H:i:s') : null,
                'precio' => $itinerario->getPrecio(),
                'principal' => $itinerario->getPrincipal(),
                'grupo' => ($grupo !== null) ? [
                    'id' => $grupo->getId(),
                    'nombre' => $grupo->getNombre(),
                    'created_at' => ($grupo->getCreatedAt() !== null) ? $grupo->getCreatedAt()->format('Y-m-d H:i:s') : null,
                    'updated_at' => ($grupo->getUpdatedAt() !== null) ? $grupo->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                    'deleted_at' => ($grupo->getDeletedAt() !== null) ? $grupo->getDeletedAt()->format('Y-m-d H:i:s') : null,
                ] : null,
                'viaje' => ($viaje !== null) ? [
                    'id' => $viaje->getId(),
                    'nombre' => $viaje->getNombre(),
                    'description' => $viaje->getDescripcion(),
                    'anio' => $viaje->getAnio(),
                    'activo' => $viaje->getActivo(),
                    'fecha_inicio' => ($viaje->getFechaInicio() !== null) ? $viaje->getFechaInicio()->format('Y-m-d H:i:s') : null,
                    'fecha_fin' => ($viaje->getFechaInicio() !== null) ? $viaje->getFechaInicio()->format('Y-m-d H:i:s') : null,
                    'titulo' => $viaje->getTitulo(),
                    'subtitulo' => $viaje->getSubtitulo(),
                    'destacado' => $viaje->getDestacado(),
                    'created_at' => $viaje->getCreatedAt(),
                    'updated_at' => $viaje->getUpdatedAt(),
                    'deleted_at' => $viaje->getDeletedAt(),
                ] : null,

            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario/list_web", methods={"POST"}, name="itinerario_list_web")
     * @return JsonResponse
     */
    public function listweb(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $itinerarioRepository = $this->em->getRepository(Itinerario::class);

        if(isset($data['nombre'])){
            $itinerariosTotal = $itinerarioRepository->findBy(['Nombre' => $data['nombre']]);

            $itinerarios = $itinerarioRepository->findBy(
                array('Nombre' => $data['nombre']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else if(isset($data['grupo'])){
            $itinerariosTotal = $itinerarioRepository->findBy(['Grupo' => $data['grupo']]);

            $itinerarios = $itinerarioRepository->findBy(
                array('Grupo' => $data['grupo']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else if(isset($data['viaje'])){
            $itinerariosTotal = $itinerarioRepository->findBy(['Viaje' => $data['viaje']]);

            $itinerarios = $itinerarioRepository->findBy(
                array('Viaje' => $data['viaje']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else{
            $itinerariosTotal = $itinerarioRepository->findAll();

            $itinerarios = $itinerarioRepository->findBy(
                array(),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        

        $data = [];

        foreach ($itinerarios as $itinerario) {
            $viaje = $itinerario->getViaje();
            $grupo = $itinerario->getGrupo();
            $data[] = [
                'id' => $itinerario->getId(),
                'nombre' => $itinerario->getNombre(),
                'fecha_inicio' => ($itinerario->getFechaInicio() !== null) ? $itinerario->getFechaInicio()->format('Y-m-d') : null,
                'fecha_fin' => ($itinerario->getFechaFin() !== null) ? $itinerario->getFechaFin()->format('Y-m-d') : null,
                'precio' => $itinerario->getPrecio(),
                'principal' => $itinerario->getPrincipal(),
                'grupo' => ($grupo !== null) ? [
                    'id' => $grupo->getId(),
                    'nombre' => $grupo->getNombre(),
                ] : null,
                'viaje' => ($viaje !== null) ? [
                    'id' => $viaje->getId(),
                    'nombre' => $viaje->getNombre(),
                    'description' => $viaje->getDescripcion(),
                    'anio' => $viaje->getAnio(),
                    'activo' => $viaje->getActivo(),
                    'fecha_inicio' => ($viaje->getFechaInicio() !== null) ? $viaje->getFechaInicio()->format('Y-m-d H:i:s') : null,
                    'fecha_fin' => ($viaje->getFechaInicio() !== null) ? $viaje->getFechaInicio()->format('Y-m-d H:i:s') : null,
                    'titulo' => $viaje->getTitulo(),
                    'subtitulo' => $viaje->getSubtitulo(),
                    'destacado' => $viaje->getDestacado(),
                ] : null,

            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($itinerariosTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/itinerario/create_itinerarios_detalle", methods={"POST"}, name="itinerario_create_itinerarios_detalle")
     * @param Request $request
     * @return JsonResponse
     */
    public function create_itinerarios_detalle(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        if (isset($data['ciudades'])) {
            if (count($data['ciudades']) > 0) {
                foreach ($data['ciudades'] as $ciudad_aux) {
                    //la ciudad ya existe
                    $ciudadRepository = $this->em->getRepository(Ciudad::class);
                    if ($ciudad_aux['ciudad_id'] !== null) {
                        $ciudad = $ciudadRepository->find($ciudad_aux['ciudad_id']);
                    }
                    //la ciudad es nueva
                    else {
                        $ciudad = new Ciudad();
                        $ciudad->setNombre($ciudad_aux['nombre']);
                        $paisRepository = $this->em->getRepository(Pais::class);
                        $pais = $paisRepository->find($ciudad_aux['pais_id']);
                        $ciudad->setPais($pais);
                        $ciudad->setDescripcion($ciudad_aux['descripcion']);
                        $entityManager->persist($ciudad);
                        $entityManager->flush();
                    }
                    $itinerario_detalle = new ItinerarioDetalle();
                    $itinerario_detalle->setFechaInicio($ciudad_aux['fecha_inicio']);
                    $itinerario_detalle->setFechaFin($ciudad_aux['fecha_fin']);
                    $itinerario_detalle->setCiudad($ciudad);
                    $entityManager->persist($itinerario_detalle);
                    $entityManager->flush();
                }
            }
        }

        if (isset($data['trayectos'])) {
            if (count($data['trayectos']) > 0) {
                foreach ($data['trayectos'] as $trayecto_aux) {
                    $trayectoRepository = $this->em->getRepository(Trayecto::class);
                    if ($trayecto_aux['trayecto_id'] !== null) {
                        $trayecto = $trayectoRepository->find($trayecto_aux['trayecto_id']);
                    } else {
                        $ciudadRepository = $this->em->getRepository(Ciudad::class);
                        if (isset($trayecto_aux['ciudad_inicio'])) {
                            //la ciudad ya existe
                            if ($trayecto_aux['ciudad_inicio']['ciudad_id'] !== null) {
                                $ciudad_inicio = $ciudadRepository->find($trayecto_aux['ciudad_inicio']['ciudad_id']);
                            }
                            //la ciudad es nueva
                            else {
                                $ciudad_inicio = new Ciudad();
                                $ciudad_inicio->setNombre($trayecto_aux['ciudad_inicio']['nombre']);
                                $paisRepository = $this->em->getRepository(Pais::class);
                                $pais = $paisRepository->find($trayecto_aux['ciudad_inicio']['pais_id']);
                                $ciudad_inicio->setPais($pais);
                                $ciudad_inicio->setDescripcion($trayecto_aux['ciudad_inicio']['descripcion']);
                                $entityManager->persist($ciudad_inicio);
                                $entityManager->flush();
                            }
                        }
                        if (isset($trayecto_aux['ciudad_fin'])) {
                            //la ciudad ya existe
                            if ($trayecto_aux['ciudad_fin']['ciudad_id'] !== null) {
                                $ciudad_fin = $ciudadRepository->find($trayecto_aux['ciudad_fin']['ciudad_id']);
                            }
                            //la ciudad es nueva
                            else {
                                $ciudad_fin = new Ciudad();
                                $ciudad_fin->setNombre($trayecto_aux['ciudad_fin']['nombre']);
                                $paisRepository = $this->em->getRepository(Pais::class);
                                $pais = $paisRepository->find($trayecto_aux['ciudad_fin']['pais_id']);
                                $ciudad_fin->setPais($pais);
                                $ciudad_fin->setDescripcion($trayecto_aux['ciudad_fin']['descripcion']);
                                $entityManager->persist($ciudad_fin);
                                $entityManager->flush();
                            }
                        }
                        $trayecto = new Trayecto();
                        if (isset($ciudad_inicio)) {
                            $trayecto->setCiudadInicio($ciudad_inicio);
                        }
                        if (isset($ciudad_fin)) {
                            $trayecto->setCiudadFin($ciudad_fin);
                        }
                        $entityManager->persist($trayecto);
                        $entityManager->flush();
                    }

                    $itinerario_detalle = new ItinerarioDetalle();
                    $itinerario_detalle->setFechaInicio($trayecto_aux['fecha_inicio']);
                    $itinerario_detalle->setFechaFin($trayecto_aux['fecha_fin']);
                    $itinerario_detalle->setTrayecto($trayecto);
                    $entityManager->persist($itinerario_detalle);
                    $entityManager->flush();
                }
            }
        }
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Los itinerarios detalles se crearon correctamente'], 200);
    }

    /**
     * @Route("/itinerario/create", methods={"POST"}, name="itinerario_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $itinerario = new Itinerario();
        $itinerario->setNombre($data['itinerario']['nombre']);
        if (isset($data['itinerario']['fecha_inicio'])) {
            $string_fecha_inicio = $data['itinerario']['fecha_inicio'];
            $date_fecha_inicio = DateTimeImmutable::createFromFormat('Y-m-d', $string_fecha_inicio);
            $itinerario->setFechaInicio($date_fecha_inicio);
        }
        if (isset($data['itinerario']['fecha_fin'])) {
            $string_fecha_fin = $data['itinerario']['fecha_fin'];
            $date_fecha_fin = DateTimeImmutable::createFromFormat('Y-m-d', $string_fecha_fin);
            $itinerario->setFechaFin($date_fecha_fin);
        }
        if (isset($data['itinerario']['precio'])) {
            $itinerario->setPrecio($data['itinerario']['precio']);
        }
        $itinerario->setPrincipal($data['itinerario']['principal']);
        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
        $itinerario->setCreatedAt($date_fecha_actual);
        $viajeRepository = $this->em->getRepository(Viaje::class);
        $viaje = $viajeRepository->find($data['itinerario']['viaje_id']);
        $itinerario->setViaje($viaje);
        if (isset($data['itinerario']['grupo_id'])) {
            $grupoRepository = $this->em->getRepository(Grupo::class);
            $grupo = $grupoRepository->find($data['itinerario']['grupo_id']);
            $itinerario->setGrupo($grupo);
        }

        $entityManager->persist($itinerario);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El itinerario se creo correctamente'], 200);
    }

    /**
     * @Route("/itinerario/update/{id}", methods={"PUT", "PATCH"}, name="itinerario_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
        $itinerario = $itinerarioRepository->find($id);

        if (!$itinerario) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el itinerario'], 400);
        }

        $itinerario->setNombre($data['itinerario']['nombre']);
        if (isset($data['itinerario']['fecha_inicio'])) {
            $string_fecha_inicio = $data['itinerario']['fecha_inicio'];
            $date_fecha_inicio = DateTimeImmutable::createFromFormat('Y-m-d', $string_fecha_inicio);
            $itinerario->setFechaInicio($date_fecha_inicio);
        }
        if (isset($data['itinerario']['fecha_fin'])) {
            $string_fecha_fin = $data['itinerario']['fecha_fin'];
            $date_fecha_fin = DateTimeImmutable::createFromFormat('Y-m-d', $string_fecha_fin);
            $itinerario->setFechaFin($date_fecha_fin);
        }
        if (isset($data['itinerario']['precio'])) {
            $itinerario->setPrecio($data['itinerario']['precio']);
        }
        $itinerario->setPrincipal($data['itinerario']['principal']);
        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
        $itinerario->setUpdatedAt($date_fecha_actual);
        $itinerario->setDeletedAt(null);
        $viajeRepository = $this->em->getRepository(Viaje::class);
        $viaje = $viajeRepository->find($data['itinerario']['viaje_id']);
        $itinerario->setViaje($viaje);
        if (isset($data['itinerario']['grupo_id'])) {
            $grupoRepository = $this->em->getRepository(Grupo::class);
            $grupo = $grupoRepository->find($data['itinerario']['grupo_id']);
            $itinerario->setGrupo($grupo);
        }

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El itinerario se actualizo correctamente'], 200);
    }

    /**
     * @Route("/itinerario/delete/{id}", methods={"DELETE"}, name="itinerario_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
        $itinerario = $itinerarioRepository->find($id);

        if (!$itinerario) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el itinerario'], 400);
        }

        //CHECK ITINERARIO DETALLE
        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $query = $itinerarioDetalleRepository->createQueryBuilder('i')
            ->where('i.itinerario = :itinerario_id')
            ->setParameter('itinerario_id', $id)
            ->getQuery();

        $itinerarioDetalles = $query->getResult();

        if ($itinerarioDetalles && count($itinerarioDetalles) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'El itinerario se encuentra asignado a itinerario detalle, no es posible eliminarla.'], 400);
        }

        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
        $itinerario->setDeletedAt($date_fecha_actual);

        $this->em->remove($itinerario);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El itinerario se elimino correctamente.'], 200);
    }

    /**
     * @Route("/itinerario/delete_all/{id}", methods={"DELETE"}, name="itinerario_delete_all")
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAll(int $id): JsonResponse
    {
        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
        $itinerario = $itinerarioRepository->find($id);

        if (!$itinerario) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el itinerario'], 400);
        }

        $this->em->remove($itinerario);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El itinerario se elimino correctamente'], 200);
    }

    /**
     * @Route("/itinerario/get-itinerario-by-pasajero", name="get-itinerarios-by-pasajero", methods={"POST"})
     */
    /* public function getItinerariosByPasajero(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pasajeroId = (isset($data['pasajero'])) ? $data['pasajero'] : null;


        $pasajero = $pasajeroRepository->findOneBy(
                    array('id' => $pasajeroId)
                );

      // EN ESTE CASO SOLO TRAE UN ITINERARIO ( PASAJERO - ITINERARIO 1:1 )          
        $itinerario = $this->itinerarioRepository->createQueryBuilder('i')
            ->select(
                'i.id, 
                i.Nombre,
                i.Precio,
                i.FechaInicio,
                i.FechaFin,
                v.id as idViaje 
                '
            )
            ->leftJoin('i.Viaje', 'v')
            ->where('i.Pasajero = :pas')
            ->setParameter('pas', $pasajero)
            ->orderBy('i.Nombre')
            ->getQuery()
            ->getResult();                        

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $itinerario], 200);
    }*/

    /**
     * @Route("/itinerario/get-ciudades", methods={"GET"}, name="itinerario_getCiudades")
     * @return JsonResponse
     */
    public function getCiudades(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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
                    $pasajero = $pasajeroRepository->findBy(['Persona' => $id]);
                    $pasajeroId = $pasajero[0]->getId();
                    $itinerarioId = $pasajeroRepository->findByPersonaItinerario($persona);
                    $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
                    $itinerarioDetalle = $itinerarioDetalleRepository->findBy(['itinerario' => $itinerarioId]);

                    $data = [];
                    if ($itinerarioDetalle !== null && count($itinerarioDetalle) > 0) {
                        foreach ($itinerarioDetalle as $itd) {
                            if ($itd->getCiudad() !== null) {
                                $ciudadId = $itd->getCiudad()->getId();
                                $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);
                                $ciudadCampos = $ciudadCamposRepository->findBy(['ciudad' => $ciudadId]);

                                $dataCiudadCampos = [];

                                if ($ciudadCampos !== null && count($ciudadCampos) > 0) {
                                    foreach ($ciudadCampos as $cc) {
                                        $dataCiudadCampos[] = [
                                            'id' => $cc->getId(),
                                            'nombre' => $cc->getNombre(),
                                            'valor' => $cc->getValor()
                                        ];
                                    }
                                }

                                $piquesRepository = $this->em->getRepository(Piques::class);
                                $piques = $piquesRepository->findBy(['Ciudad' => $ciudadId]);

                                $dataPiques = [];

                                if ($piques !== null && count($piques) > 0) {
                                    foreach ($piques as $piq) {
                                        $dataPiques[] = [
                                            'id' => $piq->getId(),
                                            'titulo' => $piq->getTitulo(),
                                            'descripcion' => $piq->getDescripcion()
                                        ];
                                    }
                                }

                                $puntoInteresRepository = $this->em->getRepository(PuntoInteres::class);
                                $puntosInteres = $puntoInteresRepository->findBy(['ciudad' => $ciudadId]);

                                $dataPuntosInteres = [];

                                if ($puntosInteres !== null && count($puntosInteres) > 0) {
                                    foreach ($puntosInteres as $pi) {
                                        $dataPuntosInteres[] = [
                                            'id' => $pi->getId(),
                                            'nombre' => $pi->getNombre(),
                                            'descripcion' => $pi->getDescripcion(),
                                            'google_maps' => $pi->getGoogleMaps(),
                                            'maps_me' => $pi->getMapsMe(),
                                            'web_url' => $pi->getWebUrl(),
                                            'horario' => $pi->getHorarios(),
                                            'precio' => $pi->getPrecio(),
                                            'tipo' => $pi->getTipo(),
                                            'orden' => $pi->getOrden(),
                                            'imagen' => $pi->getImageSrc(),
                                        ];
                                    }
                                }

                                $proveedorRepository = $this->em->getRepository(Proveedor::class);
                                $proveedores = $proveedorRepository->findBy(['ciudad' => $ciudadId]);

                                $dataAlojamientos = [];

                                if ($proveedores !== null && count($proveedores) > 0) {
                                    foreach ($proveedores as $p) {
                                        $alojamientoRepository = $this->em->getRepository(Alojamiento::class);
                                        $alojamientos = $alojamientoRepository->findBy(['proveedor' => $p->getId()]);

                                        if ($alojamientos !== null && count($alojamientos) > 0) {
                                            foreach ($alojamientos as $a) {
                                                $hospedajeRepository = $this->em->getRepository(Hospedaje::class);
                                                $hospedajes = $hospedajeRepository->findBy(['alojamiento' => $a->getId()]);

                                                if ($hospedajes !== null && count($hospedajes) > 0) {
                                                    foreach ($hospedajes as $h) {
                                                        $servicioRepository = $this->em->getRepository(Servicio::class);
                                                        $servicios = $servicioRepository->findBy(['hospedaje' => $h->getId()]);

                                                        if ($servicios !== null && count($servicios) > 0) {
                                                            foreach ($servicios as $s) {
                                                                $pasajeroServicioRepository = $this->em->getRepository(PasajeroServicio::class);
                                                                $pasajeroServicio = $pasajeroServicioRepository->findBy([
                                                                    'pasajero' => $pasajeroId,
                                                                    'servicio' => $s->getId()
                                                                ]);
                                                                if ($pasajeroServicio !== null && count($pasajeroServicio) > 0) {
                                                                    $dataAlojamientos[] = [
                                                                        'id' => $p->getId(),
                                                                        'nombre_proveedor' => $p->getNombre(),
                                                                        'whatsapp_proveedor' => $p->getWhatsapp(),
                                                                        'cuenta_bancaria_proveedor' => $p->getCuentaBancaria(),
                                                                        'direccion_proveedor' => $p->getDireccion(),
                                                                        'telefonos_proveedor' => $p->getTelefonos(),
                                                                        'google_maps_proveedor' => $p->getGoogleMaps(),
                                                                        'maps_me_proveedor' => $p->getMapsMe(),
                                                                        'nombre_alojamiento' => $a->getNombre(),
                                                                        'imagen_alojamiento' => $a->getImageSrc(),
                                                                        'fecha_desde' => ($h->getFechaDesde() !== null) ? $h->getFechaDesde()->format('Y-m-d H:i:s') : null,
                                                                        'fecha_hasta' => ($h->getFechaHasta() !== null) ? $h->getFechaHasta()->format('Y-m-d H:i:s') : null,
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

                                $data[] = [
                                    'id' => $itd->getCiudad()->getId(),
                                    'nombre' => $itd->getCiudad()->getNombre(),
                                    'pais_nombre' => $itd->getCiudad()->getPais()->getNombre(),
                                    'nombre_ingles' => $itd->getCiudad()->getNombreIngles(),
                                    'descripcion' => $itd->getCiudad()->getDescripcion(),
                                    'latitud' => $itd->getCiudad()->getLatitud(),
                                    'longitud' => $itd->getCiudad()->getLongitud(),
                                    'image' => $itd->getCiudad()->getImageSrc(),
                                    'puntos_interes' => $dataPuntosInteres,
                                    'alojamientos' => $dataAlojamientos,
                                    'campos' => $dataCiudadCampos,
                                    'piques' => $dataPiques
                                ];
                            }
                        }
                    }
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro la persona'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el usuario'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Token Invalido'], 400);
        }
    }

    /**
     * @Route("/itinerario/get-complete-app-data", methods={"GET"}, name="itinerario_getCompleteData")
     * @return JsonResponse
     */
    public function getCompleteData(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
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
                    $pasajero = $pasajeroRepository->findBy(['Persona' => $id]);
                    $pasajeroId = $pasajero[0]->getId();
                    $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);
                    $itinerarioId = $pasajeroRepository->findByPersonaItinerario($persona);
                    $itinerarioDetalles = $itinerarioDetalleRepository->findBy(['itinerario' => $itinerarioId]);

                    //ITINERARIO
                    $itinerario_data = [];
                    foreach ($itinerarioDetalles as $itinerarioDetalle) {
                        $ciudad = $itinerarioDetalle->getCiudad();
                        $trayecto = $itinerarioDetalle->getTrayecto();
                        if ($trayecto !== null) {

                            $trayectoId = $trayecto->getId();
                            $transporteRepository = $this->em->getRepository(Transporte::class);
                            $transportes = $transporteRepository->getTransporteByTrayectoPadreId($trayectoId);
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
                        $itinerario_data[] = [
                            'itinerario_detalle' => [
                                'id' => $itinerarioDetalle->getId(),
                                'fecha_inicio' => ($itinerarioDetalle->getFechaInicio() !== null) ? $itinerarioDetalle->getFechaInicio()->format('Y-m-d H:i:s') : null,
                                'fecha_fin' => ($itinerarioDetalle->getFechaFin() !== null) ? $itinerarioDetalle->getFechaFin()->format('Y-m-d H:i:s') : null,
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

                    //LISTAS
                    $listas_data = [];
                    $userRoleRepository = $this->em->getRepository(UserRol::class);
                    $userRol_aux = $userRoleRepository->findBy(['user' => $user['user']]);
                    //SI ES ADMIN
                    if ($userRol_aux !== null && count($userRol_aux) > 0) {
                        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                        $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                        $grupo = $itinerario_aux->getGrupo();
                        $listaRepository = $this->em->getRepository(Lista::class);
                        $listas = $listaRepository->findBy(['grupo' => $grupo->getId()]);

                        if ($listas !== null && count($listas) > 0)
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
                                $listas_data[] = [
                                    'id' => $lista->getId(),
                                    'titulo' => $lista->getTitulo(),
                                    'imagen' => $lista->getImagen(),
                                    'inscriptos' => $total_inscriptos,
                                ];
                            }
                    }
                    // SI ES PASAJERO
                    else {
                        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                        $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                        $grupo = $itinerario_aux->getGrupo();
                        $listaRepository = $this->em->getRepository(Lista::class);
                        $listas = $listaRepository->findBy(['grupo' => $grupo->getId()]);

                        if ($listas !== null && count($listas) > 0) {
                            foreach ($listas as $lista) {
                                $listas_data[] = [
                                    'id' => $lista->getId(),
                                    'titulo' => $lista->getTitulo(),
                                    'imagen' => $lista->getImagen(),
                                    'fecha_limite' => ($lista->getDisponibleHasta() !== null) ? $lista->getDisponibleHasta()->format('Y-m-d H:i:s') : null,
                                ];
                            }
                        }
                    }

                    //NOTIFICACIONES
                    $notificaciones_data = [];
                    $userRoleRepository = $this->em->getRepository(UserRol::class);
                    $userRol_aux = $userRoleRepository->findBy(['user' => $user['user']]);
                    //SI ES ADMIN
                    if ($userRol_aux !== null && count($userRol_aux) > 0) {
                        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                        $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                        $grupo = $itinerario_aux->getGrupo();
                        $notificacionRepository = $this->em->getRepository(Notificacion::class);
                        $notificaciones = $notificacionRepository->findBy(['grupo' => $grupo->getId()], ['fecha_programada' => 'DESC', 'fecha_enviado' => 'DESC']);

                        if ($notificaciones !== null && count($notificaciones) > 0) {
                            foreach ($notificaciones as $notificacion) {
                                $grupo = $notificacion->getGrupo();
                                $itinerario = $notificacion->getItinerario();
                                $ciudad = $notificacion->getCiudad();
                                $lista = $notificacion->getLista();

                                $pasajeroNotificacionRepository = $this->em->getRepository(PasajeroNotificacion::class);
                                $pasajerosNotificacion = $pasajeroNotificacionRepository->findBy(['notificacion' => $notificacion->getId()]);
                                $pasajeroNotificacion = $pasajeroNotificacionRepository->findBy(['notificacion' => $notificacion->getId(), 'pasajero' => $pasajeroId]);
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
                                if ($pasajerosNotificacion !== null && count($pasajerosNotificacion) > 0) {
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


                                    $notificaciones_data[] = [
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
                            }
                        }
                    }
                    //SI ES PASAJERO
                    else {
                        $itinerarioRepository = $this->em->getRepository(Itinerario::class);
                        $itinerario_aux = $itinerarioRepository->find($itinerarioId);
                        $grupo = $itinerario_aux->getGrupo();
                        $notificacionRepository = $this->em->getRepository(Notificacion::class);
                        $notificaciones = $notificacionRepository->findBy(['grupo' => $grupo->getId()], ['fecha_programada' => 'DESC', 'fecha_enviado' => 'DESC']);

                        if ($notificaciones !== null && count($notificaciones) > 0) {
                            foreach ($notificaciones as $notificacion) {
                                $grupo = $notificacion->getGrupo();
                                $itinerario = $notificacion->getItinerario();
                                $ciudad = $notificacion->getCiudad();
                                $lista = $notificacion->getLista();

                                $pasajeroNotificacionRepository = $this->em->getRepository(PasajeroNotificacion::class);
                                $pasajeroNotificacion = $pasajeroNotificacionRepository->findBy(['notificacion' => $notificacion->getId(), 'pasajero' => $pasajeroId]);
                                $is_read = false;
                                if ($pasajeroNotificacion && count($pasajeroNotificacion) > 0) {
                                    if ($pasajeroNotificacion[0]->getFechaVisto() !== null) {
                                        $is_read = true;
                                    }

                                    $notificaciones_data[] = [
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
                        }
                    }

                    //PERSONA
                    $fotoPersonaRepository = $this->em->getRepository(FotoPersona::class);
                    $foto = $fotoPersonaRepository->findByPersonaUltimaFoto($persona);

                    // Obtener el documento
                    $documentoRepository = $this->em->getRepository(Documento::class);
                    $documento = $documentoRepository->findByPersonaDocumento($persona);

                    $persona_data = array(
                        'id' => $persona->getId(),
                        'nombres' => $persona->getNombres(),
                        'apellidos' => $persona->getApellidos(),
                        'fecha_nac' => $persona->getFechaNacimiento(),
                        'direccion' => $persona->getDireccion(),
                        'cedula' => $persona->getCedula(),
                        'celular' => $persona->getCelular(),
                        'sexo' => $persona->getSexo(),
                        'email' => $persona->getUser()->getEmail(),
                        'documento' => $documento,
                        'foto' => $foto
                    );

                    $data = [
                        "itinerario" => $itinerario_data,
                        "listas" => $listas_data,
                        "notificaciones" => $notificaciones_data,
                        "usuario" => $persona_data,
                    ];
                }
            }
        }
        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }
}
