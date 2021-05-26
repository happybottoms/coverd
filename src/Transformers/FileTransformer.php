<?php

namespace App\Transformers;

use App\Entity\File;
use League\Fractal\TransformerAbstract;

/**
 * Lightweight transformer for files
 *
 * @package App\Transformers
 */
class FileTransformer extends TransformerAbstract
{
    public function transform(File $file = null)
    {
        if (!$file) {
            return [ 'id' => null ];
        }

        return [
            'id' => $file->getPublicId(),
            'filename' => $file->getFilename(),
            'filesize' => $file->getFilesize(),
            'mimeType' => $file->getMimeType(),
            'url' => '/file/'.$file->getPublicId(),
        ];
    }
}
