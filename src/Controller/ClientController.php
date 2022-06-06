<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\File;
use App\Helper\CompressFileHelper;
use App\Helper\ResourceManager;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;

/**
 * @Route("/descargar")
*/
class ClientController extends AbstractController
{
    /**
     * @Route("/{file}/{token}", name="descargar")
     */
    public function index(File $file, $token): Response
    {
        if ($file->getToken() !== $token) return $this->render('bundles/TwigBundle/Exception/error404.html.twig');

        // $directorio = opendir("media/transfer_files/tmp/");

        // while ($archivo = readdir($directorio))
        // {
        //     ResourceManager::deleteFile("media/transfer_files/tmp/" . $archivo);
        // }

        $filePath = "media/transfer_files/".$file->getPath();

        $fileSize = number_format(filesize($filePath) / 1000000, 2);
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        $zip = new CompressFileHelper($filePath);
        $fileNames = $zip->readCompressFileNames();
        $zip->closeZip();

        $params = [
            'token' => "K6K64cfCfZtMqbbF3Y9HJC5tYk2ts4",
            'file' => $file,
            'fileSize' => $fileSize,
            'fileExtension' => $fileExtension,
            'numberOfFiles' => count($fileNames),
        ];

        if (! $file->getIsActive()) return $this->render('client/file_blocked.html.twig', $params);

        return $this->render('client/index.html.twig', $params);
    }



    // /**
    //  * @Route("/prueba/prueba/mail-prueba", name="mail_pruebaaaaa")
    //  */
    // public function sendRegisterConfirmationEmail(MailerInterface $mailer, FileRepository $fileRepository): Response
    // {    try {
    //         $file = $fileRepository->findOneBy(["id" => 178]);

    //         $email = (new TemplatedEmail())
    //             ->from($_ENV['EMAIL_SENDER'])
    //             ->to(new Address("diego_garcia@doctaforum.com"))
    //             ->subject('Confirmación de Registro en')
    //             ->htmlTemplate("mailTemplates/fileDownloaded.html.twig")
    //             ->context([
    //                 'expiration_date' => new \DateTime('+7 days'),
    //                 'file' => $file,
    //             ]);

    //             $mailer->send($email);
    //         return new Response("True");
    //     } catch (TransportExceptionInterface $e) {
    //         return new Response("False");
    //     }

    //     return new Response("False 2");
    // }



    /**
     * @Route("/VctBRxg8LeSF9dBX/qChCvZtsLe8mqyuR/{id}", name="file_download", methods={"POST"})
     */
    public function download(Request $request, File $file, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $token = $request->get("token");
        $password = $request->get("password");

        $encodedPassword = bin2hex($password);
        $encodedPassword = base64_encode($encodedPassword);

        $filePass = $file->getPassword();

        if ($token != "K6K64cfCfZtMqbbF3Y9HJC5tYk2ts4") {
            return $this->json([
                "success" => false,
                "msg" => "Algo fue mal",
            ]);
        }

        if ($filePass != $encodedPassword) {
            return $this->json([
                "success" => false,
                "msg" => "Contraseña incorrecta",
            ]);
        }

        $path = "media/transfer_files/" . $file->getPath();

        $zip = new CompressFileHelper($path);
        $response = $zip->decompressFile($password);
        $zip->closeZip();

        if (!$response['success']) return $this->json($response);

        $zipFileNames = $response['fileNames'];

        $files = [];

        $directorio = opendir("media/transfer_files/tmp/");

        while ($archivo = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
        {
            if (is_dir($archivo) == false && in_array($archivo, $zipFileNames)) $files[] = $archivo;
        }

        $download = new Download();
        $download->setFile($file);
        $download->setDate(new \DateTime());
        $download->setVirtualAddress($request->getClientIp());

        $entityManager->persist($download);
        $entityManager->flush();

        if (count($file->getDownloads()) == 0) $subject = "Tu archivo ha sido descargado por primera vez";
        else if (count($file->getDownloads()) > 4) $subject = "Tu archivo ha sido descargado 5 veces o más";
        
        // $email = (new TemplatedEmail())
        //     ->from($_ENV['EMAIL_SENDER'])
        //     ->to($file->getAdmin()->getEmail())
        //     //->cc('cc@example.com')
        //     //->bcc('bcc@example.com')
        //     //->replyTo('fabien@example.com')
        //     //->priority(Email::PRIORITY_HIGH)
        //     ->subject("El archivo ".$file->getId()." ha sido descargado")
        //     ->htmlTemplate('mailTemplates/fileDownloaded.html.twig')
        //     ->context([
        //         'expiration_date' => new \DateTime('+7 days'),
        //         'file' => $file,
        //     ]);

        // $mailer->send($email);

        return $this->json([
            "success" => true,
            "files" => $files,
        ]);
    }

}
