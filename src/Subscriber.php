<?php

namespace App;

use App\HttpFoundation\FileRemovalResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class Subscriber implements EventSubscriberInterface
{
    private $response;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', 0]
            ],
            KernelEvents::TERMINATE => [
                ['onKernelTerminate', 0]
            ]
        ];
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$response instanceof FileRemovalResponse) return;
        $this->response = $response;
        $event->setResponse($response->getOriginalResponse());
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (!$this->response instanceof FileRemovalResponse) return;
        $this->response->processQueue($this->logger);
    }
}