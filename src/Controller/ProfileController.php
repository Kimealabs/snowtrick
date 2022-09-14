<?php

namespace App\Controller;

use App\Security\Voter\UserVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    #[Route('/compte', name: 'app_profile')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::CONNECTED, $this->getUser());
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }
}
