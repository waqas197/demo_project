<?php

namespace ContactBundle\Controller;

use ContactBundle\Entity\Contact;
use ContactBundle\Form\ContactType;
use ContactBundle\Services\ContactService;
use ContactBundle\Services\FileUploaderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is responsible to return all responses with headers, text/html
 *
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
     * key pass to template
     */
    public const FORM = 'form';

    /**
     * key pass to template
     */
    public const CONTACT = 'contact';

    /**
     * Template for add contact
     */
    public const ADD_CONTACT_TEMPLATE = '@AddressBookContact/addContact.html.twig';

    /**
     * Template for search contact
     */
    public const SEARCH_CONTACT_TEMPLATE = '@AddressBookContact/searchContact.html.twig';

    /**
     * Template for contact detail view
     */
    public const VIEW_CONTACT_TEMPLATE = '@AddressBookContact/detailContact.html.twig';

    /**
     * Template for contact list
     */
    public const VIEW_CONTACT_LIST_TEMPLATE = '@AddressBookContact/listContact.html.twig';


    /**
     * This method renders add contact page.
     *
     * @Route("/add", methods={"GET", "POST"}, name="add_contact")
     *
     * @param Request $request
     * @param ContactService $contactService
     * @param FileUploaderService $fileUploaderService
     * @return Response
     */
    public function addAction(
        Request $request,
        ContactService $contactService,
        FileUploaderService $fileUploaderService
    ): Response {

        $contact = new Contact();

        /**
         * @var FormInterface $contactForm
         */
        $contactForm = $this->createForm(ContactType::class, $contact)->add('save', SubmitType::class);

        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $picture = $contactForm[self::PICTURE]->getData();
            if ($picture) {
                $pictureUploadStatus = $this->managePicture($fileUploaderService, $picture);
                if ($pictureUploadStatus[self::STATUS] == JsonResponse::HTTP_CREATED) {
                    $contact->setPicture($pictureUploadStatus[self::DATA]);
                } else {
                    $this->addFlash(self::ERROR, $pictureUploadStatus[self::DATA]);

                    return $this->render(self::ADD_CONTACT_TEMPLATE, [
                        self::FORM => $contactForm->createView(),
                    ]);
                }
            }

            $response = $contactService->createOrUpdate($contact);
            if ($response[self::STATUS] == JsonResponse::HTTP_CREATED) {
                $this->addFlash(self::SUCCESS, $response[self::DATA]);
            } else {
                $this->addFlash(self::ERROR, $response[self::DATA]);
            }

            return $this->redirectToRoute('add_contact');
        }

        return $this->render(self::ADD_CONTACT_TEMPLATE, [
            self::FORM => $contactForm->createView(),
        ]);
    }

    /**
     * This method renders edit contact page
     *
     * @Route("/edit/{id}", methods={"GET", "POST"}, name="edit_contact", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param ContactService $contactService
     * @param FileUploaderService $fileUploaderService
     * @return Response
     */
    public function editAction(
        Request $request,
        ContactService $contactService,
        FileUploaderService $fileUploaderService,
        Contact $contact
    ): Response {
        $existingPicture = $contact->getPicture();

        /**
         * @var FormInterface $contactForm
         */
        $contactForm = $this->createForm(ContactType::class, $contact)->add('save', SubmitType::class);
        $contactForm->handleRequest($request);
        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $picture = $contactForm[self::PICTURE]->getData();
            if ($picture) {
                $pictureUploadStatus = $this->managePicture($fileUploaderService, $picture);
                if ($pictureUploadStatus[self::STATUS] == JsonResponse::HTTP_CREATED) {
                    $contact->setPicture($pictureUploadStatus[self::DATA]);
                } else {
                    $this->addFlash(self::ERROR, $pictureUploadStatus[self::DATA]);

                    return $this->render(self::ADD_CONTACT_TEMPLATE, [
                        self::FORM => $contactForm->createView(),
                    ]);
                }
            } else {
                $contact->setPicture($existingPicture);
            }

            $response = $contactService->createOrUpdate($contact);
            if ($response[self::STATUS] == JsonResponse::HTTP_CREATED) {
                $this->addFlash(self::SUCCESS, $response[self::DATA]);
            } else {
                $this->addFlash(self::ERROR, $response[self::DATA]);
            }
        }

        return $this->render(self::ADD_CONTACT_TEMPLATE, [
            self::FORM => $contactForm->createView(),
        ]);
    }

    /**
     * This method renders search contact page
     *
     * @Route("/search", methods={"GET"}, name="search_contact")
     *
     * @param ContactService $contactService
     * @return Response
     */
    public function searchAction(ContactService $contactService): Response
    {
        return $this->render(self::SEARCH_CONTACT_TEMPLATE);
    }

    /**
     * This method renders contact detail page
     *
     * @Route("/detail/{id}", methods={"GET"}, name="detail_contact")
     *
     * @param Contact $contact
     * @return Response
     */
    public function detailAction(Contact $contact): Response
    {
        return $this->render(self::VIEW_CONTACT_TEMPLATE, [
            self::CONTACT => $contact
        ]);
    }

    /**
     * This method renders list contact page
     *
     * @Route("/list", methods={"GET"}, name="list_contact")
     *
     * @param Request $request
     * @param ContactService $contactService
     * @return Response
     */
    public function listAction(Request $request, ContactService $contactService): Response
    {
        $contacts = $contactService->getPaginatedContacts($request->get('page', 1), 5);

        return $this->render(self::VIEW_CONTACT_LIST_TEMPLATE, [
            'contacts' => $contacts
        ]);
    }

    /**
     * This method manage picture (delete/upload)
     *
     * @param FileUploaderService $fileUploaderService
     * @param $picture
     * @param null $oldPicture
     * @return array
     */
    private function managePicture(FileUploaderService $fileUploaderService, $picture, $oldPicture = null): array
    {
        if ($oldPicture) {
            $picDelStatus = $fileUploaderService->delete($picture);
            if ($picDelStatus[self::STATUS] == JsonResponse::HTTP_INTERNAL_SERVER_ERROR) {

                return $picDelStatus;
            }
        }

        return $fileUploaderService->upload($picture);
    }

}
