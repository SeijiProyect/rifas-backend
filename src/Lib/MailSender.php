<?php

namespace App\Lib;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

use App\Templatemiller\Templates;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class MailSender
 * @package App\Lib
 */
class MailSender
{

    private $mailer;
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMail($data)
    {
        $transport = new SocketStream();
        $transport->setTimeout(60);

        $templates = new Templates();
        $useTemplate = $data['typeTemplate'];

        if ($useTemplate != 'error') {

           // return new JsonResponse(['status' => 'error', 'code' => 300, 'message' => 'DENTRO de send email OK'], 300);

            $email = (new Email())
                ->from(new Address($data['fromAddress'], $data['fromName']))
                ->to($data['to'])
                ->subject($data['asunto'])
                ->text('Sending emails is fun again!')
                ->html($templates->$useTemplate->template($data['dataEmail']));

            $this->mailer->send($email);
        }

        // TEST DE ENVIO DE EMAIL

        /* $email = (new Email())
        ->from(new Address($data['fromAddress'], $data['fromName']))
        ->to($data['to'])
        ->subject($data['asunto'])
        ->text('Sending emails is fun again!')
        ->html('<H1> test email </H1>');

        $this->mailer->send($email);*/
    }
}
