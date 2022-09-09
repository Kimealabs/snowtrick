<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\PostFormType;
use App\Form\CreateTrickFormType;
use App\Repository\PostRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    public function trick(Request $request, Trick $trick, PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        $posts = $postRepository->findBy(['trick' => $trick->getId()], ['createdAt' => 'DESC']);
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('only_connected_confirmed', $this->getUser());

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

    #[Route('/create', name: 'app_create_trick')]
    public function createTrick(Request $request, TrickRepository $trickRepository, EntityManagerInterface $entityManagerInterface): Response
    {
        $this->denyAccessUnlessGranted('only_connected_confirmed', $this->getUser());

        $trick = new Trick;
        $user = $this->getUser();
        $form = $this->createForm(CreateTrickFormType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            $trickExist = $trickRepository->findOneBy(['slug' => $trick->getSlug()]);
            if ($trickExist and $trick !== $trickExist) {
                $this->addFlash('danger', 'Trick name exist yet !');
                return $this->generateUrl('app_update_trick', ['slug' => $trick->getSlug()]);
            }

            $trick->setCreatedAt(new \DateTimeImmutable('NOW'));
            $trick->setUserId($user);

            $spotlight = $form->get('spotlight')->getData();
            if ($spotlight !== null) {
                $spotlightName = md5(uniqid()) . '.' . $spotlight->guessExtension();
                $spotlight->move($this->getParameter('upload_tricks_directory'), $spotlightName);
                $newImage = new Image;
                $newImage->setName($spotlightName)
                    ->setCreatedAt(new \DateTimeImmutable(('NOW')))
                    ->setType('spotlight');
                $trick->addImage(($newImage));
            }

            $images = $form->get('images')->getData();
            if ($images !== null) {
                foreach ($images as $image) {
                    $imageName = md5(uniqid()) . '.' . $image->guessExtension();
                    $image->move($this->getParameter('upload_tricks_directory'), $imageName);
                    $newImage = new Image;
                    if ($spotlight === null) {
                        $spotlight = true;
                        $newImage->setType('spotlight');
                    }
                    $newImage->setName($imageName)
                        ->setCreatedAt(new \DateTimeImmutable(('NOW')));
                    $trick->addImage(($newImage));
                }
            }

            $videos = $form->getExtraData();
            foreach ($videos["video"] as $video) {
                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video, $match);
                if (isset($match[1])) {
                    $youtubeCode = $match[1];
                    $newVideo = new Video;
                    $newVideo->setCreatedAt(new \DateTimeImmutable(('NOW')))
                        ->setEmbed($youtubeCode);
                    $trick->addVideo(($newVideo));
                }
            }

            $entityManagerInterface->persist($trick);
            $entityManagerInterface->flush();

            $this->addFlash('success', 'Your Trick is published !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('trick/create_trick.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/update/{slug}', name: 'app_update_trick')]
    public function updateTrick(Request $request, Trick $trick, TrickRepository $trickRepository, EntityManagerInterface $entityManagerInterface): Response
    {
        $this->denyAccessUnlessGranted('only_connected_confirmed', $this->getUser());

        $user = $this->getUser();
        $form = $this->createForm(CreateTrickFormType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            $trickExist = $trickRepository->findOneBy(['slug' => $trick->getSlug()]);
            if ($trickExist and $trick !== $trickExist) {
                $this->addFlash('danger', 'Trick name exist yet !');
                return $this->generateUrl('app_update_trick', ['slug' => $trick->getSlug()]);
            }


            $trick->setModifiedAt(new \DateTimeImmutable('now'));
            $trick->setUserId($user);

            $spotlight = $form->get('spotlight')->getData();
            if ($spotlight !== null) {
                $spotlightName = md5(uniqid()) . '.' . $spotlight->guessExtension();
                $spotlight->move($this->getParameter('upload_tricks_directory'), $spotlightName);
                $newImage = new Image;
                $newImage->setName($spotlightName);
                $newImage->setCreatedAt(new \DateTimeImmutable(('NOW')));
                $newImage->setType('spotlight');
                $trick->addImage(($newImage));
            }

            $images = $form->get('images')->getData();
            if ($images !== null) {
                foreach ($images as $image) {
                    $imageName = md5(uniqid()) . '.' . $image->guessExtension();
                    $image->move($this->getParameter('upload_tricks_directory'), $imageName);
                    $newImage = new Image;
                    if ($spotlight === null) {
                        $spotlight = true;
                        foreach ($trick->getImages() as $oldImage) {
                            if ($oldImage->getType() == 'spotlight') $spotlight = false;
                        }
                        if ($spotlight) $newImage->setType('spotlight');
                    }
                    $newImage->setName($imageName);
                    $newImage->setCreatedAt(new \DateTimeImmutable(('NOW')));
                    $trick->addImage(($newImage));
                }
            }

            $videos = $form->getExtraData();
            foreach ($videos["video"] as $video) {
                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video, $match);
                if (isset($match[1])) {
                    $youtubeCode = $match[1];
                    $newVideo = new Video;
                    $newVideo->setCreatedAt(new \DateTimeImmutable(('NOW')))
                        ->setEmbed($youtubeCode);
                    $trick->addVideo(($newVideo));
                }
            }

            $entityManagerInterface->flush();

            $this->addFlash('success', 'Your Trick is updated !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('trick/update_trick.html.twig', [
            'form' => $form->createView(),
            'trick' =>  $trick
        ]);
    }

    #[Route('/deleteTrick/{slug}', name: 'app_delete_trick')]
    public function deleteTrick(string $slug, TrickRepository $trickRepository, EntityManagerInterface $entityManagerInterface): Response
    {
        $this->denyAccessUnlessGranted('only_connected_confirmed', $this->getUser());

        if ($user = $this->getUser()) {
            if ($user->isConfirmed() == true) {
                $trick = $trickRepository->findOneBy(['slug' => $slug]);
                $images = $trick->getImages();
                $fileSystem = new Filesystem();
                foreach ($images as $image) {
                    $fileSystem->remove($this->getParameter('upload_tricks_directory') . '/' . $image->getName());
                }
                $entityManagerInterface->remove($trick);
                $entityManagerInterface->flush();
                $this->addFlash('success', 'Trick Deleted !');
                return $this->redirectToRoute('app_home');
            }
        }
    }

    #[Route('/listTricks/{offset<\d+>?0}', name: 'app_list_tricks')]
    public function listTricksbyFive(TrickRepository $trickRepository, int $offset): Response
    {
        $tricks = $trickRepository->findBy([], ['createdAt' => 'DESC'], 5, $offset);
        return $this->render('trick/listTricksByFive.html.twig', [
            'tricks' => $tricks
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
