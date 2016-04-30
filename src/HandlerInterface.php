<?php
namespace David2M\Commander;

interface HandlerInterface
{

    /**
     * @return string
     */
    public function getCommandName();

    /**
     * @param object $command
     *
     * @return mixed
     */
    public function handle($command);

}