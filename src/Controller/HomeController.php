<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function PHPUnit\Framework\isNull;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/home", name="home")
     */
    public function home(): Response
    {
        $roles = $this->getUser() ? $this->getUser()->getRoles() : null;
   
        if (!$roles) {
            return $this->redirectToRoute('admin_files');
        }

        if (!in_array("ROLE_SUPER_ADMIN", $this->getUser()->getRoles())) {
            return $this->redirectToRoute('file_index');
        }

        return $this->redirectToRoute('admin_files');
    }
}
