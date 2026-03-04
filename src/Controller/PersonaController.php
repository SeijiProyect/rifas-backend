<?php

namespace App\Controller;

use App\Entity\Archivo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\JwtAuthenticator;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Persona;
use App\Entity\FotoPersona;
use App\Entity\Documento;
use App\Entity\PasajeroDocumento;
use App\Entity\TipoDocumento;
use App\Repository\ArchivoRepository;
use App\Repository\DocumentoRepository;
use App\Repository\PasajeroRepository;
use App\Repository\PersonaRepository;
use App\Repository\UserRepository;
use App\Repository\FotoPersonaRepository;
use App\Repository\PaisRepository;
use App\Repository\PasajeroDocumentoRepository;
use App\Repository\TipoDocumentoRepository;

class PersonaController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/persona', name: 'persona')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PersonaController.php',
        ]);
    }


    /**
     * @Route("api/persona/editar", name="edit-persona", methods={"POST"})
     */
    public function editPersona(Request $request, PersonaRepository $personaRepository)
    {

        $data = json_decode($request->getContent(), true);
        $id = (isset($data['persona']['id'])) ? $data['persona']['id'] : null;
        $nombres = (isset($data['persona']['nombres'])) ? $data['persona']['nombres'] : null;
        $apellidos = (isset($data['persona']['apellidos'])) ? $data['persona']['apellidos'] : null;
        $fecha_nac = (isset($data['persona']['fecha_nac'])) ? $data['persona']['fecha_nac'] : null;
        $direccion = (isset($data['persona']['direccion'])) ? $data['persona']['direccion'] : null;
        $cedula = (isset($data['persona']['cedula'])) ? $data['persona']['cedula'] : null;
        $celular = (isset($data['persona']['celular'])) ? $data['persona']['celular'] : null;
        $sexo = (isset($data['persona']['sexo'])) ? $data['persona']['sexo'] : null;
        $email = (isset($data['persona']['email'])) ? $data['persona']['email'] : null;

        $fecha_format = new \DateTime($fecha_nac);

        if ($id != null) {
            $persona = $personaRepository->getPersonaById($id);
            if ($persona) {
                /*$res = array(
                'id' => $id,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'fecha_nac' => $fecha_format,
                'direccion' => $direccion,
                'cedula' => $cedula,
                'celular' => $celular,
                'sexo' => $sexo,
                'email' => $email
                );*/
                //ACTUALIZO EL EMAIL SI FUE MODIFICADO DESDE EL FRONT
                /*if ($persona->getUser()->getEmail() != $email) {

                }*/

                $persona->setNombres($nombres);
                $persona->setApellidos($apellidos);
                $persona->setCedula($cedula);
                $persona->setCelular($celular);
                $persona->setDireccion($direccion);
                $persona->setFechaNacimiento($fecha_format);
                $persona->setSexo($sexo);

                $res = $personaRepository->updatePersona($persona);

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se Actualizo correctamente', 'data' => $res], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/persona/get-datos", name="get-persona-datos", methods={"POST"})
     */
    public function getPersonaDatos(Request $request, PersonaRepository $personaRepository, FotoPersonaRepository $fotoPersonaRepository)
    {

        $data = json_decode($request->getContent(), true);
        $id = (isset($data['id'])) ? $data['id'] : null;
        //$id = $request->get('persona_id');

        if ($id != null) {

            $persona = $personaRepository->getPersonaById($id);
            if ($persona) {

                // Obtener la ultima foto
                $foto = $fotoPersonaRepository->findByPersonaUltimaFoto($persona);

                $res = array(
                    'id' => $persona->getId(),
                    'nombres' => $persona->getNombres(),
                    'apellidos' => $persona->getApellidos(),
                    'fecha_nac' => $persona->getFechaNacimiento(),
                    'direccion' => $persona->getDireccion(),
                    'cedula' => $persona->getCedula(),
                    'celular' => $persona->getCelular(),
                    'sexo' => $persona->getSexo(),
                    'email' => $persona->getUser()->getEmail(),
                    'foto' => $foto
                );

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Persona encontrado', 'data' => $res], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/persona/get_datos", name="getPersonaDatosApp", methods={"GET"})
     */
    public function getPersonaDatosApp(Request $request, JwtAuthenticator $jwtAutheticator)
    {
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                $personaRepository = $this->em->getRepository(Persona::class);
                $persona = $personaRepository->getPersonaById($id);
                if ($persona) {
                    // Obtener la ultima foto
                    $fotoPersonaRepository = $this->em->getRepository(FotoPersona::class);
                    $foto = $fotoPersonaRepository->findByPersonaUltimaFoto($persona);

                    // Obtener el documento
                    $documentoRepository = $this->em->getRepository(Documento::class);
                    $documento = $documentoRepository->findByPersonaDocumento($persona);

                    $res = array(
                        'id' => $persona->getId(),
                        'nombres' => $persona->getNombres(),
                        'apellidos' => $persona->getApellidos(),
                        'fecha_nac' => $persona->getFechaNacimiento(),
                        'direccion' => $persona->getDireccion(),
                        'cedula' => $persona->getCedula(),
                        'celular' => $persona->getCelular(),
                        'sexo' => $persona->getSexo(),
                        'email' => $persona->getUser()->getEmail(),
                        'documento' => $documento,
                        'foto' => $foto
                    );

                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Persona encontrado', 'data' => $res], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Ha ocurrido un error de autenticación.'], 400);
        }
    }

    /**
     * @Route("/persona/get_documentos", name="getPersonaDocumentos", methods={"GET"})
     */
    public function getPersonaDocumentos(Request $request, JwtAuthenticator $jwtAutheticator)
    {
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                $personaRepository = $this->em->getRepository(Persona::class);
                $persona = $personaRepository->getPersonaById($id);
                if ($persona) {
                    // Obtener el documento
                    $documentoRepository = $this->em->getRepository(Documento::class);
                    $documentos = $documentoRepository->findAll();

                    $data = [];

                    foreach ($documentos as $documento) {
                        $data[] = [
                            'id' => $documento->getId(),
                            'numero' => $documento->getNumero(),
                            'tipo' => $documento->getTipoDocumento()->getNombre(),
                            'pais' => $documento->getPais()->getNombre(),
                            'serie' => $documento->getSerie(),
                            'fecha_expedicion' => $documento->getFechaExpedicion(),
                            'fecha_vencimiento' => $documento->getFechaVencimiento(),
                            'imagen' => $documento->getImagenUrl(),
                        ];
                    }

                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Documentos encontrados', 'data' => $data], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La persona no existe.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Ha ocurrido un error de autenticación.'], 400);
        }
    }

    /**
     * @Route("/persona/get_list", methods={"GET"}, name="persona_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $personaRepository = $this->em->getRepository(Persona::class);
        $personas = $personaRepository->findAll();

        $data = [];

        foreach ($personas as $persona) {
            $data[] = [
                'id' => $persona->getId(),
                'nombre' =>  $persona->getNombres(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/persona/get-personas-list", name="persona-get-personas-list", methods={"POST"})
     */
    public function getPersonasList(Request $request, PasajeroRepository $pasajeroRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $personasTotal = $pasajeroRepository->findAll();

        $personas = $pasajeroRepository->createQueryBuilder('pas')
            ->select(
                '
                pas.id as PasId,
                per.Nombres,
                per.Apellidos,
                per.id as PerId,
                per.Cedula,
                per.Celular,

                u.Nombre as UniversidadNombre,

                i.Nombre as ItinerarioNombre,

                v.Nombre as ViajeNombre,

                pas.Estado as PasajeroEstado,
                pas.Comentarios as PasajeroComentarios
                '
            )
            ->join('pas.Persona', 'per')
            ->leftJoin('pas.Universidad', 'u')
            ->leftJoin('pas.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->groupBy('per.id')
            ->orderBy('per.Apellidos');

        $personasResult = $personas->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $auxPersonas = array();

        foreach ($personasResult as $per) {
            $user = $userRepository->findOneBy(['Persona' => $per['PerId']]);
            if ($user == null) {
                $per['email'] = '';
            } else {
                $per['email'] = $user->getEmail();
            }
            array_push($auxPersonas, $per);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['personas' => $auxPersonas, 'totalPersonas' => count($personasTotal)]], 200);
    }

    /**
     * @Route("/persona/get-personas-filter", name="persona-get-personas-filter", methods={"POST"})
     */
    public function getPersonasFilter(Request $request, PasajeroRepository $pasajeroRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $sexo = (isset($data['sexo'])) ? $data['sexo'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $personasTotal = $pasajeroRepository->findAll();

        $personas = $pasajeroRepository->createQueryBuilder('pas')
            ->select(
                '
                pas.id as PasId,
                per.Nombres,
                per.Apellidos,
                per.id as PerId,
                per.Cedula,
                per.Celular,

                u.Nombre as UniversidadNombre,

                i.Nombre as ItinerarioNombre,

                v.Nombre as ViajeNombre,

                pas.Estado as PasajeroEstado,
                pas.Comentarios as PasajeroComentarios
                '
            )
            ->join('pas.Persona', 'per')
            ->leftJoin('pas.Universidad', 'u')
            ->leftJoin('pas.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->groupBy('per.id')
            ->orderBy('per.Apellidos');

        if ($sexo != 'todos') {
            $personas->andWhere("per.Sexo = :sexo")
                ->setParameter('sexo', $sexo);
        }

        if ($desde != 0) {
            $personas->andWhere("per.FechaNacimiento >= :desde")
                ->setParameter('desde', $desde);
        }

        if ($hasta != 0) {
            $personas->andWhere("per.FechaNacimiento <= :hasta")
                ->setParameter('desde', $hasta);
        }

        $personasResult = $personas->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $auxPersonas = array();

        foreach ($personasResult as $per) {
            $user = $userRepository->findOneBy(['Persona' => $per['PerId']]);
            if ($user == null) {
                $per['email'] = '';
            } else {
                $per['email'] = $user->getEmail();
            }
            array_push($auxPersonas, $per);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['personas' => $auxPersonas, 'totalPersonas' => count($personasTotal)]], 200);
    }

    /**
     * @Route("/persona/get-personas-by-sexo", name="persona-get-personas-sexo", methods={"POST"})
     */
    public function getPersonasSexo(Request $request, PersonaRepository $personaRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $sexo = (isset($data['sexo'])) ? $data['sexo'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $personasTotal = $personaRepository->findAll();

        $personasTotalResult = $personaRepository->findBySexo($sexo, 0, count($personasTotal));

        $personasResult = $personaRepository->findBySexo($sexo, $offset, $limit);

        $auxPersonas = array();

        foreach ($personasResult as $per) {
            $user = $userRepository->findOneBy(['Persona' => $per['PerId']]);
            if ($user == null) {
                $per['email'] = '';
            } else {
                $per['email'] = $user->getEmail();
            }
            array_push($auxPersonas, $per);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['personas' => $auxPersonas, 'totalPersonas' => count($personasTotalResult)]], 200);
    }

    /**
     * @Route("/persona/get-personas-by-fechas", name="persona-get-personas-fechas", methods={"POST"})
     */
    public function getPersonasFechas(Request $request, PersonaRepository $personaRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $sexo = (isset($data['sexo'])) ? $data['sexo'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $personasTotal = $personaRepository->findAll();

        $personasTotalResult = $personaRepository->findByFechas($desde, $hasta, 0, count($personasTotal));

        $personasResult = $personaRepository->findByFechas($desde, $hasta, $offset, $limit);

        $auxPersonas = array();

        foreach ($personasResult as $per) {
            $user = $userRepository->findOneBy(['Persona' => $per['PerId']]);
            if ($user == null) {
                $per['email'] = '';
            } else {
                $per['email'] = $user->getEmail();
            }
            array_push($auxPersonas, $per);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['personas' => $auxPersonas, 'totalPersonas' => count($personasTotalResult)]], 200);
    }

    /**
     * @Route("/persona/get-personas-by-fechas-sexo", name="persona-get-personas-fechas-sexo", methods={"POST"})
     */
    public function getPersonasFechasSexo(Request $request, PersonaRepository $personaRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $sexo = (isset($data['sexo'])) ? $data['sexo'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $personasTotal = $personaRepository->findAll();

        $personasTotalResult = $personaRepository->findByFechasSexo($desde, $hasta, $sexo, 0, count($personasTotal));

        $personasResult = $personaRepository->findByFechasSexo($desde, $hasta, $sexo, $offset, $limit);

        $auxPersonas = array();

        foreach ($personasResult as $per) {
            $user = $userRepository->findOneBy(['Persona' => $per['PerId']]);
            if ($user == null) {
                $per['email'] = '';
            } else {
                $per['email'] = $user->getEmail();
            }
            array_push($auxPersonas, $per);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['personas' => $auxPersonas, 'totalPersonas' => count($personasTotalResult)]], 200);
    }

    /**
     * @Route("/persona/get-personas-by-termino", name="persona-get-personas-by-termino", methods={"POST"})
     */
    public function getPersonasByTermino(Request $request, PersonaRepository $personaRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;
        $personasTotal = $personaRepository->findAll();

        if (strpos($termino, '@')) {
            $users = $userRepository->createQueryBuilder('u')
                ->select('p.id')
                ->leftJoin('u.Persona', 'p')
                ->andWhere('u.email LIKE :termUser')
                ->setParameter('termUser', '%' . $termino . '%')
                ->distinct()
                ->getQuery()
                ->getResult();

            $longitud = count($users);
            if (count($users) > 0) {
                $personasResult = $personaRepository->findByEmail($users, $offset, $limit);
            }
        } else {
            $personasResult = $personaRepository->findByTermino($termino, $offset, $limit);
        }

        $auxPersonas = array();

        foreach ($personasResult as $per) {
            $user = $userRepository->findOneBy(['Persona' => $per['PerId']]);
            if ($user == null) {
                $per['email'] = '';
            } else {
                $per['email'] = $user->getEmail();
            }
            array_push($auxPersonas, $per);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['personas' => $auxPersonas, 'totalPersonas' => count($personasTotal)]], 200);
    }


    /**
     * @Route("api/persona/guardar-foto", name="persona-guardar-foto", methods={"POST"})
     */
    public function guardarFotoPersona(
        Request $request,
        PersonaRepository $personaRepository,
        FotoPersonaRepository $fotoPersonaRepository
    ) {
        $dir_assets = "";
        $padre = dirname(__DIR__);
        $dir_assets = str_replace('src', 'assets', $padre);

        $postdata = file_get_contents("php://input");

        if (!empty($postdata)) {
            $request = json_decode($postdata);
            $idPersona = $request->idPersona;

            $persona = $personaRepository->find((int) $idPersona);

            $dir = $dir_assets . "/imgs/persona";
            // $folderPath = $padre . "/upload/";
            $micarpeta = $dir . "/" . $idPersona;
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
            $image_parts = explode(";base64,", $request->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_base64 = base64_decode($image_parts[1]);

            $file = $folderPath . uniqid() . '.png';
            $nombre_archivo_aux = explode("foto/", $file);
            $nombre_archivo = $nombre_archivo_aux[1];
            if (file_put_contents($file, $image_base64)) {
                //Guardar Foto en BASE DE DATOS
                $foto = $fotoPersonaRepository->saveFoto($persona, $nombre_archivo, $file);
                $response[] = array('sts' => true, 'msg' => 'Successfully uploaded', 'datos' => $foto);
            }
            //echo json_encode($response);
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $response, 200]);
        }
        //return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $response, 200]);
    }

    /**
     * @Route("api/persona/guardar-documento", name="persona-guardar-documento", methods={"POST"})
     */
    public function guardarDocumentoPersona(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        FotoPersonaRepository $fotoPersonaRepository,
        DocumentoRepository $documentoRepository,
        ArchivoRepository $archivoRepository,
        PasajeroDocumentoRepository $pasajeroDocumentoRepository,
        PaisRepository $paisRepository,
        TipoDocumentoRepository $tipoDocumentoRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $doc_nombre = (isset($data['documento']['name'])) ? $data['documento']['name'] : null;
        $doc_id_tipo = (isset($data['documento']['id_tipo'])) ? $data['documento']['id_tipo'] : null;
        $doc_tipo = (isset($data['documento']['tipo'])) ? $data['documento']['tipo'] : null;
        $doc_numero = (isset($data['documento']['numero'])) ? $data['documento']['numero'] : null;
        $doc_fechaexp = (isset($data['documento']['fechaExp'])) ? $data['documento']['fechaExp'] : null;
        $doc_fechaven = (isset($data['documento']['fechaVen'])) ? $data['documento']['fechaVen'] : null;

        $doc_nacionalidad_id = (isset($data['documento']['nacionalidad'])) ? $data['documento']['nacionalidad'] : null;
        //$doc_nacionalidad_id = 1; //Uruguaya
        $archivo = (isset($data['documento']['fileUrl'])) ? $data['documento']['fileUrl'] : null;
        $doc_nacionalidad = $paisRepository->find($doc_nacionalidad_id);

        $tipoDocumento = $tipoDocumentoRepository->find($doc_id_tipo);

        $fechaexp_format = new \DateTime($doc_fechaexp);
        $fechaven_format = new \DateTime($doc_fechaven);

        /*  $data_response = "RESPUSTA: " . "nombre: " . $doc_nombre . " tipo: " . $doc_tipo .
        " numero: " . $doc_numero . " fecha Exp: " . $doc_fechaexp . " fecha Venc: " . $doc_fechaven .
            " nacionalidad: " . $doc_nacionalidad;
        // $data_response = "OK";

     return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Prueba Respuesta', 'data' => $data_response], 200);
*/
        $dir_assets = "";
        $padre = dirname(__DIR__);
        $dir_assets = str_replace('src', 'assets', $padre);

        $postdata = file_get_contents("php://input");

        if (!empty($postdata)) {
            $request = json_decode($postdata);
            $idPasajero = $request->idPasajero;

            $pasajero = $pasajeroRepository->find((int) $idPasajero);
            $persona = $pasajero->getPersona();
            $idPersona = $pasajero->getPersona()->getId();

            $dir = $dir_assets . "/imgs/persona";
            // $folderPath = $padre . "/upload/";
            $micarpeta = $dir . "/" . $idPersona;
            $folderPathFoto = $micarpeta . "/foto/";
            $folderPathDocumento = $micarpeta . "/documento/" . $doc_tipo . "/";
            // si no existe la carpeta con el idPersona se crea
            if (!file_exists($micarpeta)) {
                //crea el directorio
                mkdir($micarpeta, 0777, true);
                //crea sub-directorio foto y documento
                $dir_foto = $micarpeta . "/foto";
                $dir_documento = $micarpeta . "/documento";
                $dir_doc_tipo = $dir_documento . "/" . $doc_tipo;
                mkdir($dir_foto, 0777, true);
                mkdir($dir_documento, 0777, true);
                //creo directorio con el tipo de documento (CI, PASAPORTE, FIEBRE AMARILLA, etc)
                mkdir($dir_doc_tipo, 0777, true);
            }

            // si no existe la carpeta con el tipo documento se crea
            if (!file_exists($folderPathDocumento)) {
                $dir_documento = $micarpeta . "/documento";
                $dir_doc_tipo = $dir_documento . "/" . $doc_tipo;
                mkdir($dir_doc_tipo, 0777, true);
            }

            // GUARDO LA IMAGEN EN SERVIDOR
            $image_parts = explode(";base64,", $archivo);
            $image_type_aux = explode("/", $image_parts[0]); // Type file
            $image_extencion = "." . $image_type_aux[1]; // jpeg, png, pdf, etc
            // $image_extencion = '.png';
            /* if ($image_parts[0]) {
                // SI el archivo es de tipo PDF
                if (strpos($image_parts[0], 'pdf') !== false) {
                    $image_extencion = "." . $image_type_aux[1]; // jpeg, png, pdf, etc
                }
            }*/
            $image_base64 = base64_decode($image_parts[1]);

            if ($doc_tipo == 'Foto_Carnet') {
                // GURADO LA FOTO CARNET EN FOTO DE PERSONA
                $file = $folderPathFoto . uniqid() . $image_extencion;
                $nombre_archivo_aux = explode("foto/", $file);
                $nombre_archivo = $nombre_archivo_aux[1];
                if (file_put_contents($file, $image_base64)) {
                    //Guardar Foto en BASE DE DATOS
                    $foto = $fotoPersonaRepository->saveFoto($persona, $nombre_archivo, $file);
                    //$foto = "Se comento guardar foto";
                    $response[] = array('sts' => true, 'msg' => 'Successfully uploaded', 'datos' => $foto);
                    //return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $response, 200]);
                }
                // GUARDO FOTO CARNET
                $file_2 = $folderPathDocumento . uniqid() . $image_extencion;
                $nombre_archivo_aux = explode($doc_tipo . "/", $file_2);
                $nombre_archivo = $nombre_archivo_aux[1];
                if (file_put_contents($file_2, $image_base64)) {
                    //Guardar Documento en BASE DE DATOS
                    $documento = $documentoRepository->save(
                        $tipoDocumento,
                        $doc_nacionalidad,
                        $persona,
                        $doc_numero,
                        $fechaexp_format,
                        $fechaven_format
                    );

                    //GUARDO EL ARCHIVO
                    $archivo = $archivoRepository->save($documento, $nombre_archivo, $image_extencion, $file_2);
                    //GUARDAR PASAJERO DOCUMENTO
                    $pas_documento = $pasajeroDocumentoRepository->save($documento, $pasajero);
                    //$pas_documento = 'Se comento guadar foto';
                    $response[] = array('sts' => true, 'msg' => 'Successfully uploaded', 'datos' => $pas_documento);
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $response, 200]);
                } else {
                    //No se pudo codificar la IMAGEN A BASE64
                    //Guardar Documento en BASE DE DATOS
                    $documento = $documentoRepository->save(
                        $tipoDocumento,
                        $doc_nacionalidad,
                        $persona,
                        $doc_numero,
                        $fechaexp_format,
                        $fechaven_format
                    );
                    //GUARDO EL ARCHIVO
                    $archivo = $archivoRepository->save($documento, 'No se pudo cargar la imagen', 's/n', 's/n');
                    //GUARDAR PASAJERO DOCUMENTO
                    $pas_documento = $pasajeroDocumentoRepository->save($documento, $pasajero);
                    return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'No se pudo codificar el documento', 'data' => $response, 401]);
                }
            } else {
                // GUARDO DOCUMENTO
                $file = $folderPathDocumento . uniqid() . $image_extencion;
                $nombre_archivo_aux = explode($doc_tipo . "/", $file);
                $nombre_archivo = $nombre_archivo_aux[1];
                if (file_put_contents($file, $image_base64)) {
                    //Guardar Documento en BASE DE DATOS
                    $documento = $documentoRepository->save(
                        $tipoDocumento,
                        $doc_nacionalidad,
                        $persona,
                        $doc_numero,
                        $fechaexp_format,
                        $fechaven_format
                    );
                    //GUARDO EL ARCHIVO
                    $archivo = $archivoRepository->save($documento, $nombre_archivo, $image_extencion, $file);
                    //GUARDAR PASAJERO DOCUMENTO
                    $pas_documento = $pasajeroDocumentoRepository->save($documento, $pasajero);
                    $response[] = array('sts' => true, 'msg' => 'Successfully uploaded', 'datos' => $pas_documento);
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $response, 200]);
                } else {
                    //No se pudo codificar la IMAGEN A BASE64
                    $documento = $documentoRepository->save(
                        $tipoDocumento,
                        $doc_nacionalidad,
                        $persona,
                        $doc_numero,
                        $fechaexp_format,
                        $fechaven_format
                    );
                    //GUARDO EL ARCHIVO
                    $archivo = $archivoRepository->save($documento, 'No se pudo cargar la imagen', 's/n', 's/n');
                    //GUARDAR PASAJERO DOCUMENTO
                    $pas_documento = $pasajeroDocumentoRepository->save($documento, $pasajero);
                    return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'No se pudo codificar el documento', 'data' => $pas_documento, 200]);
                }
            }
        }
        //return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $response, 200]);
    }

    /**
     * @Route("api/persona/guardar-documento-roto", name="persona-guardar-documento-roto", methods={"POST"})
     */
    public function guardarDocumentoRoto(
        Request $request,
        PasajeroRepository $pasajeroRepository,
        FotoPersonaRepository $fotoPersonaRepository,
        DocumentoRepository $documentoRepository,
        ArchivoRepository $archivoRepository,
        PasajeroDocumentoRepository $pasajeroDocumentoRepository,
        PaisRepository $paisRepository,
        TipoDocumentoRepository $tipoDocumentoRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $doc_nombre = (isset($data['documento']['name'])) ? $data['documento']['name'] : null;
        $doc_id_tipo = (isset($data['documento']['id_tipo'])) ? $data['documento']['id_tipo'] : null;
        $doc_tipo = (isset($data['documento']['tipo'])) ? $data['documento']['tipo'] : null;
        $doc_numero = (isset($data['documento']['numero'])) ? $data['documento']['numero'] : null;
        $doc_fechaexp = (isset($data['documento']['fechaExp'])) ? $data['documento']['fechaExp'] : null;
        $doc_fechaven = (isset($data['documento']['fechaVen'])) ? $data['documento']['fechaVen'] : null;

        $doc_nacionalidad_id = (isset($data['documento']['nacionalidad'])) ? $data['documento']['nacionalidad'] : null;
        $doc_nacionalidad = $paisRepository->find($doc_nacionalidad_id);

        $tipoDocumento = $tipoDocumentoRepository->find($doc_id_tipo);

        $fechaexp_format = new \DateTime($doc_fechaexp);
        $fechaven_format = new \DateTime($doc_fechaven);

        $dir_assets = "";
        $padre = dirname(__DIR__);
        $dir_assets = str_replace('src', 'assets', $padre);

        $postdata = file_get_contents("php://input");

        if (!empty($postdata)) {
            $request = json_decode($postdata);
            $idPasajero = $request->idPasajero;
            
            $pasajero = $pasajeroRepository->find((int) $idPasajero);
            $persona = $pasajero->getPersona();
            $dir = $dir_assets . "/imgs/image-rota.png";
            $file = $dir;
            $nombre_archivo = 'image-rota';
            $image_extencion = 'png';

            if ($doc_tipo == 'Foto_Carnet') {
                // GURADO LA FOTO CARNET EN FOTO DE PERSONA
                //Guardar Foto en BASE DE DATOS
                $foto = $fotoPersonaRepository->saveFoto($persona, $nombre_archivo . "." . $image_extencion, $file);
                //Guardar Documento en BASE DE DATOS
                $documento = $documentoRepository->save(
                    $tipoDocumento,
                    $doc_nacionalidad,
                    $persona,
                    $doc_numero,
                    $fechaexp_format,
                    $fechaven_format
                );

                //GUARDO EL ARCHIVO
                $archivo = $archivoRepository->save($documento, $nombre_archivo, $image_extencion, $file);
                //GUARDAR PASAJERO DOCUMENTO
                $pas_documento = $pasajeroDocumentoRepository->save($documento, $pasajero);
                $response[] = array('sts' => true, 'msg' => 'Successfully uploaded', 'datos' => $pas_documento);
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $response, 200]);
            } else {
                //Guardar Documento en BASE DE DATOS
                $documento = $documentoRepository->save(
                    $tipoDocumento,
                    $doc_nacionalidad,
                    $persona,
                    $doc_numero,
                    $fechaexp_format,
                    $fechaven_format
                );
                //GUARDO EL ARCHIVO
                $archivo = $archivoRepository->save($documento, $nombre_archivo, $image_extencion, $file);
                //GUARDAR PASAJERO DOCUMENTO
                $pas_documento = $pasajeroDocumentoRepository->save($documento, $pasajero);
                $response[] = array('sts' => true, 'msg' => 'Successfully uploaded', 'datos' => $pas_documento);
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $response, 200]);
            }
        }
    }
}
