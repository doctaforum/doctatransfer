<?php

namespace App\Controller;

use App\Helper\ResourceManager;
use App\Repository\FileRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cron")
 */
class CronController extends AbstractController
{
    // RUN ONCE A DAY (NIGHT)
    /**
     * @Route("/block-files", name="file_cron_block-files", methods={"GET"})
     */
    public function blockFiles(Request $request, FileRepository $fileRepository, EntityManagerInterface $entityManager)
    {
        $currentDate = new DateTime();

        $files = $fileRepository->findExpired($currentDate);

        foreach ($files as $file) {
            $file->setIsActive(false);
        }

        $entityManager->flush();

        return new Response(true);
    }

    // RUN ONCE A DAY (NIGHT)
    /**
     * @Route("/delete-files", name="file_cron_delete-files", methods={"GET"})
     */
    public function deleteFiles(Request $request, FileRepository $fileRepository, EntityManagerInterface $entityManager)
    {
        $currentDate = new DateTime();
        $twoDaysBefore = $currentDate->modify('-2 days');

        $files = $fileRepository->findExpired($twoDaysBefore);

        foreach ($files as $file) {
            $directoryPath = "media/transfer_files/".explode("/", $file->getPath())[0];
            ResourceManager::deleteDir($directoryPath);

            $file->setIsActive(false);
            $file->setIsDeleted(true);
        }

        $entityManager->flush();

        return new Response(true);
    }


    // RUN ONCE A DAY (NIGHT)
    /**
     * @Route("/truncate-temp", name="file_cron_truncate_temp", methods={"GET"})
     */
    public function truncateTemp(Request $request)
    {
        $directorio = opendir("media/transfer_files/tmp/");

        while ($archivo = readdir($directorio))
        {
            ResourceManager::deleteFile("media/transfer_files/tmp/" . $archivo);
        }

        return new Response(true);
    }

}
