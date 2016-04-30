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