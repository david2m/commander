<?php
namespace David2M\Commander;

interface TaskInterface
{

    /**
     * @param object $command
     *
     * @return bool
     */
    public function supportsCommand($command);
    
}