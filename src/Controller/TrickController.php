<?php

namespace App\Controller;

use App\Entity\Trick;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/trick/{slug}', name: 'app_trick')]
    public function index(Trick $trick): Response
    {
        return $this->render('trick/index.html.twig', [
            'trick' => $trick
        ]);
    }
}
