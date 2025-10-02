
<?php

use App\Http\Middleware\SetLocaleFromHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

it('sets locale from X-Locale header', function () {

    $request = Request::create('/dummy', 'GET', [], [], [], [
        'HTTP_X_Locale' => 'en',
    ]);

    $middleware = new SetLocaleFromHeader();

    $middleware->handle($request, fn() => response('ok'));

    expect(App::getLocale())->toBe('en');
});

it('falls back to default locale if header missing', function () {

    $request = Request::create('/dummy', 'GET');

    $middleware = new SetLocaleFromHeader();

    $middleware->handle($request, fn() => response('ok'));

    expect(App::getLocale())->toBe(config('app.locale'));
});
