<?php

class HealthController {
    private $db;
    private $startTime;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->startTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
    }

    public function check() {
        $status = 'healthy';
        $dbStatus = 'connected';

        try {
            $isHealthy = $this->db->isHealthy();
            if (!$isHealthy) {
                $status = 'unhealthy';
                $dbStatus = 'disconnected';
            }
        } catch (Exception $e) {
            $status = 'unhealthy';
            $dbStatus = 'error: ' . $e->getMessage();
        }

        $uptime = time() - (int)$this->startTime;

        http_response_code(200);
        echo json_encode([
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $dbStatus,
            'uptime_seconds' => $uptime,
            'php_version' => phpversion(),
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true)
            ]
        ]);
    }
}
