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
        $this->denyAccessUnlessGranted('only_not_connected', $this->getUser());

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

    // MESSAGE PAGE OF SENDING VALIDATION EMAIL
    #[Route('/registerValidate', name: 'app_register_validate')]
    public function validateRegisterUser()
    {
        $this->denyAccessUnlessGranted('only_connected_confirmed', $this->getUser());
        return $this->render('registration/validate_user.html.twig', []);
    }

    // LINK INTO VERIFICATION EMAIL POINT THIS PAGE
    #[Route('/validAccount/{token}', name: 'app_verify_user')]
    public function verifyUser(string $token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em)
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('jwt_secret'))) {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);
            if ($user && !$user->isConfirmed()) {
                $user->setConfirmed(1);
                $em->flush($user);
                $this->addFlash('success', 'Your account is verified !');
                return $this->redirectToRoute('app_profile');
            }
            $this->addFlash('danger', 'Invalid link');
            return $this->redirectToRoute('app_home');
        }
        if (!$jwt->isValid($token) || !$jwt->check($token, $this->getParameter('jwt_secret'))) {
            $this->addFlash('danger', 'Invalid link');
        } elseif (!$jwt->isExpired($token)) $this->addFlash('danger', 'The link has expired !');
        return $this->redirectToRoute('app_home');
    }

    // RESEND OF VALIDATION LINK
    #[Route('/resendValidationAccount', name: 'app_resend_validation')]
    public function resendValidationRequest(UserTools $userTools)
    {
        $this->denyAccessUnlessGranted('only_connected_not_confirmed', $this->getUser());
        if ($user = $this->getUser()) {
            if (!$user->isConfirmed()) {
                $userTools->sendToken($user, 'Resend account verification', 'register');
                return $this->redirectToRoute('app_register_validate');
            }
        }
        return $this->redirectToRoute('login_home');
    }
}
