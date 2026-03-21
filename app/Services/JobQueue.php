<?php

namespace App\Services;

use Core\Database;

class JobQueue
{
    /**
     * Dispatch a job to the queue.
     */
    public static function dispatch(
        string $handler,
        array $payload,
        string $queue = 'default',
        ?string $delay = null
    ): int {
        $now = date('Y-m-d H:i:s');
        $availableAt = $now;

        if ($delay) {
            $availableAt = date('Y-m-d H:i:s', strtotime($delay, time()));
        }

        return Database::insert('jobs', [
            'handler'      => $handler,
            'payload'      => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'queue'        => $queue,
            'status'       => 'pending',
            'attempts'     => 0,
            'max_attempts'  => 3,
            'available_at' => $availableAt,
            'created_at'   => $now
        ]);
    }

    /**
     * Process the next available job in the queue.
     * Returns true if a job was processed, false if none available.
     */
    public static function processNext(string $queue = 'default'): bool
    {
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM jobs
                WHERE status = 'pending' AND queue = ? AND available_at <= ?
                ORDER BY id ASC
                LIMIT 1
                FOR UPDATE";

        $job = Database::fetch($sql, [$queue, $now]);

        if (!$job) {
            return false;
        }

        // Mark as processing and increment attempts
        Database::update('jobs', [
            'status'   => 'processing',
            'attempts' => $job['attempts'] + 1
        ], 'id = ?', [$job['id']]);

        try {
            $handler = $job['handler'];
            $payload = json_decode($job['payload'], true);

            $handler::handle($payload);

            Database::update('jobs', [
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$job['id']]);

        } catch (\Throwable $e) {
            $attempts = $job['attempts'] + 1;
            $maxAttempts = $job['max_attempts'] ?? 3;

            if ($attempts < $maxAttempts) {
                Database::update('jobs', [
                    'status' => 'pending'
                ], 'id = ?', [$job['id']]);
            } else {
                Database::update('jobs', [
                    'status'        => 'failed',
                    'error_message' => $e->getMessage()
                ], 'id = ?', [$job['id']]);
            }
        }

        return true;
    }

    /**
     * Run the worker: process jobs until none remain or maxJobs reached.
     * Returns count of processed jobs.
     */
    public static function runWorker(string $queue = 'default', int $maxJobs = 10): int
    {
        $processed = 0;

        while ($processed < $maxJobs) {
            if (!self::processNext($queue)) {
                break;
            }
            $processed++;
        }

        return $processed;
    }
}
