<?php

namespace Initcore\LaravelAddon\Traits;

use Illuminate\Support\Facades\Redis;

trait WorkerManagerTrait
{

    /**
     * Represents the current time.
     *
     * @var int
     */
    public int $time;

    /**
     * Represents a key.
     *
     * @var string
     */
    public string $key;


    /**
     * Set the key value in the object.
     *
     * @param string $key The key value to set.
     * @return void
     */
    public function setLockKeyName(string $key): void
    {
        $this->key = config('laravel-addon.prefix_workers') . config('laravel-addon.redis_separator') . $key;
    }

    /**
     * Retrieves the key name for the lock.
     *
     * If the lock key name is empty, a unique identifier will be generated and set as the lock key name.
     *
     * @return string The lock key name.
     */
    public function getLockKeyName(): string
    {
        if (empty($this->key))
            $this->setLockKeyName(uniqid());

        return $this->key;
    }

    /**
     * Starts the worker by setting a new expiration time for the lock, setting the lock again, and setting the current time.
     *
     *
     * @return void
     */
    public function startWorker(): void
    {
        $this->setTime(time());
        $this->setLock();
    }

    /**
     * Refreshes the lock by setting a new expiration time for the lock and setting the lock again.
     *
     *
     * @return void
     */
    public function refreshLock(): void
    {
        $this->setLock();
    }

    /**
     * Ends the worker process by deleting the lock key from Redis.
     *
     * This method deletes the lock key that is associated with the worker process.
     * Once the lock key is deleted, the worker process is considered to be ended.
     *
     * @return void
     */
    public function endWorker(): void
    {
        Redis::del($this->getLockKeyName());
    }

    /**
     * Checks if the process is currently running or not.
     *
     * @return bool True if the process is running, false otherwise.
     */
    public function isRunning(): bool
    {
        return !(($this->getLock()) === NULL);
    }

    /**
     * Sets a lock for the current process.
     *
     * @return void
     */
    public function setLock(): void
    {
        Redis::set($this->getLockKeyName(), json_encode(
            [
                'pid' => getmypid(),
                'hostname' => gethostname(),
                'time' => time()
            ]));

        Redis::expireAt($this->getLockKeyName(), $this->getExpireAt());
    }

    /**
     * Retrieves the lock value stored in Redis.
     *
     * @return string|null The lock value as a string if it exists, or null if the lock does not exist.
     */
    public function getLock(): ?string
    {
        return Redis::getEx($this->getLockKeyName());
    }

    /**
     * Renews the worker process if it has expired based on the given expiry time.
     *
     * @return void
     */
    public function renewWorker(): void
    {
        if (time() - $this->getTime() > $this->getRefreshTime()) {
            //Log::debug('REFRESH WORKER : ' . $this->getLockKeyName());
            $this->setTime(time());
            $this->refreshLock();
        }
    }

    /**
     * Counts the number of worker processes currently active.
     *
     * @return int The number of active worker processes.
     */
    public function countWorkers(): int
    {
        return count(Redis::keys(config('laravel-addon.prefix_workers') . config('laravel-addon.redis_separator') . '*'));
    }

    /**
     * Retrieves all worker processes from Redis.
     *
     * @return array An array of worker processes retrieved from Redis.
     */
    public function getAllWorkers(): array
    {
        $keys = Redis::keys(config('laravel-addon.prefix_workers') . config('laravel-addon.redis_separator') . '*');
        //dd($workers);
        $workers = [];
        foreach ($keys as $key) {
            $workers[] = Redis::getEx($key);
        }
        return $workers;
    }

    /**
     * Kills a worker process with the given process ID.
     *
     * @param int $pid The process ID of the worker to be killed.
     *
     * @return void
     */
    public function killWorker(int $pid): void
    {
        posix_kill($pid, SIGTERM);
    }

    /**
     * Set the current time in the object.
     *
     * @param int $time The time to set.
     */
    public function setTime(int $time): void
    {
        $this->time = $time;
    }

    /**
     * Get the current time stored in the object.
     *
     * @return int The current time value.
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * Retrieves the refresh time for the lock from the application configuration.
     * The refresh time is determined by dividing the configured retry after time for the Redis connection by 1.5.
     *
     * @access private
     * @return int|float The refresh time for the lock.
     */
    private function getRefreshTime(): int|float
    {
        $retryAfter = (int)config('queue.connections.redis.retry_after');
        return $retryAfter / 1.5;
    }

    /**
     * Retrieves the expiration timestamp for the lock.
     *
     * The expiration time for the lock is calculated based on the current time and the configured retry after value
     * retrieved from the Redis connection settings. The calculated expiration time is equal to the current time plus
     * 1.5 times the retry after value.
     *
     * @return int|float The expiration timestamp for the lock.
     */
    public function getExpireAt(): int|float
    {
        return time() + ((int)config('queue.connections.redis.retry_after') * 1.5);
    }

}
