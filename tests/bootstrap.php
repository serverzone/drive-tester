<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();

$configurator = new Nette\Configurator();
$configurator->setTempDirectory(__DIR__ . '/../temp/tests');
$configurator->createRobotLoader()
    ->addDirectory(__DIR__ . '/../src')
    ->register();

$configurator->addConfig(__DIR__ . '/../src/config.neon');
$configurator->addConfig(__DIR__ . '/tester.neon');

return $configurator->createContainer();
