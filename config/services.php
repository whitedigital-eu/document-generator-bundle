<?php declare(strict_types = 1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $container->parameters()
        ->set('env(PDF_HOST)', 'http://localhost')
        ->set('env(PDF_PORT)', 3000);

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$pdfHost', '%env(PDF_HOST)%')
        ->bind('$pdfPort', '%env(int:PDF_PORT)%');

    $services->load('WhiteDigital\\DocumentGeneratorBundle\\', __DIR__ . '/../src/*')
        ->exclude([__DIR__ . '/../src/{Entity}']);
};
