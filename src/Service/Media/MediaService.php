<?php

namespace App\Service\Media;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaService
{

    public function upload(UploadedFile $file): Image
    {
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move("../public/upload/tricks", $fileName);
        $newImage = new Image;
        $newImage->setName($fileName)
            ->setCreatedAt(new \DateTimeImmutable(('NOW')));
        return $newImage;
    }

    public function removeImage(Trick $trick, Image $image): Trick
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove("../public/upload/tricks/" . $image->getName());
        $trick->removeImage($image);
        return $trick;
    }

    public function addVideo(Trick $trick, string $video): Trick
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video, $match);
        if (isset($match[1])) {
            $youtubeCode = $match[1];
            $newVideo = new Video;
            $newVideo->setCreatedAt(new \DateTimeImmutable(('NOW')))
                ->setEmbed($youtubeCode);
            $trick->addVideo($newVideo);
            return $trick;
        }
        return $trick;
    }
}
