<?php

namespace App\Helper;

use Exception;
use ZipArchive;

class CompressFileHelper {

    private $zipFile;
    private static $folder = 'media/transfer_files/';
    private static $tmpFolder = "media/transfer_files/tmp";
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;

        $zip = new ZipArchive();

        if (! $zip->open($this->path) === TRUE) return new Exception("Compress file wasn't found");

        $this->zipFile = $zip;
    }
    

    public static function compressFile($file, $compressedFileName, $path, $password = null)
    {
        $zip = new ZipArchive();

        $zip->open(CompressFileHelper::$folder . $compressedFileName, \ZipArchive::CREATE);

        if ($password) $zip->setPassword($password);

        $content = file_get_contents($file->getRealPath());

        $zip->addFromString($path, $content);
        $zip->setEncryptionName($path, \ZipArchive::EM_AES_256);

        $zip->close();

        return true;
    }


    public function decompressFile($password)
    {
        $zip = $this->zipFile;

        if ($zip->setPassword($password)) {
            if (! $zip->extractTo(CompressFileHelper::$tmpFolder)) return ["success" => false, "msg" => "Tmp folder wasn't found"];
            else return ["success" => true, "fileNames" => $this->readCompressFileNames()];
        } else return ["success" => false, "msg" => "Contraseña incorrecta"];
    }


    public function readCompressFileNames()
    {
        $zip = $this->zipFile;
        $fileNames = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileNames[] = $zip->getNameIndex($i);
        }

        return $fileNames;
    }


    public function closeZip()
    {
        $this->zipFile->close();
    }


    /**
     * Get the value of zipFile
     */
    public function getZipFile()
    {
        return $this->zipFile;
    }

    /**
     * Get the value of path
     */
    public function getPath()
    {
        return $this->path;
    }

}