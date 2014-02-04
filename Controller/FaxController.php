<?php

namespace Illicomedia\Freebox\FaxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FaxController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('IllicomediaFreeboxFaxBundle:Default:index.html.twig', array('name' => $name));
    }
}
