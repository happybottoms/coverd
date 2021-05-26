<?php


namespace App\Controller;

use App\Entity\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FileController
 * @package App\Controller
 *
 * @Route(path="/file")
 */
class FileController extends BaseController
{
    /**
     * @Route(path="/{publicId}", methods={"GET"})
     */
    public function download(Request $request, Filesystem $filesystem, string $publicId): BinaryFileResponse
    {
        $file = $this->getEm()->getRepository(File::class)->findOneBy(['publicId' => $publicId]);

        if (!$file) {
            throw new NotFoundHttpException('Could not find file');
        }

        $tmpFileName = $filesystem->tempnam(sys_get_temp_dir(), 'sb_');
        $tmpFile = fopen($tmpFileName, 'wb+');
//        if (!is_resource($tmpFile)) {
//            throw new \RuntimeException('Unable to create a temporary file.');
//        }

        file_put_contents($tmpFileName, fread($file->getContent(), $file->getFilesize()));

        $response = $this->file(stream_get_meta_data($tmpFile)['uri'], $file->getFilename());
        $response->headers->set('Content-type', $file->getMimeType());

        return $response;
    }
}
