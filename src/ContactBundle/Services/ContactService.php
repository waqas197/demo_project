<?php

namespace ContactBundle\Services;

use ContactBundle\Controller\ContactController;
use ContactBundle\Entity\Contact;
use ContactBundle\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This class is responsible to manage contacts
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
    ) {
        $this->fileUploaderService = $fileUploaderService;
        $this->contactRepository = $contactRepository;
    }

    /**
     * This method is responsible to create new contact
     *
     * @param Contact $contact
     * @return array
     */
    public function create(Contact $contact): array
    {
        $response = $this->contactRepository->create($contact);
        if ($response) {

            return [
                ContactController::STATUS => JsonResponse::HTTP_CREATED,
                ContactController::DATA => "Contact created successfully"
            ];
        } else {

            return [
                ContactController::STATUS => JsonResponse::HTTP_FORBIDDEN,
                ContactController::DATA => "Contact creating failed"
            ];
        }
    }

}
