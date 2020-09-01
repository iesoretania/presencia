<?php

namespace App\Form\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class FileImport
{
    /**
     * @Assert\File()
     * @var UploadedFile
     */
    public $file;
}
