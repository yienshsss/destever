<?php
/**
 * Deep menu landing page template.
 *
 * @package Avada
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

get_header();

$slug   = get_post_field( 'post_name', get_queried_object_id() );
$config = project_b_deep_menu_landing_config();
$entry  = isset( $config[ $slug ] ) ? $config[ $slug ] : null;

if ( ! $entry ) {
	get_template_part( 'page' );
	return;
}

$items = $entry['items'];
$hide_tabs = ! empty( $entry['hide_tabs'] );

if ( ! function_exists( 'project_b_get_landing_card_item' ) ) {
	function project_b_get_landing_card_item( $page_slug, $item ) {
		if ( 'oc-couples' === $page_slug && 'page' === $item['type'] && 'oc-couples' === $item['slug'] ) {
			return null;
		}

		return $item;
	}
}

if ( ! function_exists( 'project_b_get_landing_card_copy' ) ) {
	function project_b_get_landing_card_copy( $page_slug, $card_item, $resource, $title, $desc ) {
		if ( 'serial' === $page_slug && ! empty( $card_item['slug'] ) ) {
			$serial_meta = function_exists( 'project_b_get_serial_publication_meta' ) ? project_b_get_serial_publication_meta( $card_item['slug'] ) : array();

			if ( ! empty( $serial_meta['title'] ) ) {
				$title = $serial_meta['title'];
			}
		}

		if ( 'oc' === $page_slug && 'page' === $card_item['type'] && 'oc-couples' === $card_item['slug'] ) {
			return array(
				'title' => '자캐 커플',
				'desc'  => 'Yien의 자캐 커플 로그 백업',
			);
		}

		if ( 'page' === $card_item['type'] && 'oc-couples' === $card_item['slug'] ) {
			$title = '자캐 커플';
		}

		return array(
			'title' => $title,
			'desc'  => $desc,
		);
	}
}
?>

<style>
	.pb-landing {
		background: #f7f6ef;
		color: #111;
		padding: 22px 0 88px;
	}

	.pb-landing *,
	.pb-landing *::before,
	.pb-landing *::after {
		box-sizing: border-box;
	}

	.pb-landing a {
		color: inherit;
		text-decoration: none;
	}

	.pb-landing__shell {
		width: min(1780px, calc(100% - 64px));
		margin: 0 auto;
	}

	.pb-landing__header {
		padding: 32px 0 62px;
		text-align: center;
	}

	.pb-landing__title {
		margin: 0;
		font-size: clamp(2rem, 4.2vw, 3.9rem);
		line-height: 1;
		font-weight: 800;
		letter-spacing: -0.06em;
		text-transform: uppercase;
		color: #111;
	}

	.pb-landing__tabs {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		gap: 24px;
		margin: 34px 0 0;
	}

	.pb-landing__tab {
		display: inline-flex;
		align-items: center;
		padding-bottom: 8px;
		border-bottom: 4px solid transparent;
		font-size: 16px;
		font-weight: 800;
		letter-spacing: -0.03em;
	}

	.pb-landing__tab.is-current,
	.pb-landing__tab:hover {
		color: #e78645;
		border-bottom-color: #e78645;
	}

	.pb-landing__grid {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		gap: 28px 30px;
	}

	.pb-landing__card {
		display: block;
		flex: 0 1 calc((100% - 90px) / 4);
		max-width: 340px;
		min-width: 0;
		width: 100%;
	}

	.pb-landing__thumb {
		display: block;
		position: relative;
		width: 100%;
		aspect-ratio: 1 / 1.18;
		margin-bottom: 18px;
		overflow: hidden;
		background: #e3ddcf;
	}

	.pb-landing__thumb img,
	.pb-landing__fallback {
		width: 100%;
		height: 100%;
		display: block;
		object-fit: cover;
		transition: transform .4s ease;
	}

	.pb-landing__card:hover .pb-landing__thumb img {
		transform: scale(1.04);
	}

	.pb-landing__fallback {
		background:
			linear-gradient(135deg, rgba(255,255,255,.26), rgba(0,0,0,.1)),
			linear-gradient(135deg, #1f1f1f, #b7b7b7);
	}

	.pb-landing__kicker {
		position: absolute;
		left: 18px;
		bottom: 16px;
		z-index: 2;
		color: #fff;
		font-size: 12px;
		font-weight: 800;
		letter-spacing: .14em;
		text-transform: uppercase;
		text-shadow: 0 1px 10px rgba(0,0,0,.35);
	}

	.pb-landing__card-title {
		margin: 0 0 10px;
		font-size: clamp(1.7rem, 2vw, 2.25rem);
		line-height: 1.02;
		font-weight: 800;
		letter-spacing: -0.05em;
		text-align: center;
		color: #111;
		word-break: keep-all;
	}

	.pb-landing__desc {
		margin: 0;
		font-size: 1rem;
		line-height: 1.6;
		text-align: center;
		color: #111;
		word-break: keep-all;
	}

	.pb-landing__card.is-locked {
		cursor: pointer;
	}

	.pb-landing__card.is-locked .pb-landing__thumb::after {
		content: "정식 출간 완료";
		position: absolute;
		right: 14px;
		top: 14px;
		z-index: 3;
		padding: 8px 10px;
		border-radius: 999px;
		background: rgba(17, 17, 17, 0.78);
		color: #fff;
		font-size: 11px;
		font-weight: 800;
		letter-spacing: .08em;
	}

	.pb-landing__modal {
		position: fixed;
		inset: 0;
		z-index: 9999;
		display: none;
		align-items: center;
		justify-content: center;
		padding: 24px;
		background: rgba(10, 10, 10, 0.48);
	}

	.pb-landing__modal.is-open {
		display: flex;
	}

	.pb-landing__modal-dialog {
		width: min(460px, 100%);
		padding: 28px 28px 26px;
		background: #f7f6ef;
		color: #111;
		box-shadow: 0 24px 60px rgba(0, 0, 0, 0.2);
	}

	.pb-landing__modal-title {
		margin: 0 0 10px;
		font-size: 1.8rem;
		line-height: 1.05;
		font-weight: 800;
		letter-spacing: -0.05em;
	}

	.pb-landing__modal-text {
		margin: 0;
		font-size: 1rem;
		line-height: 1.65;
		color: rgba(17, 17, 17, 0.78);
		word-break: keep-all;
	}

	.pb-landing__modal-actions {
		display: flex;
		justify-content: flex-end;
		gap: 12px;
		margin-top: 22px;
	}

	.pb-landing__modal-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		padding: 12px 18px;
		border: 1px solid rgba(17, 17, 17, 0.16);
		background: transparent;
		color: #111;
		font-size: 14px;
		font-weight: 800;
		letter-spacing: -0.02em;
		cursor: pointer;
	}

	.pb-landing__modal-btn--accent {
		border-color: #111;
		background: #111;
		color: #fff;
	}

	@media (max-width: 1280px) {
		.pb-landing__card {
			flex-basis: calc((100% - 60px) / 3);
			max-width: 320px;
		}
	}

	@media (max-width: 900px) {
		.pb-landing__shell {
			width: min(100% - 34px, 1780px);
		}

		.pb-landing__grid {
			gap: 24px 20px;
		}

		.pb-landing__card {
			flex-basis: calc((100% - 20px) / 2);
			max-width: 320px;
		}
	}

	@media (max-width: 640px) {
		.pb-landing__header {
			padding-bottom: 42px;
		}

		.pb-landing__tabs {
			gap: 18px;
		}

		.pb-landing__card {
			flex-basis: 100%;
			max-width: 100%;
		}
	}
</style>

<main class="pb-landing">
	<div class="pb-landing__shell">
		<header class="pb-landing__header">
			<h1 class="pb-landing__title"><?php echo esc_html( $entry['title'] ); ?></h1>
			<?php if ( ! $hide_tabs ) : ?>
				<nav class="pb-landing__tabs" aria-label="<?php echo esc_attr( $entry['title'] ); ?>">
					<?php foreach ( $items as $item ) : ?>
						<?php
						$url        = project_b_get_landing_resource_url( $item['type'], $item['slug'] );
						$is_current = 'page' === $item['type'] && $item['slug'] === $slug;
						?>
						<a class="pb-landing__tab<?php echo $is_current ? ' is-current' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
							<?php echo esc_html( $item['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</header>

		<section class="pb-landing__grid">
			<?php foreach ( $items as $item ) : ?>
				<?php
				$card_item = project_b_get_landing_card_item( $slug, $item );
				if ( ! $card_item ) {
					continue;
				}

				$resource = project_b_get_landing_resource( $card_item['type'], $card_item['slug'] );
				if ( ! $resource ) {
					continue;
				}

				if ( 'category' === $card_item['type'] ) {
					$image    = function_exists( 'project_b_get_landing_card_image_url' ) ? project_b_get_landing_card_image_url( 'category', $resource, 'large' ) : '';
					$title    = $resource->name;
					$desc     = $resource->description ? wp_trim_words( wp_strip_all_tags( $resource->description ), 18, '' ) : '';
					$url      = get_category_link( $resource );
				} else {
					$image = function_exists( 'project_b_get_landing_card_image_url' ) ? project_b_get_landing_card_image_url( 'page', $resource, 'large' ) : '';
					$title = get_the_title( $resource );
					$desc  = $resource->post_excerpt ? $resource->post_excerpt : wp_trim_words( wp_strip_all_tags( $resource->post_content ), 18, '' );
					$url   = get_permalink( $resource );
				}

				$copy  = project_b_get_landing_card_copy( $slug, $card_item, $resource, $title, $desc );
				$title = $copy['title'];
				$desc  = $copy['desc'];
				$title_text = wp_strip_all_tags( $title );
				$is_locked_serial = 'serial' === $slug && ! project_b_is_privileged_user() && 'category' === $card_item['type'];
				$store_url        = $is_locked_serial && function_exists( 'project_b_get_serial_store_url_for_term' ) ? project_b_get_serial_store_url_for_term( $resource ) : '';
				?>
				<a
					class="pb-landing__card<?php echo $is_locked_serial ? ' is-locked' : ''; ?>"
					href="<?php echo esc_url( $is_locked_serial ? '#' : $url ); ?>"
					<?php if ( $is_locked_serial ) : ?>
						data-locked-title="<?php echo esc_attr( $title_text ); ?>"
						data-locked-store-url="<?php echo esc_url( $store_url ); ?>"
					<?php endif; ?>
				>
					<span class="pb-landing__thumb">
						<?php if ( $image ) : ?>
							<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title_text ); ?>">
						<?php else : ?>
							<span class="pb-landing__fallback"></span>
						<?php endif; ?>
						<span class="pb-landing__kicker"><?php echo esc_html( $entry['title'] ); ?></span>
					</span>
					<h2 class="pb-landing__card-title"><?php echo wp_kses( $title, array( 'br' => array() ) ); ?></h2>
					<?php if ( $desc ) : ?>
						<p class="pb-landing__desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</section>
	</div>
</main>

<div class="pb-landing__modal" aria-hidden="true">
	<div class="pb-landing__modal-dialog" role="dialog" aria-modal="true" aria-labelledby="pb-landing-modal-title">
		<h2 id="pb-landing-modal-title" class="pb-landing__modal-title">정식 출간 완료</h2>
		<p class="pb-landing__modal-text">이 작품은 정식 출간이 완료된 작품입니다. 보러가기를 누르면 서점 링크로 이동합니다.</p>
		<div class="pb-landing__modal-actions">
			<button type="button" class="pb-landing__modal-btn" data-modal-close>닫기</button>
			<a href="#" class="pb-landing__modal-btn pb-landing__modal-btn--accent" data-modal-link>보러가기</a>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		var modal = document.querySelector('.pb-landing__modal');
		var modalTitle = modal ? modal.querySelector('.pb-landing__modal-title') : null;
		var modalText = modal ? modal.querySelector('.pb-landing__modal-text') : null;
		var modalLink = modal ? modal.querySelector('[data-modal-link]') : null;

		if (!modal || !modalTitle || !modalText || !modalLink) {
			return;
		}

		var closeModal = function () {
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
		};

		document.querySelectorAll('.pb-landing__card.is-locked').forEach(function (card) {
			card.addEventListener('click', function (event) {
				event.preventDefault();

				var title = card.getAttribute('data-locked-title') || '이 작품';
				var storeUrl = card.getAttribute('data-locked-store-url') || '';

				modalTitle.textContent = title + ' 정식 출간 완료';
				modalText.textContent = storeUrl
					? '이 작품은 정식 출간이 완료된 작품입니다. 보러가기를 누르면 서점 링크로 이동합니다.'
					: '이 작품은 정식 출간이 완료된 작품입니다. 아직 서점 링크는 등록되지 않았습니다.';
				modalLink.setAttribute('href', storeUrl || '#');
				modalLink.style.display = storeUrl ? 'inline-flex' : 'none';

				modal.classList.add('is-open');
				modal.setAttribute('aria-hidden', 'false');
			});
		});

		modal.addEventListener('click', function (event) {
			if (event.target === modal || event.target.hasAttribute('data-modal-close')) {
				closeModal();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && modal.classList.contains('is-open')) {
				closeModal();
			}
		});
	});
</script>

<?php get_footer(); ?>
