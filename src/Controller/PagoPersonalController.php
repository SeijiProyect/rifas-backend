<?php

namespace App\Controller;

use App\Repository\DepositoRepository;
use App\Repository\PagoPersonalRepository;
use App\Repository\PasajeroRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PagoPersonalController extends AbstractController
{
    /**
     * @Route("/pago/personal", name="pago_personal")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PagoPersonalController.php',
        ]);
    }

    /**
     * @Route("/api/pago-personal/delete", name="delete_pago_personal", methods={"POST"})
     */
    public function deletePagoPersonal(Request $request, PasajeroRepository $pasajeroRepository, PagoPersonalRepository $pagoPersonalRepository)
    {
        $authToken = $request->headers->get('Authorization');

        $data = json_decode($request->getContent(), true);
        $ppId = (isset($data['pagoPersonal_id'])) ? $data['pagoPersonal_id'] : null;

        if ($authToken != null && $ppId != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {
                $pp = $pagoPersonalRepository->findOneBy(array(
                    "id" => $ppId
                ));

                if ( $pp ) {
                    if ( $pp->getDeposito()->getPasajero() == $pasajero ) {
                        $pagoPersonalRepository->deletePagoPersonal($pp);
                        
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pago personal eliminado'], 200);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'El pago personal no corresponde'], 403);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pago personal no existe'], 404);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }

    /**
     * @Route("/api/pago-personal/new", name="new_pago_personal", methods={"POST"})
     */
    public function newPagoPersonal(Request $request, PasajeroRepository $pasajeroRepository, DepositoRepository $depositoRepository, PagoPersonalRepository $pagoPersonalRepository)
    {
        $authToken = $request->headers->get('Authorization');

        $data = json_decode($request->getContent(), true);
        $monto = (isset($data['monto'])) ? $data['monto'] : null;
        $deposito_id = (isset($data['deposito_id'])) ? $data['deposito_id'] : null;

        if ($authToken != null && $monto != null && $deposito_id != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                $deposito = $depositoRepository->findOneBy(array(
                    "id" => $deposito_id
                ));

                if ( $deposito ) {

                    if ( $deposito->getPasajero() == $pasajero ) {

                        $pagoPersonal = $pagoPersonalRepository->savePagoPersonal( $monto, $deposito );
                        
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Pago personal creado correctamente'], 200);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Propietario incorrecto'], 403);
                    }

                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El depósito no existe'], 404);
                }


            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }
}
