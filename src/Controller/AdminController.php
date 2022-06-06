<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Form\AdminType;
use App\Helper\FileHelper;
use App\Repository\AdminRepository;
use App\Repository\DownloadRepository;
use App\Repository\FileRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Replace;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_index", methods={"GET"})
     */
    public function index(AdminRepository $adminRepository): Response
    {    
        return $this->render('admin/index.html.twig', [
            'admins' => $adminRepository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="admin_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, SluggerInterface $slugger): Response
    {
        $admin = new Admin();
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            $pass = $form->get('password')->getData();
            $repeatPass = $request->get("repeat-password");

            if (!is_null($pass)) {
                if ($pass != $repeatPass) { 
                    return $this->render('admin/new.html.twig', [
                        'formError' => "Las contraseñas no coinciden",
                        'form' => $form->createView()
                    ]);
                }
    
                $encodedPass = $encoder->encodePassword($admin, $pass);
    
                $admin->setPassword($encodedPass);
            }

            $admin->setRoles(["ROLE_ADMIN"]);
            $admin->setCreationDate(new DateTime());
            $admin->setIsActive(true);

            $fileHelper = new FileHelper();
            $newImageName = $fileHelper->loadFiles($image,"media/admins", $slugger);

            if (!isset($image)) {
                $admin->setImage("user-default.png");
            } else {
                $admin->setImage($newImageName);
            }

            $encodedPass = $encoder->encodePassword($admin, $form['password']->getData());
            $admin->setPassword($encodedPass);

            $entityManager->persist($admin);
            $entityManager->flush();

            return $this->redirectToRoute('admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/new.html.twig', [
            'admin' => $admin,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mostrar/{id}/{edit}", name="admin_show", methods={"GET"})
     */
    public function show(Admin $admin, bool $edit = false): Response
    {
        return $this->render('admin/show.html.twig', [
            'admin' => $admin,
            'edit' => $edit
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_edit", methods={"POST"})
     */
    public function edit(Request $request, Admin $admin, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserPasswordEncoderInterface $encoder): Response
    {
        $email = $request->get("email");
        $lastPass = $request->get("last-password");
        $newPass = $request->get("new-password");
        $repeatNewPass = $request->get("repeat-password");
        $name = $request->get("name");
        $firstname = $request->get("firstname");
        $image = $request->files->get("image");

        $fileHelper = new FileHelper();

        $newImageName = $fileHelper->loadFiles($image,"media/admins", $slugger);

        if ($newPass) {
            if (!$encoder->isPasswordValid($admin, $lastPass)) { 
                return $this->render('admin/show.html.twig', [
                    'admin' => $admin,
                    'edit' => true,
                    'formError' => "La contraseña antigua es errónea",
                ]);
            }
            if ($newPass != $repeatNewPass) { 
                return $this->render('admin/show.html.twig', [
                    'admin' => $admin,
                    'edit' => true,
                    'formError' => "Las contraseñas no coinciden"
                ]);
            }
            if ($encoder->isPasswordValid($admin, $repeatNewPass)) {
                return $this->render('admin/show.html.twig', [
                    'admin' => $admin,
                    'edit' => true,
                    'formError' => "Las contraseñas antigua y la nueva no pueden ser iguales"
                ]);
            }

            $encodedPass = $encoder->encodePassword($admin, $newPass);

            $admin->setPassword($encodedPass);
        }

        $admin->setEmail($email);
        $admin->setName($name);
        $admin->setFirstname($firstname);
        $admin->setImage($newImageName);

        $entityManager->persist($admin);
        $entityManager->flush();

        return $this->redirectToRoute('admin_show', ["id" => $admin->getId()], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/{id}/enable", name="admin_enable", methods={"GET"})
     */
    public function enable(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        $admin->setIsActive(true);

        $entityManager->persist($admin);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }


    /**
     * @Route("/{id}/disable", name="admin_disable", methods={"GET"})
     */
    public function disable(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        $admin->setIsActive(false);

        $entityManager->persist($admin);
        $entityManager->flush();


        return $this->redirect($request->headers->get('referer'));
    }


    /**
     * @Route("/{id}", name="admin_delete", methods={"POST"})
     */
    public function delete(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$admin->getId(), $request->request->get('_token'))) {
            $entityManager->remove($admin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/files/", name="admin_files", methods={"GET"})
     */
    public function files(Request $request, FileRepository $fileRepository): Response
    {
        $files = $fileRepository->findAll();

        return $this->render('admin/files.html.twig', [
            'files' => $files,
        ]);
    }

    /**
     * @Route("/downloads/", name="admin_downloads", methods={"GET"})
     */
    public function downloads(Request $request, DownloadRepository $downloadRepository): Response
    {
        $downloads = $downloadRepository->findAll();

        return $this->render('admin/downloads.html.twig', [
            'downloads' => $downloads,
        ]);
    }
}
