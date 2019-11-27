<?php

namespace ContactBundle\Controller;

use ContactBundle\Entity\Contact;
use ContactBundle\Form\ContactType;
use ContactBundle\Services\ContactService;
use ContactBundle\Services\FileUploaderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/contact")
 *
 */
class ContactController extends Controller
{
    /**
     * Server response status code
     */
    public const STATUS = 'status';

    /**
     * Server response data
     */
    public const DATA = 'data';

    /**
     * type for flash message
     */
    public const ERROR = 'error';

    /**
     * type for flash message
     */
    public const SUCCESS = 'success';

    /**
     * field name for picture
     */
    public const PICTURE = 'picture';

    /**
     * Template for add contact
     */
    public const ADD_CONTACT_TEMPLATE = '@AddressBookContact/addContact.html.twig';

    /**
     * Template for search contact
     */
    public const SEARCH_CONTACT_TEMPLATE = '@AddressBookContact/searchContact.html.twig';



    /**
     * @Route("/add", methods={"GET", "POST"}, name="add_contact")
     *
     * @param Request $request
     * @param ContactService $contactService
     * @param FileUploaderService $fileUploaderService
     * @return Response
     */
    public function indexAction(
        Request $request,
        ContactService $contactService,
        FileUploaderService $fileUploaderService
    ): Response {

        $contact = new Contact();
        $contactForm = $this->createForm(ContactType::class, $contact)->add('save', SubmitType::class);

        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $picture = $contactForm[self::PICTURE]->getData();
            if ($picture) {
                $response = $fileUploaderService->upload($picture);
                if ($response[self::STATUS] == JsonResponse::HTTP_CREATED) {
                    $contact->setPicture($response[self::DATA]);
                } else {
                    $this->addFlash(self::ERROR, $response[self::DATA]);

                    return $this->render(self::ADD_CONTACT_TEMPLATE, [
                        'form' => $contactForm->createView(),
                    ]);
                }
            }

            $response = $contactService->create($contact);

            if ($response[self::STATUS] == JsonResponse::HTTP_CREATED) {
                $this->addFlash(self::SUCCESS, $response[self::DATA]);
            } else {
                $this->addFlash(self::ERROR, $response[self::DATA]);
            }

            return $this->redirectToRoute('add_contact');
        }

        return $this->render(self::ADD_CONTACT_TEMPLATE, [
            'form' => $contactForm->createView(),
        ]);

    }

    /**
     * @Route("/search", methods={"GET"}, name="search_contact")
     * @param ContactService $contactService
     * @return Response
     */
    public function searchAction(ContactService $contactService): Response
    {
        return $this->render(self::SEARCH_CONTACT_TEMPLATE);
    }
}
