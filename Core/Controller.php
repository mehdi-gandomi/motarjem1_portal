<?php
namespace Core;
use Psr\Container\ContainerInterface;
abstract class Controller{
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->view=$this->container->get("view");
        $this->flash=$this->container->get("flash");
    }
    protected function get_csrf_token($req)
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
}