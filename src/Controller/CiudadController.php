<?php

namespace App\Controller;

use App\Entity\Aereopuerto;
use App\Entity\Alojamiento;
use App\Entity\Ciudad;
use App\Entity\CiudadCampos;
use App\Entity\ItinerarioDetalle;
use App\Entity\Pais;
use App\Entity\Pasajero;
use App\Entity\Hospedaje;
use App\Entity\Servicio;
use App\Entity\PasajeroServicio;
use App\Entity\Piques;
use App\Entity\Proveedor;
use App\Entity\PuntoInteres;
use App\Entity\Trayecto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Security\JwtAuthenticator;

class CiudadController extends AbstractController
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/ciudad/get_by_id/{ciudadId}", methods={"GET"}, name="ciudad_getCiudadById")
     * @param int $ciudadId
     * @return JsonResponse
     */
    public function getCiudadById(int $ciudadId, Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
    {
        $auth = $request->headers->get('Authorization');
        $pasajeroId = null;
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                $pasajero = $pasajeroRepository->findBy(['Persona' => $id]);
                $pasajeroId = $pasajero[0]->getId();
            }
        }
        if ($pasajeroId == null) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El token expiro.'], 400);
        }

        $ciudadRepository = $this->em->getRepository(Ciudad::class);
        $ciudad = $ciudadRepository->find($ciudadId);

        if (!$ciudad) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'No se encontro la ciudad'], 400);
        }

        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);
        $ciudadCampos = $ciudadCamposRepository->findBy(['ciudad' => $ciudadId]);

        $dataCiudadCampos = [];

        if ($ciudadCampos !== null) {
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

        if ($piques !== null) {
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

        if ($puntosInteres !== null) {
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

        if ($proveedores !== null) {
            foreach ($proveedores as $p) {
                $alojamientoRepository = $this->em->getRepository(Alojamiento::class);
                $alojamientos = $alojamientoRepository->findBy(['proveedor' => $p->getId()]);

                if ($alojamientos !== null) {
                    foreach ($alojamientos as $a) {
                        $hospedajeRepository = $this->em->getRepository(Hospedaje::class);
                        $hospedajes = $hospedajeRepository->findBy(['alojamiento' => $a->getId()]);

                        if ($hospedajes !== null) {
                            foreach ($hospedajes as $h) {
                                $servicioRepository = $this->em->getRepository(Servicio::class);
                                $servicios = $servicioRepository->findBy(['hospedaje' => $h->getId()]);

                                if ($servicios !== null) {
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


        $data = [
            'id' => $ciudad->getId(),
            'nombre' => $ciudad->getNombre(),
            'pais_id' => $ciudad->getPais()->getId(),
            'pais_nombre' => $ciudad->getPais()->getNombre(),
            'nombre_ingles' => $ciudad->getNombreIngles(),
            'descripcion' => $ciudad->getDescripcion(),
            'latitud' => $ciudad->getLatitud(),
            'longitud' => $ciudad->getLongitud(),
            'imagen' => $ciudad->getImageSrc(),
            'puntos_interes' => $dataPuntosInteres,
            'alojamientos' => $dataAlojamientos,
            'campos' => $dataCiudadCampos,
            'piques' => $dataPiques
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad/list", methods={"GET"}, name="ciudad_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {

        $ciudadRepository = $this->em->getRepository(Ciudad::class);
        $ciudades = $ciudadRepository->findAll();

        $data = [];

        foreach ($ciudades as $ciudad) {
            $data[] = [
                'id' => $ciudad->getId(),
                'nombre' => $ciudad->getNombre(),
                'pais_nombre' => $ciudad->getPais()->getNombre(),
                'nombre_ingles' => $ciudad->getNombreIngles(),
                'descripcion' => $ciudad->getDescripcion(),
                'latitud' => $ciudad->getLatitud(),
                'longitud' => $ciudad->getLongitud(),
                'image' => $ciudad->getImageSrc(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad/list_web", methods={"POST"}, name="ciudad_list_web")
     * @return JsonResponse
     */
    public function listweb(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $ciudadRepository = $this->em->getRepository(Ciudad::class);

        if(isset($data['nombre'])){
            $ciudadesTotal = $ciudadRepository->findBy(['nombre' => $data['nombre']]);

            $ciudades = $ciudadRepository->findBy(
                array('nombre' => $data['nombre']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else{
            $ciudadesTotal = $ciudadRepository->findAll();

            $ciudades = $ciudadRepository->findBy(
                array(),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }

        

        $data = [];

        foreach ($ciudades as $ciudad) {
            $data[] = [
                'id' => $ciudad->getId(),
                'nombre' => $ciudad->getNombre(),
                'pais_nombre' => $ciudad->getPais()->getNombre(),
                'nombre_ingles' => $ciudad->getNombreIngles(),
                'descripcion' => $ciudad->getDescripcion(),
                'latitud' => $ciudad->getLatitud(),
                'longitud' => $ciudad->getLongitud(),
                'image' => $ciudad->getImageSrc(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($ciudadesTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/ciudad/create", methods={"POST"}, name="ciudad_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $ciudad = new Ciudad();
        $ciudad->setNombre($data['ciudad']['nombre']);
        $paisRepository = $this->em->getRepository(Pais::class);
        $pais = $paisRepository->find($data['ciudad']['pais_id']);
        $ciudad->setPais($pais);
        $ciudad->setNombreIngles($data['ciudad']['nombre_ingles']);
        $ciudad->setDescripcion($data['ciudad']['descripcion']);
        $ciudad->setLatitud($data['ciudad']['latitud']);
        $ciudad->setLongitud($data['ciudad']['longitud']);

        $entityManager->persist($ciudad);
        $entityManager->flush();

        if ($data['ciudad']['image_src'] != "") {
            //foto nueva
            $id = $ciudad->getId();
            $image = $data['ciudad']['image_src'];

            $dir_assets = "";
            $padre = dirname(__DIR__);
            $dir_assets = str_replace('src', 'assets', $padre);

            $dir = $dir_assets . "/imgs/ciudades";
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
                $foto = '/assets/imgs/ciudades/' . $id . '/foto/' . $nombre_archivo;
                $ciudad->setImageSrc($foto);
            }
        }

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'La ciudad se creo correctamente'], 200);
    }

    /**
     * @Route("/ciudad/update/{id}", methods={"PUT", "PATCH"}, name="ciudad_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $ciudadRepository = $this->em->getRepository(Ciudad::class);
        $ciudad = $ciudadRepository->find($id);

        if (!$ciudad) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro la ciudad'], 400);
        }

        $ciudad->setNombre($data['ciudad']['nombre']);
        $paisRepository = $this->em->getRepository(Pais::class);
        $pais = $paisRepository->find($data['ciudad']['pais_id']);
        $ciudad->setPais($pais);
        $ciudad->setNombreIngles($data['ciudad']['nombre_ingles']);
        $ciudad->setDescripcion($data['ciudad']['descripcion']);
        $ciudad->setLatitud($data['ciudad']['latitud']);
        $ciudad->setLongitud($data['ciudad']['longitud']);

        if ($data['ciudad']['image_src'] != "") {
            //foto nueva
            $image = $data['ciudad']['image_src'];

            $dir_assets = "";
            $padre = dirname(__DIR__);
            $dir_assets = str_replace('src', 'assets', $padre);

            $dir = $dir_assets . "/imgs/ciudades";
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
                $foto = '/assets/imgs/ciudades/' . $id . '/foto/' . $nombre_archivo;
                $ciudad->setImageSrc($foto);
            }
        }

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'La ciudad se actualizo correctamente'], 200);
    }

    /**
     * @Route("/ciudad/delete/{id}", methods={"DELETE"}, name="ciudad_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $ciudadRepository = $this->em->getRepository(Ciudad::class);
        $ciudad = $ciudadRepository->find($id);

        if (!$ciudad) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro la ciudad'], 400);
        }

        //UNA VEZ ENCONTRADA LA CIUDAD CHECKEAMOS TODAS LAS TABLAS QUE TIENEN CIUDAD ID 
        //PARA CHECKEAR QUE NO ESTE EN NINGUNA DE ELLAS Y ASI PODER BORRARLA

        //CHECK AEREOPUERTO
        $aereopuertoRepository = $this->em->getRepository(Aereopuerto::class);

        $query = $aereopuertoRepository->createQueryBuilder('a')
            ->where('a.ciudad = :ciudad_id')
            ->setParameter('ciudad_id', $id)
            ->getQuery();

        $aeropuertos = $query->getResult();

        if ($aeropuertos && count($aeropuertos) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'La ciudad se encuentra asignada a aereopuerto, no es posible eliminarla.'], 400);
        }

        //CHECK CIUDAD CAMPOS
        $ciudadCamposRepository = $this->em->getRepository(CiudadCampos::class);

        $query = $ciudadCamposRepository->createQueryBuilder('c')
            ->where('c.ciudad = :ciudad_id')
            ->setParameter('ciudad_id', $id)
            ->getQuery();

        $ciudadCampos = $query->getResult();

        if ($ciudadCampos && count($ciudadCampos) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'La ciudad se encuentra asignada a ciudad campos, no es posible eliminarla.'], 400);
        }

        //CHECK ITINERARIO DETALLE
        $itinerarioDetalleRepository = $this->em->getRepository(ItinerarioDetalle::class);

        $query = $itinerarioDetalleRepository->createQueryBuilder('i')
            ->where('i.ciudad = :ciudad_id')
            ->setParameter('ciudad_id', $id)
            ->getQuery();

        $itinerarioDetalles = $query->getResult();

        if ($itinerarioDetalles && count($itinerarioDetalles) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'La ciudad se encuentra asignada a itinerario detalle, no es posible eliminarla.'], 400);
        }

        //CHECK PIQUES
        $piquesRepository = $this->em->getRepository(Piques::class);

        $query = $piquesRepository->createQueryBuilder('p')
            ->where('p.Ciudad = :ciudad_id')
            ->setParameter('ciudad_id', $id)
            ->getQuery();

        $piques = $query->getResult();

        if ($piques && count($piques) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'La ciudad se encuentra asignada a piques, no es posible eliminarla.'], 400);
        }

        //CHECK PROVEEDOR
        $proveedorRepository = $this->em->getRepository(Proveedor::class);

        $query = $proveedorRepository->createQueryBuilder('pr')
            ->where('pr.ciudad = :ciudad_id')
            ->setParameter('ciudad_id', $id)
            ->getQuery();

        $proveedores = $query->getResult();

        if ($proveedores && count($proveedores) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'La ciudad se encuentra asignada a proveedores, no es posible eliminarla.'], 400);
        }

        //CHECK PUNTO INTERES
        $puntoInteresRepository = $this->em->getRepository(PuntoInteres::class);

        $query = $puntoInteresRepository->createQueryBuilder('pi')
            ->where('pi.ciudad = :ciudad_id')
            ->setParameter('ciudad_id', $id)
            ->getQuery();

        $puntosInteres = $query->getResult();

        if ($puntosInteres && count($puntosInteres) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'La ciudad se encuentra asignada a punto de interes, no es posible eliminarla.'], 400);
        }

        //CHECK TRAYECTO
        $trayectoRepository = $this->em->getRepository(Trayecto::class);

        $query = $trayectoRepository->createQueryBuilder('t')
            ->where('t.ciudad_inicio = :ciudad_id OR t.ciudad_fin = :ciudad_id')
            ->setParameter('ciudad_id', $id)
            ->getQuery();

        $trayectos = $query->getResult();

        if ($trayectos && count($trayectos) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'La ciudad se encuentra asignada a trayectos, no es posible eliminarla.'], 400);
        }

        $this->em->remove($ciudad);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'La ciudad se elimino correctamente'], 200);
    }
}
