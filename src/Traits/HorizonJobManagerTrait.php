<?php

namespace MathieuAlbanese\HorizonLongTask\Traits;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

trait HorizonJobManagerTrait
{

    /**
     * Represents the current timestamp.
     *
     * @var int
     */
    public int $time;
    /**
     * Represents the key used for encryption or decryption.
     *
     * @var string
     */
    public string $key;

    /**
     * The underlying queue job instance.
     *
     * @var Job
     */
    public $job;


    /**
     * Set the key value in the object.
     *
     * @param string $key The key value to set.
     * @return void
     */
    public function setLockKeyName(string $key): void
    {
        $this->key = 'queues' . config('horizon-longtask.redis_separator') . $this->job->getQueue() . config('horizon-longtask.redis_separator') . $key;
    }

    /**
     * Returns the lock key name for the current job.
     *
     * @return string The lock key name.
     */
    public function getLockKeyName(): string
    {
        return $this->key;
    }

    /**
     * Starts the execution of the current job.
     *
     * @return void
     */
    public function startJob(): void
    {
        $this->logging('START JOB : ' . $this->job->getJobId() . ' KEY: ' . $this->getLockKeyName());
        $this->setLock();
        $this->setTime(time());
    }

    /**
     * Refreshes the lock for the current job.
     *
     * @return void
     */
    public function refreshLock(): void
    {
        $this->logging('Refresh LOCK : ' . $this->job->getJobId() . ' KEY: ' . $this->getLockKeyName());
        $this->setLock();
    }

    /**
     * Ends the current job by deleting the lock key from the Redis queue.
     *
     * This method is responsible for deleting the lock key associated with the current job from the Redis queue.
     * This is typically called when the job has completed its execution or needs to be terminated prematurely.
     *
     * @return void
     */
    public function endJob(): void
    {
        Redis::del($this->getLockKeyName());
    }

    /**
     * Determines if the job is currently running.
     *
     * This method checks if the lock for the job exists. If a lock exists, it implies that
     * the job is currently running. The check is done by calling the `getLock()` method
     * and then negating the result of the comparison between the lock and `NULL`.
     *
     * @return bool Returns `true` if the job is running, `false` otherwise.
     */
    public function isRunning(): bool
    {
        return !(($this->getLock()) === NULL);
    }

    /**
     * Sets a lock for the job.
     *
     * This method sets a lock for the job by storing the current time in Redis with the lock key name
     * obtained from getLockKeyName(). The lock is set to expire at the time obtained from getExpireAt().
     *
     * @return void
     */
    public function setLock(): void
    {
        Redis::set($this->getLockKeyName(), time());
        Redis::expireAt($this->getLockKeyName(), $this->getExpireAt());
    }

    /**
     * Returns the lock for the current instance of the class.
     *
     * The lock is obtained by calling the Redis::getEx method with the specified lock key name. The lock key name
     * is constructed by concatenating the configured Redis prefix, the project-specific Redis separator, and the value returned
     * by the getLockKeyName method.
     *
     * @return string|null The lock value for the current instance, or null if the lock does not exist.
     */
    public function getLock(): ?string
    {
        return Redis::getEx(config('database.redis.options.prefix') . config('horizon-longtask.redis_separator') . $this->getLockKeyName());
    }

    /**
     * Updates the reserved_at field for the current job if the refresh condition is met.
     *
     * @return void
     */
    public function updateReservedAt(): void
    {
        if (time() - $this->getTime() > $this->getRefreshTime()) {

            $this->logging('REFRESH JOB : ' . $this->job->getJobId());
            $this->setTime(time());

            Redis::zRem('queues:' . $this->job->getQueue() . ':reserved', $this->job->getReservedJob());
            Redis::zAdd('queues:' . $this->job->getQueue() . ':reserved', $this->getExpireAt(), $this->job->getReservedJob());


            Redis::hSet('horizon:' . $this->job->getJobId(), 'reserved_at', $this->getExpireAt());
            Redis::hSet('horizon:' . $this->job->getJobId(), 'updated_at', $this->getExpireAt());

            $this->refreshLock();
        }
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
     * Returns the refresh time for the queue job retry.
     *
     * The refresh time is calculated by dividing the configured retry after time for Redis queue
     * by a factor of 1.5. This ensures that the refresh time is slightly shorter than the configured
     * retry after time to avoid delays in processing the retry job.
     *
     * @return int|float The refresh time for the queue job retry.
     */
    private function getRefreshTime(): int|float
    {
        $retryAfter = (int)config('queue.connections.redis.retry_after');
        return $retryAfter / 1.5;
    }

    /**
     * Returns the expiration timestamp for the queue job.
     *
     * The expiration timestamp is calculated by adding the configured retry after time for Redis queue,
     * multiplied by a factor of 1.5, to the current timestamp. This ensures that the job will be considered
     * expired after the specified retry after time has passed.
     *
     * @return int|float The expiration timestamp for the queue job.
     */
    public function getExpireAt(): int|float
    {
        return time() + ((int)config('queue.connections.redis.retry_after') * 1.5);
    }

    /**
     * Logs a message if logging is enabled.
     *
     * @param string $message The message to log.
     * @return void
     */
    private function logging($message)
    {
        if (config('horizon-longtask.enable_logging')) {
            Log::debug($message);
        }
    }
}
