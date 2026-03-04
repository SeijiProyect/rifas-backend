<?php

namespace App\Controller;

use App\Lib\MailSender;
use DateTimeImmutable;
use App\Entity\Itinerario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Persona;
use App\Entity\Pasajero;
use App\Entity\Universidad;
use App\Entity\Viaje;
use App\Entity\User;

use App\Repository\ViajeRepository;
use App\Repository\UserRepository;
use App\Repository\PersonaRepository;
use App\Repository\PasajeroRepository;
use App\Repository\ItinerarioRepository;
use App\Repository\UniversidadRepository;

class ViajeController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/viaje', name: 'viaje')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ViajeController.php',
        ]);
    }

    /**
     * @Route("/viaje/list", methods={"GET"}, name="punto_interes_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $viajeRepository = $this->em->getRepository(Viaje::class);
        $viajes = $viajeRepository->findAll();

        $data = [];

        foreach ($viajes as $viaje) {
            $data[] = [
                'id' => $viaje->getId(),
                'nombre' => $viaje->getNombre(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }



    /**
     * @Route("api/viaje/get-list-by-persona", name="list-viajes-by-persona", methods={"POST"})
     */
    public function getPersonaDatos(Request $request, ViajeRepository $viajeRepository)
    {

        $data = json_decode($request->getContent(), true);
        $id = (isset($data['idPersona'])) ? $data['idPersona'] : null;

        if ($id != null) {

            $viajes = $viajeRepository->viajesByPersonaNativeQuery($id);
            if ($viajes) {

                /* $res = array(
                    'id' => $persona->getId(),
                    'nombres' => $persona->getNombres(),
                    'apellidos' => $persona->getApellidos(),
                    'fecha_nac' => $persona->getFechaNacimiento(),
                    'direccion' => $persona->getDireccion(),
                    'cedula' => $persona->getCedula(),
                    'celular' => $persona->getCelular(),
                    'sexo' => $persona->getSexo(),
                    'email' => $persona->getUser()->getEmail()
                );*/

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Persona encontrado', 'data' => $viajes], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/viaje/get-by-id", methods={"POST"}, name="get-viaje-by-id")
     * 
     */
    public function getViajeById(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['idViaje'])) ? $data['idViaje'] : null;
        $viajeRepository = $this->em->getRepository(Viaje::class);
        $data = [];

        $viaje = $viajeRepository->findOneBy(
            array('id' => $id)
        );

        if ($viaje != null) {
            $data = [
                'id' => $viaje->getId(),
                'nombre' => $viaje->getNombre(),
            ];

            return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
        } else {

            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El viaje no existe.'], 400);
        }
    }

    /**
     * @Route("/api/viaje/get-token-by-id", methods={"POST"}, name="get-token-viaje-by-id")
     * 
     */
    public function getTokenViajeById(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['idViaje'])) ? $data['idViaje'] : null;
        $viajeRepository = $this->em->getRepository(Viaje::class);
        $data = [];

        $viaje = $viajeRepository->findOneBy(
            array('id' => $id)
        );

        if ($viaje != null) {

            $data = [
                'id' => $viaje->getId(),
                'nombre' => $viaje->getNombre(),
                'token' => $viaje->getToken(),
                'universidad' => $viaje->getViajeMadre()->getTipo()
            ];

            return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
        } else {

            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El viaje no existe.'], 400);
        }
    }

    /**
     * @Route("/api/viaje/inscripcion-viaje", methods={"POST"}, name="inscripcion-viaje")
     * 
     */
    public function inscripcionViaje(
        Request $request,
        UserRepository $userRepository,
        PersonaRepository $personaRepository,
        PasajeroRepository $pasajeroRepository,
        itinerarioRepository $itinerarioRepository,
        UniversidadRepository $universidadRepository,
        MailSender $mailSender
    ) {
        $data = json_decode($request->getContent(), true);
        $primer_nombre = (isset($data['form']['primer_nombre'])) ? $data['form']['primer_nombre'] : null;
        $segundo_nombre = (isset($data['form']['segundo_nombre'])) ? $data['form']['segundo_nombre'] : null;
        $primer_apellido = (isset($data['form']['primer_apellido'])) ? $data['form']['primer_apellido'] : null;
        $segundo_apellido = (isset($data['form']['segundo_apellido'])) ? $data['form']['segundo_apellido'] : null;
        $cedula = (isset($data['form']['cedula'])) ? $data['form']['cedula'] : null;
        $celular = (isset($data['form']['celular'])) ? $data['form']['celular'] : null;
        $direccion = (isset($data['form']['direccion'])) ? $data['form']['direccion'] : null;
        $fechaNac = (isset($data['form']['fechaNac'])) ? $data['form']['fechaNac'] : null;
        $sexo = (isset($data['form']['sexo'])) ? $data['form']['sexo'] : null;
        $email = (isset($data['form']['email'])) ? $data['form']['email'] : null;
        $viaje = (isset($data['form']['viaje'])) ? $data['form']['viaje'] : null;

        $itinerario_id = (isset($data['form']['itinerario'])) ? $data['form']['itinerario'] : null;
        $universidad_id = (isset($data['form']['universidad'])) ? $data['form']['universidad'] : null;
        $acompanante = (isset($data['form']['acompanante'])) ? $data['form']['acompanante'] : null;
        $comida = (isset($data['form']['comida'])) ? $data['form']['comida'] : null;
        $fecha_format = new \DateTime($fechaNac);

        $acomp_nombre_1 = (isset($acompanante['primer_nombre'])) ? $acompanante['primer_nombre'] : '';
        $acomp_nombre_2 = (isset($acompanante['segundo_nombre'])) ? $acompanante['segundo_nombre'] : '';
        $acomp_ape_1 = (isset($acompanante['primer_apellido'])) ? $acompanante['primer_apellido'] : '';
        $acomp_ape_2 = (isset($acompanante['segundo_apellido'])) ? $acompanante['segundo_apellido'] : '';
        $pareja = (isset($acompanante['pareja'])) ? $acompanante['pareja'] : false;
        $titular = (isset($acompanante['titular'])) ? $acompanante['titular'] : false;

        if ($acomp_nombre_1 == '') {
            $acompanante = "Acompañante: No tiene";
        } else {
            $relacionDueno = "";
            if ($pareja) {
                $relacionDueno = " (Es Pareja)";
            }

            if ($titular) {
                $acompanante = "Titular: " . $acomp_nombre_1 . " " . $acomp_nombre_2 . " " . $acomp_ape_1 . " " . $acomp_ape_2 . $relacionDueno;
            } else {
                $acompanante = "Acompañante: " . $acomp_nombre_1 . " " . $acomp_nombre_2 . " " . $acomp_ape_1 . " " . $acomp_ape_2 . $relacionDueno;
            }
        }

        $comentarios =  $acompanante . " / Comida Especial: " . $comida;

        /*$data_response = "RESPUSTA: " . "Primer Nombre: " . $primer_nombre . " segundo Nombre: " . $segundo_nombre .
        " Primer apellido: " . $primer_apellido . " segundo apellido: " . $segundo_apellido . " cedula: " . $cedula .
            " celular: " . $celular . " Direccion: " . $direccion . " Fecha Nac: " . $fechaNac . " Sexo: " . $sexo .
            "Email:  " . $email . " Itinerario: " . $itinerario_id . " universidad : " . $universidad_id;*/
        // $data_response = "OK";

        // return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Prueba Respuesta', 'data' => $comentarios], 200);

        $itinerario = $itinerarioRepository->findOneBy(
            array('id' => $itinerario_id)
        );

        $universidad = $universidadRepository->findOneBy(
            array('id' => $universidad_id)
        );

        $persona = $personaRepository->findOneBy(
            array('Cedula' => $cedula)
        );

        if ($persona != null) {
            $dateAux = date('Y-m-d H:i:s');
            $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);

            $inscripto = false;
            // Actualizo la informacion de la Persona
            $persona
                ->setNombres($primer_nombre . " " . $segundo_nombre)
                ->setApellidos($primer_apellido . " " . $segundo_apellido)
                ->setFechaNacimiento($fecha_format)
                ->setDireccion($direccion)
                ->setCedula($cedula)
                ->setCelular($celular)
                ->setUpdatedAt($date_fecha_actual)
                ->setSexo($sexo);

            $per = $personaRepository->updatePersona($persona);

            $user = $userRepository->findOneBy(
                array('Persona' => $per)
            );

            if ($user != null) {
                //SI la persona tiene diferente email se actualiza
                if ($user->getEmail() != $email) {
                    $user->setEmail($email);
                    $user = $userRepository->update($user);
                }
            }

            // Verifico que el pasajero no tenga el Itinerario Asignado (YA ESTA INSCRIPTO)
            $pasajeros_list = $per->getPasajeros();
            $itinerario_nombre = '';
            foreach ($per->getPasajeros() as $pas) {
                if ($pas->getItinerario()->getViaje()->getId() == $viaje) {
                    $iti_id =  $pas->getItinerario()->getId();
                    $itinerario_nombre = $pas->getItinerario()->getNombre();
                    $inscripto = true;

                    if ($iti_id == $itinerario_id) {
                        return new JsonResponse([
                            'status' => 'error', 'code' => 401, 'message' => 'Ya esta Inscripto a ese Viaje.',
                            'data_1' => $iti_id,
                            'data_2' => $itinerario_nombre,
                            'data_3' => $itinerario_id,
                            'data_4' => $itinerario->getNombre(),
                        ], 401);
                    } else {

                        return new JsonResponse([
                            'status' => 'error', 'code' => 402, 'message' => 'Ya esta Inscripto a ese Viaje Itinerario: ' . $itinerario_nombre,
                            'data_1' => $iti_id,
                            'data_2' => $itinerario_nombre,
                            'data_3' => $itinerario_id,
                            'data_4' => $itinerario->getNombre(),
                            'data_5' => $pas->getId(),
                            'data_6' => $pas->getPersona()->getId(),
                        ], 401);
                    }
                }
            }

            if ($inscripto == false) {

                // Creo el Pasajero y le asigo el Itinerario
                $pasajero = new Pasajero();
                $pasajero
                    ->setComentarios($comentarios)
                    ->setPersona($persona)
                    ->setItinerario($itinerario)
                    ->setUniversidad($universidad);

                $pasajero_response = $pasajeroRepository->save($pasajero);

                if ($pasajero_response != null) {

                    
                    // EMISOR DE INSCRIPCION A VIAJE
                    //$emailInfo = 'seiji42@hotmail.com';
                    $emailInfo = 'info@detoqueytoque.com';
                    $responseMailer = array(
                        'asunto' => $persona->getNombres() .' '. $persona->getApellidos() . ': Nueva inscripción a ' .
                            $itinerario->getViaje()->getNombre() . '-' . $itinerario->getNombre(),
                        'fromAddress' => $this->getParameter('fromAddress'),
                        'fromName' => $this->getParameter('businessName'),
                        'to' => $emailInfo,
                        'typeTemplate' => 'inscripcionViajeEmisorMail',
                        'dataEmail' => array(
                            'idPersona'  => $persona->getId(),
                            'nombrePersona' => $persona->getNombres() . " " . $persona->getApellidos(),
                            'emailPersona' => $email,
                            'itinerario' => $itinerario->getNombre(),
                            'viaje' => $itinerario->getViaje()->getNombre()

                        )
                    );
                    $mailSender->sendMail($responseMailer);

                    // RECEPTOR DE INSCRIPCION A VIAJE
                    $responseMailerReceptor = array(
                        'asunto' => 'TyT - Inscripcion Viaje',
                        'fromAddress' => 'seijitsumura1985@gmail.com',
                        'fromName' => $this->getParameter('businessName'),
                        'to' => $email,
                        'typeTemplate' => 'inscripcionViajeReceptorMail',
                        'dataEmail' => array(
                            'nombrePersona' => $persona->getNombres() . " " . $persona->getApellidos(),
                            'emailPersona' => $email,
                            'itinerario' => $itinerario->getNombre()
                        )
                    );
                    $mailSender->sendMail($responseMailerReceptor);

                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se creo correctamente', 'data' => $pasajero_response->getId()], 200);
                } else {

                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se pudo crear el Pasajero' . $pasajero_response], 400);
                }
            }
        } else {

            // Creo la persona
            $persona = new Persona();
            $persona
                ->setNombres($primer_nombre . " " . $segundo_nombre)
                ->setApellidos($primer_apellido . " " . $segundo_apellido)
                ->setFechaNacimiento($fecha_format)
                ->setDireccion($direccion)
                ->setCedula($cedula)
                ->setCelular($celular)
                ->setSexo($sexo);

            $persona_response = $personaRepository->save($persona);

            if ($persona_response != null) {

                // Creo el usuario con Rol VENDEDOR
                $roles[] = 'ROLE_VENDEDOR';
                $user = new User();
                $user
                    ->setEmail($email)
                    ->setPassword('')
                    ->setRoles($roles)
                    ->setPersona($persona_response);

                $user_response = $userRepository->save($user);

                if ($user_response != null) {

                    // Creo el Pasajero y le asigo el Itinerario
                    $pasajero = new Pasajero();
                    $pasajero
                        ->setComentarios($comentarios)
                        ->setPersona($persona_response)
                        ->setItinerario($itinerario)
                        ->setUniversidad($universidad);

                    $pasajero_response = $pasajeroRepository->save($pasajero);

                    if ($pasajero_response != null) {

                        // EMISOR DE INSCRIPCION A VIAJE
                        //$email = 'seijitsumura1985@gmail.com';
                        //$emailInfo = 'seiji42@hotmail.com';;
                        $emailInfo = 'info@detoqueytoque.com';
                        $responseMailer = array(
                            'asunto' => $persona->getNombres() .' '. $persona->getApellidos() . ': Nueva inscripción a ' .
                                $itinerario->getViaje()->getNombre() . '-' . $itinerario->getNombre(),
                            'fromAddress' => $this->getParameter('fromAddress'),
                            'fromName' => $this->getParameter('businessName'),
                            'to' => $emailInfo,
                            'typeTemplate' => 'inscripcionViajeEmisorMail',
                            'dataEmail' => array(
                                'idPersona'  => $persona_response->getId(),
                                'nombrePersona' => $persona->getNombres() . " " . $persona->getApellidos(),
                                'emailPersona' => $email,
                                'itinerario' => $itinerario->getNombre(),
                                'viaje' => $itinerario->getViaje()->getNombre()

                            )
                        );
                        $mailSender->sendMail($responseMailer);

                        // RECEPTOR DE INSCRIPCION A VIAJE
                        $responseMailerReceptor = array(
                            'asunto' => 'TyT - Inscripcion Viaje',
                            'fromAddress' => 'seijitsumura1985@gmail.com',
                            'fromName' => $this->getParameter('businessName'),
                            'to' => $email,
                            'typeTemplate' => 'inscripcionViajeReceptorMail',
                            'dataEmail' => array(
                                'nombrePersona' => $persona->getNombres() . " " . $persona->getApellidos(),
                                'emailPersona' => $email,
                                'itinerario' => $itinerario->getNombre()
                            )
                        );

                        $mailSender->sendMail($responseMailerReceptor);

                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se creo correctamente', 'data' => $pasajero_response->getId()], 200);
                    } else {

                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se pudo crear el Pasajero' . $pasajero_response], 400);
                    }
                } else {

                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se pudo crear el Usuario' . $user_response], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se pudo crear la persona' . $persona_response], 400);
            }
        }
    }


    /**
     * @Route("/api/viaje/add-token-viaje", methods={"POST"}, name="add-token-viaje")
     * 
     */
    public function guardarTokenViaje(Request $request, ViajeRepository $viajeRepository)
    {
        $data = json_decode($request->getContent(), true);
        $idViaje = (isset($data['viaje']['id'])) ? $data['viaje']['id'] : null;
        $token = (isset($data['viaje']['token'])) ? $data['viaje']['token'] : null;

        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);

        $viaje = $viajeRepository->findOneBy(
            array('id' => $idViaje)
        );
        // Actualizo la informacion del viaje
        $viaje
            ->setToken($token)
            ->setUpdatedAt($date_fecha_actual);

        $viaje = $viajeRepository->update($viaje);

        if ($viaje != null) {

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se actualizo correctamente', 'data' => $viaje], 200);
        } else {

            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se pudo actualizar viaje' . $viaje], 400);
        }
    }
}
