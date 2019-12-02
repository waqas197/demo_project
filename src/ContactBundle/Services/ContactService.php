<?php

namespace ContactBundle\Services;

use ContactBundle\Controller\ContactApiController;
use ContactBundle\Controller\ContactController;
use ContactBundle\Entity\Contact;
use ContactBundle\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This service responsible to manage contacts
 *
 * Class ContactService
 * @package ContactBundle\Services
 *
 */
class ContactService
{
    /**
     * @var FileUploaderService
     */
    private $fileUploaderService;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * ContactService constructor.
     * @param FileUploaderService $fileUploaderService
     * @param ContactRepository $contactRepository
     *
     */
    public function __construct(
        FileUploaderService $fileUploaderService,
        ContactRepository $contactRepository
    )
    {
        $this->fileUploaderService = $fileUploaderService;
        $this->contactRepository = $contactRepository;
    }

    /**
     * This method is responsible to create new contact
     *
     * @param Contact $contact
     * @return array
     */
    public function createOrUpdate(Contact $contact): array
    {
        $response = $this->contactRepository->createOrUpdate($contact);
        if ($response) {

            return [
                ContactController::STATUS => JsonResponse::HTTP_CREATED,
                ContactController::DATA => "Success"
            ];
        } else {

            return [
                ContactController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactController::DATA => "Operation failed!"
            ];
        }
    }

    /**
     * This method is used to get entity by Id
     *
     * @param int $id
     * @return object|null
     */
    public function get(int $id)
    {
        return $this->contactRepository->find($id);
    }

    /**
     * This method is responsible to delete contact
     *
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        /**
         * @var Contact $contact
         */
        $contact = $this->contactRepository->find($id);
        if ($contact == null) {

            return [
                ContactApiController::STATUS => JsonResponse::HTTP_NOT_FOUND,
                ContactApiController::DATA => "Contact not found"
            ];
        }

        $picture = $contact->getPicture();
        if ($picture) {
            $pictureStatus = $this->fileUploaderService->delete($picture);
            if ($pictureStatus[ContactApiController::STATUS] == JsonResponse::HTTP_INTERNAL_SERVER_ERROR) {

                return $pictureStatus;
            }
        }

        $response = $this->contactRepository->delete($contact);
        if ($response) {

            return [
                ContactController::STATUS => JsonResponse::HTTP_OK,
                ContactController::DATA => "Contact deleted successfully"
            ];
        } else {

            return [
                ContactController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactController::DATA => "Contact deleting failed"
            ];
        }

    }

    /**
     * This method is responsible to delete contact's picture
     *
     * @param int $id
     * @return array
     */
    public function deletePicture(int $id): array
    {
        /**
         * @var Contact $contact
         */
        $contact = $this->contactRepository->find($id);
        if ($contact == null) {

            return [
                ContactApiController::STATUS => JsonResponse::HTTP_NOT_FOUND,
                ContactApiController::DATA => "Contact not found"
            ];
        }

        $picture = $contact->getPicture();
        if ($picture === null) {
            return [
                ContactApiController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactApiController::DATA => "Picture not found"
            ];
        }

        $pictureStatus = $this->fileUploaderService->delete($picture);
        if ($pictureStatus[ContactApiController::STATUS] == JsonResponse::HTTP_INTERNAL_SERVER_ERROR) {

            return $pictureStatus;
        }
        $contact->setPicture(null);

        $response = $this->contactRepository->createOrUpdate($contact);
        if ($response === false) {

            return [
                ContactApiController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactApiController::DATA => "Picture updating failed"
            ];
        }

        return [
            ContactApiController::STATUS => JsonResponse::HTTP_OK,
            ContactApiController::DATA => "Picture deleted successfully"
        ];

    }

    /**
     * This method is responsible to search contact with specific parameters.
     *
     * @param array $search
     * @return array
     */
    public function search(array $search): array
    {
        $contact = $this->contactRepository->search($search);

        return [
            ContactApiController::STATUS => JsonResponse::HTTP_OK,
            ContactApiController::DATA => $contact
        ];
    }

    /**
     * This method is responsible to list all contact.
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function getPaginatedContacts(int $from, int $to)
    {
        return $this->contactRepository->getContacts($from, $to);
    }

}
