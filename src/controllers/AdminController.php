<?php

declare(strict_types=1);

namespace RxApps\XSentry\Src\Controllers;

use Context;
use Rhymix\Framework\Exception;
use RuntimeException;
use RxApps\XSentry\Src\BaseModule;

class AdminController extends BaseModule
{
	/**
	 * Handler for admin.dispXsentryAdminDashboard
	 *
	 * @return void
	 */
	public function dispXsentryAdminDashboard(): void
	{
		$dsn = $this->getConfiguredSentryDsn();
		Context::set('xsentry_dsn', $dsn);

		$this->setTemplatePath($this->module_path . 'views/admin');
		$this->setTemplateFile('index.blade.php');
	}

	/**
	 * Handler for admin.procXsentryAdminUpdateDsn
	 * This handler should receive some configuration of Sentry DSNs, and will validate the value and save it.
	 *
	 * @throws Exception
	 */
	public function procXsentryAdminUpdateDsn(): void
	{
		/**
		 * @var $vars object{
		 *      backend_enabled: 'Y'|null,
		 *      backend_dsn: string,
		 *      frontend_enabled: 'Y'|null,
		 *      frontend_dsn: string
		 *  }
		 */
		$vars = Context::getRequestVars();

		$backendEnabled = $vars->backend_enabled === 'Y';
		$backendDsn = trim($vars->backend_dsn ?? '');
		if ($backendEnabled && $backendDsn === '') {
			throw new Exception('백엔드 연동을 활성화하려면 백엔드 DSN을 입력해야 합니다.');
		}
		if ($backendDsn !== '' && (!str_starts_with($backendDsn, 'https://') || !str_contains($backendDsn, '@'))) {
			throw new Exception('백엔드 DSN은 https:// 로 시작해야 하며, URL 내 @ 문자가 하나 존재해야 합니다.');
		}

		$frontendEnabled = $vars->frontend_enabled === 'Y';
		$frontendDsn = trim($vars->frontend_dsn ?? '');
		if ($frontendEnabled && $frontendDsn === '') {
			throw new Exception('프론트엔드 연동을 활성화하려면 프론트엔드 DSN을 입력해야 합니다.');
		}
		if ($frontendDsn !== '' && (!str_starts_with($frontendDsn, 'https://') || !str_ends_with($frontendDsn, '.js'))) {
			throw new Exception('프론트엔드 DSN은 https:// 로 시작해야 하며, .js 로 끝나야 합니다.');
		}

		if (!$this->setConfiguredSentryDsn($backendEnabled, $backendDsn, $frontendEnabled, $frontendDsn)) {
			throw new Exception('일시적인 오류로 설정을 저장하지 못했습니다.');
		}

		$this->setMessage('저장했습니다.');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispXsentryAdminDashboard'));
	}

	/**
	 * Handler for admin.procXsentryAdminMakeTestException
	 * This handler will make a RuntimeException and then redirect to dashboard.
	 *
	 * @return void
	 */
	public function procXsentryAdminMakeTestException(): void
	{
		$url = getNotEncodedUrl('', 'module', 'admin', 'act', 'dispXsentryAdminDashboard');
		$_SESSION['XE_VALIDATOR_ID'] = 'modules/xsentry/procXsentryAdminMakeTestException';
		$_SESSION['XE_VALIDATOR_MESSAGE_TYPE'] = 'info';
		$_SESSION['XE_VALIDATOR_MESSAGE'] = '테스트 예외를 발생시켰습니다.';
		Context::addHtmlFooter('<script> location.replace("' . $url . '"); </script>');

		throw new RuntimeException('This RuntimeException is for test purpose from rx-apps/xsentry.');
	}
}
