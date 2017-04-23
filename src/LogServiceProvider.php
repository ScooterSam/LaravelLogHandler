<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 23/04/17
 * Time: 02:00
 */

namespace ScooterSam\LaravelLogHandler;


use Carbon\Carbon;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\SyslogUdpHandler;

class LogServiceProvider extends ServiceProvider
{

	public function boot()
	{
		$this->publishes([
			__DIR__ . '/Config/laravel-log-provider.php' => config_path('laravel-log-provider.php'),
		]);
	}

	public function register()
	{
		if (config('laravel-log-provider.only_production') == true)
			if (app('app')->environment() == 'local') return;


		$monolog = app(Writer::class)->getMonolog();
		$syslog  = new SyslogUdpHandler("errorlogger.idevelopthings.com", 33333);
		$url     = request()->path();

		$formatter = new JsonFormatter();
		$formatter->includeStacktraces(true);

		$monolog->pushProcessor(function ($error) use ($url) {
			$error['extra'] = [
				'time'     => Carbon::now()->toDateTimeString(),
				'env'      => app('app')->environment(),
				'url'      => $url,
				'auth_key' => config('laravel-log-provider.auth_key'),
			];

			return $error;
		});

		$syslog->setFormatter($formatter);
		$monolog->setHandlers([$syslog]);
	}

}