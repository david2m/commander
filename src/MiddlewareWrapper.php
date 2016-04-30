<?php
namespace David2M\Commander;

use Optimus\Onion\LayerInterface;

class MiddlewareWrapper implements LayerInterface
{

    /* @var MiddlewareInterface */
    private $middleware;

    public function __construct(MiddlewareInterface $middleware)
    {
        $this->middleware = $middleware;
    }

    public function peel($object, \Closure $next)
    {
        return $this->middleware->onCommand($object, $next);
    }

}