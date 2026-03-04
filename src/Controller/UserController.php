<?php

namespace App\Controller;

use DateInterval;

use App\Entity\User;
use Firebase\JWT\JWT;
use DateTimeImmutable;
use App\Lib\MailSender;
use App\Entity\ResetCodes;
use App\Repository\UserRepository;
use App\Security\JwtAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ForgotPasswordRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/reset-link", name="reset-link", methods={"POST"})
     */
    public function sendResetLink(
        Request $request,
        ForgotPasswordRepository $forgotPasswordRepository,
        UserRepository $userRepository,
        MailSender $mailSender
    ) {
        // Imprimir línea simple
        /* $output->writeln('¡Hola, este es un mensaje en la terminal!');

        // Imprimir con formato (color verde/info, amarillo/comment, rojo/error)
        $output->writeln('<info>Mensaje de éxito</info>');*/
        /*$mensaje = "¡Hola, este es un mensaje en la terminal!";
        var_dump($mensaje);
        exit;*/

        $data = json_decode($request->getContent(), true);
        $email = (isset($data['email'])) ? $data['email'] : null;

        $user = $userRepository->findOneBy(
            array(
                "email" => $email
            )
        );

        if ($user != null) {

            $date = new \DateTime('now');
            $date->add(new \DateInterval('PT24H'));

            $token = array(
                "email" => $email,
                $date
            );

            $six_digit_random_number = mt_rand(100000, 999999);
            $encryptedLink = md5(serialize($token) . $six_digit_random_number);

            $forgotPasswordLink = $forgotPasswordRepository->findOneBy(
                array(
                    "User" => $user
                ),
                array('id' => 'DESC'),
                1,
                0
            );

            if ($forgotPasswordLink == null) {
                $forgotPasswordLink = $forgotPasswordRepository->save($user, $encryptedLink, $date);
            } else {
                $forgotPasswordLink->setUser($user);
                $forgotPasswordLink->setToken($encryptedLink);
                $forgotPasswordLink->setExpire($date);

                $forgotPasswordLink = $forgotPasswordRepository->update($forgotPasswordLink);
            }

            $responseMailer = array(
                'asunto' => $this->getParameter('businessNameShort') . ' - Resetear contraseña',
                'fromAddress' => $this->getParameter('fromAddress'),
                'fromName' => $this->getParameter('businessName'),
                'to' => $email,
                'typeTemplate' => 'forgotPasswordMail',
                'dataEmail' => array(
                    'nombrePasajero' => $user->getPersona()->getNombres(),
                    'token' => $encryptedLink
                )
            );

            //return new JsonResponse(['status' => 'success', 'code' => 200, 'message' =>  $responseMailer ], 200);

            //return new JsonResponse(['status' => 'error', 'code' => 300, 'message' => 'Verifique su casilla de correo para obtener instrucciones sobre cómo recuperar su contraseña.'], 300);
            
            $retorno = $mailSender->sendMail($responseMailer);

             return new JsonResponse(['status' => 'success', 'code' => 200, 'message' =>  'Verifique su casilla de correo para obtener instrucciones sobre cómo recuperar su contraseña.' ], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'Usuario no registrado'], 404);
        }
    }

    /**
     * @Route("/reset-password", name="reset-password", methods={"POST"})
     */
    public function resetPassword(
        Request $request,
        ForgotPasswordRepository $forgotPasswordRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $data = json_decode($request->getContent(), true);
        $token = (isset($data['token'])) ? $data['token'] : null;
        $password = (isset($data['password'])) ? $data['password'] : null;

        if ($token != '' && $token != null) {
            $forgotPassword = $forgotPasswordRepository->findOneBy(array(
                "Token" => $token
            ));

            if ($forgotPassword) {
                $date = $forgotPassword->getExpire();
                $now = new \DateTime();
                $user = $forgotPassword->getUser();

                if ($date > $now) {
                    $pattern = '/^(?=.*[A-Z])(?=.*[!@#$&*.])(?=.*[0-9])(?=.*[a-z]).{8,}$/';

                    if (preg_match($pattern, $password)) {

                        $hashedPassword = $passwordHasher->hashPassword(
                            $user,
                            $password
                        );

                        $userRepository->upgradePassword($user, $hashedPassword);
                        $forgotPasswordRepository->delete($forgotPassword);

                        return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Link correcto'], 200);
                    } else {
                        return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'La contraseña es incorrecta'], 400);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El link expiró, intente resetar nuevamente.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'No existe'], 404);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'Parámetros incompletos'], 404);
        }
    }

    /**
     * @Route("/verify-reset-link", name="verify-reset-link", methods={"POST"})
     */
    public function verifyResetLink(
        Request $request,
        ForgotPasswordRepository $forgotPasswordRepository
    ) {
        $data = json_decode($request->getContent(), true);
        $token = (isset($data['token'])) ? $data['token'] : null;

        if ($token != '' && $token != null) {
            $forgotPassword = $forgotPasswordRepository->findOneBy(array(
                "Token" => $token
            ));

            if ($forgotPassword) {
                $date = $forgotPassword->getExpire();
                $now = new \DateTime('now');

                if ($date > $now) {
                    return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Link correcto'], 200);
                } else {
                    return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'El link expiró, intente resetar nuevamente.'], 400);
                }
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 200, 'message' => 'No existe'], 200);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'Parámetros incompletos'], 404);
        }
    }

    /**
     * @Route("/user/reset-code", name="reset-code", methods={"POST"})
     */
    public function sendResetCode(
        Request $request,
        ForgotPasswordRepository $forgotPasswordRepository,
        UserRepository $userRepository,
        MailSender $mailSender
    ) {
        $data = json_decode($request->getContent(), true);
        $email = (isset($data['email'])) ? $data['email'] : null;

        $user = $userRepository->findOneBy(
            array(
                "email" => $email
            )
        );

        if ($user != null) {
            $numeroAleatorio = random_int(100000, 999999);
            $reset_codes = new ResetCodes();
            $reset_codes->setUser($user);
            $reset_codes->setCode(strval($numeroAleatorio));
            $reset_codes->setIsUsed(false);
            $dateAux = date('Y-m-d H:i:s');
            $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
            $reset_codes->setCreatedAt($date_fecha_actual);
            $date_fecha_expirar = $date_fecha_actual->add(new DateInterval('PT3H'));
            $reset_codes->setExpiredAt($date_fecha_expirar);

            $this->em->persist($reset_codes);
            $this->em->flush();

            $responseMailer = array(
                'asunto' => 'TyT - Resetear contraseña',
                'fromAddress' => 'seijitsumura1985@gmail.com',
                'fromName' => 'TyT',
                'to' => $email,
                'typeTemplate' => 'forgotPasswordMailCode',
                'dataEmail' => array(
                    'nombrePasajero' => $user->getPersona()->getNombres(),
                    'code' => $numeroAleatorio
                )
            );

            $mailSender->sendMail($responseMailer);

            return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Verifique su casilla de correo para obtener instrucciones sobre cómo recuperar su contraseña.'], 200);
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'Usuario no registrado'], 404);
        }
    }

    /**
     * @Route("/user/verify-reset-code", name="verify-reset-code", methods={"POST"})
     */
    public function verifyResetCode(
        Request $request
    ) {

        $data = json_decode($request->getContent(), true);
        $data = json_decode($request->getContent(), true);
        $email = (isset($data['email'])) ? $data['email'] : null;
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(
            array(
                "email" => $email
            )
        );

        if ($user != null) {
            $resetCodesRepository = $this->em->getRepository(ResetCodes::class);
            /** @var \App\Repository\ResetCodes $resetCodesRepository */
            $query = $resetCodesRepository->createQueryBuilder('rc')
                ->where('rc.code = :code')
                ->andWhere('rc.user = :user_id')
                ->setParameter('code', $data['code'])
                ->setParameter('user_id', $user->getId())
                ->getQuery();


            $reset_code = $query->getResult();

            if ($reset_code && count($reset_code) > 0) {
                $payload = [
                    "user" => $user->getEmail(),
                    "exp"  => (new \DateTime())->modify("+100 hours")->getTimestamp(),
                ];
                $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
                $token = sprintf($jwt);

                $return_data = [
                    'token' => $token
                ];
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'El codigo ingresado es correcto.', 'data' => $return_data], 200);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro un reset code para ese usuario y codigo.'], 400);
            }
        } else {
            return new JsonResponse(['status' => 'error', 'code' => 404, 'message' => 'Usuario no registrado.'], 404);
        }
    }

    /**
     * @Route("/user/reset-password-app", methods={"POST"}, name="reset_password_app")
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPasswordApp(Request $request, JwtAuthenticator $jwtAutheticator): JsonResponse
    {
        $auth = $request->headers->get('Authorization');
        if ($auth) {
            $user = $jwtAutheticator->getAppUser($auth);
            if (is_array($user)) {
                $data = json_decode($request->getContent(), true);
                $email_user = $user['user'];
                $userRepository = $this->em->getRepository(User::class);
                $user_aux = $userRepository->findOneBy(
                    array(
                        "email" => $email_user
                    )
                );
                $options = [
                    'cost' => 12,
                ];
                $password = password_hash($data["password"], PASSWORD_BCRYPT, $options);
                $user_aux->setPassword($password);
                $this->em->flush();
                return new JsonResponse(['status' => 'success', 'code' => 200, 'message' => 'Se ha actualizado la contraseña correctamente.'], 400);
            } else {
                return new JsonResponse(['status' => 'error', 'code' => 400, 'message' => 'No se encontro el usuario.'], 400);
            }
        }
    }
}
