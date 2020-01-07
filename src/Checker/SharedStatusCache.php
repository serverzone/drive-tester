<?php

declare(strict_types=1);

namespace App\Checker;

use Jenner\SimpleFork\Cache\SharedMemory;

/**
 * Shared status cache.
 */
class SharedStatusCache
{
    /** @var string Cache file path */
    protected $cacheFilePath;

    /**  @var SharedMemory Shared cache memory */
    protected $cache;

    /**
     * Class construtor.
     *
     * @param string $pathPrefix Cache file path prefix
     */
    public function __construct(string $pathPrefix = '/tmp')
    {
        $cacheFilePath = tempnam($pathPrefix, 'drive-tester-shm');
        if ($cacheFilePath === false) {
            throw new \RuntimeException('Can not join to shared status cache!');
        }
        $this->cacheFilePath = $cacheFilePath;
        $this->cache = new SharedMemory(128 * 1024, $this->cacheFilePath);
    }

    /**
     * Class destructor.
     */
    public function __destruct()
    {
        if (file_exists($this->cacheFilePath)) {
            $this->cache->remove();
            unlink($this->cacheFilePath);
        }
    }

    /**
     * Get and remove status from cache.
     *
     * @param string $path Drive path (e.g. /dev/sda)
     * @return Status|null
     */
    public function getStatus(string $path): ?Status
    {
        $status = null;

        if ($this->cache->has($path)) {
            $json = $this->cache->get($path);
            if (is_string($json)) {
                $status = Status::fromJsonString($json);
            }
            $this->cache->delete($path);
        }

        return $status;
    }

    /**
     * Set status to cache.
     *
     * @param string $path Drive path (e.g. /dev/sda)
     * @param Status $status Status
     * @return void
     */
    public function setStatus(string $path, Status $status): void
    {
        $this->cache->set($path, $status->toJsonString());
    }
}
