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

        $stubLoginCommandHandler = $this->getFake('David2M\Commander\HandlerInterface');
        $stubLoginCommandHandler
            ->method('getCommandName')
            ->willReturn('LoginCommand');
        $this->commander->addHandler($stubLoginCommandHandler);
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

}