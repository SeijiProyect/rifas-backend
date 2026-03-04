<?php

namespace App\Controller;

use App\Repository\PasajeroRepository;
use App\Repository\DepositoRepository;
use App\Repository\PagoPersonalRepository;
use App\Repository\TalonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DepositoController extends AbstractController
{
    private $pasajeroRepository;
    private $depositoRepository;

    public function __construct(DepositoRepository $depositoRepository)
    {
        $this->depositoRepository = $depositoRepository;
    }

    /**
     * @Route("/deposito", name="deposito")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DepositoController.php',
        ]);
    }

    /**
     * @Route("/deposito/get-depositos-by-pasajero", name="get-depositos-by-pasajero", methods={"GET"})
     */

    public function getDepositosByPasajero(Request $request, PasajeroRepository $pasajeroRepository, DepositoRepository $depositoRepository, TalonRepository $talonRepository, PagoPersonalRepository $pagoPersonalRepository)
    {
        // CAMBIAR A METODO POST !!!!
        $pasajeroId = $request->get('pasajero'); // METODO GET

        $data = json_decode($request->getContent(), true);
        //$pasajeroId = (isset($data['pasajero'])) ? $data['pasajero'] : null;

        $pasajero = $pasajeroRepository->findOneBy(
            array('id' => $pasajeroId)
        );

        if ($pasajero != null) {

            $totalDepositado = 0;
            $totalRegistrado = 0;
            $totalRecaudado = 0;

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

            //$totalRifasCreditoRegistrado = 0;
            //$totalRifasCreditoRecaudado = 0;

            //$totalRifasDebitoRegistrado = 0;
            //$totalRifasDebitoRecaudado = 0;

            //$totalRifasContadoRegistrado = 0;
            //$totalRifasContadoRecaudado = 0;

            $depositos = $depositoRepository->findBy(array(
                "Pasajero" => $pasajero
            ));

            $talones = $talonRepository->findBy(array(
                "Pasajero" => $pasajero
            ));

            $pagospersonales = $pagoPersonalRepository->findBy(array(
                "Deposito" => $depositos
            ));

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
                    $totalRegistrado += $tal->getValor();
                    $totalRecaudado += $tal->getRecaudacion();

                    if ($tal->getDeposito()->getTipo() == 'Credito') {
                        $totalCreditoRegistrado += $tal->getValor();
                        $totalCreditoRecaudado += $tal->getRecaudacion();
                        // $totalRifasCreditoRegistrado += 20;
                        //$totalRifasCreditoRecaudado += 16;
                    } else if ($tal->getDeposito()->getTipo() == 'Debito') {
                        $totalDebitoRegistrado += $tal->getValor();
                        $totalDebitoRecaudado += $tal->getRecaudacion();
                        //$totalRifasDebitoRegistrado += 20;
                        //$totalRifasDebitoRecaudado += 16;
                    } else if ($tal->getDeposito()->getTipo() == 'Contado') {
                        $totalContadoRegistrado += $tal->getValor();
                        $totalContadoRecaudado += $tal->getRecaudacion();
                        // $totalRifasContadoRegistrado += 20;
                        // $totalRifasContadoRecaudado += 16;
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
                "totalPagoPersonalContado" => $totalPagoPersonalContado
            );

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
        }
    }
}
