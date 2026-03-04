<?php

namespace App\Controller;

use App\Lib\MailSender;

use App\Entity\HistorialTransferencias;
use App\Repository\CompradorRepository;
use App\Repository\DepositoRepository;
use App\Repository\PasajeroRepository;
use App\Repository\TalonRepository;
use App\Repository\HistorialTransferenciasRepository;
use App\Repository\LinkPagoRifaRepository;
use App\Repository\LinkPagoRifaTalonesRepository;
use App\Repository\PersonaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

//require_once 'DTYTSendMail.php';

class TalonController extends AbstractController
{
    private $helpers;
    /**
     * @Route("/talon", name="talon")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TalonController.php',
        ]);
    }

    /**
     * @Route("/api/talon/delete-pago", name="delete_talon_pago", methods={"POST"})
     */
    public function deletePago(Request $request, PasajeroRepository $pasajeroRepository, TalonRepository $talonRepository, MailSender $mailSender)
    {
        $authToken = $request->headers->get('Authorization');

        $data = json_decode($request->getContent(), true);
        $talonId = (isset($data['talon_id'])) ? $data['talon_id'] : null;

        if ($authToken != null && $talonId != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                $talon = $talonRepository->findOneBy(
                    array(
                        "id" => $talonId
                    )
                );

                $now = new \DateTime('now');

                if ($talon->getDeposito()->getPasajero() == $pasajero) {
                    if ($talon->getFechaSorteo() > $now) {
                        $comprador = $talon->getComprador();

                        $talon->setComprador(null);
                        $talon->setEstado('Pendiente de Pago');
                        $talon->setDeposito(null);

                        $talonRepository->updateTalon($talon);

                        if ($comprador->getEmail() != null && $comprador->getEmail() != '') {
                            $responseSolicitante = array(
                                'asunto' => $this->getParameter('businessNameShort') . ' - Cancelación de pago de Rifas',
                                'fromAddress' => $this->getParameter('fromAddress'),
                                'fromName' => $this->getParameter('businessName'),
                                'to' => $comprador->getEmail(),
                                'typeTemplate' => 'borrarRegistroTalonCompradorMail',
                                'dataEmail' => array(
                                    'nombrePasajero' => $pasajero->getPersona()->getNombres() . ' ' . $pasajero->getPersona()->getApellidos(),
                                    'comprador' => $comprador->getNombre(),
                                    'rifas' => $talon,
                                    'talonType' => $this->getParameter('talonType'),
                                    'precio' => $talon->getPrecio(),
                                    'moneda' => $this->getParameter('moneda')
                                )
                            );

                            $mailSender->sendMail($responseSolicitante);
                        }


                        if ($this->getParameter('talonType') == 'multi') {
                            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Talon actualizado'], 200);
                        } else {
                            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Rifa actualizada'], 200);
                        }
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Sorteo ya realizado'], 400);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Propietario incorrecto'], 403);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }

    /**
     * @Route("/api/talon/get-detailed-by-pasajero", name="get-detailed-talones-by-pasajero", methods={"GET"})
     */
    public function getDetailedByPasajero(Request $request, PasajeroRepository $pasajeroRepository, TalonRepository $talonRepository)
    {

        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                //TODO: Talon o Rifa (?)
                $result = $talonRepository->createQueryBuilder('t')
                    ->select('t.Numero, t.Estado, c.Nombre, t.SorteoNumero, t.Comentario, d.Tipo, t.Precio')
                    ->where("t.Pasajero = :pas")
                    ->leftJoin('t.Comprador', 'c')
                    ->leftJoin('t.Deposito', 'd')
                    ->setParameter('pas', $pasajero)
                    ->orderBy('t.Numero, t.FechaSorteo')
                    ->getQuery()
                    ->getResult();

                if (count($result) > 0) {
                    $aux = array();
                    $arrayItem = array();
                    $auxNumber = false;
                    $firstLoop = true;
                    $counter = 0;
                    foreach ($result as $item) {
                        $counter++;

                        if ($auxNumber != $item['Numero']) {
                            $auxNumber = $item['Numero'];
                            if (!$firstLoop) {
                                array_push($aux, $arrayItem);
                            } else {
                                $firstLoop = false;
                            }
                            $arrayItem = array();
                        }

                        array_push($arrayItem, $item);

                        if ($counter == count($result)) {
                            array_push($aux, $arrayItem);
                        }
                    }

                    $result = $aux;
                }
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }

    /**
     * @Route("/api/talon/get-by-pasajero", name="get-talones-by-pasajero", methods={"GET"})
     */
    public function getByPasajero(Request $request, PasajeroRepository $pasajeroRepository, TalonRepository $talonRepository)
    {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                //TODO: Talon o Rifa (?)
                $result = $talonRepository->createQueryBuilder('t')
                    ->select('t.id, t.Numero, t.Estado, t.SorteoNumero, t.Precio, t.Comentario')
                    ->where("t.Pasajero = :pas")
                    ->setParameter('pas', $pasajero)
                    ->orderBy('t.Numero, t.FechaSorteo')
                    ->getQuery()
                    ->getResult();

                if (count($result) > 0) {
                    $aux = array();
                    $arrayItem = array();
                    $auxNumber = false;
                    $firstLoop = true;
                    $counter = 0;
                    foreach ($result as $item) {
                        $counter++;

                        if ($auxNumber != $item['Numero']) {
                            $auxNumber = $item['Numero'];
                            if (!$firstLoop) {
                                array_push($aux, $arrayItem);
                            } else {
                                $firstLoop = false;
                            }
                            $arrayItem = array();
                        }

                        array_push($arrayItem, $item);

                        if ($counter == count($result)) {
                            array_push($aux, $arrayItem);
                        }
                    }

                    $result = $aux;
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }

    /**
     * @Route("/api/talon/register-talones", name="register-talones", methods={"POST"})
     */
    public function registerTalones(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        CompradorRepository $compradorRepository,
        TalonRepository $talonRepository,
        DepositoRepository $depositoRepository,
        MailSender $mailSender
    ) {
        $authToken = $request->headers->get('Authorization');
        $data = json_decode($request->getContent(), true);

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                $comprador_data = (isset($data['comprador'])) ? $data['comprador'] : null;
                $talonesIds = (isset($data['talones'])) ? $data['talones'] : null;
                $deposito_id = (isset($data['deposito_id'])) ? $data['deposito_id'] : null;

                $deposito = $depositoRepository->findOneBy(
                    array(
                        "id" => $deposito_id
                    )
                );

                $comprador = $compradorRepository->saveComprador($comprador_data['Nombre'] . ' ' . $comprador_data['Apellido'], $comprador_data['Email'], $comprador_data['Celular'], $comprador_data['Departamento']);

                $talones = array();
                $monto = 0;
                $aux = array();
                $arrayItem = array();
                $auxNumber = false;
                $firstLoop = true;
                $counter = 0;

                foreach ($talonesIds as $talId) {
                    $counter++;
                    $talon = $talonRepository->findOneBy(
                        array(
                            "id" => $talId
                        )
                    );

                    $talon->setDeposito($deposito);
                    $talon->setComprador($comprador);
                    $talon->setPasajero($pasajero);
                    $talon->setEstado('Pago');

                    $monto += $talon->getPrecio();

                    $talon = $talonRepository->updateTalon($talon);

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

                    if ($counter == count($talonesIds)) {
                        array_push($aux, $arrayItem);
                    }
                }

                $talones = $aux;

                if ($comprador->getEmail() != null && $comprador->getEmail() != '') {
                    $responseSolicitante = array(
                        'asunto' => $this->getParameter('businessNameShort') . ' - Confirmación de pago de Rifas',
                        'fromAddress' => $this->getParameter('fromAddress'),
                        'fromName' => $this->getParameter('businessName'),
                        'to' => $comprador->getEmail(),
                        'typeTemplate' => 'confirmacionCompradorContadoMail',
                        'dataEmail' => array(
                            'nombrePasajero' => $pasajero->getPersona()->getNombres() . ' ' . $pasajero->getPersona()->getApellidos(),
                            'comprador' => $comprador->getNombre(),
                            'rifas' => $talones,
                            'precio' => $talones[0][0]->getPrecio(),
                            'monto' => $monto,
                            'talonType' => $this->getParameter('talonType'),
                            'moneda' => $this->getParameter('moneda')
                        )
                    );

                    $mailSender->sendMail($responseSolicitante);
                }

                if ($this->getParameter('talonType') == 'multi') {
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Talon/es registrado/s correctamente'], 200);
                } else {
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Rifa/s registrada/s correctamente'], 200);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Parámetros no válidos o incompletos.'], 403);
        }
    }

