<?php
/**
 * Custom 404 template for Project B.
 *
 * @package Avada
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

get_header();
?>

<style>
	.pb-error {
		min-height: 100vh;
		background: #f3f2eb;
		color: #111;
		padding: clamp(118px, 17vh, 164px) 24px 140px;
	}

	.pb-error *,
	.pb-error *::before,
	.pb-error *::after {
		box-sizing: border-box;
	}

	.pb-error__shell {
		width: min(1120px, 100%);
		margin: 0 auto;
		text-align: center;
	}

	.pb-error__code {
		display: block;
		margin: 0 0 24px;
		color: #111 !important;
		font-size: 14px;
		font-weight: 900;
		line-height: 1;
		letter-spacing: 0.16em;
		text-transform: uppercase;
	}

	.pb-error__title {
		max-width: none;
		margin: 0;
		color: #111 !important;
		font-size: clamp(42px, 6.8vw, 82px);
		font-weight: 900;
		line-height: 1;
		letter-spacing: 0;
		text-transform: uppercase;
	}

	.pb-error__copy {
		max-width: none;
		margin: 22px auto 0;
		font-size: clamp(14px, 1.7vw, 18px);
		font-weight: 800;
		line-height: 1.45;
		letter-spacing: 0;
		color: #111 !important;
		word-break: keep-all;
		white-space: nowrap;
	}

	.pb-error__actions {
		display: flex;
		justify-content: center;
		margin: 38px 0 0;
	}

	.pb-error__link {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 48px;
		padding: 0 22px;
		border: 3px solid #111;
		background: #111;
		color: #f3f2eb !important;
		font-size: 15px;
		font-weight: 900;
		line-height: 1;
		letter-spacing: 0;
		text-decoration: none !important;
		transition: color 0.18s ease, background 0.18s ease, border-color 0.18s ease;
	}

	.pb-error__link:hover,
	.pb-error__link:focus {
		border-color: #111;
		background: transparent;
		color: #111 !important;
	}

	.pb-error__search {
		display: none;
	}

	@media (max-width: 700px) {
		.pb-error {
			padding: 118px 24px 100px;
		}

		.pb-error__copy {
			white-space: normal;
		}

		.pb-error__link {
			width: min(320px, 100%);
		}
	}

</style>

<main class="pb-error" id="main" role="main">
	<div class="pb-error__shell">
		<span class="pb-error__code">404 / Not Found</span>
		<h1 class="pb-error__title">Page Lost</h1>
		<p class="pb-error__copy">요청한 페이지를 찾을 수 없거나 열람 권한이 없는 페이지 입니다</p>

		<div class="pb-error__actions" aria-label="빠른 이동">
			<a class="pb-error__link" href="javascript:history.back()">뒤로가기</a>
		</div>
	</div>
</main>

<?php
get_footer();
