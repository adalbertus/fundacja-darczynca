<?php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

// #[AsEventListener]
class ExceptionListener
{
    public function __construct(private LoggerInterface $logger)
    {

    }
    public function __invoke(ExceptionEvent $event): void
    {
        // uwaga zakomentowany atrybut AsEventListener!
        // based on Symfony\Component\HttpKernel\EventListener\ErrorListener
        // You get the exception object from the received event
        $throwable = $event->getThrowable();
        $e = FlattenException::createFromThrowable($throwable);

        $this->logException(
            $throwable,
            sprintf(
                '####### Uncaught PHP Exception %s: "%s" at %s line %s',
                $e->getClass(),
                $e->getMessage(),
                $e->getFile(), $e->getLine()
            )
        );

    }
    protected function logException(\Throwable $exception, string $message, string $logLevel = null): void
    {
        if (null !== $this->logger) {
            if (null !== $logLevel) {
                $this->logger->log($logLevel, $message, ['exception' => $exception]);
            } elseif (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message, ['exception' => $exception]);
            } else {
                $this->logger->error($message, ['exception' => $exception]);
            }
        }
    }

}