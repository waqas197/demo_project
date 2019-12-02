<?php

namespace ContactBundle\Tests\Services;

use ContactBundle\Controller\ContactController;
use ContactBundle\Services\FileUploaderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class FileUploaderServiceTest extends TestCase
{
    /**
     * @var MockObject | ContainerInterface
     */
    private $container;
    /**
     * @var MockObject | Filesystem
     */
    private $file;

    /**
     * @var FileUploaderService
     */
    private $fileUploaderService;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
        $this->file = $this->createMock(Filesystem::class);

        $this->fileUploaderService = new FileUploaderService(
            $this->container,
            $this->file
        );
    }

    public function testUpload(): void
    {
        $this->container->expects(static::exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                [static::equalTo(FileUploaderService::IMG_TYPE_PARAM)],
                [static::equalTo(FileUploaderService::PICTURE_DIR_PARAM)]
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'jpg',
                    'png',
                    'jpeg'
                ],
                '/web/uploads/pictures'
            );

        $file = $this->createMock(UploadedFile::class);
        $file->expects(static::once())
            ->method('guessExtension')
            ->willReturn('jpg');

        $file->expects(static::once())
            ->method('move')
            ->willReturn(File::class);


        $result = $this->fileUploaderService->upload($file);
        static::assertIsString(
            $result[ContactController::DATA]
        );

        static::assertEquals(
            JsonResponse::HTTP_CREATED,
            $result[ContactController::STATUS]
        );
    }

    public function testUploadWithInvalidExtension(): void
    {
        $this->container->expects(static::exactly(1))
            ->method('getParameter')
            ->with(
                static::equalTo(FileUploaderService::IMG_TYPE_PARAM)
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'jpg',
                    'png',
                    'jpeg'
                ]
            );

        $file = $this->createMock(UploadedFile::class);
        $file->expects(static::once())
            ->method('guessExtension')
            ->willReturn('pdf');

        $file->expects(static::never())
            ->method('move');

        $result = $this->fileUploaderService->upload($file);
        static::assertEquals(
            JsonResponse::HTTP_FORBIDDEN,
            $result[ContactController::STATUS]
        );
    }

    public function testUploadWithException(): void
    {
        $this->container->expects(static::exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                [static::equalTo(FileUploaderService::IMG_TYPE_PARAM)],
                [static::equalTo(FileUploaderService::PICTURE_DIR_PARAM)]
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'jpg',
                    'png',
                    'jpeg'
                ],
                '/web/uploads/pictures'
            );

        $file = $this->createMock(UploadedFile::class);
        $file->expects(static::once())
            ->method('guessExtension')
            ->willReturn('jpg');

        $file->expects(static::once())
            ->method('move')
            ->will($this->throwException(new FileException()));

        $result = $this->fileUploaderService->upload($file);
        static::assertIsString(
            $result[ContactController::DATA]
        );

        static::assertEquals(
            JsonResponse::HTTP_FORBIDDEN,
            $result[ContactController::STATUS]
        );
    }

    public function testDelete(): void
    {
        $this->container->expects(static::once())
            ->method('getParameter')
            ->with(static::equalTo(FileUploaderService::PICTURE_DIR_PARAM))
            ->willReturn('/web/uploads/pictures');

        $this->file->expects(static::once())
            ->method('remove')
            ->with('/web/uploads/pictures/file.jpg');

        static::assertEquals(
            [
                ContactController::STATUS => JsonResponse::HTTP_OK,
                ContactController::DATA => "File removed"
            ],
            $this->fileUploaderService->delete('file.jpg')
        );
    }

    public function testDeleteWithException(): void
    {
        $this->container->expects(static::once())
            ->method('getParameter')
            ->with(static::equalTo(FileUploaderService::PICTURE_DIR_PARAM))
            ->willReturn('/web/uploads/pictures');

        $this->file->expects(static::once())
            ->method('remove')
            ->with('/web/uploads/pictures/file.jpg')
            ->will($this->throwException(new FileException()));


        $result = $this->fileUploaderService->delete('file.jpg');
        static::assertIsString(
            $result[ContactController::DATA]
        );

        static::assertEquals(
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            $result[ContactController::STATUS]
        );
    }

}
