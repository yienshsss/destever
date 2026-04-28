<?php
/**
 * Template used for single posts and other post-types
 * that don't have a specific template.
 *
 * @package Avada
 * @subpackage Templates
 */

if (!defined('ABSPATH')) {
	exit('Direct script access denied.');
}

get_header();
?>

<style>
	.pb-single {
		background: #f7f6ef;
		color: #111;
		padding: 0 0 88px;
	}

	.pb-single *,
	.pb-single *::before,
	.pb-single *::after {
		box-sizing: border-box;
	}

	.pb-single a {
		color: inherit;
		text-decoration: none;
	}

	.pb-single__shell {
		width: min(1180px, calc(100% - 48px));
		margin: 0 auto;
	}

	.pb-single__hero {
		position: relative;
		width: 100vw;
		margin: 0 0 56px calc(50% - 50vw);
		padding: 0 clamp(22px, 3.5vw, 54px);
		min-height: clamp(420px, 52vw, 760px);
		opacity: calc(1 - (var(--pb-hero-progress, 0) * 0.55));
		transform: translateY(calc(var(--pb-hero-progress, 0) * -28px));
		transition: opacity 0.18s linear, transform 0.18s linear;
		will-change: opacity, transform;
	}

	.pb-single__hero-inner {
		display: grid;
		grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr);
		align-items: stretch;
		min-height: clamp(420px, 52vw, 760px);
		background: #f7f6ef;
		border-radius: 0 0 34px 34px;
		overflow: hidden;
		box-shadow: 0 30px 70px rgba(17, 17, 17, 0.08);
	}

	.pb-single__hero-media,
	.pb-single__hero-media img,
	.pb-single__hero-fallback {
		display: block;
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.pb-single__hero-media {
		position: relative;
		background: #e8e1d6;
		overflow: hidden;
	}

	.pb-single__hero-fallback {
		background:
			linear-gradient(180deg, rgba(249, 246, 239, 0.06), rgba(17, 17, 17, 0.08)),
			linear-gradient(135deg, rgba(255, 255, 255, 0.18), rgba(0, 0, 0, 0.08)),
			linear-gradient(135deg, #ddd6ca, #bbb3a5);
	}

	.pb-single__hero-media::after {
		content: "";
		position: absolute;
		inset: 0;
		background:
			linear-gradient(180deg, rgba(255, 255, 255, 0.02) 0%, rgba(255, 255, 255, 0.16) 100%);
	}

	.pb-single__hero-copy {
		position: relative;
		min-height: clamp(420px, 52vw, 760px);
		display: flex;
		align-items: center;
		padding: clamp(30px, 4.2vw, 66px);
		background:
			linear-gradient(180deg, rgba(255, 255, 255, 0.82), rgba(255, 255, 255, 0.96)),
			linear-gradient(135deg, rgba(214, 213, 208, 0.48), rgba(255, 255, 255, 0));
	}

	.pb-single__hero-copy-inner {
		width: min(680px, 100%);
		color: #111;
		opacity: calc(1 - (var(--pb-hero-progress, 0) * 0.9));
		transform: translateY(calc(var(--pb-hero-progress, 0) * 24px));
		transition: opacity 0.18s linear, transform 0.18s linear;
		will-change: opacity, transform;
	}

	.pb-single__meta {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px 18px;
		margin-bottom: 18px;
	}

	.pb-single__kicker,
	.pb-single__date {
		display: inline-flex;
		align-items: center;
		font-size: 13px;
		font-weight: 800;
		letter-spacing: 0.16em;
		text-transform: uppercase;
		color: rgba(17, 17, 17, 0.85);
	}

	.pb-single__title {
		margin: 0;
		font-size: clamp(2.9rem, 5.5vw, 6.4rem);
		line-height: 0.96;
		font-weight: 900;
		letter-spacing: -0.065em;
		word-break: keep-all;
		text-wrap: balance;
		color: #545a6e;
	}

	.pb-single__dek {
		max-width: 42rem;
		margin: 20px 0 0;
		font-size: 1rem;
		line-height: 1.7;
		color: rgba(17, 17, 17, 0.66);
		word-break: keep-all;
	}

	.pb-single__divider {
		width: min(760px, 100%);
		height: 3px;
		background: #111;
		margin: 0 auto 54px;
	}

	.pb-single__content {
		width: min(820px, 100%);
		margin: 0 auto;
		font-family: "KoPubWorldDotum", "Apple SD Gothic Neo", "Malgun Gothic", "Noto Sans KR", sans-serif;
		font-size: 11pt;
		line-height: 1.5;
		letter-spacing: -0.01em;
		color: #181818;
		word-break: keep-all;
	}

	.pb-single__content p,
	.pb-single__content li,
	.pb-single__content blockquote,
	.pb-single__content span,
	.pb-single__content div,
	.pb-single__content td,
	.pb-single__content th {
		font-family: inherit !important;
		font-size: inherit !important;
		line-height: inherit !important;
		letter-spacing: inherit !important;
		color: inherit !important;
	}

	.pb-single__content p {
		margin: 0 0 1.22em !important;
	}

	.pb-single__content .pb-single__scene-break {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 18px;
		margin: 2.2em 0 2em !important;
		font-size: 0.92em !important;
		font-weight: 800 !important;
		letter-spacing: 0.34em !important;
		text-align: center !important;
		color: rgba(24, 24, 24, 0.5) !important;
	}

	.pb-single__content .pb-single__scene-break::before,
	.pb-single__content .pb-single__scene-break::after {
		content: "";
		flex: 0 1 92px;
		height: 1px;
		background: rgba(17, 17, 17, 0.18);
	}

	.pb-single__content img {
		display: block;
		max-width: 100%;
		height: auto;
		margin: 42px auto;
		border-radius: 6px;
	}

	.pb-single__content h1,
	.pb-single__content h2,
	.pb-single__content h3,
	.pb-single__content h4 {
		margin: 3.2em 0 0.9em;
		font-family: "Pretendard", "Apple SD Gothic Neo", "Malgun Gothic", sans-serif;
		line-height: 1.24;
		font-weight: 800;
		color: #111;
	}

	.pb-single__content ul,
	.pb-single__content ol {
		margin: 0 0 1.85em 1.45em;
		padding: 0;
	}

	.pb-single__content strong,
	.pb-single__content b {
		font-weight: 800 !important;
	}

	.pb-single__content em,
	.pb-single__content i {
		font-style: italic !important;
	}

	.pb-single__content blockquote {
		margin: 2.1em 0;
		padding: 0 0 0 1.15em;
		border-left: 3px solid rgba(17, 17, 17, 0.2);
		color: rgba(24, 24, 24, 0.78) !important;
	}

	.pb-single__related,
	.pb-single__comments {
		width: min(1080px, 100%);
		margin: 72px auto 0;
	}

	.pb-single h2.pb-single__section-title {
		margin: 0 0 24px;
		font-size: 12px !important;
		line-height: 1.2 !important;
		font-weight: 800 !important;
		letter-spacing: 0.18em !important;
		text-transform: uppercase !important;
		color: #111 !important;
	}

	.pb-single__related-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 18px;
	}

	.pb-single__related-item {
		display: block;
	}

	.pb-single__related-thumb {
		display: block;
		width: 100%;
		aspect-ratio: 1 / 1;
		background: #ddd;
		margin-bottom: 12px;
		overflow: hidden;
	}

	.pb-single__related-thumb img,
	.pb-single__related-fallback {
		width: 100%;
		height: 100%;
		display: block;
		object-fit: cover;
	}

	.pb-single__related-fallback {
		background:
			linear-gradient(135deg, rgba(255, 255, 255, 0.24), rgba(0, 0, 0, 0.1)),
			linear-gradient(135deg, #1f1f1f, #b5b5b5);
	}

	.pb-single__related-title {
		margin: 0 0 8px;
		font-size: 16px;
		line-height: 1.35;
		font-weight: 700;
		word-break: keep-all;
	}

	.pb-single__related-date {
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: rgba(17, 17, 17, 0.62);
	}

	.pb-single__comments {
		padding-top: 28px;
		border-top: 2px solid #111;
	}

	.pb-single__comments .comment-respond,
	.pb-single__comments .comments-area {
		width: min(760px, 100%);
		margin: 0 auto;
	}

	.pb-single__comments .comment-reply-title,
	.pb-single__comments .comments-title {
		margin: 0 0 18px;
		font-size: 14px;
		font-weight: 800;
		letter-spacing: 0.14em;
		text-transform: uppercase;
	}

	.pb-single__comments .comment-form-comment textarea,
	.pb-single__comments .comment-form-author input,
	.pb-single__comments .comment-form-email input,
	.pb-single__comments .comment-form-url input {
		width: 100%;
		padding: 14px 16px;
		border: 1px solid #cfc8bb;
		background: rgba(255, 255, 255, 0.75);
		font-size: 15px;
	}

	.pb-single__comments .form-submit .submit {
		border: 0;
		background: #111;
		color: #fff;
		padding: 14px 20px;
		font-size: 13px;
		font-weight: 700;
		letter-spacing: 0.12em;
		text-transform: uppercase;
		cursor: pointer;
	}

	@media (max-width: 960px) {
		.pb-single__hero {
			padding-inline: 22px;
			min-height: auto;
			margin-bottom: 42px;
		}

		.pb-single__hero-inner {
			grid-template-columns: 1fr;
			min-height: auto;
		}

		.pb-single__hero-media {
			min-height: 340px;
		}

		.pb-single__hero-copy {
			min-height: auto;
			padding: 28px 26px 32px;
		}

		.pb-single__related-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 640px) {
		.pb-single {
			padding: 0 0 56px;
		}

		.pb-single__shell {
			width: min(100% - 28px, 1180px);
		}

		.pb-single__hero {
			padding-inline: 14px;
			margin-bottom: 34px;
			min-height: auto;
		}

		.pb-single__hero-inner {
			border-radius: 0 0 24px 24px;
			margin-bottom: 34px;
		}

		.pb-single__hero-media {
			min-height: 250px;
		}

		.pb-single__hero-copy {
			min-height: auto;
			padding: 22px 20px 24px;
		}

		.pb-single__meta {
			gap: 10px 14px;
			margin-bottom: 14px;
		}

		.pb-single__kicker,
		.pb-single__date {
			font-size: 11px;
		}

		.pb-single__title {
			font-size: clamp(2rem, 10vw, 2.95rem);
			line-height: 1;
		}

		.pb-single__content {
			width: min(100%, 820px);
			font-size: 14.5px;
			line-height: 1.58;
		}

		.pb-single__related-grid {
			grid-template-columns: 1fr;
		}
	}
</style>

<main class="pb-single">
	<div class="pb-single__shell">
		<?php while (have_posts()) : ?>
			<?php the_post(); ?>
			<?php
			$categories = get_the_category();
			$cat_name = !empty($categories) ? $categories[0]->name : 'Uncategorized';
			$dek = has_excerpt() ? get_the_excerpt() : '';
			$hero_image = function_exists('project_b_get_post_preview_image_url') ? project_b_get_post_preview_image_url(get_the_ID(), 'full') : '';
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="pb-single__hero">
					<div class="pb-single__hero-inner">
						<div class="pb-single__hero-media">
							<?php if (!empty($hero_image)) : ?>
								<img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
							<?php else : ?>
								<span class="pb-single__hero-fallback"></span>
							<?php endif; ?>
						</div>
						<div class="pb-single__hero-copy">
							<div class="pb-single__hero-copy-inner">
								<div class="pb-single__meta">
									<div class="pb-single__kicker"><?php echo esc_html($cat_name); ?></div>
									<div class="pb-single__date"><?php echo esc_html(get_the_date('Y. m. d')); ?></div>
								</div>
								<h1 class="pb-single__title"><?php the_title(); ?></h1>
								<?php if (!empty($dek)) : ?>
									<p class="pb-single__dek"><?php echo esc_html($dek); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</header>

				<div class="pb-single__divider"></div>

				<div class="pb-single__content">
					<?php the_content(); ?>
				</div>

				<section class="pb-single__comments" aria-labelledby="pb-comments-title">
					<h2 id="pb-comments-title" class="pb-single__section-title"><?php esc_html_e('Comments', 'Avada'); ?></h2>
					<?php comments_template(); ?>
				</section>

				<section class="pb-single__related" aria-labelledby="pb-related-title">
					<h2 id="pb-related-title" class="pb-single__section-title"><?php esc_html_e('Related Posts', 'Avada'); ?></h2>
					<div class="pb-single__related-grid">
						<?php
						$related = new WP_Query(
							array(
								'category__in' => wp_get_post_categories(get_the_ID()),
								'posts_per_page' => 4,
								'post__not_in' => array(get_the_ID()),
								'ignore_sticky_posts' => 1,
								'orderby' => 'date',
							)
						);
						?>
						<?php if ($related->have_posts()) : ?>
							<?php while ($related->have_posts()) : ?>
								<?php $related->the_post(); ?>
								<a class="pb-single__related-item" href="<?php the_permalink(); ?>">
									<span class="pb-single__related-thumb">
										<?php
										$related_thumb = function_exists('project_b_get_post_preview_image_url') ? project_b_get_post_preview_image_url(get_the_ID(), 'medium_large') : '';
										?>
										<?php if (!empty($related_thumb)) : ?>
											<img src="<?php echo esc_url($related_thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
										<?php else : ?>
											<span class="pb-single__related-fallback"></span>
										<?php endif; ?>
									</span>
									<h3 class="pb-single__related-title"><?php the_title(); ?></h3>
									<div class="pb-single__related-date"><?php echo esc_html(get_the_date('Y. m. d')); ?></div>
								</a>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						<?php else : ?>
							<p><?php esc_html_e('No related posts yet.', 'Avada'); ?></p>
						<?php endif; ?>
					</div>
				</section>
			</article>
		<?php endwhile; ?>
	</div>
</main>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		var hero = document.querySelector('.pb-single__hero');
		var content = document.querySelector('.pb-single__content');
		if (!hero) {
			return;
		}

		if (content) {
			content.querySelectorAll('p').forEach(function (paragraph) {
				var text = (paragraph.textContent || '').replace(/\u00a0/g, ' ').trim();
				if (/^\*\s*\*\s*\*$/.test(text)) {
					paragraph.classList.add('pb-single__scene-break');
					paragraph.setAttribute('aria-label', 'scene break');
				}
			});
		}

		var ticking = false;
		var updateHeroProgress = function () {
			ticking = false;
			var rect = hero.getBoundingClientRect();
			var distance = Math.max(hero.offsetHeight * 0.72, 1);
			var scrolled = Math.max(-rect.top, 0);
			var progress = Math.min(scrolled / distance, 1);
			hero.style.setProperty('--pb-hero-progress', progress.toFixed(3));
		};

		var requestUpdate = function () {
			if (ticking) {
				return;
			}
			ticking = true;
			window.requestAnimationFrame(updateHeroProgress);
		};

		updateHeroProgress();
		window.addEventListener('scroll', requestUpdate, { passive: true });
		window.addEventListener('resize', requestUpdate);
	});
</script>

<?php get_footer(); ?>
