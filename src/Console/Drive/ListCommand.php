<?php

declare(strict_types=1);

namespace App\Console\Drive;

use App\Drive\Drive;
use App\Drive\DriveException;
use App\Command\DriveDiscoveryCommand;
use App\Command\GetSerialNumberCommand;
use App\Locker\FileLockFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drive list command.
 *
 */
class ListCommand extends Command
{
    /** @var string  Command default name */
    protected static $defaultName = 'drive:list';

    /** @var DriveDiscoveryCommand Drive detector command */
    private $driveDiscoveryCmd;

    /** @var GetSerialNumberCommand Get serial number command */
    private $getSerialNumberCmd;

    /**
     * Class constructor.
     *
     * @param DriveDiscoveryCommand $driveDiscoveryCmd Drive discovery command
     */
    public function __construct(DriveDiscoveryCommand $driveDiscoveryCmd, GetSerialNumberCommand $getSerialNumberCmd)
    {
        $this->driveDiscoveryCmd = $driveDiscoveryCmd;
        $this->getSerialNumberCmd = $getSerialNumberCmd;

        parent::__construct();
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('List all non system drives');
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
        $drives = $this->driveDiscoveryCmd->detectDrives();
        $systemDrivePath = $this->driveDiscoveryCmd->detectSystemDrive();

        $table = new Table($output);
        $table->setHeaders(['Device path', 'Rotates', 'Status', 'Serial number']);

        sort($drives, SORT_STRING);
        foreach ($drives as $path) {
            $sn = $this->getSerialNumberCmd->getSerialNumber($path);
            $locker = (new FileLockFactory())->create($path);

            $status = '<fg=green>Ready</>';
            if ($path === $systemDrivePath) {
                $status = '<fg=red>System</>';
            } else if ($locker->acquire(false) === false) {
                $status = '<fg=yellow>Testing</>';
            }

            try {
                $rotates = Drive::isRotate($path) === true ? 'True' : 'False';
            } catch (DriveException $ex) {
                $rotates = 'Undefined';
            }

            $table->addRow([
                $path,
                $rotates,
                $status,
                $sn,
            ]);
        }
        $table->render();

        return 0;
    }
}
