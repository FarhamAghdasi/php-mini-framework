<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Jobs\ExampleJob;

class CronController extends Controller
{
    public function runJob(Request $request, Response $response)
    {
        // Verify cron token from .env
        $token = $request->getHeader('X-Cron-Token');
        $validToken = $_ENV['CRON_TOKEN'] ?? '';
        
        if (!$token || $token !== $validToken) {
            $this->logger->warning('Invalid cron token attempt');
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        
        $jobName = $request->input('job');
        
        switch ($jobName) {
            case 'example':
                $job = new ExampleJob();
                $result = $job->handle();
                break;
                
            case 'cleanup':
                $result = $this->cleanupOldLogs();
                break;
                
            default:
                return $this->json(['error' => 'Job not found'], 404);
        }
        
        $this->json(['success' => true, 'result' => $result]);
    }
    
    private function cleanupOldLogs(): array
    {
        $logPath = STORAGE_PATH . '/logs';
        $files = glob($logPath . '/*.log');
        $deleted = 0;
        $threshold = time() - (30 * 24 * 60 * 60); // 30 days
        
        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
                $deleted++;
            }
        }
        
        return ['deleted_files' => $deleted];
    }
}