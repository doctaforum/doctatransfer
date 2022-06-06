<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileType;
use App\Helper\CompressFileHelper;
use App\Helper\FileHelper;
use App\Helper\RandomPassword;
use App\Helper\ResourceManager;
use App\Repository\DownloadRepository;
use App\Repository\FileRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/file")
 */
class FileController extends AbstractController
{
    /**
     * @Route("/", name="file_index", methods={"GET"})
     */
    public function index(FileRepository $fileRepository): Response
    {
        $files = $fileRepository->findByUser($this->getUser());

        return $this->render('file/index.html.twig', [
            'files' => $files,
        ]);
    }

    /**
     * @Route("/new", name="file_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $file = new File();
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFiles = $form->get('path')->getData();
            $issetExpirationDate = $form->get('set_expiration_date')->getData();

            $fileHelper = new FileHelper();

            $password = RandomPassword::generate();

            $newFileName = $fileHelper->loadCompressedFile($uploadedFiles, $slugger, $password);

            $file->setAdmin($this->getUser());
            $file->setPath($newFileName);

            $password = bin2hex($password);
            $password = base64_encode($password);
            $file->setPassword($password);

            $currentDate = new DateTime();

            $file->setCreateDate($currentDate);

            $file->setToken(uniqid() . $currentDate->format('YmdHis') . uniqid(), PASSWORD_DEFAULT);

            $fiveDaysAfter = new DateTime();
            $fiveDaysAfter = $fiveDaysAfter->modify('+5 days');

            if (! $issetExpirationDate) $file->setExpirationDate($fiveDaysAfter);

            $file->setIsActive(true);

            $entityManager->persist($file);
            $entityManager->flush();

            $lastFile = $entityManager->getRepository(File::class)->findOneBy(['token' => $file->getToken()]);

            return $this->redirectToRoute('file_show', ["id" => $lastFile->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('file/new.html.twig', [
            'file' => $file,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="file_show", methods={"GET"})
     */
    public function show(File $file, DownloadRepository $downloadRepository): Response
    {
        if ($this->getUser() != $file->getAdmin() && !in_array("ROLE_SUPER_ADMIN", $this->getUser()->getRoles())) {
            return new Response("No puedes ver archivos de otros usuarios");
        }

        if ($file->getIsDeleted()) return new Response("El archivo que buscas ha sido eliminado");

        $dates = [];

        foreach ($file->getDownloads() as $download) {
            $dates[] = $download->getDate();
        }

        $lastDownload = (count($dates) > 0) ? max($dates) : null;

        $password = $file->getPassword();
        $password = base64_decode($password);
        $password = hex2bin($password);

        $filePath = "media/transfer_files/".$file->getPath();

        $fileSize = number_format(filesize($filePath) / 1000000, 2);
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        $zip = new CompressFileHelper($filePath);
        $fileNames = $zip->readCompressFileNames();
        $zip->closeZip();

        return $this->render('file/show.html.twig', [
            'file' => $file,
            'filesize' => $fileSize,
            'fileExtension' => $fileExtension,
            'password' => $password,
            'lastDownload' => $lastDownload,
            'fileNames' => $fileNames,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="file_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, File $file, EntityManagerInterface $entityManager, SluggerInterface $slugger, FileRepository $fileRepository): Response
    {
        if ($this->getUser() != $file->getAdmin() && !in_array("ROLE_SUPER_ADMIN", $this->getUser()->getRoles())) {
            return new Response("No puedes editar archivos de otros usuarios");
        }

        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        $editedFile = $fileRepository->findOneBy(['token' => $file->getToken()]);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('path')->getData()) {
                $uploadedFiles = $form->get('path')->getData();
                $issetExpirationDate = $form->get('set_expiration_date');

                $fiveDaysAfter = new DateTime();
                $fiveDaysAfter = $fiveDaysAfter->modify('+5 days');

                if (! $issetExpirationDate) $file->setExpirationDate($fiveDaysAfter);

                $password = $file->getPassword();
                $password = base64_decode($password);
                $password = hex2bin($password);

                $fileHelper = new FileHelper();
                $newFileName = $fileHelper->loadCompressedFile($uploadedFiles, $slugger, $password);

                $directoryPath = "media/transfer_files/".explode("/", $file->getPath())[0];
                ResourceManager::deleteDir($directoryPath);

                $file->setPath($newFileName);
            } 

            $entityManager->persist($file);
            $entityManager->flush();

            return $this->redirectToRoute('file_show', ["id" => $editedFile->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('file/edit.html.twig', [
            'file' => $file,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="file_delete", methods={"POST"})
     */
    public function delete(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() != $file->getAdmin() && !in_array("ROLE_SUPER_ADMIN", $this->getUser()->getRoles())) {
            return new Response("No puedes eliminar archivos de otros usuarios");
        }

        if ($this->isCsrfTokenValid('delete'.$file->getId(), $request->request->get('_token'))) {
            $directoryPath = "media/transfer_files/".explode("/", $file->getPath())[0];

            ResourceManager::deleteDir($directoryPath);

            $file->setIsActive(false);
            $file->setIsDeleted(true);
            $entityManager->flush();
        }

        return $this->redirectToRoute('file_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/is-active/{id}", name="file_disable", methods={"POST"})
     */
    public function editIsActive(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() != $file->getAdmin() && !in_array("ROLE_SUPER_ADMIN", $this->getUser()->getRoles())) {
            return new Response("No puedes bloquear archivos de otros usuarios");
        }

        if ($this->isCsrfTokenValid('disable'.$file->getId(), $request->request->get('_token'))) {
            $file->setIsActive(!$file->getIsActive());

            $entityManager->persist($file);
            $entityManager->flush();
        }

        return $this->redirect($request->headers->get('referer'));
    }

}
