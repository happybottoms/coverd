<?php

namespace App\Entity\EAV;

use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class EavFile extends File
{

}
