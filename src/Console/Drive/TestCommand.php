<?php

declare(strict_types=1);

namespace App\Console\Drive;

use App\Checker\CheckerFactory;
use App\Checker\SharedStatusCache;
use App\Command\DriveDiscoveryCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Jenner\SimpleFork\Process;
use App\Event\ConsoleDriveTestCommandEvent;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * Drive test command.
 *
 * Usage:
 *   sudo bin/console.php drive:test -a
 *   sudo bin/console.php drive:test /dev/sdb
 */
class TestCommand extends Command
{
    /** @var string  Command default name */
    protected static $defaultName = 'drive:test';

    /** @var DriveDiscoveryCommand Drive discovery command */
    private $driveDiscoveryCmd;

    /** @var CheckerFactory Checker factory */
    private $checkerFactory;

    /** @var SharedStatusCache Shared status cache */
    private $cache;

    /** @var EventDispatcherInterface Event dispatcher */
    private $dispatcher;

    /** @var bool Enable write test for ssd flag */
    private $ssdWriteTestEnabled;

    /**
     * Class constructor.
     *
     * @param DriveDiscoveryCommand $driveDiscoveryCmd Drive detector command
     * @param CheckerFactory $checkerFactory Checker factory
     * @param SharedStatusCache $cache Shared status cache
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     */
    public function __construct(DriveDiscoveryCommand $driveDiscoveryCmd, CheckerFactory $checkerFactory, SharedStatusCache $cache, EventDispatcherInterface $dispatcher)
    {
        $this->driveDiscoveryCmd = $driveDiscoveryCmd;
        $this->checkerFactory = $checkerFactory;
        $this->cache = $cache;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Run drive tester')
            ->addOption('auto-detect', 'a', InputOption::VALUE_NONE, 'Auto detect drives (without system drive)')
            ->addOption('force-ssd-writes', null, InputOption::VALUE_NONE, 'Enable write test for ssd')
            ->addArgument('drives', InputArgument::IS_ARRAY, 'Drives path separate with a space (e.g. \'/dev/sdb /dev/sdc\')');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input Input interface
     * @param OutputInterface $output Output interface
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array $drives */
        $drives = $input->getArgument('drives');
        if ($this->validateDrives($drives) === false) {
            $output->writeln('Invalid drive path or not exists!');
            return 3;
        }

        /** @var bool $ssdWriteTestEnabled */
        $ssdWriteTestEnabled = $input->getOption('force-ssd-writes');
        $this->ssdWriteTestEnabled = $ssdWriteTestEnabled;

        // detect system drives
        $systemDrives = $this->driveDiscoveryCmd->detectSystemDrives();
        if (count(array_intersect($systemDrives, $drives)) > 0) {
            $output->writeln('Cannot run on system drive!');
            return 2;
        }

        // auto detect drives
        if ($input->getOption('auto-detect') === true) {
            $detectedDrives = array_diff($this->driveDiscoveryCmd->detectDrives(), $systemDrives);
            $drives = array_merge($drives, $detectedDrives);
        }

        $drives = array_unique($drives, SORT_STRING);
        if (count($drives) == 0) {
            $help = new HelpCommand();
            $help->setCommand($this);
            $help->run($input, $output);
            return 1;
        }

        // run test
        $output->writeln('Drive tester result:');
        $sections = $this->createOutputSections($drives, $output);
        $statuses = $this->runTests($drives, $sections);

        // send event
        $this->dispatcher->dispatch(new ConsoleDriveTestCommandEvent($statuses));

        return 0;
    }

    /**
     * Create output sections.
     *
     * @param array $paths Drive paths
     * @param OutputInterface|null $output Console output
     * @return ConsoleSectionOutput[]
     */
    protected function createOutputSections(array $paths, ?OutputInterface $output): array
    {
        $sections = [];

        if ($output instanceof ConsoleOutput) {
            foreach ($paths as $path) {
                $sections[$path] = $output->section();
                $sections[$path]->writeln(sprintf('   %s: Testing ...', $path));
            }
        }

        return $sections;
    }

    /**
     * Run tests on specified drives.
     *
     * @param array $paths Drive paths
     * @param array $sections Console output sections
     * @return array
     */
    protected function runTests(array $paths, array $sections): array
    {
        $statuses = [];
        $processes = $this->runCheckers($paths);

        do {
            usleep(500000);
            foreach ($processes as $path => $process) {
                // update status
                $status = $this->cache->getStatus((string)$path);
                if ($status !== null) {
                    $statuses[$path] = $status;
                    if (isset($sections[$path])) {
                        $sections[$path]->overwrite(sprintf('   %s (%s): %s', $path, $status->getSerialNumber(), $status->toString()));
                    }
                }

                // remove stoped process
                if ($process->isRunning() === false) {
                    unset($processes[$path]);
                }
            }
        } while (count($processes) > 0);

        return $statuses;
    }

    /**
     * Run parallel drive checkers.
     *
     * @param array $paths Drive paths
     * @return Process[]
     */
    protected function runCheckers(array $paths): array
    {
        $processes = [];

        // started processes
        foreach ($paths as $path) {
            $checker = $this->checkerFactory->create($path, $this->ssdWriteTestEnabled);
            $process = new Process($checker);
            if (!$process->isStarted()) {
                $process->start();
            }
            $processes[$path] = $process;
        }

        return $processes;
    }

    /**
     * Validate drives.
     *
     * @param string[] $drives Drives paths
     * @return bool
     */
    protected function validateDrives(array $drives): bool
    {
        foreach ($drives as $drive) {
            if (preg_match('#/dev/sd[a-z]+#', $drive) !== 1) {
                return false;
            }
            if (file_exists($drive) === false) {
                return false;
            }
        }

        return true;
    }
}