    /**
     * @Route("/api/talon/generate-link", name="generate-link", methods={"POST"})
     */
    public function generateLink(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        TalonRepository $talonRepository,
        LinkPagoRifaRepository $linkPagoRifaRepository,
        LinkPagoRifaTalonesRepository $linkPagoRifaTalonesRepository,
        MailSender $mailSender
    ) {
        $authToken = $request->headers->get('Authorization');
        $data = json_decode($request->getContent(), true);

        $key = 'secret-payment';
        if ($authToken != null) {
            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                $compradorNombre = (isset($data['Nombre'])) ? $data['Nombre'] : null;
                $compradorApellido = (isset($data['Apellido'])) ? $data['Apellido'] : null;
                $compradorEmail = (isset($data['Email'])) ? $data['Email'] : null;
                $compradorCelular = (isset($data['Celular'])) ? $data['Celular'] : null;
                $compradorDepartamento = (isset($data['Departamento'])) ? $data['Departamento'] : null;
                $status = (isset($data['status'])) ? $data['status'] : null;
                $talones = (isset($data['talones'])) ? $data['talones'] : null;
                $asumirRecargo = (isset($data['asumirRecargo'])) ? $data['asumirRecargo'] : false;

                if ($asumirRecargo == 'true') {
                    $asumirRecargo = true;
                } else {
                    $asumirRecargo = false;
                }

                $token = array(
                    "pasId" => $pasajero->getId(),
                    "talones" => $talones
                );

                $six_digit_random_number = mt_rand(100000, 999999);
                //            $encryptedLink = JWT::encode($token, $key, 'HS256');
                $encryptedLink = md5(serialize($token) . $six_digit_random_number);

                $link = $linkPagoRifaRepository->saveLinkPagoRifa($pasajero, $compradorEmail, $compradorNombre, $compradorApellido, $compradorCelular, $compradorDepartamento, $status, $encryptedLink, $asumirRecargo);

                $talones_objects = array();

                $aux = array();
                $arrayItem = array();
                $auxNumber = false;
                $firstLoop = true;
                $counter = 0;
                foreach ($talones as $item) {
                    $counter++;

                    $tal = $talonRepository->findOneBy(
                        array(
                            "id" => $item
                        )
                    );

                    if ($tal) {
                        $linkTal = $linkPagoRifaTalonesRepository->saveLinkPagoRifaTalones($link, $tal);
                    }

                    if ($auxNumber != $tal->getNumero()) {
                        $auxNumber = $tal->getNumero();
                        if (!$firstLoop) {
                            array_push($aux, $arrayItem);
                        } else {
                            $firstLoop = false;
                        }
                        $arrayItem = array();
                    }

                    array_push($arrayItem, $tal);

                    if ($counter == count($talones)) {
                        array_push($aux, $arrayItem);
                    }
                }

                $talones_objects = $aux;

                $responseSolicitante = array(
                    'asunto' => $this->getParameter('businessNameShort') . ' - Ayuda a ' . $pasajero->getPersona()->getNombres() . ' ' . $pasajero->getPersona()->getApellidos() . ' a realizar su viaje',
                    'fromAddress' => $this->getParameter('fromAddress'),
                    'fromName' => $this->getParameter('businessName'),
                    'to' => $compradorEmail,
                    'typeTemplate' => 'linkCompradorMail',
                    'dataEmail' => array(
                        'nombrePasajero' => $pasajero->getPersona()->getNombres() . ' ' . $pasajero->getPersona()->getApellidos(),
                        'comprador' => $compradorNombre . ' ' . $compradorApellido,
                        'rifas' => $talones_objects,
                        'token' => $encryptedLink,
                        'talonType' => $this->getParameter('talonType'),
                        'moneda' => $this->getParameter('moneda'),
                        'business' => $this->getParameter('business'),
                        'manual' => $this->getParameter('manualUrl')
                    )
                );

                $mailSender->sendMail($responseSolicitante);

                //$this->enviarMail($compradorEmail, $pasajero->getPersona()->getNombres());

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Link creado correctamente', 'data' => $encryptedLink], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/get-bolsa-by-pasajero", name="get-bolsa-by-pasajero", methods={"GET"})
     */
    public function getBolsaByPasajero(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        TalonRepository $talonRepository
    ) {
        $authToken = $request->headers->get('Authorization');
        $data = json_decode($request->getContent(), true);

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {
                //TODO: Talon o Rifa (?)
                $result = $talonRepository->createQueryBuilder('t')
                    ->select('t.Numero, t.Estado, t.SorteoNumero')
                    ->where("t.Estado = 'Para transferir'")
                    ->where("t.Pasajero = :pas AND t.Estado = 'Para transferir'")
                    ->setParameter('pas', $pasajero)
                    ->orderBy('t.Numero, t.FechaSorteo')
                    ->getQuery()
                    ->getResult();

                if (count($result) > 0) {
                    $aux = array();
                    $arrayItem = array();
                    $auxNumber = false;
                    $firstLoop = true;
                    $counter = 0;
                    foreach ($result as $item) {
                        $counter++;
                        if ($auxNumber != $item['Numero']) {
                            $auxNumber = $item['Numero'];
                            if (!$firstLoop) {
                                array_push($aux, $arrayItem);
                            } else {
                                $firstLoop = false;
                            }
                            $arrayItem = array();
                        }

                        array_push($arrayItem, $item);

                        if ($counter == count($result)) {
                            array_push($aux, $arrayItem);
                        }
                    }

                    $result = $aux;
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/get-rifas-to-transfer", name="get-rifas-to-transfer", methods={"GET"})
     */
    public function getRifasToTransfer(Request $request, PasajeroRepository $pasajeroRepository, TalonRepository $talonRepository)
    {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                //TODO: Talon o Rifa (?)
                $result = $talonRepository->createQueryBuilder('t')
                    ->select('t.Numero, t.Estado, t.SorteoNumero')
                    ->where("t.Pasajero = :pas AND t.Estado = 'Pendiente de Pago'")
                    ->leftJoin('t.Comprador', 'c')
                    ->setParameter('pas', $pasajero)
                    ->orderBy('t.Numero, t.FechaSorteo')
                    ->getQuery()
                    ->getResult();

                if (count($result) > 0) {
                    $aux = array();
                    $arrayItem = array();
                    $auxNumber = false;
                    $firstLoop = true;
                    $counter = 0;
                    foreach ($result as $item) {
                        $counter++;

                        if ($auxNumber != $item['Numero']) {
                            $auxNumber = $item['Numero'];
                            if (!$firstLoop) {
                                array_push($aux, $arrayItem);
                            } else {
                                $firstLoop = false;
                            }
                            $arrayItem = array();
                        }

                        array_push($arrayItem, $item);

                        if ($counter == count($result)) {
                            array_push($aux, $arrayItem);
                        }
                    }

                    $result = $aux;
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/put-rifa-bolsa", name="put-rifa-bolsa", methods={"POST"})
     */
    public function putRifaBolsa(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        HistorialTransferenciasRepository $historialTransferenciasRepository,
        TalonRepository $talonRepository
    ) {
        $authToken = $request->headers->get('Authorization');
        $data = json_decode($request->getContent(), true);

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {
                $rifas = (isset($data['rifas'])) ? $data['rifas'] : null;

                foreach ($rifas as $rifa) {
                    // var_dump($rifa);
                    $talones = $talonRepository->findBy(
                        array(
                            "Pasajero" => $pasajero,
                            "Numero" => $rifa[0]['Numero'],
                            "Estado" => "Pendiente de Pago"
                        )
                    );

                    foreach ($talones as $tal) {
                        $tal->setEstado('Para transferir');
                        $talonRepository->updateTalon($tal);

                        $historial = $historialTransferenciasRepository->saveHistorialTransferencias($pasajero, $tal, "Para transferir");
                    }
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $talones], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/guardar-comentario", name="guardar-comentario", methods={"POST"})
     */
    public function guardarComentario(
        Request $request,
        TalonRepository $talonRepository
    ) {
        $authToken = $request->headers->get('Authorization');
        $data = json_decode($request->getContent(), true);
        $numero = (isset($data['numero'])) ? $data['numero'] : null;
        $comment = (isset($data['comment'])) ? $data['comment'] : null;

        if ($authToken != null) {

            $talon = $talonRepository->findOneBy(
                array(
                    "Numero" => $numero
                )
            );

            if ($comment == '') {
                $comment = null;
            }

            $talon->setComentario($comment);
            $talonRepository->updateTalon($talon);

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $talon], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/quitar-rifa-bolsa", name="quitar-rifa-bolsa", methods={"POST"})
     */
    public function quitarRifaBolsa(Request $request, PasajeroRepository $pasajeroRepository, TalonRepository $talonRepository, HistorialTransferenciasRepository $historialTransferenciasRepository)
    {
        $authToken = $request->headers->get('Authorization');
        $data = json_decode($request->getContent(), true);

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {
                $numeroRifa = (isset($data['numeroRifa'])) ? $data['numeroRifa'] : null;

                $talones = $talonRepository->findBy(
                    array(
                        "Pasajero" => $pasajero,
                        "Numero" => $numeroRifa,
                        "Estado" => "Para transferir"
                    )
                );

                foreach ($talones as $tal) {
                    $tal->setEstado("Pendiente de Pago");

                    $tal = $talonRepository->updateTalon($tal);

                    $historial = $historialTransferenciasRepository->saveHistorialTransferencias($pasajero, $tal, "Quitó de la bolsa");
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $talones], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/transferencia-directa", name="transferencia-directa", methods={"POST"})
     */
    public function transferenciaDirecta(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        TalonRepository $talonRepository,
        HistorialTransferenciasRepository $historialTransferenciasRepository,
        UserRepository $userRepository,
        MailSender $mailSender
    ) {
        $authToken = $request->headers->get('Authorization');
        $data = json_decode($request->getContent(), true);

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                $duenopas = $pasajero;
                $rifas = (isset($data['rifas'])) ? $data['rifas'] : null;
                $mailTransf = (isset($data['mailTransf'])) ? $data['mailTransf'] : null;

                $pasajeroTransf = $userRepository->findOneBy(
                    array(
                        "email" => $mailTransf
                    )
                );

                if ($pasajeroTransf) {
                    $transfItiPas = $pasajeroRepository->findOneBy(
                        array(
                            "Persona" => $pasajeroTransf->getPersona()
                        )
                    );

                    if ($transfItiPas) {
                        $lError = false;
                        $mailArray = array();

                        foreach ($rifas as $rifa) {
                            $talones = $talonRepository->findBy(
                                array(
                                    "Pasajero" => $duenopas,
                                    "Numero" => $rifa[0]['Numero'],
                                    "Estado" => "Pendiente de Pago"
                                )
                            );
                        }

                        if (!$lError) {
                            array_push(
                                $mailArray,
                                array(
                                    'transfName' => $transfItiPas->getPersona()->getNombres() . ' ' . $transfItiPas->getPersona()->getApellidos(),
                                    'transfEmail' => $pasajeroTransf->getEmail(),
                                    'transfPhone' => $transfItiPas->getPersona()->getCelular(),
                                    'rifas' => $rifas
                                )
                            );

                            foreach ($rifas as $rifa) {
                                $talones = $talonRepository->findBy(
                                    array(
                                        "Pasajero" => $duenopas,
                                        "Numero" => $rifa[0]['Numero'],
                                        "Estado" => "Pendiente de Pago"
                                    )
                                );

                                foreach ($talones as $tal) {
                                    $tal->setEstado("Solicitada");
                                    $tal->setSolicitante($transfItiPas);

                                    $tal = $talonRepository->updateTalon($tal);

                                    $historial = $historialTransferenciasRepository->saveHistorialTransferencias($duenopas, $tal, "Transf directa a " . $transfItiPas->getId());
                                }
                            }

                            $responseSolicitante = array(
                                'asunto' => $this->getParameter('businessNameShort') . ' - Transferencias | ' . $duenopas->getPersona()->getNombres() . ' ' . $duenopas->getPersona()->getApellidos() . ' desea transferirte rifas de forma directa.',
                                'fromAddress' => $this->getParameter('fromAddress'),
                                'fromName' => $this->getParameter('businessName'),
                                'to' => $transfItiPas->getPersona()->getUser()->getEmail(),
                                'typeTemplate' => 'transferenciaDirectaMail',
                                'dataEmail' => array(
                                    'transfName' => $transfItiPas->getPersona()->getNombres(),
                                    'duenoName' => $duenopas->getPersona()->getNombres() . ' ' . $duenopas->getPersona()->getApellidos(),
                                    'rifas' => $rifas
                                )
                            );

                            $mailSender->sendMail($responseSolicitante);

                            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $talones], 200);
                        } else {
                            if ($this->getParameter('talonType') == 'multi') {
                                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => "Uno o más talones no están disponibles."], 404);
                            } else {
                                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => "Una o más rifas no están disponibles."], 404);
                            }
                        }
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => "Pasajero a transferir inválido"], 404);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => "Pasajero a transferir inválido"], 200);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/get-rifas-para-entregar", name="get-rifas-para-entregar", methods={"GET"})
     */
    public function getRifasParaEntregar(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        TalonRepository $talonRepository
    ) {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                //TODO: Talon o Rifa (?)
                $result = $talonRepository->createQueryBuilder('t')
                    ->select('t.Numero, t.Estado, per.Nombres, per.Apellidos, ts.id, per.Celular, t.SorteoNumero')
                    ->where("t.Pasajero = :pas AND t.Estado = 'Solicitada'")
                    ->leftJoin('t.Solicitante', 'ts')
                    ->leftJoin('ts.Persona', 'per')
                    ->setParameter('pas', $pasajero)
                    ->orderBy('ts.id, t.Numero, t.FechaSorteo')
                    ->getQuery()
                    ->getResult();

                if (count($result) > 0) {
                    $aux = array();
                    $arrayItem = array();
                    $auxNumber = false;
                    $firstLoop = true;
                    $counter = 0;
                    foreach ($result as $item) {
                        $counter++;
                        if ($auxNumber != $item['Numero']) {
                            $auxNumber = $item['Numero'];
                            if (!$firstLoop) {
                                array_push($aux, $arrayItem);
                            } else {
                                $firstLoop = false;
                            }
                            $arrayItem = array();
                        }

                        array_push($arrayItem, $item);

                        if ($counter == count($result)) {
                            array_push($aux, $arrayItem);
                        }
                    }

                    $firstLoop = true;
                    $complete = array();
                    $counter = 0;
                    $auxItipasId = '';
                    $auxItipasArray = array();
                    foreach ($aux as $rifa) {
                        $counter++;
                        if ($firstLoop) {
                            $firstLoop = false;
                            $auxItipasId = $rifa[0]['id'];
                        }
                        if ($auxItipasId == $rifa[0]['id']) {
                            array_push($auxItipasArray, $rifa);
                        } else {

                            $pasAux = $pasajeroRepository->findOneBy(
                                array(
                                    'id' => $auxItipasArray[0][0]['id']
                                )
                            );


                            array_push(
                                $complete,
                                array(
                                    "dueno" => array(
                                        "duenoPasId" => $auxItipasArray[0][0]['id'],
                                        "duenoName" => $auxItipasArray[0][0]['Nombres'] . ' ' . $auxItipasArray[0][0]['Apellidos'],
                                        "duenoPhone" => $auxItipasArray[0][0]['Celular'],
                                        "duenoEmail" => $pasAux->getPersona()->getUser()->getEmail()
                                    ),
                                    "rifas" => $auxItipasArray
                                )
                            );
                            $auxItipasId = $rifa[0]['id'];
                            $auxItipasArray = array();
                            array_push($auxItipasArray, $rifa);
                        }

                        if ($counter == count($aux)) {
                            $pasAux = $pasajeroRepository->findOneBy(
                                array(
                                    'id' => $rifa[0]['id']
                                )
                            );

                            array_push(
                                $complete,
                                array(
                                    "dueno" => array(
                                        "duenoPasId" => $rifa[0]['id'],
                                        "duenoName" => $rifa[0]['Nombres'] . ' ' . $rifa[0]['Apellidos'],
                                        "duenoPhone" => $rifa[0]['Celular'],
                                        "duenoEmail" => $pasAux->getPersona()->getUser()->getEmail()
                                    ),
                                    "rifas" => $auxItipasArray
                                )
                            );
                        }
                    }

                    $result = $complete;
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/get-rifas-a-recibir", name="get-rifas-a-recibir", methods={"GET"})
     */
    public function getRifasARecibir(Request $request, PasajeroRepository $pasajeroRepository, TalonRepository $talonRepository)
    {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {

                // TODO: Talon o Rifa (?)
                $result = $talonRepository->createQueryBuilder('t')
                    ->select('t.Numero, t.Estado, per.Nombres, per.Apellidos, p.id, per.Celular, t.SorteoNumero')
                    ->where("t.Solicitante = :pas AND t.Estado = 'Solicitada'")
                    ->leftJoin('t.Pasajero', 'p')
                    ->leftJoin('p.Persona', 'per')
                    ->setParameter('pas', $pasajero)
                    ->orderBy('p.id, t.Numero, t.FechaSorteo')
                    ->getQuery()
                    ->getResult();

                if (count($result) > 0) {
                    $aux = array();
                    $auxItipasArray = array();
                    $auxItipasId = '';
                    $arrayItem = array();
                    $auxNumber = false;
                    $firstLoop = true;
                    $counter = 0;
                    foreach ($result as $item) {
                        $counter++;
                        if ($auxNumber != $item['Numero']) {
                            $auxNumber = $item['Numero'];
                            if (!$firstLoop) {
                                array_push($aux, $arrayItem);
                            } else {
                                $firstLoop = false;
                            }
                            $arrayItem = array();
                        }

                        array_push($arrayItem, $item);

                        if ($counter == count($result)) {
                            array_push($aux, $arrayItem);
                        }
                    }

                    $firstLoop = true;
                    $complete = array();
                    $counter = 0;
                    $duenoAux = null;
                    foreach ($aux as $rifa) {
                        $counter++;
                        if ($firstLoop) {
                            $firstLoop = false;
                            $auxItipasId = $rifa[0]['id'];
                        }
                        if ($auxItipasId == $rifa[0]['id']) {

                            $pasAux = $pasajeroRepository->findOneBy(
                                array(
                                    'id' => $rifa[0]['id']
                                )
                            );

                            array_push($auxItipasArray, $rifa);
                            $duenoAux = array(
                                "duenoPasId" => $rifa[0]['id'],
                                "duenoName" => $rifa[0]['Nombres'] . ' ' . $rifa[0]['Apellidos'],
                                "duenoPhone" => $rifa[0]['Celular'],
                                "duenoEmail" => $pasAux->getPersona()->getUser()->getEmail()
                            );
                        } else {
                            array_push(
                                $complete,
                                array(
                                    "dueno" => $duenoAux,
                                    "rifas" => $auxItipasArray
                                )
                            );
                            $auxItipasId = $rifa[0]['id'];
                            $auxItipasArray = array();
                            array_push($auxItipasArray, $rifa);
                        }

                        if ($counter == count($aux)) {

                            $pasAux = $pasajeroRepository->findOneBy(
                                array(
                                    'id' => $rifa[0]['id']
                                )
                            );

                            array_push(
                                $complete,
                                array(
                                    "dueno" => array(
                                        "duenoPasId" => $rifa[0]['id'],
                                        "duenoName" => $rifa[0]['Nombres'] . ' ' . $rifa[0]['Apellidos'],
                                        "duenoPhone" => $rifa[0]['Celular'],
                                        "duenoEmail" => $pasAux->getPersona()->getUser()->getEmail()
                                    ),
                                    "rifas" => $auxItipasArray
                                )
                            );
                        }
                    }

                    $result = $complete;
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/rifa-entregada", name="rifa-entregada", methods={"POST"})
     */
    public function rifaEntregada(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        HistorialTransferenciasRepository $historialTransferenciasRepository,
        TalonRepository $talonRepository,
        MailSender $mailSender
    ) {
        $authToken = $request->headers->get('Authorization');
        $data = json_decode($request->getContent(), true);

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);
            $rifas = (isset($data['rifas'])) ? $data['rifas'] : null;

            if ($pasajero != null) {
                $solicitantePas = $pasajero;

                foreach ($rifas as $rifa) {
                    $talones = $talonRepository->findBy(
                        array(
                            "Solicitante" => $solicitantePas,
                            "Numero" => $rifa[0]['Numero'],
                            "Estado" => "Solicitada"
                        )
                    );

                    //                    if( count($talones) == 5) {
                    $duenoPas = $talones[0]->getPasajero();

                    foreach ($talones as $tal) {
                        $tal->setEstado("Pendiente de Pago");
                        $tal->setSolicitante(null);
                        $tal->setPasajero($solicitantePas);

                        $tal = $talonRepository->updateTalon($tal);

                        $historial = $historialTransferenciasRepository->saveHistorialTransferencias($solicitantePas, $tal, "Recibió");
                    }
                }

                $responseSolicitante = array(

                    'asunto' => $this->getParameter('businessNameShort') . ' - Transferencias |  ' . $solicitantePas->getPersona()->getNombres() . ' ' . $solicitantePas->getPersona()->getApellidos() . ' confirmó la entrega de rifas',
                    'fromAddress' => $this->getParameter('fromAddress'),
                    'fromName' => $this->getParameter('businessName'),
                    'to' => $duenoPas->getPersona()->getUser()->getEmail(),
                    'typeTemplate' => 'rifaEntregadaMail',
                    'dataEmail' => array(
                        'exDueno' => $duenoPas->getPersona()->getNombres(),
                        'actualDueno' => $solicitantePas->getPersona()->getNombres() . ' ' . $solicitantePas->getPersona()->getApellidos(),
                        'actualDuenoName' => $solicitantePas->getPersona()->getNombres()
                    )
                );

                $mailSender->sendMail($responseSolicitante);

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $talones], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/get-bolsa", name="get-bolsa", methods={"GET"})
     */
    public function getBolsa(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        TalonRepository $talonRepository
    ) {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);

            if ($pasajero != null) {
                //TODO: Talon o Rifa (?)
                /*$result = $talonRepository->createQueryBuilder('t')
                    ->select('t.Numero, t.Estado, per.Nombres, per.Apellidos, p.id, t.SorteoNumero')
                    //->where("t.Estado = 'Para transferir'")
                    ->where("t.Pasajero <> :pas AND t.Estado = 'Para transferir'")
                    ->leftJoin('t.Pasajero', 'p')
                    ->leftJoin('p.Persona', 'per')
                    ->setParameter('pas', $pasajero)
                    ->orderBy('t.Numero, t.FechaSorteo')
                    ->getQuery()
                    ->getResult();*/

                $talones = $talonRepository->talonesListBolsa($pasajero);

                // AGRUPO POR RIFA Y NUMERO
                $aux_array_rifas = array();
                $aux_array_sorteos = array();
                foreach ($talones as $tal) {
                    $aux_rifa = array(
                        'id_talon'  => $tal['id'],
                        'id' => $tal['rifa_id'],
                        'Numero' => $tal['Numero'],
                        'Rifa' => $tal['rifa_nombre'],
                        'grupo' => $tal['Numero'] . $tal['rifa_id'],
                        'Sorteos' => $aux_array_sorteos
                    );
                    array_push($aux_array_rifas, $aux_rifa);
                }

                $aux_array_rifas = $this->unique_multidim_array($aux_array_rifas, 'grupo');

                $rifas = $aux_array_rifas;
                $rifas_array = array();
                foreach ($rifas as $rifa) {
                    $aux_array_sorteos = array();
                    foreach ($talones as $tal) {
                        if ($rifa['id'] == $tal['rifa_id'] && $rifa['Numero'] == $tal['Numero']) {
                            $aux_sorteo = array(
                                'id_talon'  => $tal['id'],
                                'id' => $tal['sorteo_id'],
                                'sorteo_numero' => $tal['sorteo_numero'],
                                'fecha' => $tal['fecha_sorteo'],
                                'Estado' => $tal['Estado'],
                                'persona_id' => $tal['PerId'],
                                'pasajero_id' =>  $tal['pasajero_id'],
                                'pasajero_nombres' => $tal['Nombres'],
                                'pasajero_apellidos' => $tal['Apellidos']
                            );
                            array_push($aux_array_sorteos, $aux_sorteo);
                        }
                    }
                    $rifa['Sorteos'] = $aux_array_sorteos;
                    array_push($rifas_array, $rifa);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $rifas_array], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/api/talon/solicitar-rifas", name="solicitar-rifas", methods={"POST"})
     */
    public function solicitarRifas(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        TalonRepository $talonRepository,
        HistorialTransferenciasRepository $historialTransferenciasRepository,
        MailSender $mailSender
    ) {
        $authToken = $request->headers->get('Authorization');

        if ($authToken != null) {

            $pasajero = $pasajeroRepository->getPasajeroByToken($authToken);
            $data = json_decode($request->getContent(), true);

            if ($pasajero != null) {

                $rifas = (isset($data['rifas'])) ? $data['rifas'] : null;
                $solicitante = $pasajero;

                $lError = false;
                $contacts = array();
                $mailArray = array();

                foreach ($rifas as $rifa) {
                    $dueno = $rifa['owner'];

                    $duenoPas = $pasajeroRepository->findOneBy(
                        array(
                            "id" => $dueno
                        )
                    );

                    //array_push($contacts, $duenoPas);
                    array_push(
                        $contacts,
                        array(
                            'pasajeroId' => $duenoPas->getId(),
                            'Nombres' => $duenoPas->getPersona()->getNombres() . ' ' . $duenoPas->getPersona()->getApellidos(),
                            'Email' => $duenoPas->getPersona()->getUser()->getEmail(),
                            'Celular' => $duenoPas->getPersona()->getCelular()

                        )
                    );

                    foreach ($rifa['rifas'] as $item) {
                        $talones = $talonRepository->findBy(
                            array(
                                "Pasajero" => $duenoPas,
                                "Numero" => $item['Numero'],
                                "Estado" => "Para transferir"
                            )
                        );
                    }
                }

                if (!$lError) {
                    foreach ($rifas as $rifa) {
                        $dueno = $rifa['owner'];

                        $duenoPas = $pasajeroRepository->findOneBy(
                            array(
                                "id" => $dueno
                            )
                        );

                        array_push(
                            $mailArray,
                            array(
                                'duenoName' => $duenoPas->getPersona()->getNombres() . ' ' . $duenoPas->getPersona()->getApellidos(),
                                'duenoEmail' => $duenoPas->getPersona()->getUser()->getEmail(),
                                'duenoPhone' => $duenoPas->getPersona()->getCelular(),
                                'rifas' => $rifa['rifas']
                            )
                        );

                        foreach ($rifa['rifas'] as $item) {
                            $talones = $talonRepository->findBy(
                                array(
                                    "Pasajero" => $duenoPas,
                                    "Numero" => $item['Numero'],
                                    "Estado" => "Para transferir"
                                )
                            );

                            foreach ($talones as $tal) {
                                $tal->setEstado("Solicitada");
                                $tal->setSolicitante($solicitante);

                                $tal = $talonRepository->updateTalon($tal);

                                $historial = $historialTransferenciasRepository->saveHistorialTransferencias($solicitante, $tal, "Solicitó");
                            }
                        }
                    }

                    foreach ($rifas as $rifa) {
                        $dueno = $rifa['owner'];

                        $duenoPas = $pasajeroRepository->findOneBy(
                            array(
                                "id" => $dueno
                            )
                        );

                        $responseDueno = array(
                            'asunto' => $this->getParameter('businessNameShort') . ' - Transferencias | ' . $solicitante->getPersona()->getNombres() . ' ' . $solicitante->getPersona()->getApellidos() . ' solicitó rifas tuyas de la bolsa',
                            'fromAddress' => $this->getParameter('fromAddress'),
                            'fromName' => $this->getParameter('businessName'),
                            'to' => $duenoPas->getPersona()->getUser()->getEmail(),
                            'typeTemplate' => 'solicitaDuenoMail',
                            'dataEmail' => array(
                                'solicitanteName' => $solicitante->getPersona()->getNombres() . ' ' . $solicitante->getPersona()->getApellidos(),
                                'solicitanteFirstname' => $solicitante->getPersona()->getNombres(),
                                'solicitantePhone' => $solicitante->getPersona()->getCelular(),
                                'solicitanteEmail' => $solicitante->getPersona()->getUser()->getEmail(),
                                'rifas' => $rifa['rifas'],
                                'duenoName' => $duenoPas->getPersona()->getNombres()
                            )
                        );

                        $mailSender->sendMail($responseDueno);
                    }

                    $responseSolicitante = array(
                        'asunto' => $this->getParameter('businessNameShort') . ' - Transferencias | Rifas solicitadas.',
                        'fromAddress' => $this->getParameter('fromAddress'),
                        'fromName' => $this->getParameter('businessName'),
                        'to' => $solicitante->getPersona()->getUser()->getEmail(),
                        'typeTemplate' => 'solicitaSolicitanteMail',
                        'dataEmail' => array(
                            'solicitanteName' => $solicitante->getPersona()->getNombres(),
                            'rifas' => $rifas,
                            'info' => $mailArray

                        )
                    );

                    $mailSender->sendMail($responseSolicitante);
                } else {
                    if ($this->getParameter('talonType') == 'multi') {
                        return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'Uno o más talones no están disponibles.'], 404);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'Una o más rifas no están disponibles.'], 404);
                    }
                }
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $contacts], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'El pasajero no existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Error de autorización'], 403);
        }
    }

    /**
     * @Route("/talon/validate-link", name="validate-link", methods={"POST"})
     */
    public function getLinkDataAction(
        Request $request,
        TalonRepository $talonRepository,
        PersonaRepository $personaRepository,
        LinkPagoRifaRepository $linkPagoRifaRepository,
        LinkPagoRifaTalonesRepository $linkPagoRifaTalonesRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $token = (isset($data['token'])) ? $data['token'] : null;

        if ($token != '' && $token != null) {
            $linkData = $linkPagoRifaRepository->findOneBy(
                array(
                    "EncryptedLink" => $token,
                    "Estado" => 'Pendiente de Pago'
                )
            );

            if ($linkData) {
                $talones = array();
                $linkTalones = $linkPagoRifaTalonesRepository->findBy(
                    array(
                        "LinkPagoRifa" => $linkData
                    )
                );

                $result = $linkPagoRifaTalonesRepository->createQueryBuilder('lpt')->where("lpt.LinkPagoRifa = :linkData")
                    ->join("lpt.Talon", "t")
                    ->setParameter('linkData', $linkData)
                    ->orderBy('t.Numero, t.FechaSorteo')
                    ->getQuery()
                    ->getResult();

                if (count($result) > 0) {
                    $aux = array();
                    $arrayItem = array();
                    $auxNumber = false;
                    $firstLoop = true;
                    $counter = 0;

                    foreach ($result as $item) {
                        if ($item->getTalon()->getEstado() != 'Pendiente de Pago' || $item->getTalon()->getPasajero() != $linkData->getPasajero()) {
                            if ($this->getParameter('talonType') == 'multi') {
                                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Talones pagos'], 200);
                            } else {
                                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Rifas pagas'], 200);
                            }
                        }
                        $counter++;

                        if ($auxNumber != $item->getTalon()->getNumero()) {
                            $auxNumber = $item->getTalon()->getNumero();
                            if (!$firstLoop) {
                                array_push($aux, $arrayItem);
                            } else {
                                $firstLoop = false;
                            }
                            $arrayItem = array();
                        }

                        array_push(
                            $arrayItem,
                            $talonRepository->responseTalon($item->getTalon())
                        );

                        if ($counter == count($result)) {
                            array_push($aux, $arrayItem);
                        }
                    }

                    $result = $aux;
                }

                if (count($linkTalones)) {
                    foreach ($result as $linkTal) {
                        array_push($talones, $linkTal);
                    }

                    return new JsonResponse(
                        [
                            'status' => 'success',
                            'code' => 200,
                            'message' => '',
                            'data' => array(
                                "linkData" => $linkPagoRifaRepository->responseLinkPagoRifa($linkData),
                                "talones" => $result,
                                "persona" => $personaRepository->responsePersona($linkData->getPasajero()->getPersona())
                            )
                        ],
                        200
                    );
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'No existe'], 404);
                }
            } else {

                $linkData2 = $linkPagoRifaRepository->findOneBy(
                    array(
                        "EncryptedLink" => $token,
                        "Estado" => 'Pago'
                    )
                );

                if ($linkData2) {
                    return new JsonResponse(['status' => 'error', 'code' => 405, 'message' => 'Ya se pago'], 405);
                }


                return new JsonResponse(['status' => 'error', 'code' => 406, 'message' => 'No existe link'], 406);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 403, 'message' => 'Token inválido'], 403);
        }

        return $this->helpers->json($data);
    }

    function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();
        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}
