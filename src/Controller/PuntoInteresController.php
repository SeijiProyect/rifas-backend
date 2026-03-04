<?php

namespace App\Controller;

use App\Entity\PuntoInteres;
use App\Entity\Ciudad;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PuntoInteresController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/punto_interes/get_by_id/{puntoInteresId}", methods={"GET"}, name="punto_interes_getPuntoInteresById")
     * @param int $puntoInteresId
     * @return JsonResponse
     */
    public function getPuntoInteresById(int $puntoInteresId): JsonResponse
    {
        $puntoInteresRepository = $this->em->getRepository(PuntoInteres::class);
        $puntoInteres = $puntoInteresRepository->find($puntoInteresId);

        if (!$puntoInteres) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'No se encontro el punto de interes'], 400);
        }

        $ciudad = $puntoInteres->getCiudad();

        $data = [
            'id' => $puntoInteres->getId(),
            'nombre' => $puntoInteres->getNombre(),
            'ciudad' => ($ciudad !== null) ? [
                'id' => $ciudad->getId(),
                'nombre' => $ciudad->getNombre(),
                'pais_nombre' => $ciudad->getPais()->getNombre(),
                'nombre_ingles' => $ciudad->getNombreIngles(),
            ] : null,
            'descripcion' => $puntoInteres->getDescripcion(),
            'maps_me' => $puntoInteres->getMapsMe(),
            'google_maps' => $puntoInteres->getGoogleMaps(),
            'horarios' => $puntoInteres->getHorarios(),
            'precio' => $puntoInteres->getPrecio(),
            'tipo' => $puntoInteres->getTipo(),
            'orden' => $puntoInteres->getOrden(),
            'image_src' => $puntoInteres->getImageSrc()
        ];

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/punto_interes/list", methods={"GET"}, name="punto_interes_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $puntoInteresRepository = $this->em->getRepository(PuntoInteres::class);
        $puntoIntereses = $puntoInteresRepository->findAll();

        $data = [];

        foreach ($puntoIntereses as $puntoInteres) {
            $ciudad = $puntoInteres->getCiudad();
            $data[] = [
                'id' => $puntoInteres->getId(),
                'nombre' => $puntoInteres->getNombre(),
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                    'pais_nombre' => $ciudad->getPais()->getNombre(),
                    'nombre_ingles' => $ciudad->getNombreIngles(),
                ] : null,
                'descripcion' => $puntoInteres->getDescripcion(),
                'maps_me' => $puntoInteres->getMapsMe(),
                'google_maps' => $puntoInteres->getGoogleMaps(),
                'horarios' => $puntoInteres->getHorarios(),
                'precio' => $puntoInteres->getPrecio(),
                'tipo' => $puntoInteres->getTipo(),
                'orden' => $puntoInteres->getOrden(),
                'image_src' => $puntoInteres->getImageSrc(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/punto_interes/list_web", methods={"POST"}, name="punto_interes_list_web")
     * @return JsonResponse
     */
    public function list_web(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $puntoInteresRepository = $this->em->getRepository(PuntoInteres::class);

        if(isset($data['nombre'])){
            $puntoInteresTotal = $puntoInteresRepository->findBy(['nombre' => $data['nombre']]);

            $puntoIntereses = $puntoInteresRepository->findBy(
                array('nombre' => $data['nombre']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else if(isset($data['ciudad_id'])){
            $puntoInteresTotal = $puntoInteresRepository->findBy(['ciudad' => $data['ciudad_id']]);

            $puntoIntereses = $puntoInteresRepository->findBy(
                array('ciudad' => $data['ciudad_id']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else{
            $puntoInteresTotal = $puntoInteresRepository->findAll();

            $puntoIntereses = $puntoInteresRepository->findBy(
                array(),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }

        $data = [];

        foreach ($puntoIntereses as $puntoInteres) {
            $ciudad = $puntoInteres->getCiudad();
            $data[] = [
                'id' => $puntoInteres->getId(),
                'nombre' => $puntoInteres->getNombre(),
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                    'pais_nombre' => $ciudad->getPais()->getNombre(),
                    'nombre_ingles' => $ciudad->getNombreIngles(),
                ] : null,
                'descripcion' => $puntoInteres->getDescripcion(),
                'maps_me' => $puntoInteres->getMapsMe(),
                'google_maps' => $puntoInteres->getGoogleMaps(),
                'horarios' => $puntoInteres->getHorarios(),
                'precio' => $puntoInteres->getPrecio(),
                'tipo' => $puntoInteres->getTipo(),
                'orden' => $puntoInteres->getOrden(),
                'image_src' => $puntoInteres->getImageSrc(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($puntoInteresTotal), 'data' => $data], 200);
    }

    /**
     * @Route("/punto_interes/create", methods={"POST"}, name="punto_interes_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $puntoInteres = new PuntoInteres();
        $puntoInteres->setNombre($data['punto']['nombre']);
        $ciudadRepository = $this->em->getRepository(Ciudad::class);
        $ciudad = $ciudadRepository->find($data['punto']['ciudad_id']);
        $puntoInteres->setCiudad($ciudad);
        $puntoInteres->setDescripcion($data['punto']['descripcion']);
        $puntoInteres->setMapsMe($data['punto']['maps_me']);
        $puntoInteres->setGoogleMaps($data['punto']['google_maps']);
        $puntoInteres->setHorarios($data['punto']['horarios']);
        $puntoInteres->setPrecio($data['punto']['precio']);
        $puntoInteres->setTipo($data['punto']['tipo']);
        $puntoInteres->setOrden($data['punto']['orden']);

        $entityManager->persist($puntoInteres);
        $entityManager->flush();

        if ($data['punto']['image_src'] != "") {
            //foto nueva
            $id = $puntoInteres->getId();
            $image = $data['punto']['image_src'];

            $dir_assets = "";
            $padre = dirname(__DIR__);
            $dir_assets = str_replace('src', 'assets', $padre);

            $dir = $dir_assets . "/imgs/puntos-de-interes";
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
                $foto = '/assets/imgs/puntos-de-interes/'.$id.'/foto/'.$nombre_archivo;
                $puntoInteres->setImageSrc($foto);
            }
        }

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El punto de interes se creo correctamente'], 200);
    }

    /**
     * @Route("/punto_interes/update/{id}", methods={"PUT", "PATCH"}, name="punto_interes_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $puntoInteresRepository = $this->em->getRepository(PuntoInteres::class);
        $puntoInteres = $puntoInteresRepository->find($id);

        if (!$puntoInteres) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el punto de interes'], 400);
        }

        $puntoInteres->setNombre($data['punto']['nombre']);
        $ciudadRepository = $this->em->getRepository(Ciudad::class);
        $ciudad = $ciudadRepository->find($data['punto']['ciudad_id']);
        $puntoInteres->setCiudad($ciudad);
        $puntoInteres->setDescripcion($data['punto']['descripcion']);
        $puntoInteres->setMapsMe($data['punto']['maps_me']);
        $puntoInteres->setGoogleMaps($data['punto']['google_maps']);
        $puntoInteres->setHorarios($data['punto']['horarios']);
        $puntoInteres->setPrecio($data['punto']['precio']);
        $puntoInteres->setTipo($data['punto']['tipo']);
        $puntoInteres->setOrden($data['punto']['orden']);

        if ($data['punto']['image_src'] != "") {
            //foto nueva
            $image = $data['punto']['image_src'];

            $dir_assets = "";
            $padre = dirname(__DIR__);
            $dir_assets = str_replace('src', 'assets', $padre);

            $dir = $dir_assets . "/imgs/puntos-de-interes";
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
                $foto = '/assets/imgs/puntos-de-interes/'.$id.'/foto/'.$nombre_archivo;
                $puntoInteres->setImageSrc($foto);
            }
        }

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El punto de interes se actualizo correctamente'], 200);
    }

    /**
     * @Route("/punto_interes/delete/{id}", methods={"DELETE"}, name="punto_interes_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $puntoInteresRepository = $this->em->getRepository(PuntoInteres::class);
        $puntoInteres = $puntoInteresRepository->find($id);

        if (!$puntoInteres) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el punto de interes'], 400);
        }

        $this->em->remove($puntoInteres);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El punto de interes se elimino correctamente'], 200);
    }

    /**
     * @Route("/punto_interes/get_by_ciudad_id/{ciudadId}", methods={"GET"}, name="punto_interes_get_data_by_id")
     * @param int $puntoInteresId
     * @return JsonResponse
     */
    public function getPuntoInteresByCiudadId(int $ciudadId): JsonResponse
    {
        $puntoInteresRepository = $this->em->getRepository(PuntoInteres::class);
        $puntoIntereses = $this->$puntoInteresRepository->findBy(['ciudad' => $ciudadId]);

        $data = [];

        foreach ($puntoIntereses as $puntoInteres) {
            $ciudad = $puntoInteres->getCiudad();
            $data[] = [
                'id' => $puntoInteres->getId(),
                'nombre' => $puntoInteres->getNombre(),
                'ciudad' => ($ciudad !== null) ? [
                    'id' => $ciudad->getId(),
                    'nombre' => $ciudad->getNombre(),
                    'pais_nombre' => $ciudad->getPais()->getNombre(),
                    'nombre_ingles' => $ciudad->getNombreIngles(),
                ] : null,
                'descripcion' => $puntoInteres->getDescripcion(),
                'google_maps' => $puntoInteres->getGoogleMaps(),
                'horario' => $puntoInteres->getHorarios(),
                'precio' => $puntoInteres->getPrecio(),
                'tipo' => $puntoInteres->getTipo(),
                'orden' => $puntoInteres->getOrden(),
                'image_src' => $puntoInteres->getImageSrc(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }
}
