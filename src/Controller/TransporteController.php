<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\JwtAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ciudad;
use App\Entity\Persona;
use App\Entity\Proveedor;
use App\Entity\Transporte;
use App\Entity\Servicio;
use App\Entity\TransporteTipo;
use App\Entity\Trayecto;
use App\Repository\TransporteRepository;
use DateTimeImmutable;
use Symfony\Component\Mailer\Transport;

class TransporteController extends AbstractController
{

    private $transporteRepository;
    private $em;

    public function __construct(TransporteRepository $transporteRepository, EntityManagerInterface $em)
    {
        $this->transporteRepository = $transporteRepository;
        $this->em = $em;
    }

    /**
     * @Route("/transporte/get-transportes", name="get-transportes", methods={"GET"})
     */
    public function getTransportesList()
    {
        $transporteRepository = $this->em->getRepository(Transporte::class);
        $transportes = $transporteRepository->findAll();

        $data = [];

        foreach ($transportes as $transporte) {
            $data[] = [
                'id' => $transporte->getId(),
                'nombre' =>  $transporte->getTrayecto()->getCiudadInicio()->getNombre() . ' - ' . $transporte->getTrayecto()->getCiudadFin()->getNombre(),
                'tipo' =>  $transporte->getTransporteTipo()->getNombre(),
                'fecha_inicio' => ($transporte->getFechaInicio() !== null) ? $transporte->getFechaInicio()->format('Y-m-d H:i:s') : null,
                'fecha_fin' => ($transporte->getFechaFin() !== null) ? $transporte->getFechaFin()->format('Y-m-d H:i:s') : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $data], 200);
    }

