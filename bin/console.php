#!/usr/bin/env php
<?php

declare(strict_types=1);

$container = require __DIR__ . '/../src/bootstrap.php';

exit($container->getByType(Contributte\Console\Application::class)->run());
