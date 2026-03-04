<?php

namespace App\Controller;

use App\Lib\MailSender;
use App\Repository\CompradorRepository;
use App\Repository\CostoExtraRepository;
use App\Repository\DepositoRepository;
use App\Repository\LinkPagoRifaRepository;
use App\Repository\LinkPagoRifaSeleccionRepository;
use App\Repository\LinkPagoRifaTalonesRepository;
use App\Repository\PasajeroRepository;
use App\Repository\TalonRepository;
use App\Repository\TarjetaRepository;
use App\Security\DigitalSign;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;

class GeopayController extends AbstractController
{
    protected $digital_sign;
    private $netId;
    private $netDescription;
    private $callbackUrl;
    private $invoiceDescription;
    private $echoTestUrl;
    private $paymentUrl;
    private $business = 'dtyt';
    private $testProd = false;

    private $costoExtraRepository;
    private $depositoRepository;
    private $tarjetaRepository;
    private $linkPagoRifaRepository;
    private $linkPagoRifaTalonesRepository;
    private $pasajeroRepository;
    private $compradorRepository;
    private $talonRepository;
    private $linkPagoRifaSeleccionRepository;

    public function __construct(
        EntityManagerInterface $em,
        CostoExtraRepository $costoExtraRepository,
        CompradorRepository $compradorRepository,
        DepositoRepository $depositoRepository,
        PasajeroRepository $pasajeroRepository,
        LinkPagoRifaRepository $linkPagoRifaRepository,
        LinkPagoRifaTalonesRepository $linkPagoRifaTalonesRepository,
        TalonRepository $talonRepository,
        TarjetaRepository $tarjetaRepository,
        LinkPagoRifaSeleccionRepository $linkPagoRifaSeleccionRepository
    ) {

        $this->digital_sign = new DigitalSign($em);
        $this->costoExtraRepository = $costoExtraRepository;
        $this->depositoRepository = $depositoRepository;
        $this->tarjetaRepository = $tarjetaRepository;
        $this->linkPagoRifaRepository = $linkPagoRifaRepository;
        $this->linkPagoRifaTalonesRepository = $linkPagoRifaTalonesRepository;
        $this->pasajeroRepository = $pasajeroRepository;
        $this->talonRepository = $talonRepository;
        $this->compradorRepository = $compradorRepository;
        $this->linkPagoRifaSeleccionRepository = $linkPagoRifaSeleccionRepository;

        if ($this->business == 'testing') {
            $this->netId = "DETOQUEYTOQUE_TEST";
            $this->netDescription = "Ecommerce Rifas Testing - GV CCEE 23";
            $this->callbackUrl = "https://dev.rifasgv23.com/assets/php/process.php";
            $this->invoiceDescription = "Rifa Test - Ciencias Economicas";
            $this->echoTestUrl = "https://geopaytest.geocom.com.uy:8099/geoswitchService/rest/process/echoTest";
            $this->paymentUrl = "https://geopaytest.geocom.com.uy:8099/geoswitchService/rest/process/payment";
        } else if ($this->business == 'ccee') {
            $this->netId = "DTYT_CCEE";
            $this->netDescription = "Ecommerce Rifas - GV CCEE 23";
            $this->callbackUrl = "https://rifasgv23.com/assets/php/process.php";
            $this->invoiceDescription = "Rifa - Ciencias Economicas";
            $this->echoTestUrl = "http://bridge.detoqueytoque.com/echo-test.php";
            $this->paymentUrl = "http://bridge.detoqueytoque.com/payment.php";
        } else if ($this->business == 'dtyt') {
            $this->netId = "DE_TOQUE_Y_TOQUE";
            $this->netDescription = "Ecommerce Rifas - de Toque y Toque";
            //$this->callbackUrl = "https://rifas.detoqueytoque.com/assets/php/process.php";
            $this->callbackUrl = $_ENV['URL_CALLBACK_GEOPAY'];
            $this->invoiceDescription = "Rifa - de Toque y Toque";
            $this->echoTestUrl = "http://bridge.detoqueytoque.com/echo-test.php";
            $this->paymentUrl = "http://bridge.detoqueytoque.com/payment.php";
        }
    }

