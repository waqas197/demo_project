<?php

namespace ContactBundle\Services;

use ContactBundle\Controller\ContactController;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This class is responsible to manage picture (upload, delete etc)
 *
 * Class FileUploaderService
 * @package ContactBundle\Services
 */
class FileUploaderService
{

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var Filesystem
     */
    private $file;

    /**
     * FileUploaderService constructor.
     * @param ContainerInterface $container
     * @param Filesystem $file
     */
    public function __construct(
        ContainerInterface $container,
        Filesystem $file
    ) {
        $this->container = $container;
        $this->file = $file;
    }

    /**
     * This function is responsible to upload picture
     *
     * @param UploadedFile $file
     * @return array
     */
    public function upload(UploadedFile $file): array
    {
        $supportedImageTypes = $this->container->getParameter("supported_image_types");
        if (!\in_array($file->guessExtension(), $supportedImageTypes)) {

            return [
                ContactController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactController::DATA => "Invalid type, supported types are : " . \implode(", ", $supportedImageTypes)
            ];
        }

        $fileName = \uniqid() . '.' . $file->guessExtension();
        try {
            $targetDirectory = $this->container->getParameter("picture_directory");
            $file->move($targetDirectory, $fileName);

            return [
                ContactController::STATUS => JsonResponse::HTTP_CREATED,
                ContactController::DATA => $fileName
            ];
        } catch (FileException $e) {

            return [
                ContactController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactController::DATA => $e->getMessage()
            ];
        }
    }

}
