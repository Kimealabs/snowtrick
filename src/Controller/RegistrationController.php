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
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $userTools->createUser($user);
            $userTools->sendToken($user, 'Vérification du compte', 'register');

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

    #[Route('/validAccount/{token}', name: 'app_verify_user')]
    public function verifyUser(string $token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em)
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('jwt_secret'))) {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);
            if ($user && !$user->isConfirmed()) {
                $user->setConfirmed(1);
                $em->flush($user);
                $this->addFlash('success', 'Votre compte a été validé !');
                return $this->redirectToRoute('app_profile');
            }
            $this->addFlash('danger', 'Le lien est invalide');
            return $this->redirectToRoute('app_home');
        }
        if (!$jwt->isValid($token) || !$jwt->check($token, $this->getParameter('jwt_secret'))) {
            $this->addFlash('danger', 'Le lien est invalide');
        } elseif (!$jwt->isExpired($token)) $this->addFlash('danger', 'Le lien a expiré !');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/resendValidationAccount', name: 'app_resend_validation')]
    public function resendValidationRequest(UserTools $userTools)
    {
        if ($user = $this->getUser()) {
            if (!$user->isConfirmed()) {
                $userTools->sendToken($user, 'Renvoi de vérification du compte', 'register');
                return $this->redirectToRoute('app_register_validate');
            }
        }
        return $this->redirectToRoute('login_home');
    }
}
