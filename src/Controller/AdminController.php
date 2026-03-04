<?php

namespace App\Controller;

use App\Repository\CompradorRepository;
use App\Repository\SorteoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\RifaRepository;
use App\Repository\TalonRepository;
use App\Repository\UserRepository;
use App\Repository\ViajeRepository;
use App\Repository\DepositoRepository;
use App\Repository\PagoPersonalRepository;
use App\Repository\TarjetaRepository;
use App\Repository\PasajeroRepository;
use App\Repository\PersonaRepository;
use App\Repository\UniversidadRepository;
use App\Repository\GrupoRepository;
use App\Repository\ItinerarioRepository;

/*  PRUEBA GIT HUB SEIJI*/
/* PRIMER CAMBIO  ACTION GITHUB SERVER 11 */

class AdminController extends AbstractController
{

    private $depositoRepository;
    private $personaRepository;

    public function __construct(DepositoRepository $depositoRepository, PersonaRepository $personaRepository)
    {
        $this->depositoRepository = $depositoRepository;
        $this->personaRepository = $personaRepository;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AdminController.php',
        ]);
    }

    /**
     * @Route("/admin/get-depositos-list", name="admin-get-depositos-list", methods={"POST"})
     */
    public function getDepositosList(Request $request, DepositoRepository $depositoRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $tipo = (isset($data['tipo'])) ? $data['tipo'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;
        $pasajero = (isset($data['pasajero'])) ? $data['pasajero'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

        $depositos = $depositoRepository->createQueryBuilder('d')
            ->select(
                'd.id, 
                d.Monto, 
                d.Tipo, 
                d.Fecha, 
                d.FechaProcesado, 
                d.Comentario, 
                
                p.id as PasId, 
                p.Estado as PasajeroEstado,
                p.Comentarios as PasajeroComentarios,

                u.Nombre as UniversidadNombre,

                i.Nombre as ItinerarioNombre,
                
                v.Nombre as ViajeNombre,
                
                per.id as PerId,
                per.Nombres, 
                per.Apellidos, 
                per.Cedula,
                per.Celular
                '
            )
            ->leftJoin('d.Pasajero', 'p')
            ->leftJoin('p.Persona', 'per')
            ->leftJoin('p.Universidad', 'u')
            ->leftJoin('p.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->orderBy('d.Fecha')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $totalDepositos = $depositoRepository->createQueryBuilder('de')
            ->select('de.id');

        if ($tipo != 'todos') {
            $depositos->andWhere("d.Tipo = :tipo")
                ->setParameter('tipo', $tipo);

            $totalDepositos->andWhere("de.Tipo = :tipo")
                ->setParameter('tipo', $tipo);
        }

        if ($termino != null && strlen($termino) > 2) {
            $depositos->andWhere('d.Monto LIKE :term OR per.Nombres LIKE :term OR per.Apellidos LIKE :term OR per.Cedula LIKE :term OR per.Celular LIKE :term OR p.id = :termEqual OR per.id = :termEqual OR d.id = :termEqual')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEqual', $termino);

            $totalDepositos->andWhere('de.Monto LIKE :term OR per.Nombres LIKE :term OR per.Apellidos LIKE :term OR per.Cedula LIKE :term OR per.Celular LIKE :term OR p.id = :termEqual OR per.id = :termEqual OR de.id = :termEqual')
                ->leftJoin('de.Pasajero', 'p')
                ->leftJoin('p.Persona', 'per')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEqual', $termino);
        }

        if ($pasajero != 'todos' && $pasajero != '') {
            $depositos->andWhere("p.id = :pas")
                ->setParameter('pas', $pasajero);

            $totalDepositos->andWhere("p.id = :pas")
                ->setParameter('pas', $pasajero)
                ->leftJoin('de.Pasajero', 'p');
        }

        if ($desde != '') {
            $depositos->andWhere('d.Fecha >= :des')
                ->setParameter('des', $desde);

            $totalDepositos->andWhere('de.Fecha >= :des')
                ->setParameter('des', $desde);
        }

        if ($hasta != '') {
            $depositos->andWhere('d.Fecha <= :has')
                ->setParameter('has', $hasta);

            $totalDepositos->andWhere('de.Fecha <= :has')
                ->setParameter('has', $hasta);
        }

        $depositos = $depositos->getQuery()
            ->getResult();

        $totalDepositos = $totalDepositos->getQuery()
            ->getResult();

        $auxDepositos = array();

        foreach ($depositos as $dep) {
            $user = $userRepository->findOneBy(['Persona' => $dep['PerId']]);
            $dep['Email'] = $user->getEmail();
            array_push($auxDepositos, $dep);
        }

        $totalDepositos = count($totalDepositos);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['depositos' => $auxDepositos, 'totalDepositos' => $totalDepositos]], 200);
    }

    /**
     * @Route("/admin/get-personas-list", name="admin-get-personas-list", methods={"POST"})
     */
    public function getPersonasList(Request $request, PasajeroRepository $pasajeroRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $estado = (isset($data['estado'])) ? $data['estado'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;
        $viaje = (isset($data['viaje'])) ? $data['viaje'] : null;
        $termino = (isset($data['termino'])) ? $data['termino'] : null;

        $limit = 20;
        $offset = $pageIndex * $limit;

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


        if ($termino != null && strlen($termino) > 2) {
            if (strpos($termino, '@')) {
                $users = $userRepository->createQueryBuilder('u')
                    ->select('p.id')
                    ->leftJoin('u.Persona', 'p')
                    ->andWhere('u.email = :termUser')
                    ->setParameter('termUser', $termino)
                    ->getQuery()
                    ->getResult();

                $personas->andWhere('per.id = :users')
                    ->setParameter('users', $users);
            } else {
                $personas->andWhere('per.Nombres LIKE :term OR per.Apellidos LIKE :term OR per.Cedula LIKE :term OR per.Celular LIKE :term OR per.Apellidos LIKE :term OR v.Nombre LIKE :term')
                    ->setParameter('term', '%' . $termino . '%');
            }
        }

        if ($estado != 'todos') {
            $personas->andWhere("pas.Estado = :estatus")
                ->setParameter('estatus', $estado);
        }

        if ($viaje != 'todos') {
            $personas->andWhere("v.id = :viajeId")
                ->setParameter('viajeId', $viaje);
        }

        $totalPersonas = $personas->getQuery()
            ->getResult();


        $personasResult = $personas->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $auxPersonas = array();

        foreach ($personasResult as $per) {
            $user = $userRepository->findOneBy(['Persona' => $per['PerId']]);
            $per['email'] = $user->getEmail();
            array_push($auxPersonas, $per);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => ['personas' => $auxPersonas, 'totalPersonas' => count($totalPersonas)]], 200);
    }

    /**
     * @Route("/admin/get-rifas-list", name="admin-get-rifas-list", methods={"POST"})
     */
    public function getRifasList(Request $request, TalonRepository $talonRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $status = (isset($data['status'])) ? $data['status'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;
        $pasajero = (isset($data['pasajero'])) ? $data['pasajero'] : null;
        $all = (isset($data['all'])) ? $data['all'] : false;

        $talones = $talonRepository->createQueryBuilder('t')
            ->select(
                't.id, 
                t.Numero, 
                t.SorteoNumero, 
                t.FechaSorteo, 
                t.Estado, 
                t.Precio, 
                t.FechaRegistro, 

                r.id as rifa_id,
                r.nombre as rifa_nombre,

                s.id as sorteo_id,
                s.sorteoNumero as sorteo_numero,
                s.fechaSorteo as fecha_sorteo,
                s.valorTalon as valor_talon,

                p.id as PasId, 
                p.Estado as PasajeroEstado,
                p.Comentarios as PasajeroComentarios,

                u.Nombre as UniversidadNombre,

                i.Nombre as ItinerarioNombre,
                
                v.Nombre as ViajeNombre,
                
                per.id as PerId,
                per.Nombres, 
                per.Apellidos, 
                per.Cedula,

                d.id as DepId,
                d.Monto,
                d.Tipo,
                d.Fecha,
                d.Comentario as DepositoComentario,

                c.Nombre as CompradorNombre, 
                c.Email as CompradorEmail, 
                c.Celular as CompradorCelular, 
                c.Departamento as CompradorDepartamento'
            )
            // ->andWhere("t.Numero > 300")
            ->leftJoin('t.Pasajero', 'p')
            ->leftJoin('t.sorteo', 's')
            ->leftJoin('s.rifa', 'r')
            ->leftJoin('p.Persona', 'per')
            ->leftJoin('t.Comprador', 'c')
            ->leftJoin('p.Universidad', 'u')
            ->leftJoin('p.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->leftJoin('t.Deposito', 'd')
            ->orderBy('t.Numero, t.FechaSorteo');

        if (!$all) {
            $limit = 100;
            $offset = $pageIndex * $limit;
            $talones
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $totalTalones = $talonRepository->createQueryBuilder('ta')
            ->select('ta.id');

        if ($status != 'todos') {
            $talones->andWhere("t.Estado = :status")
                ->setParameter('status', $status);

            $totalTalones->andWhere("ta.Estado = :status")
                ->setParameter('status', $status);
        }

        if ($pasajero != 'todos' && $pasajero != '') {
            $talones->andWhere("p.id = :pas")
                ->setParameter('pas', $pasajero);

            $totalTalones->andWhere("p.id = :pas")
                ->setParameter('pas', $pasajero)
                ->leftJoin('ta.Pasajero', 'p');
        }

        if ($desde != '') {
            $talones->andWhere('t.Numero >= :des')
                ->setParameter('des', $desde);

            $totalTalones->andWhere('ta.Numero >= :des')
                ->setParameter('des', $desde);
        }

        if ($hasta != '') {
            $talones->andWhere('t.Numero <= :has')
                ->setParameter('has', $hasta);

            $totalTalones->andWhere('ta.Numero <= :has')
                ->setParameter('has', $hasta);
        }

        $talones = $talones->getQuery()
            ->getResult();

        $totalTalones = $totalTalones->getQuery()
            ->getResult();

        $totalTalones = count($totalTalones);

        $auxTalones = array();

        foreach ($talones as $tal) {
            $user = $userRepository->findOneBy(['Persona' => $tal['PerId']]);
            $tal['Email'] = $user->getEmail();
            array_push($auxTalones, $tal);
        }

        $talones = $auxTalones;

        if (count($talones) > 0) {
            $aux = array();
            $arrayItem = array();
            $auxNumber = false;
            $firstLoop = true;
            $counter = 0;
            foreach ($talones as $tal) {
                $counter++;

                if ($auxNumber != $tal['Numero']) {
                    $auxNumber = $tal['Numero'];
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

            $talones = $aux;
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Limite de consulta: ' . $limit, 'data' => ['talones' => $talones, 'totalTalones' => $totalTalones]], 200);
    }

    /**
     *  by SEIJI
     * @Route("/admin/get-rifas-list-new", name="admin-get-rifas-list-new", methods={"POST"})
     */
    public function getRifasListNew(Request $request, RifaRepository $rifaRepository, TalonRepository $talonRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $status = (isset($data['status'])) ? $data['status'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;
        $pasajero = (isset($data['pasajero'])) ? $data['pasajero'] : null;
        $rifa_request = (isset($data['selectedRifa'])) ? $data['selectedRifa'] : null;
        $sorteo_request = (isset($data['selectedSorteo'])) ? $data['selectedSorteo'] : null;

        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $loteDatos = (isset($data['loteDatos'])) ? $data['loteDatos'] : null;
        $pageRegisterLimit = (isset($data['pageRegisterLimit'])) ? $data['pageRegisterLimit'] : null;
        $limit = $pageRegisterLimit;
        $offset = $loteDatos * $limit;

        try {
            $talones = $talonRepository->talonesList($offset, $limit, $status, $pasajero, $desde, $hasta, $rifa_request, $sorteo_request);
        } catch (\Exception $exception) {
            $response = new JsonResponse([
                'message' => $exception->getMessage(),
                'data' => [],
                'errors' => []
            ]);

            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

            return $response;
        }

        /* $totalTalones = $talonRepository->createQueryBuilder('ta')
            ->select('ta.id')
            ->getQuery()
            ->getResult();

        $totalTalones = count($totalTalones);*/


        $totalTalones = count($talones);

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
                    $user = $userRepository->findOneBy(['Persona' => $tal['PerId']]);
                    if ($user == null) {
                        $tal['Email'] = 'sin correo electronico';
                    } else {
                        $tal['Email'] = $user->getEmail();
                    }
                    $aux_sorteo = array(
                        'id_talon'  => $tal['id'],
                        'id' => $tal['sorteo_id'],
                        'sorteo_numero' => $tal['sorteo_numero'],
                        'fecha' => $tal['fecha_sorteo'],
                        'Estado' => $tal['Estado'],
                        'Precio' =>  $tal['Precio'],
                        'persona_id' => $tal['PerId'],
                        'persona_cedula' => $tal['Cedula'],
                        'pasajero_nombres' => $tal['Nombres'],
                        'pasajero_apellidos' => $tal['Apellidos'],
                        'persona_celular' => $tal['Celular'],
                        'persona_email' => $tal['Email'],
                        'pasajero_itinerario' => $tal['ItinerarioNombre'],
                        'pasajero_viaje' => $tal['ViajeNombre'],
                        'deposito_id' => $tal['DepId'],
                        'deposito_tipo' => $tal['Tipo'],
                        'deposito_monto' => $tal['Monto'],
                        'deposito_fecha' => $tal['Fecha'],
                        'deposito_comentario' => $tal['DepositoComentario'],
                        'comprador_id' => $tal['CompradorId'],
                        'comprador_nombre' => $tal['CompradorNombre'],
                        'comprador_email' => $tal['CompradorEmail'],
                        'comprador_celular' => $tal['CompradorCelular'],
                        'comprador_departamento' => $tal['CompradorDepartamento'],
                    );
                    array_push($aux_array_sorteos, $aux_sorteo);
                }
            }
            $rifa['Sorteos'] = $aux_array_sorteos;
            array_push($rifas_array, $rifa);
        }

        return new JsonResponse([
            'status' => 'success', 'code' => 200, 'message' => 'Correcto',
            'data' => [
                'talones' => $talones,
                'rifas' => $rifas_array,
                'rifas_NUMERO' => $aux_array_rifas,
                'totalTalones' => $totalTalones
            ]
        ], 200);
    }

    /**
     *  by SEIJI
     * @Route("/admin/get-rifas-list-filter", name="admin-get-rifas-list-filter", methods={"POST"})
     */
    public function getRifasListFilter(Request $request, RifaRepository $rifaRepository, TalonRepository $talonRepository, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $status = (isset($data['status'])) ? $data['status'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;
        $pasajero = (isset($data['pasajero'])) ? $data['pasajero'] : null;
        $rifa_request = (isset($data['selectedRifa'])) ? $data['selectedRifa'] : null;
        $sorteo_request = (isset($data['selectedSorteo'])) ? $data['selectedSorteo'] : null;

        $pageIndex = (isset($data['pageIndex'])) ? $data['pageIndex'] : null;
        $loteDatos = (isset($data['loteDatos'])) ? $data['loteDatos'] : null;
        $pageRegisterLimit = (isset($data['pageRegisterLimit'])) ? $data['pageRegisterLimit'] : null;
        $limit = $pageRegisterLimit;
        $offset = $loteDatos * $limit;

        $talones = $talonRepository->talonesListFilter($offset, $limit, $status, $pasajero, $desde, $hasta, $rifa_request, $sorteo_request);

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
                    $user = $userRepository->findOneBy(['Persona' => $tal['PerId']]);
                    if ($user == null) {
                        $tal['Email'] = 'sin correo electronico';
                    } else {
                        $tal['Email'] = $user->getEmail();
                    }
                    $aux_sorteo = array(
                        'id_talon'  => $tal['id'],
                        'id' => $tal['sorteo_id'],
                        'sorteo_numero' => $tal['sorteo_numero'],
                        'fecha' => $tal['fecha_sorteo'],
                        'Estado' => $tal['Estado'],
                        'persona_id' => $tal['PerId'],
                        'persona_cedula' => $tal['Cedula'],
                        'pasajero_nombres' => $tal['Nombres'],
                        'pasajero_apellidos' => $tal['Apellidos'],
                        'persona_celular' => $tal['Celular'],
                        'persona_email' => $tal['Email'],
                        'pasajero_itinerario' => $tal['ItinerarioNombre'],
                        'pasajero_viaje' => $tal['ViajeNombre'],
                        'deposito_id' => $tal['DepId'],
                        'deposito_tipo' => $tal['Tipo'],
                        'deposito_monto' => $tal['Monto'],
                        'deposito_fecha' => $tal['Fecha'],
                        'deposito_comentario' => $tal['DepositoComentario'],
                        'comprador_id' => $tal['CompradorId'],
                        'comprador_nombre' => $tal['CompradorNombre'],
                        'comprador_email' => $tal['CompradorEmail'],
                        'comprador_celular' => $tal['CompradorCelular'],
                        'comprador_departamento' => $tal['CompradorDepartamento'],
                    );
                    array_push($aux_array_sorteos, $aux_sorteo);
                }
            }
            $rifa['Sorteos'] = $aux_array_sorteos;
            array_push($rifas_array, $rifa);
        }

        return new JsonResponse([
            'status' => 'success', 'code' => 200, 'message' => '',
            'data' => [
                'talones' => $talones,
                'rifas' => $rifas_array,
                'rifas_NUMERO' => $aux_array_rifas,
                'totalTalones' => count($rifas_array)
            ]
        ], 200);
    }

    /**
     * @Route("/admin/entregar-rifas", name="admin-entregar-rifas", methods={"POST"})
     */
    public function entregarRifas(Request $request, TalonRepository $talonRepository, PasajeroRepository $pasajeroRepository)
    {
        $fechaActual = date('Y-m-d H:i:s');
        $data = json_decode($request->getContent(), true);
        $pasajero = (isset($data['pasajero'])) ? $data['pasajero'] : null;
        $desde = (isset($data['desde'])) ? $data['desde'] : null;
        $hasta = (isset($data['hasta'])) ? $data['hasta'] : null;

        $talones = $talonRepository->createQueryBuilder('t')
            ->select(
                't.id, 
                t.Numero, 
                t.SorteoNumero, 
                t.FechaSorteo, 
                t.Estado, 
                t.Precio, 
                t.FechaRegistro, 
                    
                p.id as PasId,

                per.id as PerId,
                per.Nombres, 
                per.Apellidos
                '
            )
            ->leftJoin('t.Pasajero', 'p')
            ->leftJoin('p.Persona', 'per')
            ->orderBy('t.Numero, t.FechaSorteo')
            ->andWhere('t.Numero >= :des')
            ->setParameter('des', $desde)
            ->andWhere('t.Numero <= :has')
            ->setParameter('has', $hasta)
            ->andWhere('t.FechaSorteo >= :fecha')
            ->setParameter('fecha', $fechaActual)
            ->getQuery()
            ->getResult();

        $totalTalones = $talonRepository->createQueryBuilder('ta')
            ->select('ta.id')
            ->andWhere('ta.Numero >= :des')
            ->setParameter('des', $desde)
            ->andWhere('ta.Numero <= :has')
            ->setParameter('has', $hasta)
            ->andWhere('ta.FechaSorteo >= :fecha')
            ->setParameter('fecha', $fechaActual)
            ->getQuery()
            ->getResult();

        $totalTalones = count($totalTalones);

        if (count($talones) > 0) {
            $aux = array();
            $arrayItem = array();
            $auxNumber = false;
            $firstLoop = true;
            $counter = 0;
            foreach ($talones as $tal) {
                $counter++;

                if ($auxNumber != $tal['Numero']) {
                    $auxNumber = $tal['Numero'];
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

            $talones = $aux;
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => '', 'data' => ['talones' => $talones, 'totalTalones' => $totalTalones]], 200);
        } else {

            $pas = $pasajeroRepository->findOneBy(
                array(
                    "id" => $pasajero
                )
            );

            $talonRepository->insertEntrega($pas, $desde, $hasta);

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Rifas entregadas'], 200);
        }
    }

    /**
     * @Route("/admin/entrega-rifas", name="admin-entrega-rifas", methods={"POST"})
     * by Seiji
     */
    public function entregaRifas(Request $request, TalonRepository $talonRepository, PasajeroRepository $pasajeroRepository, SorteoRepository $sorteoRepository)
    {
        $fechaActual = date('Y-m-d H:i:s');
        $data = json_decode($request->getContent(), true);
        $pasajero = (isset($data['entregaRifa']['pasajero'])) ? $data['entregaRifa']['pasajero'] : null;
        $desde = (isset($data['entregaRifa']['desde'])) ? $data['entregaRifa']['desde'] : null;
        $hasta = (isset($data['entregaRifa']['hasta'])) ? $data['entregaRifa']['hasta'] : null;
        $sorteos = (isset($data['entregaRifa']['sorteos'])) ? $data['entregaRifa']['sorteos'] : null;

        $respuesta = array();
        $entregas = array();
        $talonesAsignados_array = array();

        $pas = $pasajeroRepository->findOneBy(
            array(
                "id" => $pasajero
            )
        );

        $talon_entregado = false;
        // Verificar que el numero de talon no este entregado en los Sorteos

        foreach ($sorteos as $sor) {

            $sorteo = $sorteoRepository->find($sor['sorteo_id']);
            if ($sorteo) {
                // Buscar que no tenga numeros asignados para ese Sorteo
                $talonesAsignados = $talonRepository->findBetweenNumero($desde, $hasta, $sorteo);

                // Hay talones asignados a esos numeros
                if ($talonesAsignados) {
                    $talon_entregado = true;
                    foreach ($talonesAsignados as $talon) {
                        $aux_talon = array(
                            'id' => $talon['id'],
                            'Numero' => $talon['Numero'],
                            'Precio' => $talon['Precio'],
                            'Estado' =>  $talon['Estado'],
                            'SorteoNumero' =>  $talon['SorteoNumero'],
                            'FechaSorteo' =>  $talon['FechaSorteo'],
                            'pasajero_nombres' =>  $talon['nombres'],
                            'pasajero_apellidos' =>  $talon['apellidos']
                        );
                        array_push($talonesAsignados_array, $aux_talon);
                    }
                }

                $auxEntrega = array(
                    'idSorteo' => $sorteo->getId(),
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'idPasajero' =>  $pas->getId(),
                );
                array_push($entregas, $auxEntrega);
            }
        }

        if ($talon_entregado) {

            return new JsonResponse([
                'status' => 'error',
                'code' => 400,
                'message' => 'Estos numeros ya estan asignados',
                'data' => $talonesAsignados_array
            ], 200);
        } else {
            foreach ($entregas as $item_entrega) {
                $talonRepository->insertEntregaTalon(
                    $item_entrega['idSorteo'],
                    $item_entrega['idPasajero'],
                    $item_entrega['desde'],
                    $item_entrega['hasta']
                );
            }
            return new JsonResponse([
                'status' => 'success',
                'code' => 200,
                'message' => 'Datos Entrega: ' . " PASAJERO ID: " . $pasajero . " desde: " . $desde . " hasta: " . $hasta,
                'data' => $entregas
            ], 200);
        }
    }

    /**
     * @Route("/admin/get-pasajeros-list", name="admin-get-pasajeros-list", methods={"GET"})
     */
    public function getPasajerosList(PasajeroRepository $pasajeroRepository)
    {
        $pasajeros = $pasajeroRepository->pasajerosNativeQuery();
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $pasajeros], 200);
    }

    /**
     * @Route("/admin/get-universidades-list", name="admin-get-universidades-list", methods={"GET"})
     */
    public function getUniversidadesList(UniversidadRepository $universidadRepository)
    {
        $universidades = $universidadRepository->createQueryBuilder('u')
            ->select(
                'u.id, 
                u.Nombre
                '
            )
            ->orderBy('u.Nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $universidades], 200);
    }

    /**
     * @Route("/admin/get-viajes-activos-list", name="admin-get-viajes-activos-list", methods={"GET"})
     */
    public function getViajesActivosList(ViajeRepository $viajeRepository, GrupoRepository $grupoRepository)
    {
        $fechaActual = date('Y-m-d H:i:s');
        $viajes = $viajeRepository->createQueryBuilder('v')
            ->select(
                'v.id, 
                v.Nombre,
                v.FechaInicio,
                v.FechaFin
                '
            )
            ->where('v.FechaInicio >= :fecha')
            ->setParameter('fecha', $fechaActual)
            ->orderBy('v.Nombre')
            ->getQuery()
            ->getResult();

        $aux_array_viajes = array();
        $grupos = array();
        foreach ($viajes as $item) {

            $viaje = $viajeRepository->findOneBy(
                array('id' => $item['id'])
            );

            $grupos = $grupoRepository->createQueryBuilder('g')
                ->select(
                    'g.id, 
                    g.Nombre
                    '
                )
                ->where('g.Viaje = :viaje')
                ->setParameter('viaje', $viaje)
                ->orderBy('g.Nombre')
                ->getQuery()
                ->getResult();

            $aux_viaje = array(
                'id'  => $item['id'],
                'Nombre' => $item['Nombre'],
                'FechaInicio' => $item['FechaInicio'],
                'FechaFin' => $item['FechaFin'],
                'Grupos' => $grupos
            );

            array_push($aux_array_viajes, $aux_viaje);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $aux_array_viajes], 200);
    }

    /**
     * @Route("/admin/get-viajes-list", name="admin-get-viajes-list", methods={"GET"})
     */
    public function getViajesList(ViajeRepository $viajeRepository)
    {
        $viajes = $viajeRepository->createQueryBuilder('v')
            ->select(
                'v.id, 
                v.Nombre
                '
            )
            ->orderBy('v.Nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $viajes], 200);
    }

    /**
     * @Route("/admin/get-itinerarios-by-viaje", name="admin-get-itinerarios-by-viaje", methods={"POST"})
     */
    public function getItinerariosByViaje(Request $request, ViajeRepository $viajeRepository, ItinerarioRepository $itinerarioRepository)
    {
        $data = json_decode($request->getContent(), true);
        $viajeId = (isset($data['viaje'])) ? $data['viaje'] : null;


        $viaje = $viajeRepository->findOneBy(
            array('id' => $viajeId)
        );

        $itinerarios = $itinerarioRepository->createQueryBuilder('i')
            ->select(
                'i.id, 
                i.Nombre
                '
            )
            ->where('i.Viaje = :v')
            ->setParameter('v', $viaje)
            ->orderBy('i.Nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $itinerarios], 200);
    }

    /**
     * @Route("/admin/get-itinerarios-by-grupo", name="admin-get-itinerarios-by-grupo", methods={"POST"})
     */
    public function getItinerariosByGrupo(Request $request, GrupoRepository $grupoRepository, ItinerarioRepository $itinerarioRepository)
    {
        $data = json_decode($request->getContent(), true);
        $grupoId = (isset($data['grupo'])) ? $data['grupo'] : null;


        $grupo = $grupoRepository->findOneBy(
            array('id' => $grupoId)
        );

        $itinerarios = $itinerarioRepository->createQueryBuilder('i')
            ->select(
                'i.id, 
                i.Nombre
                '
            )
            ->where('i.Grupo = :g')
            ->setParameter('g', $grupo)
            ->orderBy('i.Nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $itinerarios], 200);
    }

    /**
     * @Route("/admin/get-grupos-by-viaje", name="admin-get-grupos-by-viaje", methods={"POST"})
     */
    public function getGruposByViaje(Request $request, ViajeRepository $viajeRepository, GrupoRepository $grupoRepository)
    {
        $data = json_decode($request->getContent(), true);
        $viajeId = (isset($data['viaje'])) ? $data['viaje'] : null;

        $viaje = $viajeRepository->findOneBy(
            array('id' => $viajeId)
        );

        $grupos = $grupoRepository->createQueryBuilder('g')
            ->select(
                'g.id, 
                g.Nombre
                '
            )
            ->where('g.Viaje = :viaje')
            ->setParameter('viaje', $viaje)
            ->orderBy('g.Nombre')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $grupos], 200);
    }

    /**
     * @Route("/api/search-depositos", name="search_depositos", methods={"GET"})
     */
    public function searchDepositos(Request $request, DepositoRepository $depositoRepository): JsonResponse
    {
        $data = array();
        $str = $request->query->get('s');

        if ($str != null && !empty($str) && strlen($str) > 2) {
            $depositos = $depositoRepository->search($str);

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $depositos], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Parámetros inválidos.'], Response::HTTP_OK);
            // return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => ''], 200);
        }
    }

    /**
     * @Route("/admin/procesar-depositos-csv", name="procesar-depositos-csv", methods={"POST"})
     */
    public function procesarDepositosCsv(Request $request, PasajeroRepository $pasajeroRepository, PersonaRepository $personaRepository, DepositoRepository $depositoRepository)
    {
        $data = json_decode($request->getContent(), true);
        $depositos = (isset($data['depositos'])) ? $data['depositos'] : null;

        $result = array();

        if ($depositos != null && !empty($depositos)) {

            foreach ($depositos as $dep) {
                $status = '';


                if ($dep['persona'] != '' && $dep['persona'] != null) {
                    $persona = $this->getPersonaByCI($dep['cedula']);

                    if (count($persona) > 0 && $persona != null) {
                        $pasajero = $pasajeroRepository->findOneBy(['Persona' => $persona]);
                        $lAuxDate = explode('/', $dep['fecha']);
                        $lAuxDate = $lAuxDate[1] . '/' . $lAuxDate[0] . '/' . $lAuxDate[2];
                        $lAuxDate = new \DateTime($lAuxDate);
                        $monto = explode(',', $dep['monto']);
                        $deposito = $depositoRepository->saveDeposito($pasajero, $monto[0], 'Contado', $lAuxDate, true, '', false);

                        if ($deposito) {
                            $status = 'Procesado correctamente';
                        }
                    } else {
                        $status = 'No se pudo encontrar la persona';
                    }
                } else {
                    $status = 'No se pudo encontrar la persona';
                    $persona = $this->getPersonaByCI('12345678');

                    if ($persona) {
                        $pasajero = $pasajeroRepository->findOneBy(['Persona' => $persona]);

                        $lAuxDate = explode('/', $dep['fecha']);
                        $lAuxDate = $lAuxDate[1] . '/' . $lAuxDate[0] . '/' . $lAuxDate[2];
                        $lAuxDate = new \DateTime($lAuxDate);
                        $monto = explode(',', $dep['monto']);
                        $deposito = $depositoRepository->saveDeposito($pasajero, $monto[0], 'Contado', $lAuxDate, true, '', false);
                    }
                }

                $auxDep = array(
                    'fecha' => $dep['fecha'],
                    'status' => $status,
                    'persona' => $dep['persona'],
                    'cedula' => $dep['cedula'],
                    'monto' => $dep['monto'],
                );

                array_push($result, $auxDep);
            }

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $result], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Parámetros inválidos.'], Response::HTTP_OK);
        }
    }

    /**
     * @Route("/admin/ingresar-deposito-tarjeta", name="ingresar-deposito-tarjeta", methods={"POST"})
     */
    public function ingresarDepositoTarjeta(Request $request, PasajeroRepository $pasajeroRepository, DepositoRepository $depositoRepository, TarjetaRepository $tarjetaRepository)
    {
        $data = json_decode($request->getContent(), true);
        $deposito = (isset($data['deposito'])) ? $data['deposito'] : null;
        $tarjeta = (isset($data['tarjeta'])) ? $data['tarjeta'] : null;

        if ($deposito != null && !empty($deposito)) {

            $pasajero = $pasajeroRepository->findOneBy(
                array('id' => $deposito['pasajero'])
            );

            if ($pasajero != null) {
                $lAuxDate = explode('T', $deposito['fecha'])[0];
                $lAuxDate = explode('-', $lAuxDate);
                $lAuxDate = $lAuxDate[1] . '/' . $lAuxDate[2] . '/' . $lAuxDate[0] . ' ' . $deposito['hora'];
                $lAuxDate = new \DateTime($lAuxDate);
                $newDep = $depositoRepository->saveDeposito($pasajero, $deposito['monto'], $deposito['tipo'], $lAuxDate, false, $deposito['comentario'], $deposito['geopay']);

                if ($deposito['tipo'] == 'Credito' || $deposito['tipo'] == 'Debito') {
                    $tarjeta = $tarjetaRepository->saveTarjeta($newDep, $tarjeta['tipo'], $tarjeta['moneda'], $tarjeta['cuotas'], $lAuxDate, $tarjeta['codAut'], $tarjeta['nroTarjeta'], $tarjeta['emisor'], '');
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Pasajero inválido.'], Response::HTTP_OK);
            }

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $newDep->getId()], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Parámetros inválidos.'], Response::HTTP_OK);
        }
    }

    /**
     * @Route("/admin/asignar-deposito-talones", name="asignar-deposito-talones", methods={"POST"})
     */
    public function asignarDepositoTalones(Request $request, DepositoRepository $depositoRepository, TalonRepository $talonRepository, CompradorRepository $compradorRepository)
    {
        $data = json_decode($request->getContent(), true);
        $depositoId = (isset($data['depositoId'])) ? $data['depositoId'] : null;
        $talones = (isset($data['talones'])) ? $data['talones'] : null;
        $compradorData = (isset($data['comprador'])) ? $data['comprador'] : null;

        $deposito = $depositoRepository->findOneBy(
            array('id' => $depositoId)
        );

        $comprador = $compradorRepository->saveComprador($compradorData['nombre'], $compradorData['email'], $compradorData['celular'], $compradorData['departamento']);

        foreach ($talones as $talonId) {
            $talon = $talonRepository->findOneBy(
                array('id' => $talonId)
            );

            $talon->setEstado('Pago');
            $talon->setDeposito($deposito);
            $talon->setComprador($comprador);
            $talon->setFechaRegistro(new \DateTimeImmutable('now'));

            $talonRepository->updateTalon($talon);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => []], 200);
    }

    /**
     * @Route("/admin/juntar-depositos", name="juntar-depositos", methods={"POST"})
     */
    public function juntarDepositos(Request $request, DepositoRepository $depositoRepository, PasajeroRepository $pasajeroRepository)
    {
        $data = json_decode($request->getContent(), true);
        $depositos = (isset($data['depositos'])) ? $data['depositos'] : null;

        $pasajero = $pasajeroRepository->findOneBy(
            array('id' =>  $depositos[0]['idPasajero'])
        );

        $comentario = 'Creado apartir de los saldos de los depostios:';
        $monto = 0;
        foreach ($depositos as $deposito) {
            $monto += $deposito['saldo'];
            $comentario .= " Deposito Id: " . $deposito['id'] . " - Saldo: " . $deposito['saldo'] . ' /';
        }
        $comentario = substr($comentario, 0, -1);

        //Crear nuevo deposito
        $deposito_new = $depositoRepository->saveDeposito($pasajero, $monto, 'Contado', new \DateTimeImmutable('now'), true, $comentario, false);
        $idDeposito_new = $deposito_new->getId();
        $comentario_2 = '';
        foreach ($depositos as $deposito) {

            $comentario_2 = "Se quitan " . $deposito['saldo'] . " que se usan para crear el depósito id " . $idDeposito_new;

            $dep = $depositoRepository->findOneBy(
                array('id' => $deposito['id'])
            );
            $monto = $dep->getMonto() - $deposito['saldo'];

            $dep->setMonto($monto);
            $dep->setComentario($comentario_2);
            $depositoRepository->updateDeposito($dep);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se junto correctamente', 'data' => []], 200);
    }

    /**
     * @Route("/admin/ingresar-pago-personal", name="ingresar-pago-personal", methods={"POST"})
     */
    public function savePagoPersonal(Request $request, DepositoRepository $depositoRepository, PagoPersonalRepository $pagoPersonalRepository)
    {
        $data = json_decode($request->getContent(), true);
        $monto = (isset($data['monto'])) ? $data['monto'] : null;
        $depositoId = (isset($data['depositoId'])) ? $data['depositoId'] : null;

        $deposito = $depositoRepository->findOneBy(
            array('id' => $depositoId)
        );

        $pagoPersonalRepository->savePagoPersonal($monto, $deposito);

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => []], 200);
    }

    /**
     * @Route("/admin/get-saldo", name="get-saldo", methods={"POST"})
     */
    public function getSaldo(Request $request, TalonRepository $talonRepository, DepositoRepository $depositoRepository, PagoPersonalRepository $pagoPersonalRepository)
    {
        $data = json_decode($request->getContent(), true);
        $depositoId = (isset($data['depositoId'])) ? $data['depositoId'] : null;
        $gastado = 0;

        if ($depositoId != null) {

            $deposito = $depositoRepository->findOneBy(
                array('id' => $depositoId)
            );

            $talones = $talonRepository->findBy(
                array('Deposito' => $deposito)
            );

            foreach ($talones as $talon) {
                $gastado += $talon->getPrecio();
            }

            $pagosPersonales = $pagoPersonalRepository->findBy(
                array('Deposito' => $deposito)
            );

            foreach ($pagosPersonales as $pagoPersonal) {
                $gastado += $pagoPersonal->getMonto();
            }

            $saldo = $deposito->getMonto() - $gastado;

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $saldo], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Parámetros inválidos.'], Response::HTTP_OK);
        }
    }

    /**
     * @Route("/admin/upload-csv", name="admin-upload-csv", methods={"POST"})
     */
    public function uploadCsv(Request $request, PasajeroRepository $pasajeroRepository)
    {
        $file = $request->files->get('csvToUpload');

        if (!empty($file) && $file != null) {
            $ext = $file->guessExtension();
            $name = $file->getClientOriginalName();

            $path = 'uploads';
            $csv = array();

            if ($ext == 'csv' || $ext == 'txt') {
                $file_name = explode(".", $name)[0] . "_" . time() . "." . $ext;

                if (($handle = fopen($file->getPathname(), 'r')) !== FALSE) {
                    set_time_limit(0);

                    $row = 0;

                    while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                        $col_count = count($data);
                        $concepto = $data[2];

                        if (strpos($concepto, 'DEPOSITO') !== false || strpos($concepto, 'DEP. BUZON') !== false || strpos($concepto, 'ILINK') !== false || strpos($concepto, 'DEP 24 HORAS') !== false || strpos($concepto, 'CRE. CAMBIOS') !== false) {
                            $lAuxDate = explode('/', $data[1]);

                            $lAuxDate = $lAuxDate[1] . '/' . $lAuxDate[0] . '/' . $lAuxDate[2];
                            $lAuxDate = new \DateTime($lAuxDate);
                            $ultimaFecha = explode('-', $this->obtenerUltimaFecha());
                            $ultimaFecha = $ultimaFecha[0] . '/' . $ultimaFecha[1] . '/' . $ultimaFecha[2];
                            $ultimaFecha = new \DateTime($ultimaFecha);

                            if ($lAuxDate > $ultimaFecha && $lAuxDate->format('Y-m-d') < (new \DateTime('now'))->format('Y-m-d')) {
                                $csv[$row]['fecha'] = $data[1];
                                $csv[$row]['concepto'] = $data[2];
                                // $csv[$row]['col4'] = $data[3];
                                $csv[$row]['debito'] = $data[3];
                                $csv[$row]['credito'] = $data[4];
                                $csv[$row]['monto'] = $data[5];
                                $csv[$row]['cedula'] = $this->obtenerCedula($csv[$row]['concepto']);
                                $persona = $this->getPersonaByCI($csv[$row]['cedula']);
                                $csv[$row]['persona'] = ($persona) ? $persona['nombres'] . ' ' . $persona['apellidos'] : null;
                                $row++;
                            }
                        }
                    }

                    fclose($handle);
                }
            }
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => '', 'data' => $csv], 200);
    }

    private function getPersonaByCI($pCI)
    {
        $persona = $this->personaRepository->createQueryBuilder('p')
            ->where('p.Cedula LIKE :ci')
            ->setParameter('ci', $pCI . '%')
            ->getQuery()
            ->execute();

        if (count($persona) > 0) {
            $persona = $persona[0];

            return array(
                'id' => $persona->getId(),
                'nombres' => $persona->getNombres(),
                'apellidos' => $persona->getApellidos(),
                'cedula' => $persona->getCedula()
            );
        } else {
            return null;
        }
    }

    private function obtenerCedula($concepto)
    {
        if (strpos($concepto, 'CRE. CAMBIOS') !== false) {
            // Retorno el usuario de dTyT
            return '12345678';
        }

        if (strpos($concepto, 'DEPOSITO') !== false || strpos($concepto, 'DEP. BUZON') !== false || strpos($concepto, 'ILINK') !== false || strpos($concepto, 'DEP 24 HORAS') !== false) {
            if (strpos($concepto, 'DEPOSITO') !== false) {
                $concepto = str_replace('DEPOSITO', '', $concepto);
            } else if (strpos($concepto, 'DEP. BUZON') !== false) {
                $concepto = str_replace('DEP. BUZON', '', $concepto);
            } else if (strpos($concepto, 'ILINK') !== false) {
                $concepto = str_replace('ILINK', '', $concepto);
            } else if (strpos($concepto, 'DEP 24 HORAS') !== false) {
                $concepto = str_replace('DEP 24 HORAS', '', $concepto);
            }

            $cadena = $concepto;
            $cedula = "";
            $primer_numero = true;
            $counter = 0;
            for ($i = 0; $i < strlen($cadena); $i++) {

                $char = substr($cadena, $i, 1);
                if ($char == '0' && $primer_numero) {
                    //do nothing
                } else {
                    if (is_numeric($char) && $counter < 7) {
                        $counter++;
                        $cedula .= $char;
                        $primer_numero = false;
                    } else {
                        //do nothing
                    }
                }
            }
        }

        if ($cedula == '') {
            // Retorno el usuario de dTyT
            return '12345678';
        }

        return $cedula;
    }

    private function obtenerUltimaFecha()
    {
        $ultimoDeposito = $this->depositoRepository->findOneBy(
            array(
                "Csv" => true,
                "Tipo" => 'Contado'
            ),
            array('Fecha' => 'DESC')
        );

        return $ultimoDeposito->getFecha()->format('m-d-Y');
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
