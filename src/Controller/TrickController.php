<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Trick;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TrickRepository $tricks): Response
    {
        $tricks = $tricks->findAll();
        return $this->render('trick/home.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    #[Route('/trick/{slug}', name: 'app_trick')]
    public function trick(Request $request, Trick $trick, PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy(['trick' => $trick->getId()]);
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        }
        return $this->render('trick/trick.html.twig', [
            'trick' => $trick,
            'posts' => $posts,
            'form' => $form->createView()
        ]);
    }
}