    /**
     * @Route("/transporte/get_by_id/{transporteId}", methods={"GET"}, name="transporte_getTransporteById")
     * @param int $transporteId
     * @return JsonResponse
     */
    public function getTransporteById(int $transporteId, Request $request, JwtAuthenticator $jwtAutheticator)
    {
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                $personaRepository = $this->em->getRepository(Persona::class);
                $persona = $personaRepository->getPersonaById($id);
                if ($persona) {
                    $transporteRepository = $this->em->getRepository(Transporte::class);
                    $transporte = $transporteRepository->find($transporteId);

                    if (!$transporte) {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el transporte'], 400);
                    }

                    $trayecto = $transporte->getTrayecto();
                    $transporteTipo = $transporte->getTransporteTipo();
                    $proveedor = $transporte->getProveedor();
                    $trayectoPadre = $transporte->getTrayectoPadre();
                    $data = [
                        'id' => $transporte->getId(),
                        'trayecto' => ($trayecto !== null) ? [
                            'id' => $trayecto->getId(),
                            'ciudad_inicio' => $trayecto->getCiudadInicio()->getNombre(),
                            'ciudad_fin' => $trayecto->getCiudadFin()->getNombre(),
                        ] : null,
                        'transporteTipo' => ($transporteTipo !== null) ? [
                            'id' => $transporteTipo->getId(),
                            'nombre' => $transporteTipo->getNombre(),
                        ] : null,
                        'proveedor' => ($proveedor !== null) ? [
                            'id' => $proveedor->getId(),
                            'nombre' => $proveedor->getNombre()
                        ] : null,
                        'trayecto_padre' => ($trayectoPadre !== null) ? [
                            'id' => $trayectoPadre->getId(),
                            'ciudad_inicio' => $trayectoPadre->getCiudadInicio()->getNombre(),
                            'ciudad_fin' => $trayectoPadre->getCiudadFin()->getNombre(),
                        ] : null,
                        'fecha_inicio' => ($transporte->getFechaInicio() !== null) ? $transporte->getFechaInicio()->format('Y-m-d H:i:s') : null,
                        'fecha_fin' => ($transporte->getFechaFin() !== null) ? $transporte->getFechaFin()->format('Y-m-d H:i:s') : null,
                        'comentarios' => $transporte->getComentarios(),
                        'orden' => $transporte->getOrden(),
                        'duracion' => $transporte->getDuracion(),
                    ];
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Transporte encontrado', 'data' => $data], 200);
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
     * @Route("/transporte/list", methods={"GET"}, name="transporte_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $transporteRepository = $this->em->getRepository(Transporte::class);
        $transportes = $transporteRepository->findAll();

        $data = [];

        foreach ($transportes as $transporte) {
            $trayecto = $transporte->getTrayecto();
            $transporte_tipo = $transporte->getTransporteTipo();
            $proveedor = $transporte->getProveedor();
            $trayecto_padre = $transporte->getTrayectoPadre();

            $data[] = [
                'id' => $transporte->getId(),
                'fecha_inicio' => ($transporte->getFechaInicio() !== null) ? $transporte->getFechaInicio()->format('Y-m-d H:i:s') : null,
                'fecha_fin' => ($transporte->getFechaFin() !== null) ? $transporte->getFechaFin()->format('Y-m-d H:i:s') : null,
                'comentarios' => $transporte->getComentarios(),
                'orden' => $transporte->getOrden(),
                'duracion' => $transporte->getDuracion(),
                'trayecto' => ($trayecto !== null) ? [
                    'id' => $trayecto->getId(),
                    'ciudad_inicio' => $trayecto->getCiudadInicio()->getNombre(),
                    'ciudad_fin' => $trayecto->getCiudadFin()->getNombre(),
                ] : null,
                'transporte_tipo' => ($transporte_tipo !== null) ? [
                    'id' => $transporte_tipo->getId(),
                    'nombre' => $transporte_tipo->getNombre(),
                ] : null,
                'proveedor' => ($proveedor !== null) ? [
                    'id' => $proveedor->getId(),
                    'nombre' => $proveedor->getNombre(),
                    'contacto' => $proveedor->getContacto(),
                    'froma_contacto' => $proveedor->getFormaContacto(),
                    'pais' => $proveedor->getPais()->getNombre(),
                    'ciudad' => $proveedor->getCiudad()->getNombre(),
                    'whatsapp' => $proveedor->getWhatsapp(),
                    'facebook' => $proveedor->getFacebook(),
                    'telefonos' => $proveedor->getTelefonos(),
                    'mails' => $proveedor->getMails(),
                    'cuenta_bancaria' => $proveedor->getCuentaBancaria(),
                    'comentarios' => $proveedor->getComentarios(),
                    'direccion' => $proveedor->getDireccion(),
                ] : null,
                'trayecto_padre' => ($trayecto_padre !== null) ? [
                    'id' => $trayecto_padre->getId(),
                    'ciudad_inicio' => $trayecto_padre->getCiudadInicio()->getNombre(),
                    'ciudad_fin' => $trayecto_padre->getCiudadFin()->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/transporte/list_web", methods={"POST"}, name="transporte_list_web")
     * @return JsonResponse
     */
    public function list_web(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $transporteRepository = $this->em->getRepository(Transporte::class);

        if(isset($data['nombre'])){
            $transporteTotal = $transporteRepository->findBy(['nombre' => $data['nombre']]);

            $transportes = $transporteRepository->findBy(
                array('nombre' => $data['nombre']),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }
        else if(isset($data['ciudad_inicio'])){
            $transporteTotal = $transporteRepository->getTodosTransporteByCiudadInicioId($data['ciudad_inicio']);
            $transportes = $transporteRepository->getTransporteByCiudadInicioId($data['ciudad_inicio'], $offset, $limit);
        }
        else if(isset($data['ciudad_fin'])){
            $transporteTotal = $transporteRepository->getTodosTransporteByCiudadFinId($data['ciudad_fin']);
            $transportes = $transporteRepository->getTransporteByCiudadFinId($data['ciudad_fin'], $offset, $limit);
        }
        else{
            $transporteTotal = $transporteRepository->findAll();

            $transportes = $transporteRepository->findBy(
                array(),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        }

        $data_aux = [];

        foreach ($transportes as $transporte_aux) {
            if(isset($data['ciudad_inicio'])){
                $transporte = new Transporte();
                $transporte = $transporteRepository->findBy(
                    array('id' => $transporte_aux['id']),
                );
                $transporte = $transporte[0];
            }
            else if (isset($data['ciudad_fin'])){
                $transporte = new Transporte();
                $transporte = $transporteRepository->findBy(
                    array('id' => $transporte_aux['id']),
                );
                $transporte = $transporte[0];
            }
            else{
                $transporte = $transporte_aux;
            }
            
            $trayecto = $transporte->getTrayecto();
            $transporte_tipo = $transporte->getTransporteTipo();
            $proveedor = $transporte->getProveedor();
            $trayecto_padre = $transporte->getTrayectoPadre();

            $data_aux[] = [
                'id' => $transporte->getId(),
                'fecha_inicio' => ($transporte->getFechaInicio() !== null) ? $transporte->getFechaInicio()->format('Y-m-d H:i:s') : null,
                'fecha_fin' => ($transporte->getFechaFin() !== null) ? $transporte->getFechaFin()->format('Y-m-d H:i:s') : null,
                'comentarios' => $transporte->getComentarios(),
                'orden' => $transporte->getOrden(),
                'duracion' => $transporte->getDuracion(),
                'trayecto' => ($trayecto !== null) ? [
                    'id' => $trayecto->getId(),
                    'ciudad_inicio' => $trayecto->getCiudadInicio()->getNombre(),
                    'ciudad_fin' => $trayecto->getCiudadFin()->getNombre(),
                ] : null,
                'transporte_tipo' => ($transporte_tipo !== null) ? [
                    'id' => $transporte_tipo->getId(),
                    'nombre' => $transporte_tipo->getNombre(),
                ] : null,
                'proveedor' => ($proveedor !== null) ? [
                    'id' => $proveedor->getId(),
                    'nombre' => $proveedor->getNombre(),
                    'contacto' => $proveedor->getContacto(),
                    'froma_contacto' => $proveedor->getFormaContacto(),
                    'pais' => ($proveedor->getPais() !== null) ? $proveedor->getPais()->getNombre() : null,
                    'ciudad' => ($proveedor->getCiudad() !== null) ? $proveedor->getCiudad()->getNombre() : null,
                    'whatsapp' => $proveedor->getWhatsapp(),
                    'facebook' => $proveedor->getFacebook(),
                    'telefonos' => $proveedor->getTelefonos(),
                    'mails' => $proveedor->getMails(),
                    'cuenta_bancaria' => $proveedor->getCuentaBancaria(),
                    'comentarios' => $proveedor->getComentarios(),
                    'direccion' => $proveedor->getDireccion(),
                ] : null,
                'trayecto_padre' => ($trayecto_padre !== null) ? [
                    'id' => $trayecto_padre->getId(),
                    'ciudad_inicio' => $trayecto_padre->getCiudadInicio()->getNombre(),
                    'ciudad_fin' => $trayecto_padre->getCiudadFin()->getNombre(),
                ] : null,
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'total' => count($transporteTotal), 'data' => $data_aux], 200);
    }

    /**
     * @Route("/transporte/create", methods={"POST"}, name="transporte_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $transporte = new Transporte();
        $string_fecha_inicio = $data['transporte']['fecha_inicio'];
        $date_fecha_inicio = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $string_fecha_inicio);
        $transporte->setFechaInicio($date_fecha_inicio);
        $string_fecha_fin = $data['transporte']['fecha_fin'];
        $date_fecha_fin = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $string_fecha_fin);
        $transporte->setFechaFin($date_fecha_fin);
        $transporte->setComentarios($data['transporte']['comentarios']);
        $transporte->setOrden($data['transporte']['orden']);
        $transporte->setDuracion($data['transporte']['duracion']);
        $trayectoRepository = $this->em->getRepository(Trayecto::class);
        $trayecto = $trayectoRepository->find($data['transporte']['trayecto_id']);
        $transporte->setTrayecto($trayecto);
        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);
        $transporteTipo = $transporteTipoRepository->find($data['transporte']['transporte_tipo_id']);
        $transporte->setTransporteTipo($transporteTipo);
        $proveedorRepository = $this->em->getRepository(Proveedor::class);
        $proveedor = $proveedorRepository->find($data['transporte']['proveedor_id']);
        $transporte->setProveedor($proveedor);
        $trayectoPadreRepository = $this->em->getRepository(Trayecto::class);
        $trayectoPadre = $trayectoPadreRepository->find($data['transporte']['trayecto_padre_id']);
        $transporte->setTrayectoPadre($trayectoPadre);

        $entityManager->persist($transporte);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El transporte se creo correctamente'], 200);
    }

    /**
     * @Route("/transporte/update/{id}", methods={"PUT", "PATCH"}, name="transporte_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $transporteRepository = $this->em->getRepository(Transporte::class);
        $transporte = $transporteRepository->find($id);

        if (!$transporte) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el transporte'], 400);
        }

        $string_fecha_inicio = $data['transporte']['fecha_inicio'];
        $date_fecha_inicio = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $string_fecha_inicio);
        $transporte->setFechaInicio($date_fecha_inicio);
        $string_fecha_fin = $data['transporte']['fecha_fin'];
        $date_fecha_fin = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $string_fecha_fin);
        $transporte->setFechaFin($date_fecha_fin);
        $transporte->setComentarios($data['transporte']['comentarios']);
        $transporte->setOrden($data['transporte']['orden']);
        $transporte->setDuracion($data['transporte']['duracion']);
        $trayectoRepository = $this->em->getRepository(Trayecto::class);
        $trayecto = $trayectoRepository->find($data['transporte']['trayecto_id']);
        $transporte->setTrayecto($trayecto);
        $transporteTipoRepository = $this->em->getRepository(TransporteTipo::class);
        $transporteTipo = $transporteTipoRepository->find($data['transporte']['transporte_tipo_id']);
        $transporte->setTransporteTipo($transporteTipo);
        $proveedorRepository = $this->em->getRepository(Proveedor::class);
        $proveedor = $proveedorRepository->find($data['transporte']['proveedor_id']);
        $transporte->setProveedor($proveedor);
        $trayectoPadreRepository = $this->em->getRepository(Trayecto::class);
        $trayectoPadre = $trayectoPadreRepository->find($data['transporte']['trayecto_padre_id']);
        $transporte->setTrayectoPadre($trayectoPadre);

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El transporte se actualizo correctamente'], 200);
    }

    /**
     * @Route("/transporte/delete/{id}", methods={"DELETE"}, name="transporte_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $transporteRepository = $this->em->getRepository(Transporte::class);
        $transporte = $transporteRepository->find($id);

        if (!$transporte) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el transporte'], 400);
        }

        $this->em->remove($transporte);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El transporte se elimino correctamente'], 200);
    }

    /**
     * @Route("/transporte/get_ciudades", methods={"GET"}, name="transporte_get_ciudades")
     * @return JsonResponse
     */
    public function get_ciudades(): JsonResponse
    {
        $transporteRepository = $this->em->getRepository(Transporte::class);
        $transportes = $transporteRepository->findAll();

        $ciudades_inicio = [];
        $ciudades_fin = [];
        $data = [];

        foreach ($transportes as $transporte) {
            $trayecto = $transporte->getTrayecto();
            if($trayecto !== null){
                $ciudades_inicio[] = [
                    'id' => $trayecto->getCiudadInicio()->getId(),
                    'nombre' => $trayecto->getCiudadInicio()->getNombre(),
                ];
                $ciudades_fin[] = [
                    'id' => $trayecto->getCiudadFin()->getId(),
                    'nombre' => $trayecto->getCiudadFin()->getNombre(),
                ];
            }  
        }
        $data = [
            "ciudades_inicio"=> $ciudades_inicio,
            "ciudades_fin"=> $ciudades_fin
        ];  
        

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }
 
}
