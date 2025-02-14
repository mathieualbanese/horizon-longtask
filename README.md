# Laravel Horizon Long Task

A Laravel package for managing long-running tasks with Laravel Horizon.

## Installation

You can install the package via composer:

```bash
composer require mathieualbanese/horizon-longtask
```

## Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --provider="MathieuAlbanese\HorizonLongTask\Providers\HorizonLongTaskServiceProvider"
```

## Usage

This package provides a trait `HorizonJobManagerTrait` that helps you manage long-running jobs in Laravel Horizon. Here's how to use it:

1. First, use the trait in your job class:

````php
use MathieuAlbanese\HorizonLongTask\Traits\HorizonJobManagerTrait;

class YourLongRunningJob implements ShouldQueue
{
    use HorizonJobManagerTrait;

    public function handle()
    {
        // 1. set the lock key name
        $this->setLockKeyName('your-job-lock-key');

        // 2. check if the job is already running with the same key name
        if ($this->isRunning()) {
            die('JOB IS ALREADY RUNNING - killed Process');
            exit();
        }

        //3. start the job
        $this->startJob();

        try {
            // 4. Your long-running logic here
            while (true) {
                // Do some work...

                // 5. Refresh the job lock to prevent timeout
                $this->updateReservedAt();

                //OR
                if (!($this->job instanceof SyncJob))
                    $this->updateReservedAt();
                //this code block is better than the other one because it will not update the reserved_at timestamp if the job is a sync job

                // 6. Optional: Check if the job is still running
                if (!$this->isRunning()) {
                    // Job was terminated externally
                    break;
                }
            }
        } finally {
            // 7. Always end the job properly
            $this->endJob();
            $this->delete(); // Delete the job from the queue
        }
    }
}

### Key Features

- **Lock Management**: The trait provides methods to manage job locks using Redis

  - `setLockKeyName()`: Set a unique lock key for your job
  - `startJob()`: Initialize the job and set the lock
  - `refreshLock()`: Manually refresh the job lock
  - `endJob()`: Clean up the job lock
  - `isRunning()`: Check if the job is still running

- **Automatic Timeout Prevention**:
  - `updateReservedAt()`: Automatically updates the job's reserved_at timestamp to prevent timeout
  - The refresh time is calculated based on your Redis queue configuration (`queue.connections.redis.retry_after`)

### Configuration

The package uses the following configuration values from `config/horizon-longtask.php`:

```php
return [
    'enable_logging' => false,
    'redis_separator' => ':'

];
````

### Best Practices

1. Always use a unique lock key for each job instance
2. Call `startJob()` at the beginning of your job
3. Call `updateReservedAt()` regularly in long-running loops
4. Use `isRunning()` to check if the job should continue
5. Always call `endJob()` when the job is complete (preferably in a `finally` block)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
