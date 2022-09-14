<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Trick;
use App\Form\PostFormType;
use App\Form\CreateTrickFormType;
use App\Security\Voter\UserVoter;
use App\Repository\PostRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\VideoRepository;
use App\Service\Media\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('trick/home.html.twig');
    }

    #[Route('/trick/{slug}', name: 'app_trick')]
    public function trick(Request $request, Trick $trick, PostRepository $postRepository, EntityManagerInterface $entityManagerInterface): Response
    {
        $posts = $postRepository->findBy(['trick' => $trick->getId()], ['createdAt' => 'DESC']);
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(UserVoter::ONLY_CONNECTED_CONFIRMED, $this->getUser());

            $post->setCreatedAt(new \DateTimeImmutable('NOW'));
            $post->setTrick($trick);
            $post->setUserId($this->getUser());
            $entityManagerInterface->persist($post);
            $entityManagerInterface->flush($post);
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
    public function createTrick(
        Request $request,
        TrickRepository $trickRepository,
        MediaService $mediaService,
        EntityManagerInterface $entityManagerInterface
    ): Response {
        $this->denyAccessUnlessGranted(UserVoter::ONLY_CONNECTED_CONFIRMED, $this->getUser());

        $trick = new Trick;
        $user = $this->getUser();
        $form = $this->createForm(CreateTrickFormType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            // IF TRICK NAME EXIST YET TEST
            $trickExist = $trickRepository->findOneBy(['slug' => $trick->getSlug()]);
            if ($trickExist and $trick !== $trickExist) {
                $this->addFlash('danger', 'Trick name exist yet !');
                return $this->generateUrl('app_update_trick', ['slug' => $trick->getSlug()]);
            }

            $trick->setCreatedAt(new \DateTimeImmutable('NOW'));
            $trick->setUserId($user);

            // ADD IMAGE TO DB (type 'spotlight') AND FILE FROM INPUT FILE
            $spotlight = $form->get('spotlight')->getData();
            if ($spotlight !== null) {
                $newImage = $mediaService->upload($spotlight);
                $newImage->setType('spotlight');
                $trick->addImage(($newImage));
            }

            // ADD IMAGE TO DB AND FILES FROM INPUT FILES
            $images = $form->get('images')->getData();
            if ($images !== null) {
                foreach ($images as $image) {
                    $newImage = $mediaService->upload($image);
                    if ($spotlight === null) {
                        $spotlight = true;
                        $newImage->setType('spotlight');
                    }
                    $trick->addImage(($newImage));
                }
            }

            // ADD VIDEO (filter Youtube) TO DB
            $extraForm = $form->getExtraData();
            if (isset($extraForm["video"])) {
                foreach ($extraForm["video"] as $video) {
                    $trick = $mediaService->addVideo($trick, $video);
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
    public function updateTrick(
        Request $request,
        Trick $trick,
        TrickRepository $trickRepository,
        ImageRepository $imageRepository,
        VideoRepository $videoRepository,
        MediaService $mediaService,
        EntityManagerInterface $entityManagerInterface
    ): Response {
        $this->denyAccessUnlessGranted(UserVoter::ONLY_CONNECTED_CONFIRMED, $this->getUser());

        $user = $this->getUser();
        $form = $this->createForm(CreateTrickFormType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            // IF TRICK NAME EXIST YET TEST
            $trickExist = $trickRepository->findOneBy(['slug' => $trick->getSlug()]);
            if ($trickExist and $trick !== $trickExist) {
                $this->addFlash('danger', 'Trick name exist yet !');
                return $this->generateUrl('app_update_trick', ['slug' => $trick->getSlug()]);
            }

            $trick->setModifiedAt(new \DateTimeImmutable('now'));
            $trick->setUserId($user);

            // ADD VIDEO (filter Youtube) TO DB
            $extraForm = $form->getExtraData();
            if (isset($extraForm["video"])) {
                foreach ($extraForm["video"] as $video) {
                    $trick = $mediaService->addVideo($trick, $video);
                }
            }

            // REMOVE VIDEO TO DELETE BY INPUT HIDDEN ARRAY
            if (isset($extraForm["videoToDelete"])) {
                foreach ($extraForm["videoToDelete"] as $videoToDelete) {
                    $videoTarget = $videoRepository->find($videoToDelete);
                    $trick->removeVideo($videoTarget);
                }
            }

            // REMOVE IMG TO DELETE FROM DB AND FILES BY INPUT HIDDEN ARRAY
            if (isset($extraForm["imageToDelete"])) {
                foreach ($extraForm["imageToDelete"] as $imageToDelete) {
                    $imgTarget = $imageRepository->find($imageToDelete);
                    if ($imgTarget) $trick = $mediaService->removeImage($trick, $imgTarget);
                }
            }

            // IF PRESENT -> ADD IMAGE TO DB AND FILE FROM INPUT FILE SPOTLIGHT
            // SPOTLIGHT VARIABLE USED TO NEXT TESTS
            $spotlight = $form->get('spotlight')->getData();
            $newImage = null;
            if ($spotlight !== null) {
                $newImage = $mediaService->upload($spotlight);
                $trick->addImage($newImage);
            }

            // ADD IMAGE TO DB AND FILES FROM INPUT FILES
            $images = $form->get('images')->getData();
            if ($images !== null) {
                foreach ($images as $image) {
                    $newImage = $mediaService->upload($image);
                    $trick->addImage(($newImage));
                }
            }

            $entityManagerInterface->persist($trick);
            $entityManagerInterface->flush();

            // IF NEW SPOTLIGHT IMAGE (TYPE TO NULL ON OLD SPOTLIGHT IF EXIST) -> UPDATE TYPE OF FIRST IMAGE TO SPOTLIGHT
            // IF NOT NEW SPOTLIGHT IMAGE -> UPDATE TYPE OF FIRST IMAGE TO SPOTLIGHT IF SPOTLIGHT DON'T EXIST YET
            $images = $trick->getImages();
            if (count($images) > 0) {
                $imageSpotlight = $imageRepository->findOneBy(['trick' => $trick, 'Type' => 'spotlight']);
                if ($spotlight !== null) {
                    if ($imageSpotlight) {
                        $imageSpotlight->setType(null);
                    }
                    $newImage->setType('spotlight');
                } else {
                    if (!$imageSpotlight) {
                        $images[0]->setType('spotlight');
                    }
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
    public function deleteTrick(
        Trick $trick,
        MediaService $mediaService,
        EntityManagerInterface $entityManagerInterface
    ): Response {

        $this->denyAccessUnlessGranted(UserVoter::ONLY_CONNECTED_CONFIRMED, $this->getUser());
        if ($user = $this->getUser()) {
            if ($user->isConfirmed() == true) {
                //$trick = $trickRepository->findOneBy(['slug' => $slug]);
                $images = $trick->getImages();
                foreach ($images as $image) {
                    $trick = $mediaService->removeImage($trick, $image);
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
