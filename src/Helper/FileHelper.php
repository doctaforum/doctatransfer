<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileHelper {
    
    public function loadImage($image, $slugger)
    {
        if ($image) {
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

            try {
                $image->move("media/admins", $newFilename);
            } catch (FileException $e) {
                // handle exception
            }

            return $newFilename;
        }
    }

    public function loadFiles($files, $path, $slugger) {
        if ($files) {
            $imgNames = [];

            foreach ($files as $file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);

                $newFilename = $safeFilename . '-'.uniqid() . '.' . $file->guessExtension();
                $imgNames[] = $newFilename;

                try {
                    $file->move($path, $newFilename);
                } catch (FileException $e) {
                    return false;
                }
            }

            return $imgNames;
        }
    }


    public function loadCompressedFile($files, $slugger, $password) {
        if ($files) {
            $folderName = ResourceManager::createRandomDirByDate("media/transfer_files/");

            $newFilename = $folderName . '/archivos.zip';

            foreach ($files as $file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);

                $completeFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                CompressFileHelper::compressFile($file, $newFilename, $completeFilename, $password);
            }

            return $newFilename;
        }
    }

}