<?php

class cashEventApiTransactionExternalInfoHandlersRegistry
{
    /**
     * @var array<cashEventApiTransactionExternalInfoHandlerInterface>
     */
    private $handlers = [];

    public function add(cashEventApiTransactionExternalInfoHandlerInterface $handler): void
    {
        $this->handlers[$handler->getSource()] = $handler;
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
