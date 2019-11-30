<?php

namespace ContactBundle\Services;

use ContactBundle\Controller\ContactController;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This service is responsible to manage picture (upload, delete)
 *
 * Class FileUploaderService
 * @package ContactBundle\Services
 */
class FileUploaderService
{

    /**
     * Parameter for picture directory path
     */
    const PICTURE_DIR_PARAM = 'picture_directory';

    /**
     * Parameter for supported image types
     */
    const IMG_TYPE_PARAM = 'supported_image_types';

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
     * This method is responsible to upload picture
     *
     * @param UploadedFile $file
     * @return array
     */
    public function upload(UploadedFile $file): array
    {
        $supportedImageTypes = $this->container->getParameter(self::IMG_TYPE_PARAM);
        if (!\in_array($file->guessExtension(), $supportedImageTypes)) {

            return [
                ContactController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactController::DATA => "Invalid type, supported types are : " . \implode(", ", $supportedImageTypes)
            ];
        }

        $fileName = \uniqid() . '.' . $file->guessExtension();
        try {
            $targetDirectory = $this->container->getParameter(self::PICTURE_DIR_PARAM);
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


    /**
     *This method is responsible to delete picture
     *
     * @param $picture
     * @return array
     */
    public function delete($picture): array
    {
        try {
            $file = new Filesystem();
            $path = $this->container->getParameter(self::PICTURE_DIR_PARAM) . "/" . $picture;
            $file->remove($path);
            return [
                ContactController::STATUS => JsonResponse::HTTP_OK,
                ContactController::DATA => "File removed"
            ];
        } catch (FileException $e) {
            return [
                ContactController::STATUS => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                ContactController::DATA => $e->getMessage()
            ];
        }

    }

}
