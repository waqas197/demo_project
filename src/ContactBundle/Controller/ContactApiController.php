<?php

namespace ContactBundle\Controller;

use ContactBundle\Services\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller is responsible to return all responses with headers, application/json
 *
 * @Route("/api/contact")
 */
class ContactApiController extends Controller
{
    /**
     * search parameter for search i.e First Name, Last Name
     */
    const NAME = 'contactName';

    /**
     * search parameter for Email ID
     */
    const EMAIL = 'contactEmail';

    /**
     * search parameter for search i.e street, zip, city, country
     */
    const ADDRESS = 'contactAddress';

    /**
     * Server response status code
     */
    public const STATUS = 'status';

    /**
     * Server response data
     */
    public const DATA = 'data';

    /**
     * Template for contact listing
     */
    public const CONTACT_LISTING_TEMPLATE = '@AddressBookContact/contact_table.html.twig';

    /**
     * Search contact with specific filters.
     *
     * @Route("/search", methods={"GET"}, name="search_contact_api")
     *
     * @param Request $request
     * @param ContactService $contactService
     * @return JsonResponse
     */
    public function searchAction(Request $request, ContactService $contactService): JsonResponse
    {
        $search = [
            self::NAME => $request->get(self::NAME),
            self::EMAIL => $request->get(self::EMAIL),
            self::ADDRESS => $request->get(self::ADDRESS)
        ];

        $result = $contactService->search($search);

        $content = $this->renderView(self::CONTACT_LISTING_TEMPLATE, [
            'contacts' => $result[self::DATA]
        ]);

        return $this->json($content, $result[self::STATUS]);
    }

    /**
     * Delete contact.
     *
     * @Route("/delete/{id}", methods={"DELETE"}, name="delete_contact_api", requirements={"id": "\d+"})
     *
     * @param int $id
     * @param ContactService $contactService
     * @return JsonResponse
     */
    public function deleteAction(int $id, ContactService $contactService): JsonResponse
    {
        $result = $contactService->delete($id);

        return $this->json($result[self::DATA], $result[self::STATUS]);
    }

    /**
     * Delete contact picture
     *
     * @Route("/delete/picture/{id}", methods={"DELETE"}, name="delete_contact_picture_api", requirements={"id": "\d+"})
     *
     * @param int $id
     * @param ContactService $contactService
     * @return JsonResponse
     */
    public function deletePictureAction(int $id, ContactService $contactService): JsonResponse
    {
        $result = $contactService->deletePicture($id);

        return $this->json($result[self::DATA], $result[self::STATUS]);
    }
}
