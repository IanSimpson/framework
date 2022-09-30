<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Closure;
use Windwalker\DI\Container;

/**
 * The NewStoreDefinition class.
 */
class NewStoreDefinition implements StoreDefinitionInterface
{
    protected mixed $cache = null;

    protected array $extends = [];

    public function __construct(protected mixed $value, protected int $options)
    {
    }

    public function resolve(Container $container, array $args = []): mixed
    {
        if ($this->cache !== null && ($this->options & Container::SHARED)) {
            return $this->cache;
        }

        $value = $this->value;

        if ($this->value instanceof ObjectBuilderDefinition) {
            $this->value->addArguments($args);
            $this->value->setContainer($container);

            $value = $this->value->resolve($container);
        }

        if ($this->value instanceof Closure) {
            $value = ($this->value)($container);
        }

        // Extends
        foreach ($this->extends as $extend) {
            $value = $extend($value, $container) ?? $value;
        }

        if ($this->options & Container::SHARED) {
            $this->cache = $value;
        }

        return $value;
    }

    public function set(mixed $value): void
    {
        $this->cache = null;
        $this->value = $value;
    }

    public function isShared(): bool
    {
        return (bool) ($this->options & Container::SHARED);
    }

    public function isProtected(): bool
    {
        return (bool) ($this->options & Container::PROTECTED);
    }

    public function extend(Closure $closure): static
    {
        $this->extends[] = $closure;

        return $this;
    }

    public function reset(): void
    {
        $this->cache = null;
    }

    /**
     * @return int
     */
    public function getOptions(): int
    {
        return $this->options;
    }

    /**
     * @param  int  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(int $options): static
    {
        $this->options = $options;

        return $this;
    }
}
