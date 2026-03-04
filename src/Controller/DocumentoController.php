<?php

namespace App\Controller;

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
use App\Entity\Documento;
use App\Entity\User;

use App\Repository\ViajeRepository;
use App\Repository\UserRepository;
use App\Repository\PersonaRepository;
use App\Repository\PasajeroRepository;
use App\Repository\ItinerarioRepository;
use App\Repository\DocumentoRepository;
use App\Repository\PasajeroDocumentoRepository;


class DocumentoController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/documento', name: 'documento')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ViajeController.php',
        ]);
    }

    /**
     * @Route("admin/get-documentos-list", methods={"GET"}, name="documento_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $documentoRepository = $this->em->getRepository(Documento::class);
        $documentos = $documentoRepository->findAll();

        $data = [];

        foreach ($documentos as $documento) {
            $data[] = [
                'id' => $documento->getId(),
                'numero' => $documento->getNumero(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("api/documento/get-list-by-persona", name="get-list-by-persona", methods={"POST"})
     */
    public function getDocumentosByPersona(
        Request $request,
        DocumentoRepository $documentoRepository,
        PersonaRepository $personaRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['idPersona'])) ? $data['idPersona'] : null;

        $persona = $personaRepository->getPersonaById($id);

        if ($id != null) {

            $documentos = $documentoRepository->findByPersonaDocumento($persona);
            if ($documentos) {

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

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Documentos encontrado', 'data' => $documentos], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La Documentos no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("api/documento/get-list-by-pasajero", name="get-list-by-pasajero", methods={"POST"})
     */
    public function getDocumentosByPasajero(
        Request $request,
        PasajeroDocumentoRepository $pasajeroDocumentoRepository,
        PasajeroRepository $pasajeroRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['idPasajero'])) ? $data['idPasajero'] : null;

        $pasajero = $pasajeroRepository->find($id);

        if ($id != null) {

            //$documentos = $pasajero->getPasajeroDocumentos();
            $documentos = $pasajeroDocumentoRepository->findByPasajeroDocumento($pasajero);
            if ($documentos) {

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

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Documentos encontrado', 'data' => $documentos], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La Documentos no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("api/documento/get-archivos-by-documento", name="get-archivos-by-documento", methods={"POST"})
     */
    public function getArchivosByDocumento(
        Request $request,
        DocumentoRepository $documentoRepository,
        PersonaRepository $personaRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['idDocumento'])) ? $data['idDocumento'] : null;

        $data = [];

        if ($id != null) {

            $documento = $documentoRepository->findOneBy(
                array('id' => $id)
            );
            if ($documento) {
                $archivos = $documento->getArchivos();
                foreach ($archivos as $item) {
                    $data[] = [
                        'id' => $item->getId(),
                        'nombre' => $item->getNombre(),
                        'tipo' => $item->getTipo(),
                        'url' => $item->getUrl(),
                    ];
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Documentos encontrado', 'data' => $data], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La Documentos no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/documento/get-by-id", methods={"POST"}, name="get-viaje-by-id")
     * 
     */
    public function getDocumentoById(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['idViaje'])) ? $data['idViaje'] : null;
        $documentoRepository = $this->em->getRepository(Documento::class);
        $data = [];

        $documento = $documentoRepository->findOneBy(
            array('id' => $id)
        );

        if ($documento != null) {
            $data = [
                'id' => $documento->getId(),
                'numero' => $documento->getNumero(),
            ];

            return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
        } else {

            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El viaje no existe.'], 400);
        }
    }
}
