<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class MailerServicefl
{
    public function __construct(MailerInterface $mailer)
    {
    }

    public function sendEmail(
        $to = 'fr.libs@gmail.com',
        $content = '<p>See Twig integration for better HTML integration!</p>',
        $subject = 'Time for Symfony Mailer!'
    ): void
    {
        $email = (new Email())
            ->from($to)
            ->to($to)
            ->subject($subject)
            ->html($content);

        $this->mailer->send($email);

        // ...
    }
}
