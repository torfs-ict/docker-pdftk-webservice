<?php

namespace App\HttpFoundation;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class FileRemovalResponse extends Response
{
    /**
     * @var Response
     */
    private $originalResponse;
    /**
     * @var array
     */
    private $queue = [];

    /**
     * @return Response
     */
    public function getOriginalResponse(): Response
    {
        return $this->originalResponse;
    }

    /**
     * @param Response $originalResponse
     * @return FileRemovalResponse
     */
    public function setOriginalResponse(Response $originalResponse): FileRemovalResponse
    {
        $this->originalResponse = $originalResponse;
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return FileRemovalResponse
     */
    public function processQueue(LoggerInterface $logger): FileRemovalResponse
    {
        foreach($this->queue as $path) {
            if (!is_file($path)) continue;
            $logger->info('Deleting temporary file', ['path' => $path]);
            @unlink($path);
        }
        return $this;
    }

    /**
     * @param string $path
     * @return FileRemovalResponse
     */
    public function queue(string $path): FileRemovalResponse
    {
        $this->queue[] = $path;
        return $this;
    }
}