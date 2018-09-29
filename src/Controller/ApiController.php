<?php

namespace App\Controller;

use App\HttpFoundation\FileRemovalResponse;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 */
class ApiController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private function runCommand(array $command)
    {
        $this->logger->info('Running pdftk', ['command' => $command]);
        $process = new Process($command);
        $ret = $process->run(function($type, $buffer) {
            if (Process::ERR === $type) {
                $this->logger->error($buffer);
            } else {
                $this->logger->info($buffer);
            }
        });
        $this->logger->info('Finished pdftk', ['exit_code' => $ret]);
    }

    /**
     * ApiController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/ping", methods={"GET"})
     * @return JsonResponse
     */
    public function pingAction()
    {
        $uptime = (float)exec("awk '{print $1}' /proc/uptime");
        $ret = ['pong' => $uptime];
        return $this->json($ret);
    }

    /**
     * @Route("/merge", methods={"POST"})
     * @param Request $request
     * @return Response|BinaryFileResponse
     */
    public function mergeAction(Request $request)
    {
        $response = new FileRemovalResponse();
        $files = $request->files->get('files');
        if (!is_array($files) || empty($files)) {
            return new Response('No files posted.', 500);
        }
        $command = ['pdftk'];
        foreach($files as $index => $file) {
            if (!$file instanceof UploadedFile) {
                $this->logger->error('Invalid file upload', ['index' => $index]);
            } elseif (!$file->isValid()) {
                $this->logger->error($file->getErrorMessage(), [
                    'code' => $file->getError(),
                    'index' => $index,
                    'filename' => $file->getClientOriginalName()
                ]);
                $response->queue($file->getPathname());
            } else {
                $command[] = $file->getPathname();
                $response->queue($file->getPathname());
            }
        }
        if (1 == count($command)) {
            return $response->setOriginalResponse(
                new Response('No valid files posted.', 500)
            );
        } else {
            $output = tempnam(sys_get_temp_dir(),'pdftk_cat');
            $response->queue($output);
            $command = array_merge($command, ['cat', 'output', $output]);
            $this->runCommand($command);
            return $response->setOriginalResponse(
                new BinaryFileResponse($output, 200, ['Content-Type' => 'application/pdf'])
            );
        }
    }
}