<?php

namespace App\Controller;

use App\Entity\SecurityToken;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/signup', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $message = '';
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setCreatedAt(new \DateTimeImmutable('NOW'));
            $user->setConfirmed(0);
            $entityManager->persist($user);

            $token = new SecurityToken();
            $token->setConsumer($user);
            $entityManager->persist($token);

            $entityManager->flush();


            // do anything else you need here, like send an email
            $message = "<p><b>Bravo !</b></p><p class='mt-4'>Vous allez recevoir dans un instant un email contenant un lien afin de valider votre adresse email et finaliser votre inscription.</p>";
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'message' => $message
        ]);
    }
}
