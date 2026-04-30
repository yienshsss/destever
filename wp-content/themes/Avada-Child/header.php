<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Noto+Sans+KR:wght@400;700;900&display=swap"
		rel="stylesheet">
	<style>
		/* Global Styles for Project B */
		:root {
			--pv-black: #111;
			--pv-white: #fff;
			--pv-accent: #f04f23;
			--pv-lime: #ccff00;
			--pv-gray: #888;
			--pv-light-gray: #f5f5f5;
		}

		body {
			font-family: 'Inter', 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
			margin: 0;
			padding: 0;
			background: var(--pv-white);
			color: var(--pv-black);
			line-height: 1.6;
			overflow-x: hidden;
		}

		a {
			text-decoration: none;
			color: inherit;
			transition: all 0.3s ease;
		}

		ul {
			list-style: none;
			padding: 0;
			margin: 0;
		}

		img {
			max-width: 100%;
			height: auto;
			display: block;
		}

		/* Header & Nav */
		.pv-header {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			padding: 20px 40px;
			display: flex;
			justify-content: space-between;
			align-items: center;
			z-index: 2000;
			transition: background 0.3s;
			box-sizing: border-box;
		}

		.pv-header.scrolled {
			background: rgba(255, 255, 255, 0.95);
			border-bottom: 1px solid #eee;
		}

		.pv-header.scrolled .pv-logo a,
		.pv-header.scrolled .pv-menu-btn {
			color: var(--pv-black);
		}

		/* Default state (transparent header) - text color depends on page type, handled below */
		.pv-logo {
			font-size: 32px;
			font-weight: 900;
			font-style: italic;
			z-index: 2001;
		}

		.pv-menu-btn {
			font-size: 24px;
			cursor: pointer;
			z-index: 2001;
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		.pv-menu-line {
			width: 30px;
			height: 2px;
			background: currentColor;
			transition: 0.3s;
		}

		/* Overlay Menu */
		.pv-overlay-menu {
			position: fixed;
			top: 0;
			right: -100%;
			width: 100%;
			/* max-width not needed if we want full screen, but let's keep it responsive if desired */
			/* The user image looks like a full page or a very wide drawer. Let's make it 100% on mobile, maybe reduced on desktop? 
			   The request implies a "drawer" ("당기면 열리는..."). Let's keep 480px or expand to bigger for the split view?
			   Split view needs width. Let's try 600px-800px or just wider. */
			max-width: 600px; 
			height: 100vh;
			background: #F9F9F5; /* Cream background */
			color: var(--pv-black);
			z-index: 2002;
			transition: right 0.4s cubic-bezier(0.77, 0, 0.175, 1);
			display: flex;
			flex-direction: column;
			padding: 40px;
			box-sizing: border-box;
			box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
		}

		.pv-overlay-menu.active {
			right: 0;
		}

		.pv-menu-header {
			display: flex;
			justify-content: flex-end;
			margin-bottom: 20px;
		}

		.pv-menu-close {
			font-size: 40px;
			cursor: pointer;
			line-height: 1;
			padding: 10px;
		}

		.pv-menu-search {
			margin-bottom: 40px;
			border-bottom: 2px solid #000;
			display: flex;
			align-items: center;
			padding-bottom: 10px;
		}

		.pv-menu-search input {
			border: none;
			background: none;
			font-size: 18px;
			width: 100%;
			outline: none;
			font-weight: 700;
			color: #000;
		}

		.pv-menu-search svg {
			width: 24px;
			height: 24px;
		}

			.pv-menu-list {
				position: relative;
				display: grid;
				grid-template-columns: minmax(0, 1fr) minmax(160px, 45%);
				column-gap: 30px;
				row-gap: 10px;
				align-content: start;
				height: 100%;
			}

			.pv-menu-item {
				position: static;
				grid-column: 1;
				cursor: pointer;
				padding: 5px 0;
			}

			.pv-menu-cat {
				font-size: 32px;
				font-weight: 900;
				text-transform: uppercase;
				line-height: 1.2;
				color: var(--pv-black);
				transition: color 0.2s;
				display: block;
				width: 100%;
			}

		/* Hover Effects */
		.pv-menu-item:hover .pv-menu-cat,
		.pv-menu-item.is-active .pv-menu-cat {
			color: #F04F23; /* Orange */
		}

			.pv-menu-sub {
				display: none;
				position: absolute;
				top: 0;
				left: 55%;
				width: 45%;
				flex-direction: column;
				gap: 15px;
				text-align: left;
			padding: 4px 0 18px;
			animation: fadeIn 0.3s ease;
			z-index: 5;
		}
		
			.pv-menu-item:hover .pv-menu-sub,
			.pv-menu-item.is-active .pv-menu-sub {
				display: flex;
			}

		.pv-menu-sub a {
			font-size: 16px;
			font-weight: 700;
			color: #000;
			text-decoration: none;
			transition: color 0.2s;
		}

		.pv-menu-sub a:hover {
			color: #F04F23; /* Orange */
		}

		/* "Entire" (전체) bold or distinct style? Image shows plain text. Let's keep it bold. */

		@keyframes fadeIn {
			from { opacity: 0; transform: translateX(10px); }
			to { opacity: 1; transform: translateX(0); }
		}

		.pv-menu-footer {
			margin-top: auto;
			padding-top: 40px;
			display: flex;
			flex-wrap: wrap;
			gap: 20px;
			font-size: 13px;
			font-weight: 700;
			text-transform: uppercase;
		}

		@media (max-width: 768px) {
				.pv-overlay-menu {
					max-width: 100%; /* Full width on mobile */
				}
				.pv-menu-list {
					grid-template-columns: minmax(0, 1fr) minmax(140px, 42%);
					column-gap: 24px;
				}
				.pv-menu-cat { font-size: 28px; }
				.pv-menu-sub { left: 58%; width: 42%; }
				.pv-menu-item { position: static; }
				.pv-menu-item:hover .pv-menu-sub,
				.pv-menu-item.is-active .pv-menu-sub { display: flex; }
			}
	</style>
</head>

<body <?php body_class(); ?>>

	<header class="pv-header" id="pvHeader">
		<div class="pv-logo"><a href="<?php echo home_url(); ?>">Project B</a></div>
		<div class="pv-menu-btn" onclick="toggleMenu()">
			<div class="pv-menu-line"></div>
			<div class="pv-menu-line"></div>
			<div class="pv-menu-line"></div>
		</div>
	</header>

	<!-- Overlay Menu Global -->
	<div class="pv-overlay-menu" id="pvOverlayMenu">
		<div class="pv-menu-header">
			<div class="pv-menu-close" onclick="toggleMenu()">✕</div>
		</div>

		<div class="pv-menu-search">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
				stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round"
					d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
			</svg>
			<input type="text" placeholder="검색어 입력">
		</div>

		<div class="pv-menu-list">
			<?php
				$can_view_member_menu = function_exists( 'project_b_can_view_member_menu' ) && project_b_can_view_member_menu();

				$user_cats = [
					'BLOG'   => [ '전체', '잡상노트', '일상', '캐나다 워홀', 'Done List' ],
					'REVIEW' => [ '맛집', '영화 드라마', '전시', '책', '게임', 'IT' ],
					'TRAVEL' => [ '전체', '2025', '2024', '2023' ],
					'PROS'   => [ '개인작' ],
					'LOG'    => [ '커뮤로그', '개인 연성', '그림 연습' ],
					'OC'     => [ '전체', '자캐 커플', '커뮤 로그 백업', '그 외' ],
				];

				if ( $can_view_member_menu ) {
					$user_cats['PROS'][] = '커미션 / 리퀘';
				}

				$user_cats['PROS'][] = '연재';

			foreach ($user_cats as $cat_key => $subs) {
				// Robust Category Lookup (Slug first, then Name)
				$term_slug = strtolower($cat_key);
				$cat_obj = get_term_by('slug', $term_slug, 'category');
				if (!$cat_obj) {
					$cat_obj = get_term_by('name', $cat_key, 'category');
				}
					$cat_link = $cat_obj ? get_category_link($cat_obj->term_id) : '#';
					if ('OC' === $cat_key) {
						if (function_exists('project_b_menu_url')) {
							$cat_link = project_b_menu_url('page', 'oc');
						} else {
							$oc_page = get_page_by_path('oc');
							$cat_link = $oc_page ? get_permalink($oc_page->ID) : '#';
						}
					}

				// Ensure '전체' is first
				$display_subs = $subs;
				if (!in_array('전체', $display_subs)) {
					array_unshift($display_subs, '전체');
				} else {
					$display_subs = array_diff($display_subs, ['전체']);
					array_unshift($display_subs, '전체');
				}

				echo '<div class="pv-menu-item">';
				echo '<a href="' . esc_url($cat_link) . '" class="pv-menu-cat">' . $cat_key . '</a>';
				echo '<div class="pv-menu-sub">';
				
				foreach ($display_subs as $sub) {
					$sub_link = '#';
					
					// Special handling for '전체' (Entire)
					// Priority: Page with same slug as Main Category > Category Archive
						if ('OC' === $cat_key) {
							if ('전체' === $sub) {
								$sub_link = $cat_link;
							} elseif ('자캐 커플' === $sub) {
								if (function_exists('project_b_menu_url')) {
									$sub_link = project_b_menu_url('page', 'oc-couples');
								} else {
									$oc_couples_page = get_page_by_path('oc-couples');
									$sub_link = $oc_couples_page ? get_permalink($oc_couples_page->ID) : '#';
								}
							} elseif ('커뮤 로그 백업' === $sub) {
								if (function_exists('project_b_menu_url')) {
									$sub_link = project_b_menu_url('category', 'commu-log-backup');
								} else {
									$commu_term = get_category_by_slug('commu-log-backup');
									$sub_link = $commu_term ? get_category_link($commu_term) : '#';
								}
							} elseif ('그 외' === $sub) {
								if (function_exists('project_b_menu_url')) {
									$sub_link = project_b_menu_url('category', 'oc-etc');
								} else {
									$oc_etc_term = get_category_by_slug('oc-etc');
									$sub_link = $oc_etc_term ? get_category_link($oc_etc_term) : '#';
								}
							}
						} elseif ('PROS' === $cat_key && '커미션 / 리퀘' === $sub) {
							if (function_exists('project_b_menu_url')) {
								$sub_link = project_b_menu_url('category', 'commission-request');
							} else {
								$commission_term = get_category_by_slug('commission-request');
								$sub_link = $commission_term ? get_category_link($commission_term) : '#';
							}
						} elseif ('PROS' === $cat_key && '연재' === $sub) {
							if (function_exists('project_b_menu_url')) {
								$sub_link = project_b_menu_url('page', 'serial');
							} else {
								$serial_page = get_page_by_path('serial');
								$sub_link = $serial_page ? get_permalink($serial_page->ID) : '#';
							}
						} elseif ($sub === '전체') {
							$page_obj = get_page_by_path($term_slug); // Check for page with main cat slug (e.g. 'review')
							if ($page_obj) {
								$sub_link = get_permalink($page_obj->ID);
						} else {
							$sub_link = $cat_link; // Fallback to category archive
						}
					} else {
						// Normal sub-category logic
						$sub_obj = get_term_by('name', $sub, 'category');
						if (!$sub_obj) {
							$sub_slug = sanitize_title($sub);
							$sub_obj = get_term_by('slug', $sub_slug, 'category');
						}
						if ($sub_obj) {
							$sub_link = get_category_link($sub_obj->term_id);
						}
					}

					echo '<a href="' . esc_url($sub_link) . '">' . $sub . '</a>';
				}
				echo '</div>';
				echo '</div>';
			}
			?>
		</div>

		<div class="pv-menu-footer">
			<?php if ( is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">로그아웃</a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">설정</a>
			<?php else : ?>
				<a href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>">로그인</a>
			<?php endif; ?>
		</div>
	</div>
