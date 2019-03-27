<?php
namespace Core;
use Psr\Container\ContainerInterface;
use Slim\Http\UploadedFile;
use Slim\Http\Request;
abstract class Controller{
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->view=$this->container->get("view");
        $this->flash=$this->container->get("flash");
    }
    protected function get_csrf_token(Request $req)
    {
        $nameKey = $this->container->csrf->getTokenNameKey();
        $valueKey = $this->container->csrf->getTokenValueKey();
        $name = $req->getAttribute($nameKey);
        $value = $req->getAttribute($valueKey);

        $tokenArray = [
            $nameKey => $name,
            $valueKey => $value,
        ];
        return $tokenArray;
    }
    protected function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = \bin2hex(\random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }
}