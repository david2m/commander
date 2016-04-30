<?php
namespace David2M\Commander;

interface ExceptionHandlerInterface
{

    /**
     * @param \Exception $ex
     *
     * @return bool
     */
    public function supportsException(\Exception $ex);

    /**
     * @param \Exception $ex
     *
     * @throws \Exception
     */
    public function handle(\Exception $ex);

}