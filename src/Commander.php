<?php
namespace David2M\Commander;

use Optimus\Onion\Onion;

class Commander
{

    /* @var Onion */
    private $onion;

    /* @var HandlerInterface[] */
    private $handlers = [];

    /* @var PreTaskInterface[] */
    private $preTasks = [];

    /* @var PostTaskInterface[] */
    private $postTasks = [];

    /* @var ExceptionHandlerInterface[] */
    private $exceptionHandlers = [];

    public function __construct(Onion $onion)
    {
        $this->onion = $onion;
    }

    /**
     * @param HandlerInterface $handler
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[$handler->getCommandName()] = $handler;
    }

    /**
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->onion->layer(new MiddlewareWrapper($middleware));
    }

    /**
     * @param PreTaskInterface $preTask
     */
    public function addPreTask(PreTaskInterface $preTask)
    {
        $this->preTasks[] = $preTask;
    }

    /**
     * @param PostTaskInterface $postTask
     */
    public function addPostTask(PostTaskInterface $postTask)
    {
        $this->postTasks[] = $postTask;
    }

    /**
     * @param ExceptionHandlerInterface $handler
     */
    public function addExceptionHandler(ExceptionHandlerInterface $handler)
    {
        $this->exceptionHandlers[] = $handler;
    }

    /**
     * @param object $command
     * @return mixed Value returned by the command handler
     *
     * @throws HandlerNotFoundException
     * @throws \Exception
     */
    public function execute($command)
    {
        $className = get_class($command);
        $handler = (isset($this->handlers[$className])) ? $this->handlers[$className] : null;

        if ($handler === null) {
            throw new HandlerNotFoundException(sprintf('No command handler found for %s.', $className));
        }

        try {
            return $this->onion->peel($command, function($command) use($handler)
            {
                $this->runPreTasks($command);
                $result = $handler->handle($command);
                $this->runPostTasks($command, $result);

                return $result;
            });
        }
        catch (\Exception $ex) {
            $this->handleException($ex);
            throw $ex;
        }
    }

    /**
     * @param object $command
     */
    private function runPreTasks($command)
    {
        foreach ($this->preTasks as $preTask) {
            if ($preTask->supportsCommand($command)) {
                $preTask->onPreExecute($command);
            }
        }
    }

    /**
     * @param $command
     * @param mixed $result Value returned by the command handler.
     */
    private function runPostTasks($command, $result)
    {
        foreach ($this->postTasks as $postTask) {
            if ($postTask->supportsCommand($command)) {
                $postTask->onPostExecute($command, $result);
            }
        }
    }

    /**
     * @param \Exception $ex
     *
     * @throws \Exception
     */
    private function handleException(\Exception $ex)
    {
        foreach ($this->exceptionHandlers as $exceptionHandler) {
            if ($exceptionHandler->supportsException($ex)) {
                $exceptionHandler->handle($ex);
            }
        }
    }

}