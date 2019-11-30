<?php

namespace ContactBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is responsible to return all responses with headers, text/html
 *
 * @Route("/")
 *
 */
class DefaultController extends Controller
{

    /**
     * Application dashboard
     *
     * @Route("", methods={"GET"}, name="default")
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        return $this->render('@AddressBookContact/default.html.twig');
    }

}
