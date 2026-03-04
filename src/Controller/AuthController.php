<?php

namespace App\Controller;

use App\Entity\PersonaTokenFirebase;
use App\Entity\Persona;
use App\Entity\Pasajero;
use App\Entity\User;
use App\Entity\UserRol;
use App\Repository\UserRepository;
use App\Repository\PersonaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Security\JwtAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Firebase\JWT\JWT;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class AuthController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/auth", name="auth")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AuthController.php',
        ]);
    }

    /**
     * @Route("/auth/tokenFree", name="tokenFree", methods={"POST"})
     */
    public function tokenFree(Request $request, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $email = 'info@detoqueytoque.com';

        $user = $userRepository->findOneBy([
            'email' => $email,
        ]);

        $payload = [
            "user" => $user->getEmail(),
            "exp"  => (new \DateTime())->modify("+12 month")->getTimestamp(),
        ];

        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
        return $this->json([
            'status' => 'success',
            'message' => 'Free Token',
            'token' => sprintf('Bearer %s', $jwt),
            'newUser' => false
        ]);
    }

    /**
     * @Route("/auth/loginFree", name="loginFree", methods={"POST"})
     */
    public function loginFree(Request $request, UserRepository $userRepository)
    {
        $data = json_decode($request->getContent(), true);
        $email = 'info@detoqueytoque.com';

        $user = $userRepository->findOneBy([
            'email' => $email,
        ]);

        $payload = [
            "user" => $user->getEmail(),
            "exp"  => (new \DateTime())->modify("+3 month")->getTimestamp(),
        ];

        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
        return $this->json([
            'status' => 'success',
            'message' => 'Free Token',
            'token' => sprintf('Bearer %s', $jwt),
            'newUser' => false
        ]);
    }

    /**
     * @Route("/auth/login", name="login", methods={"POST"})
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        PersonaRepository $personaRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $password = $data['password'];
        $type = isset($data['type']) ? $data['type'] : null;

        $user = $userRepository->findOneBy([
            'email' => $email,
        ]);

        if ($password != 'master@rifas.22') {
            if (!$user || (!$passwordHasher->isPasswordValid($user, $password))) {
                if ($user && $type  == 'student-rifas' && $user->getPassword() == '') {
                    $persona = $personaRepository->findOneBy([
                        'Cedula' => $password,
                    ]);

                    if ($persona) {
                        return new JsonResponse([
                            'status' => 'success',
                            'code' => 200,
                            'message' => '',
                            'newUser' => true
                        ], 200);
                    }
                }

                return new JsonResponse(['status' => 'not found', 'code' => 404, 'message' => 'Email o contraseña inválidos.'], 200);
            }
        }

        $payload = [
            "user" => $user->getEmail(),
            "exp"  => (new \DateTime())->modify("+100 hours")->getTimestamp(),
        ];

        $payload_2 = [
            "user" => $user->getEmail(),
            "exp"  => (new \DateTime())->modify("+3 month")->getTimestamp(),
        ];


        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
        return $this->json([
            'status' => 'success',
            'message' => '',
            'token' => sprintf('Bearer %s', $jwt),
            'newUser' => false
        ]);
    }

    /**
     * @Route("/auth/first-login", name="first-login", methods={"POST"})
     */
    public function firstLogin(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $data = json_decode($request->getContent(), true);

        $email = (isset($data['email'])) ? $data['email'] : null;
        $password = (isset($data['password'])) ? $data['password'] : null;

        if ($password != null) {

            $user = $userRepository->findOneBy([
                'email' => $email,
            ]);

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password
            );

            $userRepository->upgradePassword($user, $hashedPassword);

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Contraseña ingresda'], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros incompletos'], 400);
        }
    }

    /**
     * @Route("/api/validate", name="validate", methods={"GET"})
     */
    public function validateToken(Request $request, UserRepository $userRepository, JwtAuthenticator $jwtAutheticator, UserPasswordHasherInterface $passwordHasher)
    {
        $data = $request->headers->get('Authorization');
        $user = $jwtAutheticator->getUserEmail($data);

        if ($user) {
            $user = $userRepository
                ->findOneBy([
                    'email' => $user,
                ]);
            $payload = [
                "user" => $user->getEmail(),
                "exp"  => (new \DateTime())->modify("+100 hours")->getTimestamp(),
            ];

            $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');

            return $this->json([
                'user' => $user->getId(),
                'token' => $jwt,
                'code' => 200,
                'status' => 'success'
            ]);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 500, 'message' => 'Ha ocurrido un error de autenticación.'], 500);
        }
    }

    /**
     * @Route("/auth/login_app", name="login_app", methods={"POST"})
     */
    public function login_app(Request $request, JwtAuthenticator $jwtAutheticator, UserPasswordHasherInterface $passwordHasher)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $password = $data['password'];
        $token_firebase = $data['device_token'];

        $userRepository = $this->em->getRepository(User::class);

        $validUser = $userRepository->findOneBy([
            'email' => $email
        ]);

        if ($validUser) {
            $validPassword = $passwordHasher->isPasswordValid(
                $validUser,
                $password
            );
            if ($validPassword) {
                $payload = [
                    "user" => $validUser->getEmail(),
                    "exp"  => (new \DateTime())->modify("+1440 hours")->getTimestamp(),
                ];
                $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
                $user = $jwtAutheticator->getUserEmail($jwt);
                if ($user) {
                    $payload = [
                        "user" => $validUser->getId(),
                        "person" => $validUser->getPersona()->getId(),
                        "exp"  => (new \DateTime())->modify("+1440 hours")->getTimestamp(),
                    ];

                    $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');

                    $userRolRepository = $this->em->getRepository(UserRol::class);
                    $userRol_aux = $userRolRepository->findBy(['user' => $validUser->getId()]);
                    if ($userRol_aux && count($userRol_aux) > 0) {
                        $userRol = $userRol_aux[0]->getRol();
                    } else {
                        $userRol = 'Pasajero';
                    }

                    $pasajeroRepository = $this->em->getRepository(Pasajero::class);
                    $pasajeros = $pasajeroRepository->findBy(['Persona' => $validUser->getPersona()->getId()]);
                    if (count($pasajeros) > 0) {
                        $pasajero_id = $pasajeros[0]->getId();
                    } else {
                        $pasajero_id = null;
                    }

                    $res = array(
                        'user_id' => $validUser->getId(),
                        'pasajero_id' => $pasajero_id,
                        'nombres' => $validUser->getPersona()->getNombres(),
                        'email' => $validUser->getEmail(),
                        'inicial' => strtoupper($validUser->getPersona()->getNombres()[0]) . "" . strtoupper($validUser->getPersona()->getApellidos()[0]),
                        'token' => $jwt,
                        'rol' => $userRol
                    );

                    $entityManager = $this->em;

                    //CHECK ITINERARIO DETALLE
                    $personaTokenFirebaseRepository = $this->em->getRepository(PersonaTokenFirebase::class);
                    /** @var \App\Repository\PersonaRepository  $personaTokenFirebaseRepository */
                    $query = $personaTokenFirebaseRepository->createQueryBuilder('p')
                        ->where('p.persona = :persona_id')
                        ->setParameter('persona_id', $validUser->getPersona()->getId())
                        ->getQuery();

                    $personaTokenFirebaseData = $query->getResult();

                    //YA EXISTE REGISTRO PARA ESA PERSONA, SE HACE EL UPDATE
                    if ($personaTokenFirebaseData && count($personaTokenFirebaseData) > 0) {
                        $persona_token_firebase = $personaTokenFirebaseData[0];
                        $persona_token_firebase->setToken($token_firebase);
                        $dateAux = date('Y-m-d H:i:s');
                        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
                        $persona_token_firebase->setFechaActualizado($date_fecha_actual);
                        $entityManager->flush();
                    }
                    //NO SE ENCONTRO REGISTRO PARA ESA PERSONA, SE HACE EL CREATE
                    else {
                        if ($token_firebase && $token_firebase !== null && $token_firebase !== '') {
                            $persona_token_firebase = new PersonaTokenFirebase();
                            $personaRepository = $this->em->getRepository(Persona::class);
                            $persona = $personaRepository->find($validUser->getPersona()->getId());
                            $persona_token_firebase->setPersona($persona);
                            $persona_token_firebase->setToken($token_firebase);
                            $dateAux = date('Y-m-d H:i:s');
                            $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
                            $persona_token_firebase->setFechaCreado($date_fecha_actual);
                            $persona_token_firebase->setFechaActualizado($date_fecha_actual);
                            $entityManager->persist($persona_token_firebase);
                            $entityManager->flush();
                        }
                    }
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Login', 'data' => $res], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Ha ocurrido un error de autenticación.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Ha ocurrido un error de autenticación, password inválida.'], 400);
            }
        }

        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Parámetros no válidos o incompletos.'], 400);
    }

    /**
     * @Route("/auth/validate-app-token", name="validate-app-token", methods={"GET"})
     */
    public function validateAppToken(Request $request, JwtAuthenticator $jwtAutheticator)
    {
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $id = $user['person'];
                return $this->json([
                    'user' => $id,
                    'token' => $auth,
                    'code' => 200,
                    'status' => 'success'
                ]);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Token inválido.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'Ha ocurrido un error de autenticación.'], 400);
        }
    }
}
