<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

use Windwalker\Queue\QueueMessage;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The SyncQueueDriver class.
 *
 * @since  3.2
 */
class SyncQueueDriver implements QueueDriverInterface
{
    use OptionAccessTrait;

    /**
     * @var callable
     */
    protected $handler;

    /**
     * SyncQueueDriver constructor.
     *
     * @param  callable|null  $handler
     * @param  array          $options
     */
    public function __construct(?callable $handler = null, array $options = [])
    {
        $this->handler = $handler ?? static::getDefaultHandler();
        $this->prepareOptions(
            [
                'debug' => false
            ],
            $options
        );
    }

    /**
     * push
     *
     * @param  QueueMessage  $message
     *
     * @return string
     */
    public function push(QueueMessage $message): string
    {
        $output = ($this->handler)($message);

        $debug = $this->getOption('debug');

        if ($debug) {
            if (is_callable($debug)) {
                $debug($output, $this);
            } else {
                show($output);
            }
        }

        return (string) $output;
    }

    /**
     * @return callable
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    /**
     * @param  callable  $handler
     *
     * @return  static  Return self to support chaining.
     */
    public function setHandler(callable $handler): static
    {
        $this->handler = $handler;

        return $this;
    }

    protected function runJob(callable $job)
    {
        return $job();
    }

    /**
     * pop
     *
     * @param  string|null  $channel
     *
     * @return QueueMessage|null
     */
    public function pop(?string $channel = null): ?QueueMessage
    {
        return null;
    }

    /**
     * delete
     *
     * @param  QueueMessage  $message
     *
     * @return SyncQueueDriver
     */
    public function delete(QueueMessage $message): static
    {
        return $this;
    }

    /**
     * release
     *
     * @param  QueueMessage  $message
     *
     * @return static
     */
    public function release(QueueMessage $message): static
    {
        return $this;
    }

    public static function getDefaultHandler(): \Closure
    {
        return function (QueueMessage $message) {
            $job = unserialize($message->getJob());

            $job->execute();
        };
    }
}
