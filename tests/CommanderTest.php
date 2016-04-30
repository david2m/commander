<?php
namespace Tests\David2M\Commander;

use David2M\Commander\Commander;

require('fixtures.php');

class CommanderTest extends \PHPUnit_Framework_TestCase
{

    /* @var \PHPUnit_Framework_MockObject_MockObject */
    private $fakeOnion;

    /* @var Commander */
    private $commander;
    
    public function setUp()
    {
        $this->fakeOnion = $this->getFake('Optimus\Onion\Onion');
        $this->commander = new Commander($this->fakeOnion);
    }

    /**
     * @expectedException \David2M\Commander\HandlerNotFoundException
     */
    public function test_execute_commandHandlerNotFound_throwHandlerNotFoundException()
    {
        $this->commander->execute(new \AddToCartCommand());
    }

    public function test_execute_commandHandlerFound_callTheCommandHandler()
    {
        $command = new \AddToCartCommand();
        $result = true;

        $mockHandler = $this->getFake('David2M\Commander\HandlerInterface');
        $mockHandler
            ->method('getCommandName')
            ->willReturn('AddToCartCommand');
        $mockHandler
            ->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willReturn($result);

        $this
            ->fakeOnion
            ->method('peel')
            ->willReturnCallback(function($command, \Closure $core) use ($result)
            {
                $actual = $core($command);
                $this->assertSame($result, $actual);
            });

        $this->commander->addHandler($mockHandler);

        $this->commander->execute($command);
    }

    public function test_shouldNotRunPreTask()
    {
        $stubCommandHandler = $this->getStubCommandHandler('LoginCommand', true);
        $this->commander->addHandler($stubCommandHandler);

        $mockPreTask = $this->getFake('David2M\Commander\PreTaskInterface');
        $mockPreTask
            ->method('supportsCommand')
            ->willReturn(false);
        $mockPreTask
            ->expects($this->never())
            ->method('onPreExecute');
        $this
            ->fakeOnion
            ->method('peel')
            ->willReturnCallback(function($command, \Closure $core)
            {
                $core($command);
            });

        $this->commander->addPreTask($mockPreTask);

        $this->commander->execute(new \LoginCommand());
    }

    public function test_shouldRunPreTask()
    {
        $stubCommandHandler = $this->getStubCommandHandler('LoginCommand', true);
        $this->commander->addHandler($stubCommandHandler);

        $command = new \LoginCommand();
        $mockPreTask = $this->getFake('David2M\Commander\PreTaskInterface');
        $mockPreTask
            ->method('supportsCommand')
            ->willReturn(true);
        $mockPreTask
            ->expects($this->once())
            ->method('onPreExecute')
            ->with($command);
        $this
            ->fakeOnion
            ->method('peel')
            ->willReturnCallback(function($command, \Closure $core)
            {
                $core($command);
            });

        $this->commander->addPreTask($mockPreTask);

        $this->commander->execute($command);
    }

    public function test_shouldNotRunPostTask()
    {
        $stubCommandHandler = $this->getStubCommandHandler('LoginCommand', true);
        $this->commander->addHandler($stubCommandHandler);

        $mockPostTask = $this->getFake('David2M\Commander\PostTaskInterface');
        $mockPostTask
            ->method('supportsCommand')
            ->willReturn(false);
        $mockPostTask
            ->expects($this->never())
            ->method('onPostExecute');
        $this
            ->fakeOnion
            ->method('peel')
            ->willReturnCallback(function($command, \Closure $core)
            {
                $core($command);
            });

        $this->commander->addPostTask($mockPostTask);

        $this->commander->execute(new \LoginCommand());
    }

    public function test_shouldRunPostTask()
    {
        $commandHandlerReturnValue = true;
        $stubCommandHandler = $this->getStubCommandHandler('LoginCommand', $commandHandlerReturnValue);
        $this->commander->addHandler($stubCommandHandler);

        $command = new \LoginCommand();
        $mockPostTask = $this->getFake('David2M\Commander\PostTaskInterface');
        $mockPostTask
            ->method('supportsCommand')
            ->willReturn(true);
        $mockPostTask
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($command, $commandHandlerReturnValue);
        $this
            ->fakeOnion
            ->method('peel')
            ->willReturnCallback(function($command, \Closure $core)
            {
                $core($command);
            });

        $this->commander->addPostTask($mockPostTask);

        $this->commander->execute($command);
    }

    /**
     * @expectedException \LoginException
     */
    public function test_addedExceptionHandlerShouldNotHandleException()
    {
        $stubExceptionHandler = $this->getStubExceptionHandler(false);

        $stubCommandHandler = $this->getStubCommandHandler('LoginCommand', null);
        $stubCommandHandler
            ->method('handle')
            ->willThrowException(new \LoginException());
        $this
            ->fakeOnion
            ->method('peel')
            ->willReturnCallback(function($command, \Closure $core)
            {
                $core($command);
            });

        $this->commander->addExceptionHandler($stubExceptionHandler);
        $this->commander->addHandler($stubCommandHandler);
        
        $this->commander->execute(new \LoginCommand());
    }

    /**
     * @expectedException \GenericException
     */
    public function test_addedExceptionHandlerShouldHandleException()
    {
        $stubExceptionHandler = $this->getStubExceptionHandler(true, new \GenericException());

        $stubCommandHandler = $this->getStubCommandHandler('LoginCommand', null);
        $stubCommandHandler
            ->method('handle')
            ->willThrowException(new \LoginException());
        $this
            ->fakeOnion
            ->method('peel')
            ->willReturnCallback(function($command, \Closure $core)
            {
                $core($command);
            });

        $this->commander->addExceptionHandler($stubExceptionHandler);
        $this->commander->addHandler($stubCommandHandler);

        $this->commander->execute(new \LoginCommand());
    }

    /**
     * @param string $className
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFake($className)
    {
        return $this
            ->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getStubCommandHandler($commandName, $returnValue = null)
    {
        $stubHandler = $this->getFake('David2M\Commander\HandlerInterface');
        $stubHandler
            ->method('getCommandName')
            ->willReturn($commandName);

        $stubHandler
            ->method('handle')
            ->willReturn($returnValue);

        return $stubHandler;
    }

    private function getStubExceptionHandler($supportsException, \Exception $throws = null)
    {
        $stubHandler = $this->getFake('David2M\Commander\ExceptionHandlerInterface');
        $stubHandler
            ->method('supportsException')
            ->willReturn($supportsException);

        if ($throws) {
            $stubHandler
                ->method('handle')
                ->willThrowException(new \GenericException());
        }

        return $stubHandler;
    }

}