<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator();
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();
$configurator->addConfig(__DIR__ . '/config.neon');
$configurator->addConfig(__DIR__ . '/config.local.neon');

$container = $configurator->createContainer();

return $container;
