<?php
namespace David2M\Commander;

interface PreTaskInterface extends TaskInterface
{

    /**
     * @param object $command
     */
    public function onPreExecute($command);
    
}