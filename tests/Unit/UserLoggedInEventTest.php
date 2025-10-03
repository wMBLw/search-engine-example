<?php

use App\Events\UserLoggedIn;
use App\Jobs\LogUserLoginJob;
use App\Listeners\LogUserLogin;
use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('dispatches UserLoggedIn event', function () {
    Event::fake();

    event(new UserLoggedIn(
        user: $this->user,
        ipAddress: '127.0.0.1',
        userAgent: 'Mozilla/5.0',
    ));

    Event::assertDispatched(UserLoggedIn::class, function ($event) {
        return $event->user->id === $this->user->id
            && $event->ipAddress === '127.0.0.1'
            && $event->userAgent === 'Mozilla/5.0';
    });
});

it('listener dispatches job', function () {
    Queue::fake();

    $event = new UserLoggedIn(
        user: $this->user,
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0',
    );

    $listener = new LogUserLogin();
    $listener->handle($event);

    Queue::assertPushed(LogUserLoginJob::class, function ($job) {
        return $job->userId === $this->user->id
            && $job->ipAddress === '192.168.1.1'
            && $job->userAgent === 'Mozilla/5.0'
            && $job->queue === 'login-logs';
    });
});

it('job creates login log', function () {
    $job = new LogUserLoginJob(
        userId: $this->user->id,
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    );

    $job->handle();

    $this->assertDatabaseHas('user_login_logs', [
        'user_id' => $this->user->id,
        'ip_address' => '192.168.1.1',
    ]);

    $log = UserLoginLog::where('user_id', $this->user->id)->first();
    expect($log->device_type)->toBe('desktop');
    expect($log->user_agent)->toBe('Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
});

it('job detects mobile device', function () {
    $job = new LogUserLoginJob(
        userId: $this->user->id,
        ipAddress: '10.0.0.1',
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
    );

    $job->handle();

    $log = UserLoginLog::where('user_id', $this->user->id)->first();
    expect($log->device_type)->toBe('mobile');
});

it('job detects tablet device', function () {
    $job = new LogUserLoginJob(
        userId: $this->user->id,
        ipAddress: '10.0.0.1',
        userAgent: 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X)',
    );

    $job->handle();

    $log = UserLoginLog::where('user_id', $this->user->id)->first();
    expect($log->device_type)->toBe('tablet');
});

it('job has correct queue configuration', function () {
    $job = new LogUserLoginJob(
        userId: $this->user->id,
        ipAddress: '127.0.0.1',
        userAgent: 'Test',
    );

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe([10, 30, 60]);
    expect($job->timeout)->toBe(30);
});

it('logs error when job fails', function () {
    $job = new LogUserLoginJob(
        userId: $this->user->id,
        ipAddress: '127.0.0.1',
        userAgent: 'Test',
    );

    $exception = new \Exception('Database connection failed');

    \Log::shouldReceive('error')
        ->once()
        ->with('Failed to log user login', \Mockery::on(function ($data) {
            return $data['user_id'] === $this->user->id
                && $data['error'] === 'Database connection failed'
                && isset($data['trace']);
        }));

    $job->failed($exception);
});

it('event listener and job integration works', function () {
    Event::fake([UserLoggedIn::class]);
    Queue::fake();

    event(new UserLoggedIn(
        user: $this->user,
        ipAddress: '127.0.0.1',
        userAgent: 'Integration Test',
    ));

    Event::assertDispatched(UserLoggedIn::class);
    Event::assertListening(UserLoggedIn::class, LogUserLogin::class);
});
