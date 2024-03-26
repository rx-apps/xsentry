<?php

declare(strict_types=1);

namespace RxApps\XSentry\Src\EventHandlers;

use Context;
use ErrorException;
use Rhymix\Framework\Debug;
use RxApps\XSentry\Src\BaseModule;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\SentrySdk;
use Sentry\Serializer\RepresentationSerializer;
use Sentry\StacktraceBuilder;
use Throwable;
use function Sentry\captureEvent;
use function Sentry\captureException;
use function Sentry\init;

class RegisterSentryEventHandler extends BaseModule
{
	/**
	 * Handle before "moduleHandler.init" event.
	 *
	 * @return bool
	 */
	public function handle(): bool
	{
		[ 'backend' => $backend, 'frontend' => $frontend ] = $this->getSentryDsnConfigurations();

		if ($backend && $backend['enabled'] === true && $backend['dsn']) {
			$this->registerBackendSentry($backend['dsn']);
		}
		if ($frontend && $frontend['enabled'] === true && $frontend['dsn']) {
			$this->registerFrontendSentry($frontend['dsn']);
		}

		return true;
	}

	/**
	 * Register backend sentry with provided DSN.
	 * This captures Exception and event with backtrace stack.
	 *
	 * @param string $dsn
	 *
	 * @return void
	 */
	protected function registerBackendSentry(string $dsn): void
	{
		$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
		if (!file_exists($autoloadPath)) {
			return;
		}

		require_once($autoloadPath);
		if (!class_exists('\Sentry\SentrySdk')) {
			return;
		}

		try {
			init([ 'dsn' => $dsn ]);
		}
		catch (Throwable $e) {
			return;
		}

		set_error_handler(static function (int $errNo, string $errStr, string $errFile, int $errLine) {
			Debug::addError($errNo, $errStr, $errFile, $errLine);
		});
		set_exception_handler(static function (Throwable $e) {
			captureException($e);
			Debug::exceptionHandler($e);
		});
		register_shutdown_function(static function () {
			$errInfo = error_get_last();
			if ($errInfo === null || ($errInfo['type'] !== -1 && $errInfo['type'] !== 4)) {
				return;
			}
			$errInfo['file'] = Debug::translateFilename($errInfo['file']);

			$client = SentrySdk::getCurrentHub()->getClient();
			$options = $client->getOptions();
			$stacktraceBuilder = new StacktraceBuilder($options, new RepresentationSerializer($options));

			$event = Event::createEvent();
			$event->setStacktrace($stacktraceBuilder->buildFromBacktrace(
				debug_backtrace(2), $errInfo['file'], $errInfo['line'] - 3)
			);

			$hint = new EventHint();
			$hint->exception = new ErrorException(
				$errInfo['message'], 0, $errInfo['type'], $errInfo['file'], $errInfo['line']
			);
			captureEvent($event, $hint);
		});
	}

	protected function registerFrontendSentry(string $dsn): void
	{
		/** @noinspection HtmlUnknownTarget */
		Context::addHtmlFooter(
			sprintf('<script src="%s" crossorigin="anonymous"></script>', $dsn)
		);
	}
}
