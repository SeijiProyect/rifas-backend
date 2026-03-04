<?php

namespace App\Controller;

use App\Entity\Sorteo;
use App\Repository\RifaRepository;
use App\Repository\SorteoRepository;
use App\Repository\TalonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SorteoController extends AbstractController
{
    #[Route('/sorteo', name: 'sorteo')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SorteoController.php',
        ]);
    }

    /**
     * @Route("api/sorteo/crear", name="crear-sorteo", methods={"POST"})
     */
    public function crearSorteo(Request $request, SorteoRepository $sorteoRepository, RifaRepository $rifaRepository)
    {

        $data = json_decode($request->getContent(), true);
        $fecha_sorteo = (isset($data['sorteo']['fecha_sorteo'])) ? $data['sorteo']['fecha_sorteo'] : null;
        $rifa_id = (isset($data['sorteo']['rifa']['id'])) ? $data['sorteo']['rifa']['id'] : null;
        $numero_sorteo = (isset($data['sorteo']['numero_sorteo'])) ? $data['sorteo']['numero_sorteo'] : null;
        $lugar = (isset($data['sorteo']['lugar'])) ? $data['sorteo']['lugar'] : null;
        $numero_inicial_talon = (isset($data['sorteo']['numero_inicial_talon'])) ? $data['sorteo']['numero_inicial_talon'] : null;
        $numero_final_talon = (isset($data['sorteo']['numero_final_talon'])) ? $data['sorteo']['numero_final_talon'] : null;
        $talon_valor = (isset($data['sorteo']['talon_valor'])) ? $data['sorteo']['talon_valor'] : null;
        $porcentaje_comision = (isset($data['sorteo']['porcentaje_comision'])) ? $data['sorteo']['porcentaje_comision'] : null;

        $fecha_format = new \DateTime($fecha_sorteo);
        /* Lo divido por el 100% para obtener el porcentaje de comision */
        $porcentaje_comision = $porcentaje_comision / 100;

        $rifa = $rifaRepository->getRifaById($rifa_id);
        $res = new Sorteo();
        if ($rifa != null) {
            $sorteo = new Sorteo();
            $sorteo
                ->setSorteoNumero($numero_sorteo)
                ->setFechaSorteo($fecha_format)
                ->setNumeroFinalTalon($numero_final_talon)
                ->setNumeroInicialTalon($numero_inicial_talon)
                ->setRifa($rifa)
                ->setLugar($lugar)
                ->setValorTalon($talon_valor)
                ->setPorcentajePremio($porcentaje_comision);

            // Verificar que el sorteo no este creado para esa RIFA
            $sorteo_return = null;
            $sorteos = $rifa->getSorteos();
            foreach ($sorteos as $sor) {

                if ($sor->getSorteoNumero() == $numero_sorteo) {
                    $sorteo_return = $sor;
                }
            }
            if ($sorteo_return) {

                return new JsonResponse([
                    'status' => 'error',
                    'code' => 400,
                    'message' => ' Ya existe el sorteo con ese numero ',
                    'data' => $sorteo_return
                ], 200);
            } else {
                $res = $sorteoRepository->saveSorteo($sorteo);
                return new JsonResponse([
                    'status' => 'success', 'code' => 200,
                    'message' => 'Se creo correctamente', 'data' => $res
                ], 200);
            }

            $res = $sorteoRepository->saveSorteo($sorteo);
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se creo correctamente', 'data' => $res], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro la Rifa.' . $rifa], 400);
        }
    }

    /**
     * @Route("api/sorteo/editar", name="edit-sorteo", methods={"POST"})
     */
    public function editSorteo(Request $request, SorteoRepository $sorteoRepository, TalonRepository $talonRepository)
    {

        $data = json_decode($request->getContent(), true);
        $id = (isset($data['sorteo']['id'])) ? $data['sorteo']['id'] : null;
        $fecha_sorteo = (isset($data['sorteo']['fecha_sorteo'])) ? $data['sorteo']['fecha_sorteo'] : null;
        $rifa_id = (isset($data['sorteo']['rifa']['id'])) ? $data['sorteo']['rifa']['id'] : null;
        $numero_sorteo = (isset($data['sorteo']['numero_sorteo'])) ? $data['sorteo']['numero_sorteo'] : null;
        $lugar = (isset($data['sorteo']['lugar'])) ? $data['sorteo']['lugar'] : null;
        $numero_inicial_talon = (isset($data['sorteo']['numero_inicial_talon'])) ? $data['sorteo']['numero_inicial_talon'] : null;
        $numero_final_talon = (isset($data['sorteo']['numero_final_talon'])) ? $data['sorteo']['numero_final_talon'] : null;
        $talon_valor = (isset($data['sorteo']['talon_valor'])) ? $data['sorteo']['talon_valor'] : null;
        $porcentaje_comision = (isset($data['sorteo']['porcentaje_comision'])) ? $data['sorteo']['porcentaje_comision'] : null;

        $fecha_format = new \DateTime($fecha_sorteo);
        /* Lo divido por el 100% para obtener el porcentaje de comision */
        $porcentaje_comision = $porcentaje_comision / 100;

        $existe_talones = false;

        if ($id != null) {
            $sorteo = $sorteoRepository->getSorteoById($id);
            if ($sorteo) {

                // Verifica que el sorteo tengan talones
                if (count($sorteo->getTalones()) > 0) {
                    $existe_talones = true;
                    // verificar que el valor nuevo rango de numeros concuerte con los talones entregados hasta el momento
                    foreach ($sorteo->getTalones() as $talon) {

                        if ($talon->getNumero() < $numero_inicial_talon || $talon->getNumero() > $numero_final_talon) {
                            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El rango de numero es incorrecto.'], 400);
                        }
                    }
                }

                $sorteo->setFechaSorteo($fecha_format);
                $sorteo->setLugar($lugar);
                $sorteo->setNumeroInicialTalon($numero_inicial_talon);
                $sorteo->setNumeroFinalTalon($numero_final_talon);
                $sorteo->setValorTalon($talon_valor);
                $sorteo->setPorcentajePremio($porcentaje_comision);

                $res = $sorteoRepository->updateSorteo($sorteo);

                //Actualizar talones si hay entregados para ese sorteo
                if ($existe_talones) {
                    $recaudado = $talon_valor - ($talon_valor * $porcentaje_comision);
                    $talonRepository->updateTalonesByIdSorteo($sorteo, $fecha_format, $talon_valor, $recaudado);
                }

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se Actualizo correctamente', 'data' => $res], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El Sorteo no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/sorteo/delete", name="delete-sorteo", methods={"POST"})
     */
    public function deleteSorteo(Request $request, SorteoRepository $sorteoRepository)
    {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['id'])) ? $data['id'] : null;

        if ($id != null) {
            $sorteo = $sorteoRepository->findOneBy(
                array(
                    "id" => $id
                )
            );

            // Verificar que los sorteos no tengan talones

            if (count($sorteo->getTalones()) > 0) {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El sorteo tiene talones asociados.'], 400);
            }

            $sorteoRepository->deleteSorteo($sorteo);
            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se elimino corretamente', 'data' => 'id borrado: ' . $id], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El Sorteo no existe.'], 400);
        }
    }

    /**
     * @Route("sorteo/get-datos-by-id", name="get-sorteo-datos-by-id", methods={"POST"})
     */
    public function getSorteoDatos(Request $request, SorteoRepository $sorteoRepository)
    {
        $data = json_decode($request->getContent(), true);
        $id = (isset($data['id'])) ? $data['id'] : null;
        if ($id != null) {

            $sorteo = $sorteoRepository->getSorteoById($id);
            if ($sorteo) {

                $res = array(
                    'id' => $sorteo->getId(),
                    'rifa_id' => $sorteo->getRifa()->getId(),
                    'numero_sorteo' => $sorteo->getSorteoNumero(),
                    'numero_inicial_talon' => $sorteo->getNumeroInicialTalon(),
                    'numero_final_talon' => $sorteo->getNumeroFinalTalon(),
                    'fecha_sorteo' => $sorteo->getFechaSorteo(),
                    'lugar' => $sorteo->getLugar(),
                    'talon_valor' => $sorteo->getValorTalon(),
                    'porcentaje_comision' => $sorteo->getPorcentajePremio(),
                );

                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Sorteo encontrado', 'data' => $res], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La Sorteo no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }

    /**
     * @Route("/api/soteo/get-sorteo-by-rifa", name="get-sorteos-byRifa", methods={"POST"})
     */
    public function getSorteosByIdRifa(
        Request $request,
        RifaRepository $rifaRepository,
        SorteoRepository $sorteoRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $rifaId = (isset($data['rifa'])) ? $data['rifa'] : null;

        $rifa = $rifaRepository->findOneBy(
            array('id' => $rifaId)
        );
        if ($data != null) {
            if ($rifa) {
                $sorteos = $sorteoRepository->sorteosByRifa($rifa);
                $data = [];
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Sorteos de la Rifa', 'data' => $sorteos], 200);
            } else {

                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La Rifa no existe.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
        }
    }
}
