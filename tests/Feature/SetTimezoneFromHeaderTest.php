
<?php

use App\Http\Middleware\SetTimezoneFromHeader;
use Illuminate\Http\Request;

it('sets timezone from X-Utc header with positive offset', function () {

    $request = Request::create('/dummy', 'GET', [], [], [], [
        'HTTP_X_UTC' => '+3',
    ]);

    $middleware = new SetTimezoneFromHeader();

    $middleware->handle($request, fn() => response('ok'));

    expect(config('app.timezone'))->toBe('Etc/GMT-3');
    expect(date_default_timezone_get())->toBe('Etc/GMT-3');
});

it('sets timezone from X-Utc header with negative offset', function () {

    $request = Request::create('/dummy', 'GET', [], [], [], [
        'HTTP_X_UTC' => '-1',
    ]);

    $middleware = new SetTimezoneFromHeader();

    $middleware->handle($request, fn() => response('ok'));

    expect(config('app.timezone'))->toBe('Etc/GMT+1');
    expect(date_default_timezone_get())->toBe('Etc/GMT+1');
});

it('sets timezone from default locale if header missing', function () {

    $request = Request::create('/dummy', 'GET');

    $middleware = new SetTimezoneFromHeader();

    $middleware->handle($request, fn() => response('ok'));

    expect(config('app.timezone'))->toBe('UTC');
    expect(date_default_timezone_get())->toBe('UTC');
});
