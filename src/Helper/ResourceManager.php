<?php

namespace App\Helper;

use InvalidArgumentException;

class ResourceManager
{

    public static function createDir($path, $folderName)
    {
        return mkdir($path . $folderName);
    }


    public static function createRandomDir($path)
    {
        $folderName = [];

        for ($i = 0; $i < 9; $i++) {
            $folderName[] = rand(0, 9);
        }

        $folderName = implode($folderName);

        if (ResourceManager::createDir($path, $folderName)) return $folderName;

        return false;
    }


    public static function createRandomDirByDate($path)
    {
        $date = new \DateTime();
        $folderName = $date->format('YmdHis');

        if (ResourceManager::createDir($path, $folderName)) return $folderName;

        return false;
    }


    public static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }

        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }

        $files = glob($dirPath . '*', GLOB_MARK);

        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                ResourceManager::deleteFile($file);
            }
        }

        return rmdir($dirPath);
    }


    public static function deleteFile($path) {
        if (! is_dir($path)) {
            return unlink($path);
        }

        return false;
    }

}