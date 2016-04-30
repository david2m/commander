<?php
namespace Tests\David2M\Commander\MiddlewareWrapper;

use David2M\Commander\MiddlewareWrapper;

class MiddlewareWrapperTest extends \PHPUnit_Framework_TestCase
{
    
    public function test_peel_shouldCallWrappedMiddleware()
    {
        $command = new \stdClass();
        $next = function(){};
        
        $mockMiddleware = $this->getMock('David2M\Commander\MiddlewareInterface');
        $mockMiddleware
            ->expects($this->once())
            ->method('onCommand')
            ->with($command, $next);
        
        $middlewareWrapper = new MiddlewareWrapper($mockMiddleware);
        
        $middlewareWrapper->peel($command, $next);
    }
    
}