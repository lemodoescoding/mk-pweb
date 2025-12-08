<?php

declare(strict_types=1);
namespace App\Controllers;

use App\App;
use App\View;
use PDO;

if(!isset($_SESSION['login']))
{
    header('Location: /login');
    exit;
}

class UploadController
{
    public function index(): View
    {
        return View::make('upload/index');
    }
    
    public function process()
    {
        $db = App::db();
        
        $uploadedFile = $_FILES['myFile'];
        
        $fileName = $uploadedFile['name'];
        $fileSize = (int) $uploadedFile['size'];
        $fileType = $uploadedFile['type'];
        $fileTemp = $uploadedFile['tmp_name'];
        $fileErr = $uploadedFile['error'];
        
        if($fileSize > 4096000){
            echo 'File Size is too big';
            exit;
        }
        $newFileName = uniqid() . '_' . $fileName;
        
        $stmt = $db->prepare('INSERT INTO file (filename, filesize, upload_at, filetype)
                                    VALUES
                                  (:filename, :filesize, NOW(), :filetype)');
        
        $stmt->bindValue(':filename', $newFileName, PDO::PARAM_STR);
        $stmt->bindValue(':filesize', $fileSize, PDO::PARAM_INT);
        $stmt->bindValue(':filetype', $fileType, PDO::PARAM_STR);
        
        $stmt->execute();
        
        $uploadDir = STORAGE_PATH . '/' . $newFileName;
        
        move_uploaded_file($fileTemp, $uploadDir);
        
        header('Location: /upload');
        exit;
    }
}
