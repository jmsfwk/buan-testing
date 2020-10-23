<?php

namespace Buan\Testing;

use Buan\Testing\Concerns\InteractsWithDatabase;
use Throwable;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use InteractsWithDatabase;

    /** @var callable[] The callbacks that should be run during setup. */
    protected $duringSetupCallbacks = [];
    /** @var callable[] The callbacks that should be run during teardown. */
    protected $duringTeardownCallbacks = [];
    /** @var bool Indicates if we have made it through the base setUp function. */
    protected $setUpHasRun = false;
    /** @var Throwable The exception thrown while running an application destruction callback. */
    protected $callbackException;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTraits();

        foreach ($this->duringSetupCallbacks as $callback) {
            $callback();
        }

        $this->setUpHasRun = true;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->callDuringTeardownCallbacks();
    }

    /**
     * Register a callback to be run after the application is created.
     */
    public function duringSetup(callable $callback): void
    {
        $this->duringSetupCallbacks[] = $callback;

        if ($this->setUpHasRun) {
            $callback();
        }
    }

    /**
     * Register a callback to be run before the application is destroyed.
     */
    protected function duringTeardown(callable $callback): void
    {
        $this->duringTeardownCallbacks[] = $callback;
    }

    /**
     * Execute the application's pre-destruction callbacks.
     */
    protected function callDuringTeardownCallbacks(): void
    {
        foreach ($this->duringTeardownCallbacks as $callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                if (! $this->callbackException) {
                    $this->callbackException = $e;
                }
            }
        }
    }

    /**
     * Boot the testing helper traits.
     */
    protected function setUpTraits(): void
    {
        $uses = array_flip(class_uses(static::class));

        if (isset($uses[DatabaseTransactions::class])) {
            $this->beginDatabaseTransaction();
        }
    }
}
