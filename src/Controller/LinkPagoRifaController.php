<?php

namespace App\Controller;

use App\Repository\DepositoRepository;
use App\Repository\PasajeroRepository;
use Proxies\__CG__\App\Entity\Deposito;
use App\Entity\LinkPagoRifa;
use App\Repository\LinkPagoRifaRepository;
use App\Repository\LinkPagoRifaSeleccionRepository;
use App\Repository\LinkPagoRifaTalonesRepository;
use App\Repository\TarjetaRepository;
use App\Repository\TalonRepository;
use Proxies\__CG__\App\Entity\Pasajero;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LinkPagoRifaController extends AbstractController
{

    private $linkPagoRifaRepository;
    private $linkPagoRifaSeleccionRepository;

    public function __construct(LinkPagoRifaRepository $linkPagoRifaRepository, LinkPagoRifaSeleccionRepository $linkPagoRifaSeleccionRepository)
    {
        $this->linkPagoRifaRepository = $linkPagoRifaRepository;
        $this->linkPagoRifaSeleccionRepository = $linkPagoRifaSeleccionRepository;
    }

    /**
     * @Route("/link-pago-rifa", name="rifa")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/LinkPagoRifaController.php',
        ]);
    }

    /**
     * @Route("/link-pago-rifa/edit", name="linkPago_edit", methods={"POST"})
     */
    public function update(Request $request, PasajeroRepository $pasajeroRepository, DepositoRepository $depositoRepository)
    {
        $data = json_decode($request->getContent(), true);
        //$id = (isset($data['linkId'])) ? $data['linkId'] : null;
        $linkRequest = (isset($data['linkPago'])) ? $data['linkPago'] : null;
        $id = (isset($data['linkPago']['linkId'])) ? $data['linkPago']['linkId'] : null;
        $estado = (isset($data['linkPago']['estado'])) ? $data['linkPago']['estado'] : null;
        $idPasajero = (isset($data['linkPago']['pasajero']['id'])) ? $data['linkPago']['pasajero']['id'] : null;
        $idDeposito = (isset($data['linkPago']['deposito']['id'])) ? $data['linkPago']['deposito']['id'] : null;
        $link = $this->linkPagoRifaRepository->find($id);
        $pasajero = $pasajeroRepository->find($idPasajero);
        $deposito = $depositoRepository->find($idDeposito);

        if (!$link) {
            throw $this->createNotFoundException(
                'No se encontro el Link con id ' . $id
                //return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['linkPago' => $link]]);
            );
        }

        $link->setPasajero($pasajero);
        $link->setDeposito($deposito);
        $link->setEstado($estado);

        $this->linkPagoRifaRepository->updateLinkPagoRifa($link);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['linkPago' => $link]]);
    }

    /**
     * @Route("/link-pago-rifa/list", name="link-pago-rifa-list", methods={"POST"})
     */
    public function getLinkPagoRifaList(Request $request, TarjetaRepository $tarjetaRepository, TalonRepository $talonRepository, LinkPagoRifaTalonesRepository $linkTalonesRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $limit = 20;
        $offset = $pageIndex * $limit;

        $desde = $offset;
        $hasta = $offset + $limit;

        $linkPagoList = $this->linkPagoRifaRepository->findAll();

        $linkPagoListBetween = $this->linkPagoRifaRepository->findBetweenRegister($desde, $hasta);

        $data = [];
        foreach ($linkPagoListBetween as $linkPago) {
            $idDeposito = null;
            $formaPago = null;
            $numeroTrjeta = null;
            $talones = [];
            $idLinkPago = $linkPago->getId();
            $relacionLinkTalones = $linkTalonesRepository->talonesByLinkPago($idLinkPago);
            $talones = $relacionLinkTalones;

            if (!is_null($linkPago->getDeposito())) {
                $idDeposito = $linkPago->getDeposito()->getId();
                $formaPago = $linkPago->getDeposito()->getTipo();
                $linkPagoSeleccionList = $tarjetaRepository->findOneBy(
                    array('Deposito' => $idDeposito)
                );
                if (!is_null($linkPagoSeleccionList)) {
                    $numeroTrjeta = $linkPagoSeleccionList->getNumeroTarjeta();
                }
            }

            $data[] = [
                'id' => $linkPago->getId(),
                'idPasajero' => $linkPago->getPasajero()->getId(),
                'nombrePasajero' => $linkPago->getPasajero()->getPersona()->getNombres(),
                'apellidoPasajero' => $linkPago->getPasajero()->getPersona()->getApellidos(),
                'idDeposito' => $idDeposito,
                'nombreComprador' => $linkPago->getCompradorNombre(),
                'apellidoComprador' => $linkPago->getCompradorApellido(),
                'compradorEmail' => $linkPago->getCompradorEmail(),
                'compradorCelular' => $linkPago->getCompradorCelular(),
                'estado' => $linkPago->getEstado(),
                'formaPago' => $formaPago,
                'tarjeta' => $numeroTrjeta,
                'talones' => $talones,
            ];
        }
        $longitud = count($linkPagoList);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['linksPagoRifas' => $data], 'totalLinks' => $longitud, 200]);
    }

    /**
     * @Route("/link-pago-rifa-by-numero-rifa/list", name="link-pago-rifa-by-numero-rifa", methods={"POST"})
     */
    public function getLinkPagoRifaByNumeroRifa(Request $request, TarjetaRepository $tarjetaRepository, TalonRepository $talonRepository, LinkPagoRifaTalonesRepository $linkTalonesRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $numero = (isset($data['numero_rifa'])) ? $data['numero_rifa'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $desde = $offset;
        $hasta = $offset + $limit;

        $linkPagoAll = $this->linkPagoRifaRepository->findAll();
        $longitud = count($linkPagoAll);

        $LinkPagoCompleta = $this->linkPagoRifaRepository->findByNumeroRifa($numero, 0, $longitud);
        $linkPagoListLimit = $this->linkPagoRifaRepository->findByNumeroRifa($numero, $desde, $limit);

        $data = [];
        foreach ($linkPagoListLimit as $linkPago) {
            $idDeposito = null;
            $formaPago = null;
            $numeroTrjeta = null;
            $idLinkPago = $linkPago['id'];
            $talones = [];

            $relacionLinkTalones = $linkTalonesRepository->talonesByLinkPago($idLinkPago);
            $talones = $relacionLinkTalones;

            if (!is_null($linkPago['idDeposito'])) {
                $idDeposito = $linkPago['idDeposito'];
                $formaPago = $linkPago['formaPago'];
                $linkPagoSeleccionList = $tarjetaRepository->findOneBy(
                    array('Deposito' => $idDeposito)
                );
                if (!is_null($linkPagoSeleccionList)) {
                    $numeroTrjeta = $linkPagoSeleccionList->getNumeroTarjeta();
                }
            }

            $data[] = [
                'id' => $linkPago['id'],
                'idPasajero' => $linkPago['idPasajero'],
                'nombrePasajero' => $linkPago['nombrePasajero'],
                'apellidoPasajero' => $linkPago['apellidoPasajero'],
                'idDeposito' => $idDeposito,
                'nombreComprador' => $linkPago['nombreComprador'],
                'apellidoComprador' => $linkPago['apellidoComprador'],
                'compradorEmail' => $linkPago['compradorEmail'],
                'compradorCelular' => $linkPago['compradorCelular'],
                'estado' => $linkPago['estado'],
                'formaPago' => $formaPago,
                'tarjeta' => $numeroTrjeta,
                'talones' => $talones,
            ];
        }

        $longitud = count($LinkPagoCompleta);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => ['linksPagoRifas' => $data], 'totalLinks' => $longitud]);
    }

    /**
     * @Route("/link-pago-rifa-by-termino/list", name="link-pago-rifa-by-termino", methods={"POST"})
     */
    public function getLinkPagoRifaByTermino(Request $request, TarjetaRepository $tarjetaRepository, TalonRepository $talonRepository, LinkPagoRifaTalonesRepository $linkTalonesRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;
        $estado = (isset($data['estado'])) ? $data['estado'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $desde = $offset;
        $hasta = $offset + $limit;

        $linkPagoAll = $this->linkPagoRifaRepository->findAll();
        $longitud = count($linkPagoAll);
        $LinkPagoCompleta = $this->linkPagoRifaRepository->findByTermino($termino, 0, $longitud);
        $linkPagoListLimit = $this->linkPagoRifaRepository->findByTermino($termino, $desde, $limit);

        $data = [];
        foreach ($linkPagoListLimit as $linkPago) {
            $idDeposito = null;
            $formaPago = null;
            $numeroTrjeta = null;
            $idLinkPago = $linkPago->getId();
            $talones = [];

            $relacionLinkTalones = $linkTalonesRepository->talonesByLinkPago($idLinkPago);
            $talones = $relacionLinkTalones;

            if (!is_null($linkPago->getDeposito())) {
                $idDeposito = $linkPago->getDeposito()->getId();
                $formaPago = $linkPago->getDeposito()->getTipo();
                $linkPagoSeleccionList = $tarjetaRepository->findOneBy(
                    array('Deposito' => $idDeposito)
                );
                if (!is_null($linkPagoSeleccionList)) {
                    $numeroTrjeta = $linkPagoSeleccionList->getNumeroTarjeta();
                }
            }

            $data[] = [
                'id' => $linkPago->getId(),
                'idPasajero' => $linkPago->getPasajero()->getId(),
                'nombrePasajero' => $linkPago->getPasajero()->getPersona()->getNombres(),
                'apellidoPasajero' => $linkPago->getPasajero()->getPersona()->getApellidos(),
                'idDeposito' => $idDeposito,
                'nombreComprador' => $linkPago->getCompradorNombre(),
                'apellidoComprador' => $linkPago->getCompradorApellido(),
                'compradorEmail' => $linkPago->getCompradorEmail(),
                'compradorCelular' => $linkPago->getCompradorCelular(),
                'estado' => $linkPago->getEstado(),
                'formaPago' => $formaPago,
                'tarjeta' => $numeroTrjeta,
                'talones' => $talones,
            ];
        }

        $longitud = count($LinkPagoCompleta);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => $estado, 'data' => ['linksPagoRifas' => $data], 'totalLinks' => $longitud]);
    }

    /**
     * @Route("/link-pago-rifa-by-estado/list", name="link-pago-rifa-by-estado", methods={"POST"})
     */
    public function getLinkPagoRifaByEstado(Request $request, TarjetaRepository $tarjetaRepository, TalonRepository $talonRepository, LinkPagoRifaTalonesRepository $linkTalonesRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $estado = (isset($data['estado'])) ? $data['estado'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;
        $desde = $offset;
        $hasta = $offset + $limit;

        $linkPagoAll = $this->linkPagoRifaRepository->findAll();
        $longitud = count($linkPagoAll);
        $linkPagoListTotal = $this->linkPagoRifaRepository->findByEstado($estado, 0, $longitud);
        $linkPagoList = $this->linkPagoRifaRepository->findByEstado($estado, $desde, $limit);

        $data = [];
        foreach ($linkPagoList as $linkPago) {
            $idDeposito = null;
            $formaPago = null;
            $numeroTrjeta = null;
            $idLinkPago = $linkPago->getId();
            $talones = [];

            $relacionLinkTalones = $linkTalonesRepository->talonesByLinkPago($idLinkPago);
            $talones = $relacionLinkTalones;

            if (!is_null($linkPago->getDeposito())) {
                $idDeposito = $linkPago->getDeposito()->getId();
                $formaPago = $linkPago->getDeposito()->getTipo();
                $linkPagoSeleccionList = $tarjetaRepository->findOneBy(
                    array('Deposito' => $idDeposito)
                );
                if (!is_null($linkPagoSeleccionList)) {
                    $numeroTrjeta = $linkPagoSeleccionList->getNumeroTarjeta();
                }
            }

            $data[] = [
                'id' => $linkPago->getId(),
                'idPasajero' => $linkPago->getPasajero()->getId(),
                'nombrePasajero' => $linkPago->getPasajero()->getPersona()->getNombres(),
                'apellidoPasajero' => $linkPago->getPasajero()->getPersona()->getApellidos(),
                'idDeposito' => $idDeposito,
                'nombreComprador' => $linkPago->getCompradorNombre(),
                'apellidoComprador' => $linkPago->getCompradorApellido(),
                'compradorEmail' => $linkPago->getCompradorEmail(),
                'compradorCelular' => $linkPago->getCompradorCelular(),
                'estado' => $linkPago->getEstado(),
                'formaPago' => $formaPago,
                'tarjeta' => $numeroTrjeta,
                'talones' => $talones,
            ];
        }
        $longitud = count($linkPagoListTotal);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => $estado, 'data' => ['linksPagoRifas' => $data], 'totalLinks' => $longitud]);
    }

    /**
     * @Route("/link-pago-rifa-by-termino-estado/list", name="link-pago-rifa-by-termino-estado", methods={"POST"})
     */
    public function getLinkPagoRifaByTerminoAndEstado(Request $request, TarjetaRepository $tarjetaRepository, TalonRepository $talonRepository, LinkPagoRifaTalonesRepository $linkTalonesRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $estado = (isset($data['estado'])) ? $data['estado'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $desde = $offset;
        $hasta = $offset + $limit;

        $linkPagoAll = $this->linkPagoRifaRepository->findAll();
        $longitud = count($linkPagoAll);
        $linkPagoListTotal = $this->linkPagoRifaRepository->findByTerminoAndEstado($termino, $estado, 0, $longitud);
        $linkPagoList = $this->linkPagoRifaRepository->findByTerminoAndEstado($termino, $estado, $desde, $limit);

        $data = [];
        foreach ($linkPagoList as $linkPago) {
            $idDeposito = null;
            $formaPago = null;
            $numeroTrjeta = null;
            $idLinkPago = $linkPago->getId();
            $talones = [];

            $relacionLinkTalones = $linkTalonesRepository->talonesByLinkPago($idLinkPago);
            $talones = $relacionLinkTalones;

            if (!is_null($linkPago->getDeposito())) {
                $idDeposito = $linkPago->getDeposito()->getId();
                $formaPago = $linkPago->getDeposito()->getTipo();
                $linkPagoSeleccionList = $tarjetaRepository->findOneBy(
                    array('Deposito' => $idDeposito)
                );
                if (!is_null($linkPagoSeleccionList)) {
                    $numeroTrjeta = $linkPagoSeleccionList->getNumeroTarjeta();
                }
            }

            $data[] = [
                'id' => $linkPago->getId(),
                'idPasajero' => $linkPago->getPasajero()->getId(),
                'nombrePasajero' => $linkPago->getPasajero()->getPersona()->getNombres(),
                'apellidoPasajero' => $linkPago->getPasajero()->getPersona()->getApellidos(),
                'idDeposito' => $idDeposito,
                'nombreComprador' => $linkPago->getCompradorNombre(),
                'apellidoComprador' => $linkPago->getCompradorApellido(),
                'compradorEmail' => $linkPago->getCompradorEmail(),
                'compradorCelular' => $linkPago->getCompradorCelular(),
                'estado' => $linkPago->getEstado(),
                'formaPago' => $formaPago,
                'tarjeta' => $numeroTrjeta,
                'talones' => $talones,
            ];
        }

        $longitud = count($linkPagoListTotal);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => $estado, 'data' => ['linksPagoRifas' => $data], 'totalLinks' => $longitud]);
    }

    /**
     * @Route("/link-pago-rifa/serchTalonesPendientes", name="link-pago-rifa-talones-pendientes", methods={"POST"})
     */
    public function getLinkPagoRifaByTalonesPendientes(Request $request, TarjetaRepository $tarjetaRepository, TalonRepository $talonRepository, LinkPagoRifaTalonesRepository $linkTalonesRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        // $estado = (isset($data['estado'])) ? $data['estado'] : null;
        $estado = 'Pendiente de Pago';

        $limit = 20;
        $offset = $pageIndex * $limit;
        $desde = $offset;
        $hasta = $offset + $limit;

        $linkPagoAll = $this->linkPagoRifaRepository->findAll();
        $longitud = count($linkPagoAll);
        //$linkPagoListTotal = $this->linkPagoRifaRepository->findByEstado($estado, 0, $longitud);

        $linkPagoListTotal =  $linkTalonesRepository->linksPendientesWithTalonesPendientes(0, $longitud);

        $linkPagoList = $linkTalonesRepository->linksPendientesWithTalonesPendientes($desde, $limit);

        $data = [];
        foreach ($linkPagoList as $linkPago) {
            $idDeposito = null;
            $formaPago = null;
            $numeroTrjeta = null;
            $idLinkpago = $linkPago['id'];
            $talones = [];

            $relacionLinkTalones = $linkTalonesRepository->talonesPendientesByLinkPago($idLinkpago);
            $talones = $relacionLinkTalones;

            if (!is_null($linkPago['idDeposito'])) {
                $idDeposito = $linkPago['idDeposito'];
                $formaPago = $linkPago['tipo'];
                $linkPagoSeleccionList = $tarjetaRepository->findOneBy(
                    array('Deposito' => $idDeposito)
                );
                if (!is_null($linkPagoSeleccionList)) {
                    $numeroTrjeta = $linkPagoSeleccionList->getNumeroTarjeta();
                }
            }

            $data[] = [
                'id' => $linkPago['id'],
                'idPasajero' => $linkPago['idPasajero'],
                'nombrePasajero' => $linkPago['nombrePasajero'],
                'apellidoPasajero' => $linkPago['apellidoPasajero'],
                'idDeposito' => $idDeposito,
                'nombreComprador' => $linkPago['nombreComprador'],
                'apellidoComprador' => $linkPago['apellidoComprador'],
                'compradorEmail' => $linkPago['compradorEmail'],
                'compradorCelular' => $linkPago['compradorCelular'],
                'estado' => $linkPago['estado'],
                'formaPago' => $formaPago,
                'tarjeta' => $numeroTrjeta,
                'talones' => $talones,
            ];
        }
        $longitud = count($linkPagoListTotal);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => $estado, 'data' => ['linksPagoRifas' => $data], 'totalLinks' => $longitud]);
    }
}
