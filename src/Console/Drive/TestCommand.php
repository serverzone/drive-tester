<?php

declare(strict_types=1);

namespace App\Console\Drive;

use App\Checker\CheckerFactory;
use App\Checker\SharedStatusCache;
use App\Command\DriveDiscoveryCommand;
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
            ->addOption('auto-detect', 'a', null, 'Auto detect drives (without system drive)')
            ->addArgument('drives', InputArgument::IS_ARRAY, 'Drives path separate with a space (e.g. \'/dev/sdb /dev/sdc\')');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input Input interface
     * @param OutputInterface $output Output interface
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $systemDrive = $this->driveDiscoveryCmd->detectSystemDrive();

        /** @var array */
        $paths = $input->getArgument('drives');
        if (in_array($systemDrive, $paths, true)) {
            $output->writeln(sprintf('Cannot run on system disk %s!', $systemDrive));
            return -2;
        }

        // auto detect drives
        if ($input->getOption('auto-detect') === true) {
            $detectedDrives = array_diff($this->driveDiscoveryCmd->detectDrives(), [$systemDrive]);
            $paths = array_merge($paths, $detectedDrives);
        }

        $paths = array_unique($paths, SORT_STRING);
        if (count($paths) == 0) {
            $help = new HelpCommand();
            $help->setCommand($this);
            $help->run($input, $output);
            return -1;
        }

        // run test
        $sections = $this->createOutputSections($paths, $output);
        $output->writeln('Drive tester result:');
        $statuses = $this->runTests($paths, $sections);

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
                $status = $this->cache->getStatus((string) $path);
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
            $checker = $this->checkerFactory->create($path);
            $process = new Process($checker);
            if (!$process->isStarted()) {
                $process->start();
            }
            $processes[$path] = $process;
        }

        return $processes;
    }
}
