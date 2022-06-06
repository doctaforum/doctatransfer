<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Helper\FileHelper;
use App\Helper\ResourceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/profile")
*/
class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile_index")
     */
    public function index(): Response
    {
        return $this->render('components/website/aside/index.html.twig', []);
    }


    /**
     * @Route("/edit-profile-info/{admin}", name="edit-profile-info")
     */
    public function editProfileInfo(Request $request, Admin $admin, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder): Response
    {
        $name = $request->get('name');
        $firstname = $request->get('firstname');

        if (!$name) {
            return new JsonResponse([
                "success" => false, 
                "msg" => "El nombre no puede estar vacío"
            ]);
        }

        if (!$firstname) {
            return new JsonResponse([
                "success" => false, 
                "msg" => "El apellido no puede estar vacío"
            ]);
        }

        $admin->setName($name);
        $admin->setFirstname($firstname);

        $entityManager->persist($admin);
        $entityManager->flush();

        return new JsonResponse(["success" => true]);
    }


    /**
     * @Route("/edit-profile-picture/{admin}", name="edit-profile-picture")
     */
    public function editProfilePicture(Request $request, Admin $admin, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $images = [];
        $images[] = $request->files->get('image');

        $fileHelper = new FileHelper();
        $imgNames = $fileHelper->loadFiles($images,"media/admins", $slugger);

        ResourceManager::deleteFile("media/admins/" . $admin->getImage());

        $admin->setImage($imgNames[0]);

        $entityManager->persist($admin);
        $entityManager->flush();

        return $this->redirectToRoute("file_index");
    }


    /**
     * @Route("/edit-security/{admin}", name="edit-security-info")
     */
    public function editSecurityProfile(Request $request, Admin $admin, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder): Response
    {
        $lastPass = $request->get('last-password');
        $newPass = $request->get('new-password');
        $repeatPass = $request->get('repeat-password');

        if ($newPass) {
            if (!$encoder->isPasswordValid($admin, $lastPass)) { 
                return new JsonResponse([
                    "success" => false, 
                    "msg" => "La contraseña antigua es errónea"
                ]);
            }
            if ($newPass != $repeatPass) { 
                return new JsonResponse([
                    "success" => false, 
                    "msg" => "Las contraseñas no coinciden"
                ]);
            }
            if ($encoder->isPasswordValid($admin, $repeatPass)) {
                return new JsonResponse([
                    "success" => false, 
                    "msg" => "La contraseña antigua y la nueva no pueden ser iguales"
                ]);
            }

            $encodedPass = $encoder->encodePassword($admin, $newPass);
            $admin->setPassword($encodedPass);
        } else {
            return new JsonResponse([
                "success" => false, 
                "msg" => "La contraseña no puede estar vacía"
            ]);
        }

        $entityManager->persist($admin);
        $entityManager->flush();

        return new JsonResponse(["success" => true]);
    }


    /**
     * @Route("/edit-profile", name="edit-profile")
     */
    public function loadEditProfile(): Response
    {
        return $this->render("components/website/aside/edit-profile.html.twig", []);
    }


    /**
     * @Route("/edit-security", name="edit-security")
     */
    public function loadEditSecurity(): Response
    {
        return $this->render("components/website/aside/security.html.twig", []);
    }

}
