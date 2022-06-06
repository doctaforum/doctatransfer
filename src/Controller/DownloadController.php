<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\File;
use App\Form\DownloadType;
use App\Repository\DownloadRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/download")
 */
class DownloadController extends AbstractController
{
    /**
     * @Route("/", name="download_index", methods={"GET"})
     */
    public function index(DownloadRepository $downloadRepository): Response
    {
        return $this->render('download/index.html.twig', [
            'downloads' => $downloadRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{file}", name="download_new", methods={"GET", "POST"})
     */
    public function new(File $file, Request $request, EntityManagerInterface $entityManager): Response
    {
        $download = new Download();
        
        $download->setFile($file);
        $download->setDate(new DateTime());
        $download->setVirtualAddress($request->getClientIp());

        $entityManager->persist($download);
        $entityManager->flush();

        return new JsonResponse(["success" => true]);
    }

    /**
     * @Route("/{id}", name="download_show", methods={"GET"})
     */
    public function show(Download $download): Response
    {
        return $this->render('download/show.html.twig', [
            'download' => $download,
        ]);
    }

    // /**
    //  * @Route("/{id}/edit", name="download_edit", methods={"GET", "POST"})
    //  */
    // public function edit(Request $request, Download $download, EntityManagerInterface $entityManager): Response
    // {
    //     $form = $this->createForm(DownloadType::class, $download);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->flush();

    //         return $this->redirectToRoute('download_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('download/edit.html.twig', [
    //         'download' => $download,
    //         'form' => $form->createView(),
    //     ]);
    // }

    /**
     * @Route("/{id}", name="download_delete", methods={"POST"})
     */
    public function delete(Request $request, Download $download, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$download->getId(), $request->request->get('_token'))) {
            $entityManager->remove($download);
            $entityManager->flush();
        }

        return $this->redirectToRoute('download_index', [], Response::HTTP_SEE_OTHER);
    }
}
