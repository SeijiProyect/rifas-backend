<?php

namespace App\Controller;

use App\Entity\Rifa;
use App\Repository\OrganizacionRepository;
use App\Repository\RifaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RifaController extends AbstractController
{
    /**
     * @Route("/rifa", name="rifa")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/RifaController.php',
        ]);
    }

    /**
     * @Route("api/rifa/crear", name="crear-rifa", methods={"POST"})
     */
    public function crearRifa(Request $request, RifaRepository $rifaRepository, OrganizacionRepository $organizacionRepository)
    {

        $data = json_decode($request->getContent(), true);
        $id = (isset($data['rifa']['id'])) ? $data['rifa']['id'] : null;
        $nombre = (isset($data['rifa']['nombre'])) ? $data['rifa']['nombre'] : null;
        $descripcion = (isset($data['rifa']['descripcion'])) ? $data['rifa']['descripcion'] : null;
        $fecha_inicio = (isset($data['rifa']['fecha_inicio'])) ? $data['rifa']['fecha_inicio'] : null;
        $fecha_fin = (isset($data['rifa']['fecha_fin'])) ? $data['rifa']['fecha_fin'] : null;
        $organizador_id = (isset($data['rifa']['organizador'])) ? $data['rifa']['organizador']['id'] : null;

        $fecha_inicio_format = new \DateTime($fecha_inicio);
        $fecha_fin_format = new \DateTime($fecha_fin);

        $organizador = $organizacionRepository->getOrganizadorById($organizador_id);

        /*return new JsonResponse(['status' => 'test', 'code' => 200, 
        'message' => 'Datos Entrada: ' . " ID: ". $id. 
        " nombre: ". $nombre.' descripcion: '. $descripcion. 'fecha inicio: '.$fecha_inicio. "fecha fin: ".$fecha_fin. 'Organizador: '.$organizador_id], 200);
        */
        $res = new Rifa();

        if ($organizador != null) {
            $rifa = new Rifa();
            $rifa
                ->setNombre($nombre)
                ->setDescripcion($descripcion)
                ->setFechaInicio($fecha_inicio_format)
                ->setFechaFin($fecha_fin_format)
                ->setOrganizacion($organizador)
                ->setActiva(true);
            $res = $rifaRepository->saveRifa($rifa);
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se creo correctamente', 'data' => $res], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el Organizador.' . $organizador], 400);
            // return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("api/rifa/editar", name="edit-rifa", methods={"POST"})
     */
    public function editRifa(Request $request, RifaRepository $rifaRepository)
    {

        $data = json_decode($request->getContent(), true);
        $id = (isset($data['rifa']['id'])) ? $data['rifa']['id'] : null;
        $nombre = (isset($data['rifa']['nombre'])) ? $data['rifa']['nombre'] : null;
        $descripcion = (isset($data['rifa']['descripcion'])) ? $data['rifa']['descripcion'] : null;
        $fecha_ini = (isset($data['rifa']['fecha_inicio'])) ? $data['rifa']['fecha_inicio'] : null;
        $fecha_fin = (isset($data['rifa']['fecha_fin'])) ? $data['rifa']['fecha_fin'] : null;

        //$organizador = (isset($data['rifa']['organizador'])) ? $data['rifa']['organizador'] : null;

        $fecha_ini_format = new \DateTime($fecha_ini);
        $fecha_fin_format = new \DateTime($fecha_fin);


        if ($id != null) {
            $rifa = $rifaRepository->getRifaById($id);
            if ($rifa) {

                $rifa->setNombre($nombre);
                $rifa->setDescripcion($descripcion);
                $rifa->setFechaInicio($fecha_ini_format);
                $rifa->setFechaFin($fecha_fin_format);
                //$rifa->setOrganizacion($organizador);

                $res = $rifaRepository->updateRifa($rifa);

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se Actualizo correctamente', 'data' => $res], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La rifa no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/rifa/delete", name="delete-rifa", methods={"POST"})
     */
    public function deleteRifa(Request $request, RifaRepository $rifaRepository)
    {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['id'])) ? $data['id'] : null;

        if ($id != null) {
            $rifa = $rifaRepository->findOneBy(
                array(
                    "id" => $id
                )
            );
            // Verificar que los sorteos no tengan talones
            foreach ($rifa->getSorteos() as $sorteo) {

                if (count($sorteo->getTalones()) > 0) {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La rifa tiene talones asociados.'], 400);
                }
            }

            $rifaRepository->deleteRifa($rifa);
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se elimino corretamente', 'data' => 'id borrado: ' . $id], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La rifa no existe.'], 400);
        }
    }

    /**
     * @Route("/admin/rifa/list-activas", name="list-rifa-activas", methods={"POST"})
     */
    public function listRifasActivas(Request $request, RifaRepository $rifaRepository)
    {
        $data = json_decode($request->getContent(), true);
        $res = $rifaRepository->listRifaActiva();
        //$res = 'Valor';
        if ($res != null) {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Respuesta correctamente', 'data' => $res], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/rifa/get-datos", name="get-rifa-datos", methods={"POST"})
     */
    public function getRifaDatos(Request $request, RifaRepository $rifaRepository)
    {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['id'])) ? $data['id'] : null;
        if ($id != null) {

            $rifa = $rifaRepository->getRifaById($id);
            if ($rifa) {

                $res = array(
                    'id' => $rifa->getId(),
                    'nombre' => $rifa->getNombre(),
                    'descripcion' => $rifa->getDescripcion(),
                    'fecha_inicio' => $rifa->getFechaInicio(),
                    'fecha_fin' => $rifa->getFechaFin(),
                    'organizador_id' => $rifa->getOrganizacion()->getId()
                );

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Rifa encontrado', 'data' => $res], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La Rifa no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }
}
