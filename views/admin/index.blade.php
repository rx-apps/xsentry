<div class="x_page-header">
	<h1>XSentry</h1>
</div>

<section class="section">
	<h2>활성화 여부 및 DSN 설정</h2>

	@error ('modules/xsentry/procXsentryAdminUpdateDsn')
		<div class="message {$XE_VALIDATOR_MESSAGE_TYPE}">
			<p>{$XE_VALIDATOR_MESSAGE}</p>
		</div>
	@enderror

	<form method="POST" action="./" class="x_form-horizontal">
		<input type="hidden" name="module" value="admin" />
		<input type="hidden" name="act" value="procXsentryAdminUpdateDsn" />
		<input type="hidden" name="xe_validator_id" value="modules/xsentry/procXsentryAdminUpdateDsn" />

		<div class="x_control-group">
			<label class="x_control-label" for="backend_enabled">
				백엔드 연동 활성화 여부
			</label>
			<div class="x_controls">
				<label>
					<input type="checkbox" name="backend_enabled" id="backend_enabled" value="Y"
						   @checked($xsentry_dsn['backend']['enabled'] === true) />
					<span>백엔드 연동 활성화</span>
				</label>
			</div>
		</div>
		<div class="x_control-group">
			<label class="x_control-label" for="backend_dsn">
				백엔드 DSN
			</label>
			<div class="x_controls">
				<input type="text" name="backend_dsn" id="backend_dsn"
					   value="{{ $xsentry_dsn['backend']['dsn'] }}" />
				<p class="x_help-block">
					<code>Settings &gt; [내 조직] &gt; Projects &gt; [연동할 프로젝트] &gt; SDK Setup &gt; Client Keys (DSN)</code>
					순서로 접근 후 최상단의 <code>DSN</code> 항목을 복사하여 입력하세요.<br />
					<code>https://xxx@xxx.ingest.xx.sentry.io/xxx</code> 형태로 구성되어 있는 것이 일반적입니다.
				</p>
			</div>
		</div>

		<div class="x_control-group">
			<label class="x_control-label" for="frontend_enabled">
				프론트엔드 연동 활성화 여부
			</label>
			<div class="x_controls">
				<label>
					<input type="checkbox" name="frontend_enabled" id="frontend_enabled" value="Y"
						@checked($xsentry_dsn['frontend']['enabled'] === true) />
					<span>프론트엔드 연동 활성화</span>
				</label>
			</div>
		</div>
		<div class="x_control-group">
			<label class="x_control-label" for="frontend_dsn">
				프론트엔드 DSN
			</label>
			<div class="x_controls">
				<input type="text" name="frontend_dsn" id="frontend_dsn"
					   value="{{ $xsentry_dsn['frontend']['dsn'] }}" />
				<p class="x_help-block">
					<code>Settings &gt; [내 조직] &gt; Projects &gt; [연동할 프로젝트] &gt; SDK Setup &gt; Loader Script</code>
					순서로 접근 후 최상단의 태그 내 <b>URL만을</b> 복사하여 입력하세요.<br />
					<code>https://js.sentry-cdn.com/xxx.min.js</code> 형태로 구성되어 있는 것이 일반적입니다.
				</p>
			</div>
		</div>

		<div class="x_clearfix btnArea">
			<input type="submit" class="x_btn x_btn-primary" value="저장" />
		</div>
	</form>
</section>

<section class="section">
	<h2>테스트</h2>

	@error ('modules/xsentry/procXsentryAdminMakeTestException')
		<div class="message {$XE_VALIDATOR_MESSAGE_TYPE}">
			<p>{$XE_VALIDATOR_MESSAGE}</p>
		</div>
	@enderror

	<form method="POST" action="./" class="x_form-horizontal">
		<input type="hidden" name="module" value="admin" />
		<input type="hidden" name="act" value="procXsentryAdminMakeTestException" />
		<input type="hidden" name="xe_validator_id" value="modules/xsentry/procXsentryAdminMakeTestException" />

		<div class="x_control-group">
			<label class="x_control-label" for="backend_test">
				백엔드 테스트
			</label>
			<div class="x_controls">
				<input type="submit" class="x_btn x_btn-inverse" value="테스트하기" />
				<p class="x_help-block">
					임의의 PHP 예외를 발생시킵니다. Sentry로 예외가 정상적으로 발송되는지 확인하세요.
				</p>
			</div>
		</div>

		<div class="x_control-group">
			<label class="x_control-label" for="frontend_test">
				프론트엔드 테스트
			</label>
			<div class="x_controls">
				<button type="button" id="frontend_test" class="x_btn x_btn-inverse">테스트하기</button>
				<p class="x_help-block" style="margin-top: 10px">
					임의의 JavaScript 예외를 발생시킵니다. Sentry로 예외가 정상적으로 발송되는지 확인하세요.
				</p>
				<script>
					(() => {
						$('#frontend_test').on('click', () => {
							alert('테스트 예외를 발생시켰습니다.');
						}).on('click', () => {
							throw new Error('This Error is for test purpose from rx-apps/xsentry.');
						});
					}) ();
				</script>
			</div>
		</div>
	</form>
</section>
