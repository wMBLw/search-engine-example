<?php

namespace App\Jobs;

use App\Models\UserLoginLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jenssegers\Agent\Agent;

class LogUserLoginJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];
    public $timeout = 30;

    public function __construct(
        public int $userId,
        public string $ipAddress,
        public string $userAgent,
    ) {}

    public function handle(): void
    {
        $agent = new Agent();
        $agent->setUserAgent($this->userAgent);

        UserLoginLog::create([
            'user_id' => $this->userId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'device_type' => $this->getDeviceType($agent),
            'logged_in_at' => now(),
        ]);
    }

    private function getDeviceType(Agent $agent): string
    {
        if ($agent->isDesktop()) {
            return 'desktop';
        }

        if ($agent->isTablet()) {
            return 'tablet';
        }

        if ($agent->isMobile()) {
            return 'mobile';
        }

        return 'unknown';
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to log user login', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
