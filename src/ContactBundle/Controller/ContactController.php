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
     * Template for contact detail view
     */
    public const VIEW_CONTACT_TEMPLATE = '@AddressBookContact/detailContact.html.twig';


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
                $pictureUploadStatus = $this->managePicture($fileUploaderService, $picture);
                if ($pictureUploadStatus[self::STATUS] == JsonResponse::HTTP_CREATED) {
                    $contact->setPicture($pictureUploadStatus[self::DATA]);
                } else {
                    $this->addFlash(self::ERROR, $pictureUploadStatus[self::DATA]);

                    return $this->render(self::ADD_CONTACT_TEMPLATE, [
                        'form' => $contactForm->createView(),
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
            'form' => $contactForm->createView(),
        ]);
    }

    /**
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
                        'form' => $contactForm->createView(),
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
            'form' => $contactForm->createView(),
        ]);
    }

    /**
     * This method is used to render search contact template
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
     * This method is used to render contact detail template
     *
     * @Route("/detail/{id}", methods={"GET"}, name="detail_contact")
     *
     * @param Contact $contact
     * @return Response
     */
    public function detailAction(Contact $contact): Response
    {
        return $this->render(self::VIEW_CONTACT_TEMPLATE, [
            'contact' => $contact
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
