<?php
namespace David2M\Commander;

interface PostTaskInterface extends TaskInterface
{

    /**
     * @param object $command
     * @param mixed $result Value returned by the command handler.
     */
    public function onPostExecute($command, $result);

}