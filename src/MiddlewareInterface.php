<?php
namespace David2M\Commander;

interface MiddlewareInterface
{

    /**
     * @param object $command
     * @param \Closure $next
     *
     * @return mixed
     */
    public function onCommand($command, \Closure $next);

}