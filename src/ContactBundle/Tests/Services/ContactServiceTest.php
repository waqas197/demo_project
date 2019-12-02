<?php

namespace ContactBundle\Tests\Services;

use ContactBundle\Controller\ContactApiController;
use ContactBundle\Controller\ContactController;
use ContactBundle\Entity\Contact;
use ContactBundle\Repository\ContactRepository;
use ContactBundle\Services\ContactService;
use ContactBundle\Services\FileUploaderService;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContactServiceTest extends TestCase
{
    /**
     * @var MockObject | FileUploaderService
     */
    private $fileUploaderService;

    /**
     * @var MockObject | ContactRepository
     */
    private $contactRepository;

    /**
     * ContactService
     */
    private $contactService;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileUploaderService = $this->createMock(FileUploaderService::class);
        $this->contactRepository = $this->createMock(ContactRepository::class);

        $this->contactService = new ContactService(
            $this->fileUploaderService,
            $this->contactRepository
        );
    }

    public function testCreateOrUpdate(): void
    {
        $contact = $this->createMock(Contact::class);
        $this->contactRepository->expects(static::once())
            ->method('createOrUpdate')
            ->with(static::equalTo($contact))
            ->willReturn(true);

        static::assertEquals(
            [
                ContactController::STATUS => JsonResponse::HTTP_CREATED,
                ContactController::DATA => "Success"
            ],
            $this->contactService->createOrUpdate($contact)
        );
    }

    public function testCreateOrUpdateFail(): void
    {
        $contact = $this->createMock(Contact::class);
        $this->contactRepository->expects(static::once())
            ->method('createOrUpdate')
            ->with(static::equalTo($contact))
            ->willReturn(false);

        static::assertEquals(
            [
                ContactController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactController::DATA => "Operation failed!"
            ],
            $this->contactService->createOrUpdate($contact)
        );
    }

    public function testGet(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        static::assertEquals($contact, $this->contactService->get($id)
        );
    }

    public function testDelete(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        $contact->expects(static::once())
            ->method('getPicture')
            ->willReturn('picture-name.jpg');

        $this->fileUploaderService->expects(static::once())
            ->method('delete')
            ->with(static::equalTo('picture-name.jpg'))
            ->willReturn([
                ContactController::STATUS => JsonResponse::HTTP_OK,
                ContactController::DATA => "File removed"
            ]);

        $this->contactRepository->expects(static::once())
            ->method('delete')
            ->with($contact)
            ->willReturn(true);

        static::assertEquals([
            ContactController::STATUS => JsonResponse::HTTP_OK,
            ContactController::DATA => "Contact deleted successfully"
        ], $this->contactService->delete($id));
    }

    public function testDeleteWithoutPicture(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        $contact->expects(static::once())
            ->method('getPicture')
            ->willReturn(null);

        $this->fileUploaderService->expects(static::never())
            ->method('delete');

        $this->contactRepository->expects(static::once())
            ->method('delete')
            ->with($contact)
            ->willReturn(true);

        static::assertEquals([
            ContactController::STATUS => JsonResponse::HTTP_OK,
            ContactController::DATA => "Contact deleted successfully"
        ], $this->contactService->delete($id));
    }

    public function testDeleteWithPictureDeletingFailed(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        $contact->expects(static::once())
            ->method('getPicture')
            ->willReturn('picture-name.jpg');

        $this->fileUploaderService->expects(static::once())
            ->method('delete')
            ->with(static::equalTo('picture-name.jpg'))
            ->willReturn([
                ContactController::STATUS => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                ContactController::DATA => 'Exception'
            ]);

        $this->contactRepository->expects(static::never())
            ->method('delete');

        static::assertEquals([
            ContactController::STATUS => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ContactController::DATA => 'Exception'
        ], $this->contactService->delete($id));
    }

    public function testDeleteFail(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        $contact->expects(static::once())
            ->method('getPicture')
            ->willReturn('picture-name.jpg');

        $this->fileUploaderService->expects(static::once())
            ->method('delete')
            ->with(static::equalTo('picture-name.jpg'))
            ->willReturn([
                ContactController::STATUS => JsonResponse::HTTP_OK,
                ContactController::DATA => "File removed"
            ]);

        $this->contactRepository->expects(static::once())
            ->method('delete')
            ->with($contact)
            ->willReturn(false);

        static::assertEquals([
            ContactController::STATUS => JsonResponse::HTTP_FORBIDDEN,
            ContactController::DATA => "Contact deleting failed"
        ], $this->contactService->delete($id));
    }

    public function testDeletePicture(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        $contact->expects(static::once())
            ->method('getPicture')
            ->willReturn('picture-name.png');

        $this->fileUploaderService->expects(static::once())
            ->method('delete')
            ->with(static::equalTo('picture-name.png'))
            ->willReturn([
                ContactApiController::STATUS => JsonResponse::HTTP_OK,
                ContactApiController::DATA => "File removed"
            ]);

        $contact->expects(static::once())
            ->method('setPicture')
            ->with(static::equalTo(null))
            ->willReturn($contact);

        $this->contactRepository->expects(static::once())
            ->method('createOrUpdate')
            ->with(static::equalTo($contact))
            ->willReturn(true);

        static::assertEquals([
            ContactApiController::STATUS => JsonResponse::HTTP_OK,
            ContactApiController::DATA => "Picture deleted successfully"
        ], $this->contactService->deletePicture($id));

    }

    public function testDeletePictureWithContactNotFound(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn(null);

        $contact->expects(static::never())
            ->method('getPicture');

        $this->fileUploaderService->expects(static::never())
            ->method('delete');

        $contact->expects(static::never())
            ->method('setPicture');

        $this->contactRepository->expects(static::never())
            ->method('createOrUpdate');

        static::assertEquals([
            ContactApiController::STATUS => JsonResponse::HTTP_NOT_FOUND,
            ContactApiController::DATA => "Contact not found"
        ], $this->contactService->deletePicture($id));

    }

    public function testDeletePictureWithoutPicture(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        $contact->expects(static::once())
            ->method('getPicture')
            ->willReturn(null);

        $this->fileUploaderService->expects(static::never())
            ->method('delete');

        $contact->expects(static::never())
            ->method('setPicture');

        $this->contactRepository->expects(static::never())
            ->method('createOrUpdate');

        static::assertEquals([
            ContactApiController::STATUS => JsonResponse::HTTP_FORBIDDEN,
            ContactApiController::DATA => "Picture not found"
        ], $this->contactService->deletePicture($id));

    }

    public function testDeletePictureWithPictureDeletingFailed(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        $contact->expects(static::once())
            ->method('getPicture')
            ->willReturn('picture-name.png');

        $this->fileUploaderService->expects(static::once())
            ->method('delete')
            ->with(static::equalTo('picture-name.png'))
            ->willReturn([
                ContactApiController::STATUS => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                ContactApiController::DATA => 'Exception'
            ]);

        $contact->expects(static::never())
            ->method('setPicture');

        $this->contactRepository->expects(static::never())
            ->method('createOrUpdate');

        static::assertEquals([
            ContactApiController::STATUS => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ContactApiController::DATA => 'Exception'
        ], $this->contactService->deletePicture($id));

    }

    public function testDeletePictureWithRecordUpdatingFailed(): void
    {
        $contact = $this->createMock(Contact::class);
        $id = rand(1000, 9999);
        $this->contactRepository->expects(static::once())
            ->method('find')
            ->with(static::equalTo($id))
            ->willReturn($contact);

        $contact->expects(static::once())
            ->method('getPicture')
            ->willReturn('picture-name.png');

        $this->fileUploaderService->expects(static::once())
            ->method('delete')
            ->with(static::equalTo('picture-name.png'))
            ->willReturn([
                ContactApiController::STATUS => JsonResponse::HTTP_OK,
                ContactApiController::DATA => "File removed"
            ]);

        $contact->expects(static::once())
            ->method('setPicture')
            ->with(static::equalTo(null))
            ->willReturn($contact);

        $this->contactRepository->expects(static::once())
            ->method('createOrUpdate')
            ->with(static::equalTo($contact))
            ->willReturn(false);

        static::assertEquals([
            ContactApiController::STATUS => JsonResponse::HTTP_FORBIDDEN,
            ContactApiController::DATA => "Picture updating failed"
        ], $this->contactService->deletePicture($id));

    }

    public function testSearch(): void
    {
        $search = [
            'param1' => 'search1',
            'param2' => 'search2',
            'param3' => 'search3'
        ];

        $response = [
            'firstName' => 'first name',
            'lastName' => 'last name'
        ];

        $this->contactRepository->expects(static::once())
            ->method('search')
            ->with(static::equalTo($search))
            ->willReturn($response);

        static::assertEquals([
            ContactApiController::STATUS => JsonResponse::HTTP_OK,
            ContactApiController::DATA => $response
        ], $this->contactService->search($search));
    }

    public function testGetPaginatedContacts(): void
    {
        $paginatedView = $this->createMock(SlidingPagination::class);
        $this->contactRepository->expects(static::once())
            ->method('getContacts')
            ->with(static::equalTo(1), static::equalTo(10))
            ->willReturn($paginatedView);

        static::assertEquals($paginatedView, $this->contactService->getPaginatedContacts(1, 10));
    }

}
