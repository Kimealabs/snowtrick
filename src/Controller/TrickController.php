<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Trick;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

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
    public function trick(Request $request, Trick $trick, PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        $posts = $postRepository->findBy(['trick' => $trick->getId()], ['createdAt' => 'DESC']);
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new \DateTimeImmutable('NOW'));
            $post->setTrick($trick);
            $post->setUserId($this->getUser());
            $em->persist($post);
            $em->flush($post);
            $this->addFlash('success', 'Your comment is published');
            $url = $this->generateUrl('app_trick', ['slug' => $trick->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            return $this->redirect($url);
        }
        return $this->render('trick/trick.html.twig', [
            'trick' => $trick,
            'posts' => $posts,
            'form' => $form->createView()
        ]);
    }

    #[Route('/listPosts/{id}/{offset<\d+>?0}', name: 'app_list_posts')]
    public function listPostsbyThree(PostRepository $postRepository, Trick $trick, int $offset): Response
    {
        $posts = $postRepository->findBy(['trick' => $trick->getId()], ['createdAt' => 'DESC'], 3, $offset);
        return $this->render('trick/listPostsByThree.html.twig', [
            'posts' => $posts
        ]);
    }
}
