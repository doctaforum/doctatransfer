<?php

namespace App\Helper;

use ZipArchive;
use Exception;

class CompressFileHelper {

    private $zipFile;
    private $path;
    private $size;

    public function __construct(string $path)
    {
        $this->path = $path;

        $zip = new ZipArchive();

        if (! $zip->open($this->path) === TRUE) return new Exception("Compress file wasn't found");

        $this->zipFile = $zip;
    }
    

    /**
     * Compress one file into a new or existent .zip file
     */
    public static function compressFile($file, string $fileName, string $compressFolderPath, string $zipName, string $password = null)
    {
        $zip = new ZipArchive();

        if (! $zip->open($compressFolderPath . "/" . $zipName, \ZipArchive::CREATE)) return new Exception("Directory wasn't found");

        if ($password) $zip->setPassword($password);

        $content = file_get_contents($file->getRealPath());

        $zip->addFromString($fileName, $content);
        $zip->setEncryptionName($fileName, \ZipArchive::EM_AES_256);

        $zip->close();

        return true;
    }


    /**
     * Decompress one .zip file into a choosen directory
     */
    public function decompressFile($password = "", string $decompressDirectoryPath)
    {
        $zip = $this->zipFile;

        if ($zip->setPassword($password)) {
            if (! $zip->extractTo($decompressDirectoryPath)) return ["success" => false, "msg" => "Tmp folder wasn't found"];
            else return ["success" => true, "fileNames" => $this->readCompressFileNames()];
        } else return ["success" => false, "msg" => "Contraseña incorrecta"];
    }


    /**
     * Return all file names into .zip file
     */
    public function readCompressFileNames()
    {
        $zip = $this->zipFile;
        $fileNames = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileNames[] = $zip->getNameIndex($i);
        }

        return $fileNames;
    }


    /**
     * Close the zip used
     */
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


    /**
     * Get the value of size
     */
    public function getSize()
    {
        return $this->size;
    }
}