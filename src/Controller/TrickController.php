<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TrickRepository $tricks): Response
    {
        $tricks = $tricks->findAll();
        return $this->render('home/index.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    #[Route('/trick/{slug}', name: 'app_trick')]
    public function trick(Trick $trick): Response
    {
        return $this->render('trick/index.html.twig', [
            'trick' => $trick
        ]);
    }
}
