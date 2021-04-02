<?php

class cashEventApiTransactionExternalInfoHandlersRegistry
{
    /**
     * @var array<cashEventApiTransactionExternalInfoHandlerInterface>
     */
    private $handlers = [];

    public function add(string $source, cashEventApiTransactionExternalInfoHandlerInterface $handler): void
    {
        if (!$handler instanceof cashEventApiTransactionExternalInfoHandlerInterface) {
            return;
        }

        $this->handlers[$source] = $handler;
    }

    public function has(string $source): bool
    {
        return isset($this->handlers[$source]);
    }

    public function get(string $source): cashEventApiTransactionExternalInfoHandlerInterface
    {
        if (!$this->has($source)) {
            throw new InvalidArgumentException(sprintf('No handler for %s source', $source));
        }

        return $this->handlers[$source];
    }
}
