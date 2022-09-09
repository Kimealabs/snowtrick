<?php

namespace App\Controller;

use App\Service\User\UserTools;
use Doctrine\ORM\EntityManager;
use App\Service\Mail\JWTService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use App\Form\ForgottenPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/signin', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/forgottenPassword', name: 'app_forgotten_password')]
    public function forgottenPassword(Request $request, UserTools $userTools, UserRepository $userRepo): Response
    {
        $this->denyAccessUnlessGranted('connected', $this->getUser());

        $form = $this->createForm(ForgottenPasswordFormType::class);
        $form->handleRequest($request);
        $message = '';
        if ($form->isSubmitted() and $form->isValid()) {
            $user = $userRepo->findOneByUsername($form->get('username')->getData());
            if ($user) {
                $message = ['type' => 'success', 'label' => 'The email with reset password link is sending !'];
                $userTools->sendToken($user, 'Reset password', 'forgottenpassword');
            } else {
                $message = ['type' => 'danger', 'label' => 'Oupssss, a problem blow up !'];
            }
        }
        return $this->render('security/forgottenpassword.html.twig', [
            'message' => $message,
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/resetPassword/{token}', name: 'app_reset_password')]
    public function resetPassword(Request $request, string $token, JWTService $jwt, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em)
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('jwt_secret'))) {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);
            if ($user) {
                $form = $this->createForm(ResetPasswordFormType::class);
                $form->handleRequest($request);
                $message = '';
                if ($form->isSubmitted() and $form->isValid()) {
                    $message = ['type' => 'danger', 'label' => 'Oupss, an problem blow up !'];
                    $user = $userRepository->findOneByUsername($form->get('username')->getData());
                    if ($user) {
                        $user->setPassword(
                            $userPasswordHasher->hashPassword(
                                $user,
                                $form->get('password')->getData()
                            )
                        );
                        $em->flush();
                        $this->addFlash('success', 'Your Password is updated !');
                        return $this->redirectToRoute('app_home');
                    }
                }
                return $this->render('security/resetpassword.html.twig', [
                    'form' => $form->createView(),
                    'message' => $message
                ]);
            }
            $this->addFlash('danger', 'Invalid link');
            return $this->redirectToRoute('app_reset_password');
        }
        if (!$jwt->isValid($token) || !$jwt->check($token, $this->getParameter('jwt_secret'))) {
            $this->addFlash('danger', 'Invalid Link');
        } elseif (!$jwt->isExpired($token)) $this->addFlash('danger', 'The link has expired !');
        return $this->redirectToRoute('app_reset_password');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->denyAccessUnlessGranted('connected', $this->getUser());

        $this->addFlash(
            'success',
            'Great, You are disconnected !'
        );
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
