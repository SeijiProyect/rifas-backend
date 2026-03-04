<?php

namespace App\Controller;

use App\Repository\PasajeroRepository;
use App\Repository\CostoExtraRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;

class CostoExtraController extends AbstractController
{
    /**
     * @Route("/costo-extra", name="costo_extra")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CostoExtraController.php',
        ]);
    }

    /**
     * @Route("/api/costos-extras", name="get-costos-extra-by-user", methods={"GET"})
     */
    public function getCostosExtrasByUser(Request $request, PasajeroRepository $pasajeroRepository, CostoExtraRepository $costoExtraRepository)
    {
        $authToken = $request->headers->get('Authorization');

        $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

        if ($pasajero != null) {
            $costosExtras = $costoExtraRepository->findBy(
                array(
                    "Pasajero" => $pasajero
                )
            );

            $res = [];
            foreach ($costosExtras as $ce) {
                $res[] = array(
                    'id' => $ce->getId(),
                    'descripcion' => $ce->getDescripcion(),
                    'monto' => $ce->getMonto(),
                    'fecha' => $ce->getFecha()
                );
            }

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $res], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
        }
    }


    /**
     * @Route("/api/costo-extra/delete", name="delete-costo-extra", methods={"POST"})
     */
    public function deleteCostoExtra(Request $request, CostoExtraRepository $costoExtraRepository)
    {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['id'])) ? $data['id'] : null;

        if ($id != null) {
            $costoExtra = $costoExtraRepository->findOneBy(
                array(
                    "id" => $id
                )
            );
            $costoExtraRepository->deleteCostoExtra($costoExtra);
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se elimino corretamente', 'data' => 'id borrado: ' . $id], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El costo extra no existe.'], 400);
        }
    }

    /**
     * @Route("/api/costo-extra/add", name="add-costo-extra", methods={"POST"})
     */
    public function addCostoExtra(Request $request, CostoExtraRepository $costoExtraRepository, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $idPasajero = (isset($data['idPasajero'])) ? $data['idPasajero'] : null;
        $descripcion = (isset($data['descripcion'])) ? $data['descripcion'] : null;
        $monto = (isset($data['monto'])) ? $data['monto'] : null;

        if ($idPasajero != null) {
            $pasajero = $pasajeroRepository->findOneBy(
                array(
                    "id" => $idPasajero
                )
            );

            $res = $costoExtraRepository->saveCostoExtra($pasajero, $descripcion, $monto);

            $aux_link = array(
                'id'  => $res->getId(),
                'Descripcion' => $res->getDescripcion(),
                'Monto' => $res->getMonto(),
                'Fecha' => $res->getFecha(),
            );
            //$id_link =$res->getId();

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se agrego corretamente', 'data' => $aux_link], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El pasajero no existe.'], 400);
        }
    }




}