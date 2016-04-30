<?php
namespace David2M\Commander;

use Optimus\Onion\Onion;

class Commander
{

    /* @var Onion */
    private $onion;

    /* @var HandlerInterface[] */
    private $handlers = [];

    public function __construct(Onion $onion)
    {
        $this->onion = $onion;
    }
    
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[$handler->getCommandName()] = $handler;
    }

    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->onion->layer(new MiddlewareWrapper($middleware));
    }

    public function execute($command)
    {
        $className = get_class($command);
        $handler = (isset($this->handlers[$className])) ? $this->handlers[$className] : null;

        if ($handler === null) {
            throw new HandlerNotFoundException(sprintf('No command handler found for %s.', $className));
        }

        return $this->onion->peel($command, function($command) use($handler)
        {
            return $handler->handle($command);
        });
    }

}