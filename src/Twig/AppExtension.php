<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('file_exists', [$this, 'fileExist']),
        ];
    }

    public function fileExist($fileName)
    {
        $appPath = $this->container->getParameter('kernel.root_dir');
        $publicPath = realpath($appPath . '/../public/');
        return file_exists($publicPath . $fileName);
    }
}