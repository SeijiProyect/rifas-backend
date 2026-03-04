<?php

namespace App\Controller;

use App\Entity\Trayecto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Ciudad;
use App\Entity\Transporte;

class TrayectoController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/trayecto", name="app_trayecto")
     */
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TrayectoController.php',
        ]);
    }

    /**
     * @Route("/trayecto/get_list", methods={"GET"}, name="trayecto_list")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $trayectoRepository = $this->em->getRepository(Trayecto::class);
        $trayectos = $trayectoRepository->findAll();

        $data = [];

        foreach ($trayectos as $trayecto) {
            $data[] = [
                'id' => $trayecto->getId(),
                'nombre' =>  $trayecto->getCiudadInicio()->getNombre() . ' - ' .  $trayecto->getCiudadFin()->getNombre(),
            ];
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data], 200);
    }

    /**
     * @Route("/trayecto/create", methods={"POST"}, name="trayecto_create")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->em;

        $data = json_decode($request->getContent(), true);

        $trayecto = new Trayecto();
        if (isset($data['trayecto']['ciudad_inicio'])) {
            $ciudadInicioRepository = $this->em->getRepository(Ciudad::class);
            $ciudadInicio = $ciudadInicioRepository->find($data['trayecto']['ciudad_inicio']);
            $trayecto->setCiudadInicio($ciudadInicio);
        }
        if (isset($data['trayecto']['ciudad_fin'])) {
            $ciudadFinRepository = $this->em->getRepository(Ciudad::class);
            $ciudadFin = $ciudadFinRepository->find($data['trayecto']['ciudad_fin']);
            $trayecto->setCiudadFin($ciudadFin);
        }
        $entityManager->persist($trayecto);
        $entityManager->flush();

        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El trayecto se creo correctamente'], 200);
    }

    /**
     * @Route("/trayecto/update/{id}", methods={"PUT", "PATCH"}, name="trayecto_update")
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $trayectoRepository = $this->em->getRepository(Trayecto::class);
        $trayecto = $trayectoRepository->find($id);

        if (!$trayecto) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el trayecto'], 400);
        }

        if (isset($data['trayecto']['ciudad_inicio'])) {
            $ciudadInicioRepository = $this->em->getRepository(Ciudad::class);
            $ciudadInicio = $ciudadInicioRepository->find($data['trayecto']['ciudad_inicio']);
            $trayecto->setCiudadInicio($ciudadInicio);
        }
        if (isset($data['trayecto']['ciudad_fin'])) {
            $ciudadFinRepository = $this->em->getRepository(Ciudad::class);
            $ciudadFin = $ciudadFinRepository->find($data['trayecto']['ciudad_fin']);
            $trayecto->setCiudadFin($ciudadFin);
        }

        $this->em->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El trayecto se actualizo correctamente'], 200);
    }

    /**
     * @Route("/trayecto/delete/{id}", methods={"DELETE"}, name="trayecto_delete")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $trayectoRepository = $this->em->getRepository(Trayecto::class);
        $trayecto = $trayectoRepository->find($id);

        if (!$trayecto) {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro registro de trayecto'], 400);
        }

        //CHECK TRANSPORTE
        $transporteRepository = $this->em->getRepository(Transporte::class);

        $query = $transporteRepository->createQueryBuilder('t')
            ->where('t.trayecto_id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        $transporte = $query->getResult();

        if ($transporte && count($transporte) > 0) {
            return new JsonResponse(['status' => 'error', 'code' => 401, 'message' => 'El trayecto se encuentra asociado a un transporte, no es posible eliminarla.'], 400);
        }

        $this->em->remove($trayecto);
        $this->em->flush();

        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El trayecto se elimino correctamente'], 200);
    }
}
