<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\User\UserTools;
use App\Service\Mail\JWTService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/signup', name: 'app_register')]
    public function register(
        UserTools $userTools,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $authenticator,
        UserAuthenticator $formAuthenticator
    ): Response {
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

            $userTools->createUser($user);

            return $authenticator->authenticateUser(
                $user,
                $formAuthenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/registerValidate', name: 'app_register_validate')]
    public function validateRegisterUser()
    {
        return $this->render('registration/validate_user.html.twig', []);
    }

    #[Route('/confirmEmail/{token}', name: 'app_verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em)
    {
        if ($jwt->isValid($token) and !$jwt->isExpired($token) and $jwt->check($token, $this->getParameter('jwt_secret'))) {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);
            if ($user and !$user->isConfirmed()) {
                $user->setConfirmed(1);
                $em->flush($user);
                $this->addFlash('success', 'Votre compte a été validé !');
                return $this->redirectToRoute('app_home');
            }
            $this->addFlash('danger', 'Le lien est invalide ou a expiré');
            return $this->redirectToRoute('app_login');
        }
        $this->addFlash('danger', 'Le lien est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