    /**
     * @Route("/geopay", name="geopay")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/GeopayController.php',
        ]);
    }

    /**
     * @Route("/geopay/echo-test", name="echo-test", methods={"POST"})
     */
    public function echoTest(Request $request)
    {
        $auditNumber = date("YmdHis");
        $dateTime = date("Y-m-d H:i:s");
        $dataJson = array(
            "requestHeader" => array(
                "auditNumber" => $auditNumber,
                "dateTime" => $dateTime,
                "netDescription" => $this->netDescription,
                "netId" => $this->netId,
                "version" => "1.14"
            )
        );

        $signature = $this->digital_sign->generateDigitalSign($dataJson);

        $dataJson = array(
            "requestHeader" => array(
                "auditNumber" => $auditNumber,
                "dateTime" => $dateTime,
                "digitalSign" => $signature,
                "netDescription" => $this->netDescription,
                "netId" => $this->netId,
                "version" => "1.14"
            )
        );

        $result = $this->doRequestTest($this->echoTestUrl, $dataJson);

        if ($result === false) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Ha ocurrido un error de autenticación.', "data" => false], 200);
        } else {
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', "data" => $result], 200);
        }
    }

    private function doRequestTest($pUrl, $pData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pData));
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //     'application/x-www-form-urlencoded'
        // ));

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
            )
        );

        $result = curl_exec($ch);

        $result = json_decode($result, true);
        curl_close($ch);

        $signature = $result['responseHeader']['digitalSign'];

        if ($this->digital_sign->verifyRequest($signature, $pData)) {
            return $result;
        } else {
            return false;
        }
    }

    private function doRequest($pUrl, $pData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pData));
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'application/x-www-form-urlencoded'
            )
        );

        $result = curl_exec($ch);

        $result = json_decode($result, true);
        curl_close($ch);

        $signature = $result['responseHeader']['digitalSign'];

        if ($this->digital_sign->verifyRequest($signature, $pData)) {
            return $result;
        } else {
            return false;
        }
    }

    public function insertCostoExtra($pPas, $pMonto, $pCompradorName)
    {
        return $this->costoExtraRepository->saveCostoExtra($pPas, 'Recargo rifa vendida a ' . $pCompradorName, $pMonto);
    }

    public function insertComprador($clientData)
    {
        return $this->compradorRepository->saveComprador($clientData->client_fname . ' ' . $clientData->client_lname, $clientData->client_email, $clientData->client_mobile, $clientData->client_department);
    }

    public function insertDeposito($payment, $pasajero, $monto, $comentario = null, $isGeopay = false)
    {
        if ($payment->card->cardBrandCode == 'MASTER' || $payment->card->cardBrandCode == 'OCA') {
            $tipo = "Credito";
        } else {
            $tipo = ($payment->card->type == 'D') ? "Debito" : "Credito";
        }

        $deposito_fecha = $payment->dateTime;

        $deposito = $this->depositoRepository->saveDeposito($pasajero, $monto, $tipo, new \DateTime($deposito_fecha), 0, $comentario, $isGeopay);

        if ($tipo == 'Credito' || $tipo == 'Debito') {
            if ($payment->currency == "840") {
                $currency = "DOLARES";
            } else if ($payment->currency == "858") {
                $currency = "PESOS";
            }

            $cuotas = 1;

            if ($tipo == "Credito") {
                $cuotas = $payment->installments;
            }

            if ($payment->card->cardBrandCode == 'CLUBDELESTE') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else if ($payment->card->cardBrandCode == 'MASTER') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else if ($payment->card->cardBrandCode == 'OCA') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else if ($payment->card->cardBrandCode == 'AMEX') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else if ($payment->card->cardBrandCode == 'ANDA') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else if ($payment->card->cardBrandCode == 'CABAL') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else if ($payment->card->cardBrandCode == 'CREDITEL') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else if ($payment->card->cardBrandCode == 'CREDIR') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else if ($payment->card->cardBrandCode == 'PASSCARD') {
                $tarjeta_acquirer = $payment->card->cardBrandName;
            } else {
                $tarjeta_acquirer = $payment->card->issuerName;
            }

            $tarjeta_issuer = $payment->card->cardBrandCode;
            $tarjeta_moneda = $currency;
            $tarjeta_cuotas = $cuotas;
            $tarjeta_fechaTransaccion = $payment->dateTime;
            $tarjeta_codigoAutorizacion = $payment->authorizer->authorizationCode;
            $tarjeta_numeroTarjeta = $payment->card->cardMask;
            $tarjeta_vencimientoTarjeta = $payment->card->dueDate;
            $tarjeta_nombreTarjeta = '';

            $tarjeta = $this->tarjetaRepository->saveTarjeta($deposito, $tarjeta_issuer, $tarjeta_moneda, $tarjeta_cuotas, new \DateTime($tarjeta_fechaTransaccion), $tarjeta_codigoAutorizacion, $tarjeta_numeroTarjeta, $tarjeta_acquirer, $tarjeta_nombreTarjeta, $tarjeta_vencimientoTarjeta);
        }

        return array('deposito' => $deposito, 'tarjeta' => $tarjeta);
    }

    private function recargo($pCuotas, $pTarjeta)
    {
        return false;

        if ($this->business == 'ccee')
            return false;

        $lCuotas = intval($pCuotas);
        $selectedCard = $pTarjeta;

        if ($pCuotas != "" && $selectedCard != "") {

            if ($selectedCard == "Visa" && $lCuotas > 10) {
                return true;
            }

            if ($selectedCard == "OCA" && $lCuotas > 6) {
                return true;
            }
        }
        return false;
    }

    /**
     * @Route("/geopay/process-geopay-result", name="process-geopay-result", methods={"POST"})
     */
    public function processGeopayResult(Request $request, MailSender $mailSender)
    {
        $json = json_decode($request->get('json'));
        $data = json_decode($json->data);

        if ($data != '' && $data != null) {
            $additionalData = $data->ecommerceAdditionalData;
            $token = $this->searchCustom($additionalData, 'name', 'clientToken')[0]['value'];

            $asumirRecargo = $this->searchCustom($additionalData, 'name', 'asumirRecargo');

            if ($token != '' && $token != null) {
                $linkData = $this->linkPagoRifaRepository->findOneBy(
                    array(
                        "EncryptedLink" => $token
                    )
                );

                $talones = $this->linkPagoRifaTalonesRepository->findBy(
                    array(
                        "LinkPagoRifa" => $linkData
                    )
                );

                if ($linkData) {
                    if ($linkData->getEstado() == 'Pendiente de pago') {
                        $digitalSign = $data->responseHeader->digitalSign;

                        if ($this->digital_sign->verifyRequest($digitalSign, json_decode(json_encode($data), true))) {
                            $responseCode = $data->responseHeader->responseCode;
                            $payment = $data->payment;

                            if ($responseCode == '00') {
                                $clientData = $this->searchCustom($additionalData, 'name', 'clientData')[0]['value'];
                                $clientData = json_decode($clientData);

                                $itiPas = $linkData->getPasajero();

                                $comprador = $this->insertComprador($clientData);

                                if ($this->testProd) {
                                    $montoAux = 0;
                                    foreach ($talones as $tal) {
                                        if ($this->business == 'testing' || $this->business == 'ccee') {
                                            // if ($payment->card->type == 'D') {
                                            //     $montoAux += $tal->getTalon()->getPrecio();
                                            // } else {
                                            //     $montoAux += 5290;
                                            // }
                                            $montoAux += 4761;
                                        } else if ($this->business == 'dtyt') {
                                            $montoAux += $tal->getTalon()->getPrecio();
                                        }
                                    }
                                } else {
                                    $montoAux = $payment->amount / 100;
                                    // $montoAux = $payment->amount;
                                }

                                if ($this->business == 'ccee') {
                                    $montoAux = 4761;
                                    // if (($payment->amount / 100) == 2) {
                                    //     $montoAux = 5290;
                                    // } else if (($payment->amount / 100) == 1) {
                                    //     $alertTxt = 'El comprador seleccionó una tarjeta de débito en nuestro sitio por lo que el monto a pagar fue de $4990, pero en Geocom efectuó la compra con una tarjeta de crédito, por lo que debía haber pago $5290.';
                                    //     $montoAux = 4990;
                                    // }
                                }


                                $deposito_tarjeta = $this->insertDeposito($payment, $itiPas, $montoAux, null, true);

                                $deposito_tipo = ($payment->card->type == 'D') ? "Debito" : "Credito";

                                if ($deposito_tipo == 'Credito') {
                                    $cuotas = $payment->installments;

                                    $tarjeta_ac = $payment->card->cardBrandName;

                                    $recargo = $this->recargo($cuotas, $tarjeta_ac);

                                    $itiPasInterno = $this->pasajeroRepository->findOneBy(
                                        array(
                                            "id" => 1991
                                        )
                                    );

                                    if ($recargo && (($payment->amount) / 100 % 20) == 0) {
                                        $montoRecargo = ($payment->amount / 100) * 0.05;

                                        $costExtra = $this->insertCostoExtra($itiPas, $montoRecargo, $clientData->client_fname . ' ' . $clientData->client_lname);
                                        $dep_interno = $this->insertDeposito($payment, $itiPasInterno, $montoRecargo, 'Asumido por vendedor - ' . $itiPas->getId(), true);
                                    } else if ($recargo && $clientData->client_email != 'camiloalies@gmail.com') {
                                        $dep_interno = $this->insertDeposito($payment, $itiPasInterno, ($payment->amount / 100) - $montoAux, 'Asumido por comprador - ' . $itiPas->getId(), true);
                                    }
                                }

                                if ($comprador != null && $deposito_tarjeta['deposito'] && $deposito_tarjeta['tarjeta']) {
                                    $aux = array();
                                    $arrayItem = array();
                                    $auxNumber = false;
                                    $firstLoop = true;
                                    $counter = 0;
                                    $monto = 0;
                                    $totalRecaudado = 0.00;

                                    foreach ($talones as $tal) {
                                        $counter++;

                                        $talon = $tal->getTalon();
                                        $talon->setDeposito($deposito_tarjeta['deposito']);
                                        $talon->setEstado("Pago");
                                        $talon->setComprador($comprador);
                                        $talon->setFechaRegistro(new \DateTime('now'));

                                        $totalRecaudado += $talon->getRecaudacion();

                                        if ($this->business == 'testing' || $this->business == 'ccee') {
                                            // if (($payment->amount / 100) % 5290 == 0 || ($payment->amount / 100) == 2) {
                                            //     $talon->setPrecio(5290);
                                            // }

                                            // if (($payment->amount / 100) % 4990 ==0 || ($payment->amount / 100) == 1) {
                                            //     $talon->setPrecio(4990);
                                            // }
                                            $talon->setPrecio(4761);
                                        }

                                        $this->talonRepository->updateTalon($talon);


                                        if ($auxNumber != $talon->getNumero()) {
                                            $auxNumber = $talon->getNumero();
                                            if (!$firstLoop) {
                                                array_push($aux, $arrayItem);
                                            } else {
                                                $firstLoop = false;
                                            }
                                            $arrayItem = array();
                                        }

                                        array_push($arrayItem, $talon);

                                        if ($counter == count($talones)) {
                                            array_push($aux, $arrayItem);
                                        }
                                    }

                                    // if ($this->business == 'testing' || $this->business == 'ccee') {
                                    // $monto = $payment->amount / 100;
                                    // } else if ($this->business == 'dtyt') {
                                    //     $monto = $payment->amount;
                                    // }

                                    if ($this->testProd) {
                                        $monto = 0;
                                        foreach ($talones as $tal) {
                                            if ($this->business == 'testing' || $this->business == 'ccee') {
                                                // if ($payment->card->type == 'D') {
                                                //     $monto += $tal->getTalon()->getPrecio();
                                                // } else {
                                                //     $monto += 5290;
                                                // }
                                                $monto += 4761;
                                            } else if ($this->business == 'dtyt') {
                                                $monto += $tal->getTalon()->getPrecio();
                                            }
                                        }
                                    } else {
                                        $monto = $payment->amount / 100;
                                        // $montoAux = $payment->amount;
                                    }

                                    if ($this->business == 'ccee') {
                                        // if (($payment->amount / 100) == 2) {
                                        //     $monto = 5290;
                                        // } else if (($payment->amount / 100) == 1) {
                                        //     $monto = 4990;
                                        // }
                                        $monto = 4761;
                                    }


                                    $montoRecaudado = $totalRecaudado;

                                    $talones = $aux;

                                    $linkData->setEstado("Pago");
                                    $linkData->setDeposito($deposito_tarjeta['deposito']);
                                    $linkData->setUpdatedAt(new \DateTimeImmutable('now'));

                                    $this->linkPagoRifaRepository->updateLinkPagoRifa($linkData);

                                    $alertTxt = '';

                                    if ($this->business == 'ccee' || $this->business == 'testing') {
                                        if ($payment->card->type == 'D') {
                                            if (($payment->amount / 100) % 5290 == 0 || ($payment->amount / 100) == 2) {
                                                $alertTxt = 'El comprador seleccionó una tarjeta de crédito en nuestro sitio por lo que el monto a pagar fue de $5290, pero en Geocom efectuó la compra con una tarjeta de débito, por lo que debía haber pago $4990.';
                                            }
                                        } else if ($payment->card->type == 'C') {
                                            if (($payment->amount / 100) % 4990 == 0 || ($payment->amount / 100) == 1) {
                                                $alertTxt = 'El comprador seleccionó una tarjeta de débito en nuestro sitio por lo que el monto a pagar fue de $4990, pero en Geocom efectuó la compra con una tarjeta de crédito, por lo que debía haber pago $5290.';
                                                // $alertTxt = 'El comprador seleccionó una tarjeta de débito en nuestro sitio pero en Geocom efectuó la compra con una tarjeta de crédito.';
                                            }
                                        }
                                    }

                                    $responseVendedor = array(
                                        'asunto' => 'Confirmación de pago de Rifas - ' . $comprador->getNombre(),
                                        'fromAddress' => $this->getParameter('fromAddress'),
                                        'fromName' => $this->getParameter('businessName'),
                                        'to' => $linkData->getPasajero()->getPersona()->getUser()->getEmail(),
                                        'typeTemplate' => 'confirmacionVendedorMail',
                                        'dataEmail' => array(
                                            'nombrePasajero' => $itiPas->getPersona()->getNombres() . ' ' . $itiPas->getPersona()->getApellidos(),
                                            'comprador' => $comprador->getNombre(),
                                            'rifas' => $talones,
                                            'monto' => $monto,
                                            'montoRecaudado' => $montoRecaudado,
                                            'talonType' => $this->getParameter('talonType'),
                                            'moneda' => $this->getParameter('moneda'),
                                            'business' => $this->getParameter('business'),
                                            'alertTxt' => $alertTxt
                                        )
                                    );

                                    $mailSender->sendMail($responseVendedor);

                                    $responseComprador = array(
                                        'asunto' => $this->getParameter('businessNameShort') . ' - Confirmación de pago de Rifas',
                                        'fromAddress' => $this->getParameter('fromAddress'),
                                        'fromName' => $this->getParameter('businessName'),
                                        'to' => $clientData->client_email,
                                        'typeTemplate' => 'confirmacionCompradorMail',
                                        'dataEmail' => array(
                                            'nombrePasajero' => $itiPas->getPersona()->getNombres() . ' ' . $itiPas->getPersona()->getNombres(),
                                            'comprador' => $comprador->getNombre(),
                                            'rifas' => $talones,
                                            'monto' => $monto,
                                            'montoRecaudado' => $montoRecaudado,
                                            'talonType' => $this->getParameter('talonType'),
                                            'moneda' => $this->getParameter('moneda')
                                        )
                                    );

                                    $mailSender->sendMail($responseComprador);

                                    //obtener codigo de respuesta y decidir que procesar
                                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => "00 - Correcto",], 200);
                                } else {
                                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => "002 - Error de creacion de comprador, deposito y/o tarjeta.",], 200);
                                }
                            } else {
                                if ($data->responseHeader->responseCode == '05') {
                                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => "05",], 200);
                                }

                                if ($data->responseHeader->responseCode == '25') {
                                    // Token expirado

                                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => "25",], 200);
                                }

                                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => $data->responseHeader->responseCode . " - " . $data->responseHeader->responseDescription,], 200);
                            }
                        } else {
                            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => '001 - Error de autorización.'], 200);
                        }
                    } else {
                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => "10 - Correcto",], 200);
                        // return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => '002 - Error de autorización.'], 200);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => '003 - Error de autorización.'], 200);
                }
                // } else {
                //     return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => '004 - Error de autorización.'], 200);
                // }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => '005 - Error de autorización.'], 200);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => '006 - Error de autorización.'], 200);
        }
    }

    private function searchCustom($array, $key, $value)
    {
        $results = array();

        if (is_object($array)) {
            $array = (array) $array;
        }

        if (is_array($array)) {

            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, $this->searchCustom($subarray, $key, $value));
            }
        }

        return $results;
    }

    /**
     * @Route("/geopay/init-payment", name="init-payment", methods={"POST"})
     */
    public function initPayment(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $token = (isset($data['token'])) ? $data['token'] : null;
        $authCheck = $this->digital_sign->authCheck($token);
        $amount = 0;
        $contador = 0;

        if ($authCheck == true) {

            if ($data != null) {
                if ($this->testProd) {
                    if ($data['tipo_tarjeta'] == 'Credito') {
                        $amount = 200;
                    } else if ($data['tipo_tarjeta'] == 'Debito') {
                        $amount = 100;
                    }
                } else {
                    $amount = (isset($data['amount'])) ? $data['amount'] : null;
                }
                $client_department = (isset($data['client_department'])) ? $data['client_department'] : null;
                $client_email = (isset($data['client_email'])) ? $data['client_email'] : null;
                $client_fname = (isset($data['client_fname'])) ? $data['client_fname'] : null;
                $client_lname = (isset($data['client_lname'])) ? $data['client_lname'] : null;
                $client_mobile = (isset($data['client_mobile'])) ? $data['client_mobile'] : null;
                $venta_id = (isset($data['venta_id'])) ? $data['venta_id'] : null;
                $cuotas = (isset($data['cuotas'])) ? $data['cuotas'] : null;
                $tarjetaSeleccionada = (isset($data['tarjeta'])) ? $data['tarjeta'] : null;
                $tipoTarjeta = (isset($data['tipo_tarjeta'])) ? $data['tipo_tarjeta'] : null;
                $asumirRecargo = (isset($data['asumir_recargo'])) ? $data['asumir_recargo'] : null;

                if ($this->getParameter('moneda') == "USD") {
                    $currency = "840";
                } else if ($this->getParameter('moneda') == "$") {
                    $currency = "858";
                }

                if (trim($client_email) == 'camiloalies@gmail.com') {
                    if (strpos($amount, '.') === false) {
                        //                        $amount = (int) $amount * 100;
                    } else {
                        $amount = str_replace('.', '', $amount);
                    }
                } else {
                    if (strpos($amount, '.') === false) {
                        //                        $amount = (int) $amount * 100;
                    } else {
                        $amount = str_replace('.', '', $amount);
                    }
                }

                $client = array(
                    'client_department' => $client_department,
                    'client_email' => $client_email,
                    'client_fname' => $client_fname,
                    'client_lname' => $client_lname,
                    'client_mobile' => $client_mobile
                );

                $linkData = $this->linkPagoRifaRepository->findOneBy(
                    array(
                        "EncryptedLink" => $token
                    )
                );

                $linkData->setCompradorNombre($client_fname);
                $linkData->setCompradorApellido($client_lname);
                if ($this->getParameter('saveCompradorEmail')) {
                    $linkData->setCompradorEmail($client_email);
                }
                if ($this->getParameter('saveCompradorCelular')) {
                    $linkData->setCompradorCelular(trim(str_replace(' ', '', $client_mobile)));
                }
                if ($this->getParameter('saveCompradorDepartamento')) {
                    $linkData->setCompradorDepartamento($client_department);
                }

                $linkData = $this->linkPagoRifaRepository->updateLinkPagoRifa($linkData);

                $auditNumber = date("YmdHis");
                $dateTime = date("Y-m-d H:i:s");
                $date = date("Y-m-d");
                $dataJson = array(
                    "amount" => (int) $amount,
                    "client" => array(
                        "clientId" => $client_email,
                        "clientIdType" => "email",
                        "email" => $client_email,
                        "firstName" => $client_fname,
                        "lastName" => $client_lname,
                        "mobile" => $client_mobile
                    ),
                    "config" => array(
                        "callbackUrl" => $this->callbackUrl,
                    ),
                    "currency" => $currency,
                    "ecommerceAdditionalData" => [
                        array(
                            "name" => "asumirRecargo",
                            "value" => $asumirRecargo
                        ),
                        array(
                            "name" => "clientData",
                            "value" => json_encode($client)
                        ),
                        array(
                            "name" => "clientToken",
                            "value" => $token
                        )
                    ],
                    "indi" => 0,
                    "installments" => $cuotas,
                    "invoice" => array(
                        "address" => array(
                            "city" => "Montevideo",
                            "country" => "UY",
                            "doorNumber" => "2323",
                            "street" => "21 de Setiembre"
                        ),
                        "currency" => $currency,
                        "date" => $date,
                        "description" => $this->invoiceDescription,
                        "finalConsumer" => "false",
                        "number" => strval($venta_id),
                        "totalAmount" => (int) $amount
                    ),
                    "reference" => $venta_id,
                    "requestHeader" => array(
                        "auditNumber" => $auditNumber,
                        "dateTime" => $dateTime,
                        "netDescription" => $this->netDescription,
                        "netId" => $this->netId,
                        "version" => "1.14"
                    ),
                    "taxAmount" => 0,
                    "taxedAmount" => 0
                );

                $signature = $this->digital_sign->generateDigitalSign($dataJson);

                $dataJson = array(
                    "amount" => (int) $amount,
                    "client" => array(
                        "clientId" => $client_email,
                        "clientIdType" => "email",
                        "email" => $client_email,
                        "firstName" => $client_fname,
                        "lastName" => $client_lname,
                        "mobile" => $client_mobile
                    ),
                    "config" => array(
                        "callbackUrl" => $this->callbackUrl,
                    ),
                    "currency" => $currency,
                    "ecommerceAdditionalData" => [
                        array(
                            "name" => "asumirRecargo",
                            "value" => $asumirRecargo
                        ),
                        array(
                            "name" => "clientData",
                            "value" => json_encode($client)
                        ),
                        array(
                            "name" => "clientToken",
                            "value" => $token
                        )
                    ],
                    "indi" => 0,
                    "installments" => $cuotas,
                    "invoice" => array(
                        "address" => array(
                            "city" => "Montevideo",
                            "country" => "UY",
                            "doorNumber" => "2323",
                            "street" => "21 de Setiembre"
                        ),
                        "currency" => $currency,
                        "date" => $date,
                        "description" => $this->invoiceDescription,
                        "finalConsumer" => "false",
                        "number" => strval($venta_id),
                        "totalAmount" => (int) $amount
                    ),
                    "reference" => $venta_id,
                    "requestHeader" => array(
                        "auditNumber" => $auditNumber,
                        "dateTime" => $dateTime,
                        "digitalSign" => $signature,
                        "netDescription" => $this->netDescription,
                        "netId" => $this->netId,
                        "version" => "1.14"
                    ),
                    "taxAmount" => 0,
                    "taxedAmount" => 0
                );

                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $log = "Request: " . json_encode($dataJson) . PHP_EOL .
                    "Date: " . date("F j, Y, g:i a") . PHP_EOL .
                    "-------------------------" . PHP_EOL;

                $ruta_archivo_log = $_ENV['RUTA_LOG_GEOPAY'];
                // Save LOG
                if ($this->business == 'dtyt') {

                    file_put_contents($ruta_archivo_log . date("j.n.Y") . '.log', $log, FILE_APPEND);
                } else if ($this->business == 'testing') {
                    file_put_contents('/home/detoqueytoquenew/dev.rifasgv23.com/assets/php/logs/request_testing_' . date("j.n.Y") . '.log', $log, FILE_APPEND);
                } else if ($this->business == 'ccee') {
                    file_put_contents('/home/detoqueytoquenew/rifasgv23.com/assets/php/logs/request_' . date("j.n.Y") . '.log', $log, FILE_APPEND);
                }

                if ($this->business == 'testing') {
                    $result = $this->doRequestTest($this->paymentUrl, $dataJson);
                } else {
                    $result = $this->doRequest($this->paymentUrl, $dataJson);
                }

                $linkData = $this->linkPagoRifaRepository->findOneBy(
                    array(
                        "EncryptedLink" => $token
                    )
                );

                $linkData->setGeocomToken($result['token']);

                $this->linkPagoRifaRepository->updateLinkPagoRifa($linkData);

                $this->linkPagoRifaSeleccionRepository->saveLinkPagoRifaSeleccion($linkData, $tarjetaSeleccionada, $tipoTarjeta, $cuotas);
                $contador++;

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Error en JSON.'], 200);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Error de autorización.'], 200);
        }
    }

    /**
     * @Route("/geopay/void-payment", name="void-payment", methods={"POST"})
     */
    public function voidPaymentAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $linkPago_Id = (isset($data['linkPago_Id'])) ? $data['linkPago_Id'] : null;

        $linkData = $this->linkPagoRifaRepository->findOneBy(
            array(
                "id" => 197
            )
        );

        $auditNumber = date("YmdHis");
        $dateTime = date("Y-m-d H:i:s");
        $dataJson = array(
            "accessToken" => $linkData->getGeocomToken(),
            "requestHeader" => array(
                "auditNumber" => $auditNumber,
                "dateTime" => $dateTime,
                "netDescription" => $this->netDescription,
                "netId" => $this->netId,
                "version" => "1.14"
            )
        );

        $signature = $this->digital_sign->generateDigitalSign($dataJson);

        $dataJson = array(
            "accessToken" => $linkData->getGeocomToken(),
            "requestHeader" => array(
                "auditNumber" => $auditNumber,
                "dateTime" => $dateTime,
                "digitalSign" => $signature,
                "netDescription" => $this->netDescription,
                "netId" => $this->netId,
                "version" => "1.14"
            )
        );

        $result = $this->doRequest('https://geopaytest.geocom.com.uy:8099/geoswitchService/rest/process/voidPayment', $dataJson);

        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => '', 'data' => $result], 200);
    }
}
