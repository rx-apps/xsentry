<?php

declare(strict_types=1);

namespace RxApps\XSentry\Src;

use ModuleObject;
use Rhymix\Framework\Storage;

class BaseModule extends ModuleObject
{
	/**
	 * @const string SENTRY_DSN_CONFIG_PATH Configuration file path.
	 */
	protected const SENTRY_DSN_CONFIG_PATH = RX_BASEDIR . 'files/config/sentry.php';

	/**
	 * Get configured sentry DSNs.
	 *
	 * @return array{backend: array{enabled: bool, dsn: string}, frontend: array{enabled: bool, dsn: string}}
	 */
	protected function getConfiguredSentryDsn(): array
	{
		if (!file_exists(self::SENTRY_DSN_CONFIG_PATH)) {
			return [
				'backend' => [
					'enabled' => false,
					'dsn' => '',
				],
				'frontend' => [
					'enabled' => false,
					'dsn' => ''
				],
			];
		}
		return include self::SENTRY_DSN_CONFIG_PATH;
	}

	/**
	 * Set configured sentry DSNs and save into SENTRY_DSN_CONFIG_PATH.
	 *
	 * @param bool   $backendEnabled True if Sentry for backend should be enabled.
	 * @param string $backendDsn  Sentry DSN for backend (PHP).
	 * @param bool   $frontendEnabled True if Sentry for frontend should be enabled.
	 * @param string $frontendDsn Sentry DSN for frontend (JS).
	 *
	 * @return bool
	 */
	protected function setConfiguredSentryDsn(
		bool $backendEnabled, string $backendDsn, bool $frontendEnabled, string $frontendDsn
	): bool {
		$config = [
			'backend' => [
				'enabled' => $backendEnabled,
				'dsn' => $backendDsn,
			],
			'frontend' => [
				'enabled' => $frontendEnabled,
				'dsn' => $frontendDsn,
			],
		];

		$buff = implode("\n", [
			'<?php',
			'// xSentry DSN configuration',
			'return ' . var_export($config, true) . ';'
		]);
		if (!Storage::write(self::SENTRY_DSN_CONFIG_PATH, $buff)) {
			return false;
		}

		$configuredDsn = $this->getConfiguredSentryDsn();
		return json_encode($configuredDsn) === json_encode($config);
	}
}
