<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\Mail\SendMail;
use App\Service\Mail\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserTools extends AbstractController
{
    public function __construct(EntityManagerInterface $em, SendMail $mail, JWTService $jwt)
    {
        $this->em = $em;
        $this->mail = $mail;
        $this->jwt = $jwt;
    }

    public function createUser(User $user): User
    {
        $user->setCreatedAt(new \DateTimeImmutable('NOW'));
        $user->setConfirmed(0);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function sendToken(User $user, string $title, string $template): void
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload = [
            'user_id' => $user->getId()
        ];

        $token = $this->jwt->generate($header, $payload, $this->getParameter('jwt_secret'));

        $this->mail->send(
            'registration@snowtrick-noreply',
            $user->getEmail(),
            $title,
            $template,
            compact('user', 'token')
        );
    }
}
