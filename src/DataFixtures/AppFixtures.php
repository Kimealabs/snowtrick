<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr-FR');

        $users = [];
        $categories = [];
        $categoriesFinded = ['Grabs', 'Rotations', 'Flips', 'Rotations désaxées', 'Slides', 'One foot', 'Old school'];
        $tricksName = ['Air', 'Ollie', 'Nollie', 'Frontflip', 'Backflip', 'BS 180', 'FS Invert', 'FS Rodeo 540', 'Frontroll', 'Backroll'];

        for ($i = 0; $i < 10; $i++) {
            $user = new User();

            $user->setName($faker->userName)
                ->setEmail($faker->safeEmail)
                ->setPassword($this->hasher->hashPassword($user, 'password'))
                ->setCreatedAt(new \DateTimeImmutable('2021-' . $faker->numberBetween(1, 12) . '-' . $faker->numberBetween(1, 28) . ' ' . $faker->numberBetween(1, 23) . ':00:00'));
            $manager->persist($user);
            $users[] = $user;
        }

        foreach ($categoriesFinded as $category) {
            $newCategory = new Category();
            $newCategory->setLabel($category);
            $manager->persist($newCategory);
            $categories[] = $newCategory;
        }

        foreach ($tricksName as $trickName) {
            $trick = new Trick();
            $trick->setName($trickName)
                ->setDescription($faker->paragraph(5))
                ->setCreatedAt(new \DateTimeImmutable('now'))
                ->setModifiedAt(new \DateTimeImmutable('now'))
                ->setUserId($faker->randomElement($users))
                ->addCategory($faker->randomElement($categories));

            $manager->persist($trick);

            $image = new Image();
            $image->setName($trick->getName() . '.jpg')
                ->setTrick($trick)
                ->setCreatedAt(new \DateTimeImmutable('now'));
            $manager->persist($image);


            for ($l = 0; $l < mt_rand(1, 3); $l++) {
                $video = new Video();
                $video->setEmbed('<iframe width="560" height="315" src="https://www.youtube.com/embed/G9qlTInKbNE" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>')
                    ->setTrick($trick)
                    ->setCreatedAt(new \DateTimeImmutable('now'));
                $manager->persist($video);
            }

            // 0 to 30 Comment by Trick
            for ($m = 0; $m < mt_rand(0, 30); $m++) {
                $post = new Post();
                $post->setContent($faker->sentence(mt_rand(1, 5)))
                    ->setCreatedAt(new \DateTimeImmutable('now'))
                    ->setUserId($faker->randomElement($users))
                    ->setTrick($trick);

                $manager->persist($post);
            }
        }

        $manager->flush();
    }
}
