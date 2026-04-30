<?php
/**
 * Custom category archive template.
 *
 * @package Avada
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$current_cat = get_queried_object();

if ( ! ( $current_cat instanceof WP_Term ) ) {
	get_header();
	get_template_part( 'archive' );
	return;
}

$is_done_list_view = 'blog-done-list' === $current_cat->slug;
$done_list_form_post_id = $is_done_list_view && isset( $_GET['edit_post'] ) ? (int) wp_unslash( $_GET['edit_post'] ) : 0;
$done_list_form_mode    = 'create';
$done_list_form_title   = '';
$done_list_form_content = '';

if ( $is_done_list_view && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['project_b_done_list_action'] ) ) {
	if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( '권한이 없습니다.', 'Avada' ) );
	}

	check_admin_referer( 'project_b_done_list_quick_note', 'project_b_done_list_nonce' );

	if ( 'delete-note' === wp_unslash( $_POST['project_b_done_list_action'] ) ) {
		$target_post_id = isset( $_POST['project_b_done_list_post_id'] ) ? (int) wp_unslash( $_POST['project_b_done_list_post_id'] ) : 0;
		$existing_post  = $target_post_id > 0 ? get_post( $target_post_id ) : null;

		if ( ! $existing_post instanceof WP_Post || (int) get_current_user_id() !== (int) $existing_post->post_author ) {
			wp_die( esc_html__( 'ê¶Œí•œì´ ì—†ìŠµë‹ˆë‹¤.', 'Avada' ) );
		}

		wp_delete_post( $target_post_id, true );

		$redirect_args = array(
			'done_deleted' => 1,
		);

		if ( isset( $_POST['done_year'] ) ) {
			$redirect_args['done_year'] = sanitize_text_field( wp_unslash( $_POST['done_year'] ) );
		} elseif ( isset( $_GET['done_year'] ) ) {
			$redirect_args['done_year'] = sanitize_text_field( wp_unslash( $_GET['done_year'] ) );
		}

		if ( isset( $_POST['done_month'] ) ) {
			$redirect_args['done_month'] = sanitize_text_field( wp_unslash( $_POST['done_month'] ) );
		} elseif ( isset( $_GET['done_month'] ) ) {
			$redirect_args['done_month'] = sanitize_text_field( wp_unslash( $_GET['done_month'] ) );
		}

		if ( isset( $_POST['done_year_page'] ) ) {
			$redirect_args['done_year_page'] = (int) wp_unslash( $_POST['done_year_page'] );
		} elseif ( isset( $_GET['done_year_page'] ) ) {
			$redirect_args['done_year_page'] = (int) wp_unslash( $_GET['done_year_page'] );
		}

		wp_safe_redirect( add_query_arg( $redirect_args, get_category_link( $current_cat ) ) );
		exit;
	}

	$entry_title    = isset( $_POST['project_b_done_list_title'] ) ? sanitize_text_field( wp_unslash( $_POST['project_b_done_list_title'] ) ) : '';
	$entry_content  = isset( $_POST['project_b_done_list_content'] ) ? wp_kses_post( wp_unslash( $_POST['project_b_done_list_content'] ) ) : '';
	$target_post_id = isset( $_POST['project_b_done_list_post_id'] ) ? (int) wp_unslash( $_POST['project_b_done_list_post_id'] ) : 0;
	$entry_title    = trim( $entry_title );
	$entry_content  = trim( $entry_content );

	if ( '' !== $entry_content ) {
		$plain_title = '' !== $entry_title ? $entry_title : wp_strip_all_tags( $entry_content );
		$plain_title = preg_replace( '/\s+/', ' ', $plain_title );
		$plain_title = trim( (string) $plain_title );
		$plain_title = '' !== $plain_title ? mb_substr( $plain_title, 0, 36 ) : current_time( 'Y.m.d H:i' ) . ' Done List';

		if ( $target_post_id > 0 ) {
			$existing_post = get_post( $target_post_id );

			if ( ! $existing_post instanceof WP_Post || (int) get_current_user_id() !== (int) $existing_post->post_author ) {
				wp_die( esc_html__( 'ê¶Œí•œì´ ì—†ìŠµë‹ˆë‹¤.', 'Avada' ) );
			}

			$post_id = wp_update_post(
				array(
					'ID'           => $target_post_id,
					'post_title'   => $plain_title,
					'post_content' => $entry_content,
				),
				true
			);

			if ( ! is_wp_error( $post_id ) ) {
				wp_set_post_categories( $post_id, array( (int) $current_cat->term_id ), true );
			}
		} else {
			$post_id = wp_insert_post(
				array(
					'post_type'     => 'post',
					'post_status'   => 'publish',
					'post_title'    => $plain_title,
					'post_content'  => $entry_content,
					'post_category' => array( (int) $current_cat->term_id ),
				),
				true
			);
		}

		if ( ! is_wp_error( $post_id ) ) {
			$redirect_args = array(
				'done_saved' => 1,
			);

			if ( isset( $_POST['done_year'] ) ) {
				$redirect_args['done_year'] = sanitize_text_field( wp_unslash( $_POST['done_year'] ) );
			} elseif ( isset( $_GET['done_year'] ) ) {
				$redirect_args['done_year'] = sanitize_text_field( wp_unslash( $_GET['done_year'] ) );
			}

			if ( isset( $_POST['done_month'] ) ) {
				$redirect_args['done_month'] = sanitize_text_field( wp_unslash( $_POST['done_month'] ) );
			} elseif ( isset( $_GET['done_month'] ) ) {
				$redirect_args['done_month'] = sanitize_text_field( wp_unslash( $_GET['done_month'] ) );
			}

			if ( isset( $_POST['done_year_page'] ) ) {
				$redirect_args['done_year_page'] = (int) wp_unslash( $_POST['done_year_page'] );
			} elseif ( isset( $_GET['done_year_page'] ) ) {
				$redirect_args['done_year_page'] = (int) wp_unslash( $_GET['done_year_page'] );
			}

			wp_safe_redirect( add_query_arg( $redirect_args, get_category_link( $current_cat ) ) );
			exit;
		}
	}
}

if ( $is_done_list_view && $done_list_form_post_id > 0 && is_user_logged_in() ) {
	$done_list_form_post = get_post( $done_list_form_post_id );

	if ( $done_list_form_post instanceof WP_Post && (int) $done_list_form_post->post_author === (int) get_current_user_id() ) {
		$done_list_form_mode    = 'edit';
		$done_list_form_title   = $done_list_form_post->post_title;
		$done_list_form_content = $done_list_form_post->post_content;
	} else {
		$done_list_form_post_id = 0;
	}
}

get_header();

$title_cat  = $current_cat;
$sub_cats   = array();
$current_id = (int) $current_cat->term_id;

if ( $current_cat->parent > 0 ) {
	$title_cat = get_term( $current_cat->parent, 'category' );
	$sub_cats  = get_categories(
		array(
			'parent'     => $title_cat->term_id,
			'hide_empty' => false,
		)
	);
} else {
	$sub_cats = get_categories(
		array(
			'parent'     => $current_cat->term_id,
			'hide_empty' => false,
		)
	);
}

if ( 'blog' === $title_cat->slug && function_exists( 'project_b_get_blog_board_terms' ) ) {
	$sub_cats = project_b_get_blog_board_terms();
}

$show_blog_board_thumbs = ! $is_done_list_view && 'blog' === $title_cat->slug;

$landing_config = function_exists( 'project_b_deep_menu_landing_config' ) ? project_b_deep_menu_landing_config() : array();
$board_all_url  = get_category_link( $title_cat );

if ( isset( $landing_config[ $title_cat->slug ] ) ) {
	$landing_page = get_page_by_path( $title_cat->slug );

	if ( $landing_page ) {
		$board_all_url = get_permalink( $landing_page );
	}

	$tab_order = array();

	foreach ( $landing_config[ $title_cat->slug ]['items'] as $index => $item ) {
		if ( 'category' === $item['type'] && ! empty( $item['slug'] ) ) {
			$tab_order[ $item['slug'] ] = $index;
		}
	}

	if ( ! empty( $tab_order ) && ! empty( $sub_cats ) ) {
		usort(
			$sub_cats,
			function ( $a, $b ) use ( $tab_order ) {
				$a_order = isset( $tab_order[ $a->slug ] ) ? $tab_order[ $a->slug ] : 9999;
				$b_order = isset( $tab_order[ $b->slug ] ) ? $tab_order[ $b->slug ] : 9999;

				if ( $a_order === $b_order ) {
					return strnatcasecmp( $a->name, $b->name );
				}

				return $a_order <=> $b_order;
			}
		);
	}
}

$gallery_root_ids = array();

foreach ( $landing_config as $entry ) {
	if ( empty( $entry['items'] ) || ! is_array( $entry['items'] ) ) {
		continue;
	}

	foreach ( $entry['items'] as $item ) {
		if ( empty( $item['type'] ) || 'category' !== $item['type'] || empty( $item['slug'] ) || empty( $item['label'] ) ) {
			continue;
		}

		if ( '그림' !== $item['label'] ) {
			continue;
		}

		$term = get_category_by_slug( $item['slug'] );

		if ( $term instanceof WP_Term ) {
			$gallery_root_ids[] = (int) $term->term_id;
		}
	}
}

$gallery_root_ids = array_values( array_unique( array_filter( $gallery_root_ids ) ) );
$is_gallery_view  = false;

foreach ( $gallery_root_ids as $gallery_root_id ) {
	if ( $current_id === $gallery_root_id || term_is_ancestor_of( $gallery_root_id, $current_id, 'category' ) ) {
		$is_gallery_view = true;
		break;
	}
}

$current_search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$current_sort   = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'newest';
$allowed_sorts  = array( 'newest', 'oldest', 'title' );
$current_done_year      = $is_done_list_view && isset( $_GET['done_year'] ) ? sanitize_text_field( wp_unslash( $_GET['done_year'] ) ) : '';
$current_done_month     = $is_done_list_view && isset( $_GET['done_month'] ) ? sanitize_text_field( wp_unslash( $_GET['done_month'] ) ) : '';
$current_done_year_page = $is_done_list_view && isset( $_GET['done_year_page'] ) ? (int) wp_unslash( $_GET['done_year_page'] ) : 0;

if ( $is_done_list_view && preg_match( '/^(\d{4})-(\d{2})$/', $current_done_month, $done_month_parts ) ) {
	if ( '' === $current_done_year ) {
		$current_done_year = $done_month_parts[1];
	}

	$current_done_month = $done_month_parts[2];
}

if ( ! in_array( $current_sort, $allowed_sorts, true ) ) {
	$current_sort = 'newest';
}

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

$query_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'ignore_sticky_posts' => 1,
	'paged'               => $paged,
	'posts_per_page'      => (int) get_option( 'posts_per_page' ),
	'cat'                 => $current_id,
);

if ( $is_done_list_view ) {
	$query_args['posts_per_page'] = -1;
}

if ( $current_search ) {
	$query_args['s'] = $current_search;
}

if ( $is_done_list_view && preg_match( '/^\d{4}$/', $current_done_year ) ) {
	$query_args['date_query'] = array(
		array(
			'year' => (int) $current_done_year,
		),
	);
}

if ( $is_done_list_view && preg_match( '/^\d{4}$/', $current_done_year ) && preg_match( '/^\d{2}$/', $current_done_month ) ) {
	$query_args['date_query'] = array(
		array(
			'year'     => (int) $current_done_year,
			'monthnum' => (int) $current_done_month,
		),
	);
}

switch ( $current_sort ) {
	case 'oldest':
		$query_args['orderby'] = 'date';
		$query_args['order']   = 'ASC';
		break;
	case 'title':
		$query_args['orderby'] = 'title';
		$query_args['order']   = 'ASC';
		break;
	default:
		$query_args['orderby'] = 'date';
		$query_args['order']   = 'DESC';
		break;
}

$display_query = new WP_Query( $query_args );

$gallery_filter_terms = array();
$gallery_base_url     = '';

if ( $is_gallery_view ) {
	$gallery_filter_parent = $current_id;
	$gallery_filter_terms  = get_categories(
		array(
			'parent'     => $gallery_filter_parent,
			'hide_empty' => false,
		)
	);

	if ( empty( $gallery_filter_terms ) && $current_cat->parent > 0 ) {
		$gallery_filter_parent = (int) $current_cat->parent;
		$gallery_filter_terms  = get_categories(
			array(
				'parent'     => $gallery_filter_parent,
				'hide_empty' => false,
			)
		);
	}

	if ( $gallery_filter_parent ) {
		$gallery_base_url = get_category_link( $gallery_filter_parent );
	}
}

$pagination_args = array();

if ( $current_search ) {
	$pagination_args['s'] = $current_search;
}

if ( 'newest' !== $current_sort ) {
	$pagination_args['sort'] = $current_sort;
}

$done_list_years          = array();
$done_list_year_values    = array();
$done_list_months         = array();
$done_list_visible_years  = array();
$done_list_year_page_max  = 0;
$done_list_has_prev_years = false;
$done_list_has_next_years = false;

if ( $is_done_list_view ) {
	global $wpdb;

	$done_list_years = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT DISTINCT DATE_FORMAT(p.post_date, '%%Y') AS year_value
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE p.post_type = 'post'
			  AND p.post_status = 'publish'
			  AND tt.taxonomy = 'category'
			  AND tt.term_id = %d
			ORDER BY year_value DESC
			",
			$current_id
		),
		ARRAY_A
	);

	$done_list_months = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT DISTINCT DATE_FORMAT(p.post_date, '%%Y-%%m') AS month_value,
			       DATE_FORMAT(p.post_date, '%%Y') AS year_value,
			       DATE_FORMAT(p.post_date, '%%m') AS month_value_only
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE p.post_type = 'post'
			  AND p.post_status = 'publish'
			  AND tt.taxonomy = 'category'
			  AND tt.term_id = %d
			ORDER BY month_value DESC
			",
			$current_id
		),
			ARRAY_A
		);
}

if ( $is_done_list_view ) {
	$done_list_year_values = array_values(
		array_filter(
			array_map(
				static function ( $year_row ) {
					return isset( $year_row['year_value'] ) ? (string) $year_row['year_value'] : '';
				},
				(array) $done_list_years
			)
		)
	);

	$done_list_year_page_max = max( 0, count( $done_list_year_values ) - 3 );
	$current_done_year_page  = max( 0, min( $current_done_year_page, $done_list_year_page_max ) );

	if ( preg_match( '/^\d{4}$/', $current_done_year ) ) {
		$selected_year_index = array_search( $current_done_year, $done_list_year_values, true );

		if ( false !== $selected_year_index ) {
			if ( $selected_year_index < $current_done_year_page ) {
				$current_done_year_page = $selected_year_index;
			} elseif ( $selected_year_index > $current_done_year_page + 2 ) {
				$current_done_year_page = $selected_year_index - 2;
			}
		}
	}

	$done_list_visible_years  = array_slice( $done_list_year_values, $current_done_year_page, 3 );
	$done_list_has_prev_years = $current_done_year_page > 0;
	$done_list_has_next_years = $current_done_year_page < $done_list_year_page_max;
}

$injected_posts = array();

if ( ! $is_gallery_view && function_exists( 'project_b_get_board_injected_post_slugs' ) ) {
	$injected_map = project_b_get_board_injected_post_slugs();

	if ( isset( $injected_map[ $current_cat->slug ] ) && is_array( $injected_map[ $current_cat->slug ] ) ) {
		$existing_post_ids = wp_list_pluck( $display_query->posts, 'ID' );
		$existing_post_ids = array_map( 'intval', is_array( $existing_post_ids ) ? $existing_post_ids : array() );

		foreach ( $injected_map[ $current_cat->slug ] as $post_slug ) {
			$injected_post = get_page_by_path( $post_slug, OBJECT, 'post' );

			if ( $injected_post instanceof WP_Post && ! in_array( (int) $injected_post->ID, $existing_post_ids, true ) ) {
				$injected_posts[] = $injected_post;
			}
		}
	}
}

$can_create_board_post = is_user_logged_in() && current_user_can( 'edit_posts' );

if ( ! $can_create_board_post && is_user_logged_in() ) {
	foreach ( (array) $display_query->posts as $board_post ) {
		if ( $board_post instanceof WP_Post && current_user_can( 'edit_post', $board_post->ID ) ) {
			$can_create_board_post = true;
			break;
		}
	}

	if ( ! $can_create_board_post ) {
		foreach ( $injected_posts as $injected_post ) {
			if ( $injected_post instanceof WP_Post && current_user_can( 'edit_post', $injected_post->ID ) ) {
				$can_create_board_post = true;
				break;
			}
		}
	}
}
?>

<style>
	.pb-archive {
		background: #f7f6ef;
		color: #111;
		padding: 24px 0 90px;
	}

	.pb-archive *,
	.pb-archive *::before,
	.pb-archive *::after {
		box-sizing: border-box;
	}

	.pb-archive a {
		color: inherit;
		text-decoration: none;
	}

	.pb-archive__shell {
		width: min(1380px, calc(100% - 56px));
		margin: 0 auto;
	}

	.pb-archive__header {
		padding: 34px 0 42px;
		text-align: center;
	}

	.pb-archive__title {
		margin: 0;
		font-size: clamp(2rem, 4.6vw, 4.2rem);
		line-height: 1;
		font-weight: 800;
		letter-spacing: -0.05em;
		text-transform: uppercase;
		color: #111;
	}

	.pb-archive__tabs {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		gap: 22px;
		margin: 28px 0 0;
	}

	.pb-archive__tab {
		display: inline-flex;
		align-items: center;
		padding-bottom: 8px;
		border-bottom: 3px solid transparent;
		font-size: 15px;
		font-weight: 800;
		letter-spacing: -0.02em;
	}

	.pb-archive__tab.is-current,
	.pb-archive__tab:hover {
		color: #e78645;
		border-bottom-color: #e78645;
	}

	.pb-archive__pagination {
		display: flex;
		justify-content: center;
		gap: 6px;
		padding-top: 30px;
	}

	.pb-archive__pagination .page-numbers {
		min-width: 36px;
		height: 36px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 700;
		color: rgba(17, 17, 17, 0.75);
	}

	.pb-archive__pagination .current,
	.pb-archive__pagination .page-numbers:hover {
		background: #111;
		color: #fff;
	}

	.pb-archive__empty {
		padding: 28px 6px;
		font-size: 16px;
		color: rgba(17, 17, 17, 0.72);
	}

	.pb-board__table {
		border-top: 3px solid #111;
	}

	.pb-board__toolbar {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 14px 18px;
		margin-bottom: 20px;
		padding-top: 6px;
	}

	.pb-board__actions {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px;
	}

	.pb-board__primary {
		display: flex;
		flex: 1;
		min-width: min(320px, 100%);
	}

	.pb-board__secondary {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		margin-left: auto;
	}

	.pb-board__button,
	.pb-board__search-input {
		min-height: 46px;
		border-radius: 16px;
		border: 1px solid rgba(17, 17, 17, 0.12);
		background: rgba(255, 255, 255, 0.72);
		font-size: 14px;
		font-weight: 700;
		color: #111;
	}

	.pb-board__button {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		padding: 0 18px;
		transition: background .2s ease, color .2s ease, border-color .2s ease;
	}

	.pb-board__button:hover {
		background: #111;
		border-color: #111;
		color: #fff;
	}

	.pb-board__search-form {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px;
	}

	.pb-board__search-input {
		width: min(320px, 62vw);
		padding: 0 14px;
		outline: none;
	}

	.pb-board__row {
		display: grid;
		grid-template-columns: 130px minmax(0, 1fr) 160px;
		gap: 24px;
		align-items: center;
		padding: 18px 6px;
		border-bottom: 1px solid rgba(17, 17, 17, 0.12);
	}

	.pb-board__row.pb-board__row--with-thumb {
		grid-template-columns: 130px 90px minmax(0, 1fr) 160px;
	}

	.pb-board__row:hover {
		background: rgba(255, 255, 255, 0.58);
	}

	.pb-board__date {
		font-size: 13px;
		font-weight: 700;
		letter-spacing: 0.04em;
		color: rgba(17, 17, 17, 0.62);
	}

	.pb-board__main {
		min-width: 0;
	}

	.pb-board__main-link {
		display: block;
		color: inherit;
		text-decoration: none;
	}

	.pb-board__post-title {
		margin: 0 0 6px;
		font-size: 22px;
		line-height: 1.1;
		font-weight: 800;
		letter-spacing: -0.04em;
		word-break: keep-all;
		color: #111;
	}

	.pb-board__excerpt {
		font-size: 15px;
		line-height: 1.5;
		color: rgba(17, 17, 17, 0.72);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.pb-board__row-actions {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 8px;
		align-self: end;
	}

	.pb-board__action-btn {
		width: 44px;
		height: 44px;
		padding: 0;
		border: 1px solid rgba(17, 17, 17, 0.08);
		border-radius: 999px;
		background: rgba(17, 17, 17, 0.06);
		color: #111;
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		transition: background .2s ease, color .2s ease, border-color .2s ease, opacity .2s ease, transform .2s ease;
	}

	.pb-board__action-btn:hover:not(:disabled) {
		background: #111;
		border-color: #111;
		color: #fff;
		transform: translateY(-1px);
	}

	.pb-board__action-btn:disabled {
		opacity: 0.55;
		cursor: wait;
	}

	.pb-board__action-btn svg {
		width: 19px;
		height: 19px;
		stroke: currentColor;
		fill: none;
		stroke-width: 1.9;
		stroke-linecap: round;
		stroke-linejoin: round;
	}

	.pb-editor-modal[hidden] {
		display: none !important;
	}

	.pb-editor-modal {
		position: fixed;
		inset: 0;
		z-index: 5000;
	}

	.pb-editor-modal__backdrop {
		position: absolute;
		inset: 0;
		background: rgba(17, 17, 17, 0.45);
		backdrop-filter: blur(6px);
	}

	.pb-editor-modal__panel {
		position: relative;
		width: min(920px, calc(100vw - 32px));
		max-height: calc(100vh - 32px);
		margin: 16px auto;
		padding: 20px;
		border-radius: 28px;
		background: #f8f6ef;
		box-shadow: 0 28px 70px rgba(17, 17, 17, 0.22);
		display: flex;
		flex-direction: column;
		gap: 16px;
		overflow: hidden;
	}

	.pb-editor-modal__title {
		width: 100%;
		min-height: 58px;
		padding: 0 18px;
		border: 1px solid rgba(17, 17, 17, 0.12);
		border-radius: 18px;
		background: rgba(255, 255, 255, 0.88);
		font-size: 18px;
		font-weight: 800;
		color: #111;
		outline: none;
	}

	#pb-editor-toolbar {
		border: 1px solid rgba(17, 17, 17, 0.12);
		border-radius: 18px 18px 0 0;
		background: rgba(255, 255, 255, 0.88);
	}

	#pb-editor-body {
		flex: 1;
		min-height: 340px;
		border: 1px solid rgba(17, 17, 17, 0.12);
		border-top: 0;
		border-radius: 0 0 18px 18px;
		background: rgba(255, 255, 255, 0.94);
		overflow: auto;
	}

	#pb-editor-body .ql-editor {
		min-height: 340px;
		font-size: 16px;
		line-height: 1.72;
		color: #111;
	}

	.pb-editor-modal__footer {
		display: flex;
		justify-content: flex-end;
		gap: 10px;
	}

	.pb-editor-modal__footer button {
		min-height: 44px;
		padding: 0 18px;
		border: 1px solid rgba(17, 17, 17, 0.12);
		border-radius: 999px;
		background: rgba(255, 255, 255, 0.88);
		font-size: 14px;
		font-weight: 800;
		color: #111;
		cursor: pointer;
	}

	.pb-editor-modal__footer button:hover:not(:disabled) {
		background: #111;
		border-color: #111;
		color: #fff;
	}

	.pb-editor-modal__footer button:disabled {
		opacity: 0.6;
		cursor: wait;
	}

	.pb-editor-modal__status {
		min-height: 20px;
		font-size: 13px;
		font-weight: 700;
		color: rgba(17, 17, 17, 0.62);
	}

	.pb-board__thumb {
		width: 90px;
		height: 90px;
		border-radius: 10px;
		overflow: hidden;
		flex-shrink: 0;
		background: #ddd6c8;
	}

	.pb-board__thumb img {
		display: block;
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.pb-board__thumb-fallback {
		width: 100%;
		height: 100%;
		background: linear-gradient(135deg, #e8e4dc, #c8c2b8);
	}

	.pb-done {
		display: flex;
		flex-direction: column;
		gap: 28px;
	}

	.pb-done__intro {
		display: flex;
		flex-direction: column;
		align-items: stretch;
		gap: 18px;
		margin-bottom: 6px;
	}

	.pb-done__headline {
		display: none;
	}

	.pb-done__filters {
		display: flex;
		flex-direction: column;
		gap: 14px;
	}

	.pb-done__years {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.pb-done__year-track {
		display: flex;
		flex: 1;
		align-items: center;
		justify-content: flex-end;
		flex-wrap: nowrap;
		gap: 10px;
		overflow: hidden;
	}

	.pb-done__year-nav {
		width: 42px;
		min-width: 42px;
		height: 42px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 999px;
		border: 1px solid rgba(17, 17, 17, 0.12);
		background: rgba(255, 255, 255, 0.76);
		font-size: 18px;
		font-weight: 700;
		color: #111;
		transition: background .2s ease, color .2s ease, border-color .2s ease;
	}

	.pb-done__year-nav.is-disabled {
		opacity: 0.35;
		pointer-events: none;
	}

	.pb-done__year-nav:hover {
		background: #111;
		border-color: #111;
		color: #fff;
	}

	.pb-done__year,
	.pb-done__month {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 40px;
		padding: 0 18px;
		border-radius: 999px;
		border: 1px solid rgba(17, 17, 17, 0.12);
		background: rgba(255, 255, 255, 0.76);
		font-size: 13px;
		font-weight: 800;
		color: #111;
		white-space: nowrap;
		transition: background .2s ease, color .2s ease, border-color .2s ease;
	}

	.pb-done__year {
		min-width: 92px;
	}

	.pb-done__months {
		display: flex;
		flex-wrap: wrap;
		justify-content: flex-end;
		gap: 10px;
		padding-top: 2px;
	}

	.pb-done__year.is-current,
	.pb-done__year:hover,
	.pb-done__month.is-current,
	.pb-done__month:hover {
		background: #111;
		border-color: #111;
		color: #fff;
	}

	.pb-done__quick {
		padding: 22px 22px 20px;
		border: 1px solid rgba(17, 17, 17, 0.08);
		border-radius: 26px;
		background: rgba(255, 255, 255, 0.8);
	}

	.pb-done__quick-title {
		margin: 0 0 12px;
		font-size: 15px;
		font-weight: 800;
		letter-spacing: -0.02em;
	}

	.pb-done__quick-input,
	.pb-done__quick textarea {
		width: 100%;
		padding: 16px 18px;
		border: 1px solid rgba(17, 17, 17, 0.12);
		border-radius: 18px;
		background: #fff;
		font-size: 15px;
		line-height: 1.6;
		color: #111;
		outline: none;
	}

	.pb-done__quick-input {
		min-height: 54px;
		margin-bottom: 12px;
	}

	.pb-done__quick textarea {
		min-height: 118px;
		resize: vertical;
	}

	.pb-done__quick-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		justify-content: flex-end;
		margin-top: 14px;
	}

	.pb-done__quick button,
	.pb-done__entry-action,
	.pb-done__quick-cancel {
		min-height: 44px;
		padding: 0 18px;
		border: 0;
		border-radius: 999px;
		background: #111;
		color: #fff;
		font-size: 14px;
		font-weight: 800;
		cursor: pointer;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.pb-done__quick-cancel {
		background: rgba(17, 17, 17, 0.08);
		color: #111;
	}

	.pb-done__entry-tools {
		display: flex;
		gap: 8px;
		justify-content: flex-end;
		margin-top: 18px;
	}

	.pb-done__entry-action {
		width: 44px;
		min-width: 44px;
		padding: 0;
		background: rgba(17, 17, 17, 0.08);
		color: #111;
	}

	.pb-done__entry-action svg {
		width: 18px;
		height: 18px;
		stroke: currentColor;
		fill: none;
		stroke-width: 1.9;
		stroke-linecap: round;
		stroke-linejoin: round;
	}

	.pb-done__entry-delete {
		border: 0;
	}

	.pb-done__saved {
		padding: 12px 16px;
		border-radius: 16px;
		background: rgba(24, 134, 74, 0.1);
		font-size: 14px;
		font-weight: 700;
		color: #186a3b;
	}

	.pb-done__stream {
		display: flex;
		flex-direction: column;
		gap: 22px;
	}

	.pb-done__entry {
		padding: 26px 26px 24px;
		border: 1px solid rgba(17, 17, 17, 0.08);
		border-radius: 28px;
		background: rgba(255, 255, 255, 0.8);
		box-shadow: 0 16px 40px rgba(17, 17, 17, 0.04);
	}

	.pb-done__entry-head {
		display: flex;
		flex-wrap: wrap;
		align-items: baseline;
		justify-content: space-between;
		gap: 8px 16px;
		margin-bottom: 16px;
	}

	.pb-done__entry-title {
		margin: 0;
		font-size: 1.18rem;
		line-height: 1.2;
		font-weight: 800;
		letter-spacing: -0.03em;
		word-break: keep-all;
		color: #111;
	}

	.pb-done__entry-date {
		font-size: 12px;
		font-weight: 800;
		letter-spacing: 0.08em;
		color: rgba(17, 17, 17, 0.5);
	}

	.pb-done__entry-body {
		font-size: 15px;
		line-height: 1.72;
		color: #181818;
		word-break: keep-all;
	}

	.pb-done__entry-body > :first-child {
		margin-top: 0 !important;
	}

	.pb-done__entry-body > :last-child {
		margin-bottom: 0 !important;
	}

	.pb-done__entry-body p {
		margin: 0 0 0.95em !important;
	}

	.pb-done__entry-body ul,
	.pb-done__entry-body ol {
		margin: 0 0 0.95em 1.3em;
	}

	.pb-gallery__toolbar {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 14px 18px;
		margin-bottom: 26px;
		padding-top: 6px;
	}

	.pb-gallery__actions,
	.pb-gallery__filters {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px;
	}

	.pb-gallery__button,
	.pb-gallery__search-input,
	.pb-gallery__select {
		min-height: 46px;
		border-radius: 16px;
		border: 1px solid rgba(17, 17, 17, 0.12);
		background: rgba(255, 255, 255, 0.72);
		font-size: 14px;
		font-weight: 700;
		color: #111;
	}

	.pb-gallery__button {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		padding: 0 18px;
		transition: background .2s ease, color .2s ease, border-color .2s ease;
	}

	.pb-gallery__button:hover {
		background: #111;
		border-color: #111;
		color: #fff;
	}

	.pb-gallery__search-form,
	.pb-gallery__filter-form {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 12px;
	}

	.pb-gallery__search-input,
	.pb-gallery__select {
		padding: 0 14px;
		outline: none;
	}

	.pb-gallery__search-input {
		width: min(280px, 56vw);
	}

	.pb-gallery__select {
		min-width: 138px;
	}

	.pb-gallery__grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 18px;
		align-items: start;
	}

	.pb-gallery__card {
		position: relative;
		display: block;
		width: 100%;
	}

	.pb-gallery__thumb {
		position: relative;
		display: block;
		overflow: hidden;
		border-radius: 18px;
		background: #ddd6c8;
		aspect-ratio: 1 / 1.08;
	}

	.pb-gallery__thumb img,
	.pb-gallery__fallback {
		display: block;
		width: 100%;
		height: 100%;
		object-fit: cover;
		transition: filter .35s ease, transform .35s ease;
	}

	.pb-gallery__thumb img {
		filter: grayscale(1);
	}

	.pb-gallery__card:hover .pb-gallery__thumb img,
	.pb-gallery__card:focus-visible .pb-gallery__thumb img {
		filter: grayscale(0);
		transform: scale(1.03);
	}

	.pb-gallery__fallback {
		background:
			linear-gradient(135deg, rgba(255,255,255,.26), rgba(0,0,0,.1)),
			linear-gradient(135deg, #1f1f1f, #b7b7b7);
	}

	.pb-gallery__overlay {
		position: absolute;
		inset: 0;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		padding: 14px;
		background: linear-gradient(180deg, rgba(10, 10, 10, 0.08) 0%, rgba(10, 10, 10, 0.78) 100%);
		color: #fff;
		opacity: 0;
		transition: opacity .25s ease;
		pointer-events: none;
	}

	.pb-gallery__card:hover .pb-gallery__overlay,
	.pb-gallery__card:focus-visible .pb-gallery__overlay {
		opacity: 1;
	}

	.pb-gallery__overlay-top {
		display: flex;
		justify-content: flex-end;
	}

	.pb-gallery__views {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 7px 10px;
		border-radius: 999px;
		background: rgba(255, 255, 255, 0.16);
		backdrop-filter: blur(10px);
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.01em;
	}

	.pb-gallery__views svg {
		width: 15px;
		height: 15px;
		fill: currentColor;
	}

	.pb-gallery__overlay-bottom {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.pb-gallery__title {
		margin: 0;
		font-size: 1.2rem;
		line-height: 1.16;
		font-weight: 800;
		letter-spacing: -0.04em;
		word-break: keep-all;
	}

	.pb-gallery__sub {
		display: flex;
		align-items: center;
		gap: 10px;
		font-size: 12px;
		font-weight: 700;
		color: rgba(255, 255, 255, 0.9);
	}

	@media (max-width: 980px) {
		.pb-archive__shell {
			width: min(100% - 28px, 1380px);
		}

		.pb-gallery__grid {
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}

		.pb-board__row {
			grid-template-columns: 1fr;
			gap: 8px;
			padding: 16px 0;
		}

		.pb-board__row.pb-board__row--with-thumb .pb-board__thumb {
			width: 68px;
			height: 68px;
		}

		.pb-board__toolbar {
			align-items: stretch;
		}

		.pb-board__actions,
		.pb-board__primary,
		.pb-board__secondary,
		.pb-board__search-form {
			width: 100%;
		}

		.pb-board__secondary {
			justify-content: flex-start;
			margin-left: 0;
		}

		.pb-board__search-input {
			width: 100%;
		}

		.pb-board__row-actions {
			justify-content: flex-start;
			align-self: start;
		}

		.pb-done__entry {
			padding: 22px 20px 20px;
		}
	}

	@media (max-width: 640px) {
		.pb-archive__header {
			padding-bottom: 30px;
		}

		.pb-archive__tabs {
			gap: 16px;
		}

		.pb-gallery__toolbar {
			align-items: stretch;
		}

		.pb-gallery__actions,
		.pb-gallery__filters,
		.pb-gallery__search-form,
		.pb-gallery__filter-form {
			width: 100%;
		}

		.pb-gallery__search-input,
		.pb-gallery__select {
			width: 100%;
		}

		.pb-gallery__grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.pb-gallery__thumb {
			border-radius: 14px;
		}

		.pb-gallery__overlay {
			padding: 12px;
		}

		.pb-gallery__title {
			font-size: 1rem;
		}

		.pb-done__intro {
			align-items: stretch;
		}

		.pb-done__years {
			align-items: stretch;
		}

		.pb-done__year-track {
			justify-content: flex-start;
			flex-wrap: wrap;
		}

		.pb-done__year {
			min-width: 0;
		}

		.pb-done__quick {
			padding: 18px;
			border-radius: 22px;
		}
	}

	@media (max-width: 480px) {
		.pb-gallery__grid {
			grid-template-columns: 1fr;
		}
	}
</style>

<main class="pb-archive">
	<div class="pb-archive__shell">
		<header class="pb-archive__header">
			<h1 class="pb-archive__title"><?php echo esc_html( $title_cat->name ); ?></h1>
			<?php if ( ! empty( $sub_cats ) ) : ?>
				<nav class="pb-archive__tabs" aria-label="<?php echo esc_attr( $title_cat->name ); ?>">
						<a class="pb-archive__tab<?php echo ( $current_cat->term_id === $title_cat->term_id ) ? ' is-current' : ''; ?>" href="<?php echo esc_url( $board_all_url ); ?>">
							전체
						</a>
						<?php foreach ( $sub_cats as $sub_cat ) : ?>
							<?php
							$sub_cat_url = get_category_link( $sub_cat );

							if ( 'pros' === $title_cat->slug && ( '연재' === $sub_cat->name || 'serial' === $sub_cat->slug ) ) {
								$sub_cat_url = function_exists( 'project_b_menu_url' ) ? project_b_menu_url( 'page', 'serial' ) : home_url( '/serial/' );
							}
							?>
							<a class="pb-archive__tab<?php echo ( $current_id === (int) $sub_cat->term_id ) ? ' is-current' : ''; ?>" href="<?php echo esc_url( $sub_cat_url ); ?>">
								<?php echo esc_html( $sub_cat->name ); ?>
							</a>
						<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</header>

		<?php if ( $is_done_list_view ) : ?>
			<section class="pb-done">
				<div class="pb-done__intro">
					<div class="pb-done__filters">
						<?php
						$done_all_url = add_query_arg(
							array(
								'done_year_page' => $current_done_year_page,
							),
							get_category_link( $current_cat )
						);
						$prev_year_url = add_query_arg(
							array(
								'done_year_page' => max( 0, $current_done_year_page - 1 ),
							),
							get_category_link( $current_cat )
						);
						$next_year_url = add_query_arg(
							array(
								'done_year_page' => min( $done_list_year_page_max, $current_done_year_page + 1 ),
							),
							get_category_link( $current_cat )
						);
						?>
						<div class="pb-done__years">
							<a class="pb-done__year<?php echo '' === $current_done_year ? ' is-current' : ''; ?>" href="<?php echo esc_url( $done_all_url ); ?>">&#51204;&#52404;</a>

							<div class="pb-done__year-track">
								<a class="pb-done__year-nav<?php echo $done_list_has_prev_years ? '' : ' is-disabled'; ?>" href="<?php echo esc_url( $prev_year_url ); ?>" aria-label="Previous years">&lsaquo;</a>
								<?php foreach ( $done_list_visible_years as $done_year_value ) : ?>
									<a
										class="pb-done__year<?php echo $current_done_year === $done_year_value ? ' is-current' : ''; ?>"
										href="<?php echo esc_url( add_query_arg( array( 'done_year' => $done_year_value, 'done_year_page' => $current_done_year_page ), get_category_link( $current_cat ) ) ); ?>"
									>
										<?php echo esc_html( $done_year_value ); ?>&#45380;
									</a>
								<?php endforeach; ?>
								<a class="pb-done__year-nav<?php echo $done_list_has_next_years ? '' : ' is-disabled'; ?>" href="<?php echo esc_url( $next_year_url ); ?>" aria-label="Next years">&rsaquo;</a>
							</div>
						</div>

						<?php if ( preg_match( '/^\d{4}$/', $current_done_year ) ) : ?>
							<div class="pb-done__months">
								<?php for ( $month_index = 1; $month_index <= 12; $month_index++ ) : ?>
									<?php $month_value = sprintf( '%02d', $month_index ); ?>
									<a
										class="pb-done__month<?php echo $current_done_month === $month_value ? ' is-current' : ''; ?>"
										href="<?php echo esc_url( add_query_arg( array( 'done_year' => $current_done_year, 'done_month' => $month_value, 'done_year_page' => $current_done_year_page ), get_category_link( $current_cat ) ) ); ?>"
									>
										<?php echo esc_html( $month_index ); ?>&#50900;
									</a>
								<?php endfor; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( isset( $_GET['done_saved'] ) ) : ?>
					<div class="pb-done__saved">&#44172;&#49884;&#44544;&#51060; &#46321;&#47197;&#46104;&#50632;&#49845;&#45768;&#45796;.</div>
				<?php endif; ?>
				<?php if ( isset( $_GET['done_deleted'] ) ) : ?>
					<div class="pb-done__saved">&#44172;&#49884;&#44544;&#51060; &#49325;&#51228;&#46104;&#50632;&#49845;&#45768;&#45796;.</div>
				<?php endif; ?>

				<?php if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) : ?>
					<form class="pb-done__quick" method="post" action="<?php echo esc_url( get_category_link( $current_cat ) ); ?>">
						<h3 class="pb-done__quick-title"><?php echo 'edit' === $done_list_form_mode ? '&#44172;&#49884;&#44544; &#49688;&#51221;' : '&#49352; &#44172;&#49884;&#44544; &#51089;&#49457;'; ?></h3>
						<?php wp_nonce_field( 'project_b_done_list_quick_note', 'project_b_done_list_nonce' ); ?>
						<input type="hidden" name="project_b_done_list_action" value="quick-note">
						<input type="hidden" name="project_b_done_list_post_id" value="<?php echo esc_attr( (string) $done_list_form_post_id ); ?>">
						<input class="pb-done__quick-input" type="text" name="project_b_done_list_title" placeholder="&#51228;&#47785;&#51012; &#51077;&#47141;&#54616;&#49464;&#50836;" value="<?php echo esc_attr( $done_list_form_title ); ?>">
						<?php if ( $current_done_year ) : ?>
							<input type="hidden" name="done_year" value="<?php echo esc_attr( $current_done_year ); ?>">
						<?php endif; ?>
						<?php if ( $current_done_month ) : ?>
							<input type="hidden" name="done_month" value="<?php echo esc_attr( $current_done_month ); ?>">
						<?php endif; ?>
						<input type="hidden" name="done_year_page" value="<?php echo esc_attr( (string) $current_done_year_page ); ?>">
						<textarea name="project_b_done_list_content" placeholder="&#45236;&#50857;&#51012; &#51077;&#47141;&#54616;&#49464;&#50836;. &#44172;&#49884;&#54616;&#47732; Done List &#52852;&#53580;&#44256;&#47532;&#50640; &#48148;&#47196; &#46321;&#47197;&#46121;&#45768;&#45796;."><?php echo esc_textarea( $done_list_form_content ); ?></textarea>
						<div class="pb-done__quick-actions">
							<?php if ( 'edit' === $done_list_form_mode ) : ?>
								<a class="pb-done__quick-cancel" href="<?php echo esc_url( add_query_arg( array(), get_category_link( $current_cat ) ) ); ?>">&#52712;&#49548;</a>
							<?php endif; ?>
							<button type="submit"><?php echo 'edit' === $done_list_form_mode ? '&#51200;&#51109;' : '&#44172;&#49884;&#44544; &#44172;&#49884;'; ?></button>
						</div>
					</form>
				<?php endif; ?>

				<?php if ( $display_query->have_posts() ) : ?>
					<div class="pb-done__stream">
						<?php while ( $display_query->have_posts() ) : ?>
							<?php $display_query->the_post(); ?>
							<article class="pb-done__entry">
								<header class="pb-done__entry-head">
									<h3 class="pb-done__entry-title"><?php the_title(); ?></h3>
									<div class="pb-done__entry-date"><?php echo esc_html( get_the_date( 'Y. m. d H:i' ) ); ?></div>
								</header>
								<div class="pb-done__entry-body">
									<?php echo apply_filters( 'the_content', get_the_content() ); ?>
								</div>
								<?php if ( is_user_logged_in() && (int) get_current_user_id() === (int) get_post_field( 'post_author', get_the_ID() ) ) : ?>
									<div class="pb-done__entry-tools">
										<a class="pb-done__entry-action" href="<?php echo esc_url( add_query_arg( array( 'edit_post' => get_the_ID(), 'done_year' => $current_done_year, 'done_month' => $current_done_month, 'done_year_page' => $current_done_year_page ), get_category_link( $current_cat ) ) ); ?>" aria-label="Edit post">
											<svg viewBox="0 0 24 24" aria-hidden="true">
												<path d="M12 20h9"></path>
												<path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
											</svg>
										</a>
										<form method="post" action="<?php echo esc_url( get_category_link( $current_cat ) ); ?>" onsubmit="return window.confirm('이 글을 삭제할까요?');">
											<?php wp_nonce_field( 'project_b_done_list_quick_note', 'project_b_done_list_nonce' ); ?>
											<input type="hidden" name="project_b_done_list_action" value="delete-note">
											<input type="hidden" name="project_b_done_list_post_id" value="<?php echo esc_attr( (string) get_the_ID() ); ?>">
											<?php if ( $current_done_year ) : ?>
												<input type="hidden" name="done_year" value="<?php echo esc_attr( $current_done_year ); ?>">
											<?php endif; ?>
											<?php if ( $current_done_month ) : ?>
												<input type="hidden" name="done_month" value="<?php echo esc_attr( $current_done_month ); ?>">
											<?php endif; ?>
											<input type="hidden" name="done_year_page" value="<?php echo esc_attr( (string) $current_done_year_page ); ?>">
											<button class="pb-done__entry-action pb-done__entry-delete" type="submit" aria-label="Delete post">
												<svg viewBox="0 0 24 24" aria-hidden="true">
													<path d="M3 6h18"></path>
													<path d="M8 6V4h8v2"></path>
													<path d="M19 6l-1 14H6L5 6"></path>
													<path d="M10 11v6"></path>
													<path d="M14 11v6"></path>
												</svg>
											</button>
										</form>
									</div>
								<?php endif; ?>
							</article>
						<?php endwhile; ?>
					</div>
				<?php else : ?>
					<div class="pb-archive__empty">기록된 Done List가 없습니다.</div>
				<?php endif; ?>
			</section>
		<?php elseif ( $is_gallery_view ) : ?>
			<section class="pb-gallery">
				<div class="pb-gallery__toolbar">
					<div class="pb-gallery__actions">
						<a class="pb-gallery__button" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">글쓰기</a>
						<form class="pb-gallery__search-form" method="get" action="<?php echo esc_url( get_category_link( $current_cat ) ); ?>">
							<input class="pb-gallery__search-input" type="search" name="s" value="<?php echo esc_attr( $current_search ); ?>" placeholder="그림 찾기">
							<?php if ( 'newest' !== $current_sort ) : ?>
								<input type="hidden" name="sort" value="<?php echo esc_attr( $current_sort ); ?>">
							<?php endif; ?>
							<button class="pb-gallery__button" type="submit">찾기</button>
						</form>
					</div>

					<div class="pb-gallery__filters">
						<select class="pb-gallery__select" onchange="if(this.value){window.location.href=this.value;}">
							<option value=""><?php esc_html_e( '분류', 'Avada' ); ?></option>
							<?php if ( $gallery_base_url ) : ?>
								<option value="<?php echo esc_url( $gallery_base_url ); ?>"><?php esc_html_e( '전체', 'Avada' ); ?></option>
							<?php endif; ?>
							<?php foreach ( $gallery_filter_terms as $filter_term ) : ?>
								<option value="<?php echo esc_url( get_category_link( $filter_term ) ); ?>"<?php selected( $current_id, (int) $filter_term->term_id ); ?>>
									<?php echo esc_html( $filter_term->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>

						<form class="pb-gallery__filter-form" method="get" action="<?php echo esc_url( get_category_link( $current_cat ) ); ?>">
							<select class="pb-gallery__select" name="sort" onchange="this.form.submit()">
								<option value="newest"<?php selected( $current_sort, 'newest' ); ?>>정렬: 최신순</option>
								<option value="oldest"<?php selected( $current_sort, 'oldest' ); ?>>정렬: 오래된순</option>
								<option value="title"<?php selected( $current_sort, 'title' ); ?>>정렬: 제목순</option>
							</select>

							<?php if ( $current_search ) : ?>
								<input type="hidden" name="s" value="<?php echo esc_attr( $current_search ); ?>">
							<?php endif; ?>
						</form>
					</div>
				</div>

				<?php if ( $display_query->have_posts() ) : ?>
					<div class="pb-gallery__grid">
						<?php while ( $display_query->have_posts() ) : ?>
							<?php $display_query->the_post(); ?>
							<?php
							$thumb      = function_exists( 'project_b_get_post_preview_image_url' ) ? project_b_get_post_preview_image_url( get_the_ID(), 'large' ) : get_the_post_thumbnail_url( get_the_ID(), 'large' );
							$author     = get_the_author();
							$year       = get_the_date( 'Y' );
							$view_count = 0;
							$view_keys  = array( 'post_views_count', 'views', 'view_count', 'post_views', 'hit' );

							foreach ( $view_keys as $view_key ) {
								$raw_view = get_post_meta( get_the_ID(), $view_key, true );

								if ( '' !== $raw_view && null !== $raw_view ) {
									$view_count = (int) $raw_view;
									break;
								}
							}
							?>
							<a class="pb-gallery__card" href="<?php the_permalink(); ?>">
								<span class="pb-gallery__thumb">
									<?php if ( $thumb ) : ?>
										<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>">
									<?php else : ?>
										<span class="pb-gallery__fallback"></span>
									<?php endif; ?>
									<span class="pb-gallery__overlay">
										<span class="pb-gallery__overlay-top">
											<span class="pb-gallery__views" aria-label="<?php echo esc_attr( sprintf( '조회수 %d', $view_count ) ); ?>">
												<svg viewBox="0 0 24 24" aria-hidden="true">
													<path d="M12 5c5.93 0 10.44 4.88 11.63 6.32a1.1 1.1 0 0 1 0 1.36C22.44 14.12 17.93 19 12 19S1.56 14.12.37 12.68a1.1 1.1 0 0 1 0-1.36C1.56 9.88 6.07 5 12 5Zm0 2C7.67 7 4.16 10.3 2.5 12c1.66 1.7 5.17 5 9.5 5s7.84-3.3 9.5-5C19.84 10.3 16.33 7 12 7Zm0 1.75A3.25 3.25 0 1 1 8.75 12 3.25 3.25 0 0 1 12 8.75Zm0 2A1.25 1.25 0 1 0 13.25 12 1.25 1.25 0 0 0 12 10.75Z"/>
												</svg>
												<?php echo esc_html( number_format_i18n( $view_count ) ); ?>
											</span>
										</span>
										<span class="pb-gallery__overlay-bottom">
											<span class="pb-gallery__title"><?php the_title(); ?></span>
											<span class="pb-gallery__sub">
												<span><?php echo esc_html( $author ); ?></span>
												<span><?php echo esc_html( $year ); ?></span>
											</span>
										</span>
									</span>
								</span>
							</a>
						<?php endwhile; ?>
					</div>
				<?php else : ?>
					<div class="pb-archive__empty">그림 게시물이 없습니다.</div>
				<?php endif; ?>
			</section>
		<?php else : ?>
			<section class="pb-board__toolbar" aria-label="게시판 도구">
				<div class="pb-board__actions pb-board__primary">
					<form class="pb-board__search-form" method="get" action="<?php echo esc_url( get_category_link( $current_cat ) ); ?>">
						<input class="pb-board__search-input" type="search" name="s" value="<?php echo esc_attr( $current_search ); ?>" placeholder="글 찾기">
						<button class="pb-board__button" type="submit">찾기</button>
					</form>
				</div>
				<?php if ( $can_create_board_post ) : ?>
					<div class="pb-board__secondary">
						<button
							class="pb-board__button pb-board__create-btn"
							type="button"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
							data-category-id="<?php echo esc_attr( (string) $current_id ); ?>"
						>
							글쓰기
						</button>
					</div>
				<?php endif; ?>
			</section>

			<section class="pb-board__table">
				<?php if ( $display_query->have_posts() ) : ?>
					<?php while ( $display_query->have_posts() ) : ?>
						<?php $display_query->the_post(); ?>
						<?php
						$excerpt   = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 20, '' );
						$post_cats = get_the_category();
						$can_edit  = is_user_logged_in() && current_user_can( 'edit_post', get_the_ID() );
						?>
						<div class="pb-board__row<?php echo $show_blog_board_thumbs ? ' pb-board__row--with-thumb' : ''; ?>" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>">
							<div class="pb-board__date"><?php echo esc_html( get_the_date( 'Y. m. d' ) ); ?></div>
							<?php if ( $show_blog_board_thumbs ) : ?>
								<?php
								$thumb_url = function_exists( 'project_b_get_post_preview_image_url' )
									? project_b_get_post_preview_image_url( get_the_ID(), 'thumbnail' )
									: get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
								?>
								<div class="pb-board__thumb">
									<?php if ( $thumb_url ) : ?>
										<img src="<?php echo esc_url( $thumb_url ); ?>"
											alt="<?php the_title_attribute(); ?>"
											loading="lazy">
									<?php else : ?>
										<div class="pb-board__thumb-fallback"></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<div class="pb-board__main">
								<a class="pb-board__main-link" href="<?php the_permalink(); ?>">
									<h2 class="pb-board__post-title"><?php the_title(); ?></h2>
									<?php if ( $excerpt ) : ?>
										<div class="pb-board__excerpt"><?php echo esc_html( $excerpt ); ?></div>
									<?php endif; ?>
								</a>
							</div>
							<div class="pb-board__row-actions">
								<?php if ( $can_edit ) : ?>
									<button class="pb-board__action-btn pb-board__edit-btn"
										type="button"
										data-post-id="<?php echo esc_attr( get_the_ID() ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
										aria-label="글 수정">
										<svg viewBox="0 0 24 24" aria-hidden="true">
											<path d="M12 20h9"></path>
											<path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
										</svg>
									</button>
									<button class="pb-board__action-btn pb-board__delete-btn"
										type="button"
										data-post-id="<?php echo esc_attr( get_the_ID() ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
										aria-label="글 삭제">
										<svg viewBox="0 0 24 24" aria-hidden="true">
											<path d="M3 6h18"></path>
											<path d="M8 6V4h8v2"></path>
											<path d="M19 6l-1 14H6L5 6"></path>
											<path d="M10 11v6"></path>
											<path d="M14 11v6"></path>
										</svg>
									</button>
								<?php endif; ?>
							</div>
						</div>
					<?php endwhile; ?>
					<?php foreach ( $injected_posts as $injected_post ) : ?>
						<?php
						$excerpt   = $injected_post->post_excerpt ? $injected_post->post_excerpt : wp_trim_words( wp_strip_all_tags( $injected_post->post_content ), 20, '' );
						$post_cats = get_the_category( $injected_post->ID );
						$can_edit  = is_user_logged_in() && current_user_can( 'edit_post', $injected_post->ID );
						?>
						<div class="pb-board__row<?php echo $show_blog_board_thumbs ? ' pb-board__row--with-thumb' : ''; ?>" data-post-id="<?php echo esc_attr( $injected_post->ID ); ?>">
							<div class="pb-board__date"><?php echo esc_html( get_the_date( 'Y. m. d', $injected_post ) ); ?></div>
							<?php if ( $show_blog_board_thumbs ) : ?>
								<?php
								$thumb_url = function_exists( 'project_b_get_post_preview_image_url' )
									? project_b_get_post_preview_image_url( $injected_post->ID, 'thumbnail' )
									: get_the_post_thumbnail_url( $injected_post->ID, 'thumbnail' );
								?>
								<div class="pb-board__thumb">
									<?php if ( $thumb_url ) : ?>
										<img src="<?php echo esc_url( $thumb_url ); ?>"
											alt="<?php echo esc_attr( get_the_title( $injected_post ) ); ?>"
											loading="lazy">
									<?php else : ?>
										<div class="pb-board__thumb-fallback"></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<div class="pb-board__main">
								<a class="pb-board__main-link" href="<?php echo esc_url( get_permalink( $injected_post ) ); ?>">
									<h2 class="pb-board__post-title"><?php echo esc_html( get_the_title( $injected_post ) ); ?></h2>
									<?php if ( $excerpt ) : ?>
										<div class="pb-board__excerpt"><?php echo esc_html( $excerpt ); ?></div>
									<?php endif; ?>
								</a>
							</div>
							<div class="pb-board__row-actions">
								<?php if ( $can_edit ) : ?>
									<button class="pb-board__action-btn pb-board__edit-btn"
										type="button"
										data-post-id="<?php echo esc_attr( $injected_post->ID ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
										aria-label="글 수정">
										<svg viewBox="0 0 24 24" aria-hidden="true">
											<path d="M12 20h9"></path>
											<path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
										</svg>
									</button>
									<button class="pb-board__action-btn pb-board__delete-btn"
										type="button"
										data-post-id="<?php echo esc_attr( $injected_post->ID ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
										aria-label="글 삭제">
										<svg viewBox="0 0 24 24" aria-hidden="true">
											<path d="M3 6h18"></path>
											<path d="M8 6V4h8v2"></path>
											<path d="M19 6l-1 14H6L5 6"></path>
											<path d="M10 11v6"></path>
											<path d="M14 11v6"></path>
										</svg>
									</button>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<?php if ( ! empty( $injected_posts ) ) : ?>
						<?php foreach ( $injected_posts as $injected_post ) : ?>
							<?php
							$excerpt   = $injected_post->post_excerpt ? $injected_post->post_excerpt : wp_trim_words( wp_strip_all_tags( $injected_post->post_content ), 20, '' );
							$post_cats = get_the_category( $injected_post->ID );
							$can_edit  = is_user_logged_in() && current_user_can( 'edit_post', $injected_post->ID );
							?>
							<div class="pb-board__row<?php echo $show_blog_board_thumbs ? ' pb-board__row--with-thumb' : ''; ?>" data-post-id="<?php echo esc_attr( $injected_post->ID ); ?>">
								<div class="pb-board__date"><?php echo esc_html( get_the_date( 'Y. m. d', $injected_post ) ); ?></div>
								<?php if ( $show_blog_board_thumbs ) : ?>
									<?php
									$thumb_url = function_exists( 'project_b_get_post_preview_image_url' )
										? project_b_get_post_preview_image_url( $injected_post->ID, 'thumbnail' )
										: get_the_post_thumbnail_url( $injected_post->ID, 'thumbnail' );
									?>
									<div class="pb-board__thumb">
										<?php if ( $thumb_url ) : ?>
											<img src="<?php echo esc_url( $thumb_url ); ?>"
												alt="<?php echo esc_attr( get_the_title( $injected_post ) ); ?>"
												loading="lazy">
										<?php else : ?>
											<div class="pb-board__thumb-fallback"></div>
										<?php endif; ?>
									</div>
								<?php endif; ?>
								<div class="pb-board__main">
									<a class="pb-board__main-link" href="<?php echo esc_url( get_permalink( $injected_post ) ); ?>">
										<h2 class="pb-board__post-title"><?php echo esc_html( get_the_title( $injected_post ) ); ?></h2>
										<?php if ( $excerpt ) : ?>
											<div class="pb-board__excerpt"><?php echo esc_html( $excerpt ); ?></div>
										<?php endif; ?>
									</a>
								</div>
								<div class="pb-board__row-actions">
									<?php if ( $can_edit ) : ?>
										<button class="pb-board__action-btn pb-board__edit-btn"
											type="button"
											data-post-id="<?php echo esc_attr( $injected_post->ID ); ?>"
											data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
											aria-label="글 수정">
											<svg viewBox="0 0 24 24" aria-hidden="true">
												<path d="M12 20h9"></path>
												<path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
											</svg>
										</button>
										<button class="pb-board__action-btn pb-board__delete-btn"
											type="button"
											data-post-id="<?php echo esc_attr( $injected_post->ID ); ?>"
											data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
											aria-label="글 삭제">
											<svg viewBox="0 0 24 24" aria-hidden="true">
												<path d="M3 6h18"></path>
												<path d="M8 6V4h8v2"></path>
												<path d="M19 6l-1 14H6L5 6"></path>
												<path d="M10 11v6"></path>
												<path d="M14 11v6"></path>
											</svg>
										</button>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="pb-archive__empty">게시글이 없습니다.</div>
					<?php endif; ?>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( ! $is_done_list_view ) : ?>
			<nav class="pb-archive__pagination" aria-label="<?php esc_attr_e( 'Posts', 'Avada' ); ?>">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'total'     => $display_query->max_num_pages,
							'current'   => $paged,
							'prev_text' => '&lsaquo;',
							'next_text' => '&rsaquo;',
							'add_args'  => $pagination_args,
						)
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</main>

<?php if ( ! $is_gallery_view && ! $is_done_list_view ) : ?>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<div id="pb-editor-modal" class="pb-editor-modal" hidden>
	<div class="pb-editor-modal__backdrop"></div>
	<div class="pb-editor-modal__panel" role="dialog" aria-modal="true" aria-labelledby="pb-editor-title">
		<input id="pb-editor-title" class="pb-editor-modal__title" type="text" placeholder="제목">
		<div id="pb-editor-toolbar">
			<span class="ql-formats">
				<select class="ql-size">
					<option value="small">Small</option>
					<option selected>Normal</option>
					<option value="large">Large</option>
					<option value="huge">Huge</option>
				</select>
			</span>
			<span class="ql-formats">
				<button class="ql-bold" type="button"></button>
				<button class="ql-italic" type="button"></button>
				<button class="ql-underline" type="button"></button>
				<button class="ql-strike" type="button"></button>
			</span>
			<span class="ql-formats">
				<button class="ql-align" value="" type="button"></button>
				<button class="ql-align" value="center" type="button"></button>
				<button class="ql-align" value="right" type="button"></button>
			</span>
		</div>
		<div id="pb-editor-body"></div>
		<div id="pb-editor-status" class="pb-editor-modal__status" aria-live="polite"></div>
		<div class="pb-editor-modal__footer">
			<button id="pb-editor-cancel" type="button">취소</button>
			<button id="pb-editor-save" type="button">저장</button>
		</div>
	</div>
</div>
<script>
(function () {
	const rows = document.querySelectorAll('.pb-board__row');
	if (!rows.length) {
		return;
	}
	const modal = document.getElementById('pb-editor-modal');
	const modalBackdrop = modal ? modal.querySelector('.pb-editor-modal__backdrop') : null;
	const titleInput = document.getElementById('pb-editor-title');
	const saveButton = document.getElementById('pb-editor-save');
	const cancelButton = document.getElementById('pb-editor-cancel');
	const statusNode = document.getElementById('pb-editor-status');
	const boardTable = document.querySelector('.pb-board__table');
	const emptyNode = boardTable ? boardTable.querySelector('.pb-archive__empty') : null;
	let quill = null;
	let editorMode = 'edit';
	let activePostId = '';
	let activeNonce = '';
	let activeRow = null;
	let activeCategoryId = '';

	function setStatus(message) {
		if (statusNode) {
			statusNode.textContent = message || '';
		}
	}

	function stripHtml(html) {
		const temp = document.createElement('div');
		temp.innerHTML = html;
		return (temp.textContent || temp.innerText || '').replace(/\s+/g, ' ').trim();
	}

	function buildExcerpt(html) {
		const text = stripHtml(html);
		if (!text) {
			return '';
		}

		return text.length > 120 ? text.slice(0, 120).trim() + '...' : text;
	}

	function ensureQuill() {
		if (quill || !window.Quill) {
			if (quill) {
				return true;
			}

			return false;
		}

		quill = new window.Quill('#pb-editor-body', {
			theme: 'snow',
			modules: {
				toolbar: '#pb-editor-toolbar'
			}
		});

		return true;
	}

	function openModal() {
		if (!modal) {
			return;
		}

		modal.hidden = false;
		document.body.style.overflow = 'hidden';
	}

	function closeModal() {
		if (!modal) {
			return;
		}

		modal.hidden = true;
		document.body.style.overflow = '';
		editorMode = 'edit';
		activePostId = '';
		activeNonce = '';
		activeRow = null;
		activeCategoryId = '';
		setStatus('');

		if (titleInput) {
			titleInput.value = '';
		}

		if (quill) {
			quill.setContents([]);
		}

		if (saveButton) {
			saveButton.disabled = false;
			saveButton.textContent = '저장';
		}
	}

	function getCurrentDateLabel() {
		const now = new Date();
		const year = now.getFullYear();
		const month = String(now.getMonth() + 1).padStart(2, '0');
		const day = String(now.getDate()).padStart(2, '0');
		return year + '. ' + month + '. ' + day;
	}

	function createActionButtons(postId, nonce) {
		const actions = document.createElement('div');
		actions.className = 'pb-board__row-actions';
		actions.innerHTML =
			'<button class="pb-board__action-btn pb-board__edit-btn" type="button" data-post-id="' + String(postId).replace(/"/g, '&quot;') + '" data-nonce="' + String(nonce).replace(/"/g, '&quot;') + '" aria-label="글 수정">' +
				'<svg viewBox="0 0 24 24" aria-hidden="true">' +
					'<path d="M12 20h9"></path>' +
					'<path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>' +
				'</svg>' +
			'</button>' +
			'<button class="pb-board__action-btn pb-board__delete-btn" type="button" data-post-id="' + String(postId).replace(/"/g, '&quot;') + '" data-nonce="' + String(nonce).replace(/"/g, '&quot;') + '" aria-label="글 삭제">' +
				'<svg viewBox="0 0 24 24" aria-hidden="true">' +
					'<path d="M3 6h18"></path>' +
					'<path d="M8 6V4h8v2"></path>' +
					'<path d="M19 6l-1 14H6L5 6"></path>' +
					'<path d="M10 11v6"></path>' +
					'<path d="M14 11v6"></path>' +
				'</svg>' +
			'</button>';
		bindRowActions(actions);
		return actions;
	}

	function createBoardRow(postId, nonce, title, contentHtml, permalink) {
		const row = document.createElement('div');
		const excerpt = buildExcerpt(contentHtml);
		row.className = 'pb-board__row<?php echo $show_blog_board_thumbs ? ' pb-board__row--with-thumb' : ''; ?>';
		row.setAttribute('data-post-id', postId);
		row.innerHTML =
			'<div class="pb-board__date">' + getCurrentDateLabel() + '</div>' +
			<?php if ( $show_blog_board_thumbs ) : ?>
			'<div class="pb-board__thumb"><div class="pb-board__thumb-fallback"></div></div>' +
			<?php endif; ?>
			'<div class="pb-board__main">' +
				'<a class="pb-board__main-link" href="' + String(permalink || '#').replace(/"/g, '&quot;') + '">' +
					'<h2 class="pb-board__post-title"></h2>' +
				'</a>' +
			'</div>';

		const titleNode = row.querySelector('.pb-board__post-title');
		const linkNode = row.querySelector('.pb-board__main-link');

		if (titleNode) {
			titleNode.textContent = title;
		}

		if (excerpt && linkNode) {
			const excerptNode = document.createElement('div');
			excerptNode.className = 'pb-board__excerpt';
			excerptNode.textContent = excerpt;
			linkNode.appendChild(excerptNode);
		}

		row.appendChild(createActionButtons(postId, nonce));
		return row;
	}

	function prependBoardRow(row) {
		if (!boardTable || !row) {
			return;
		}

		const firstRow = boardTable.querySelector('.pb-board__row');
		if (emptyNode && emptyNode.parentNode === boardTable) {
			emptyNode.remove();
		}

		if (firstRow) {
			boardTable.insertBefore(row, firstRow);
		} else {
			boardTable.appendChild(row);
		}
	}

	async function openEditor(button) {
		const postId = button.getAttribute('data-post-id');
		const nonce = button.getAttribute('data-nonce');
		const row = button.closest('.pb-board__row');

		if (!postId || !nonce || !row) {
			return;
		}

		if (!ensureQuill()) {
			window.alert('에디터를 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.');
			return;
		}

		activePostId = postId;
		activeNonce = nonce;
		activeRow = row;
		editorMode = 'edit';
		openModal();
		setStatus('글을 불러오는 중...');

		if (titleInput) {
			titleInput.value = '';
		}

		quill.setContents([]);

		try {
			const response = await fetch('/wp-json/wp/v2/posts/' + encodeURIComponent(postId) + '?context=edit', {
				headers: {
					'X-WP-Nonce': nonce
				},
				credentials: 'same-origin'
			});

			if (!response.ok) {
				throw new Error('load_failed');
			}

			const data = await response.json();
			if (titleInput) {
				titleInput.value = data && data.title && typeof data.title.raw === 'string' ? data.title.raw : '';
			}

			quill.clipboard.dangerouslyPasteHTML(data && data.content && typeof data.content.raw === 'string' ? data.content.raw : '');
			setStatus('');
		} catch (error) {
			setStatus('');
			closeModal();
			window.alert('글을 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.');
		}
	}

	function openCreateEditor(button) {
		const nonce = button.getAttribute('data-nonce');
		const categoryId = button.getAttribute('data-category-id');

		if (!nonce || !categoryId) {
			return;
		}

		if (!ensureQuill()) {
			window.alert('에디터를 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.');
			return;
		}

		editorMode = 'create';
		activePostId = '';
		activeNonce = nonce;
		activeRow = null;
		activeCategoryId = categoryId;

		openModal();
		setStatus('');

		if (titleInput) {
			titleInput.value = '';
			titleInput.focus();
		}

		quill.setContents([]);
	}

	async function saveEditor() {
		if (!activeNonce || !quill) {
			return;
		}

		const title = titleInput ? titleInput.value.trim() : '';
		const content = quill.root.innerHTML;

		if (!title) {
			setStatus('제목을 입력해 주세요.');
			if (titleInput) {
				titleInput.focus();
			}
			return;
		}

		saveButton.disabled = true;
		saveButton.textContent = '저장 중';
		setStatus('저장하는 중...');

		try {
			const isCreateMode = editorMode === 'create';
			const endpoint = isCreateMode
				? '/wp-json/wp/v2/posts'
				: '/wp-json/wp/v2/posts/' + encodeURIComponent(activePostId);
			const payload = {
				title: title,
				content: content
			};

			if (isCreateMode) {
				payload.status = 'publish';
				payload.categories = activeCategoryId ? [Number(activeCategoryId)] : [];
			}

			const response = await fetch(endpoint, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': activeNonce
				},
				credentials: 'same-origin',
				body: JSON.stringify(payload)
			});

			if (!response.ok) {
				throw new Error('save_failed');
			}

			const data = await response.json();

			if (isCreateMode) {
				const newRow = createBoardRow(
					data && data.id ? data.id : '',
					activeNonce,
					title,
					content,
					data && data.link ? data.link : '#'
				);

				prependBoardRow(newRow);
				setStatus('');
				closeModal();
				return;
			}

			const titleNode = activeRow.querySelector('.pb-board__post-title');
			const excerptNode = activeRow.querySelector('.pb-board__excerpt');
			const nextExcerpt = buildExcerpt(content);

			if (titleNode) {
				titleNode.textContent = title;
			}

			if (excerptNode) {
				if (nextExcerpt) {
					excerptNode.textContent = nextExcerpt;
				} else {
					excerptNode.remove();
				}
			} else if (nextExcerpt) {
				const link = activeRow.querySelector('.pb-board__main-link');
				if (link) {
					const nextExcerptNode = document.createElement('div');
					nextExcerptNode.className = 'pb-board__excerpt';
					nextExcerptNode.textContent = nextExcerpt;
					link.appendChild(nextExcerptNode);
				}
			}

			setStatus('');
			closeModal();
		} catch (error) {
			saveButton.disabled = false;
			saveButton.textContent = '저장';
			setStatus('저장에 실패했습니다. 잠시 후 다시 시도해 주세요.');
		}
	}

	document.querySelectorAll('.pb-board__edit-btn').forEach(function (button) {
		button.addEventListener('click', function () {
			openEditor(button);
		});
	});

	document.querySelectorAll('.pb-board__create-btn').forEach(function (button) {
		button.addEventListener('click', function () {
			openCreateEditor(button);
		});
	});

	function bindRowActions(root) {
		if (!root) {
			return;
		}

		root.querySelectorAll('.pb-board__edit-btn').forEach(function (button) {
			if (button.dataset.pbBound === '1') {
				return;
			}

			button.dataset.pbBound = '1';
			button.addEventListener('click', function () {
				openEditor(button);
			});
		});

		root.querySelectorAll('.pb-board__delete-btn').forEach(function (button) {
			if (button.dataset.pbBound === '1') {
				return;
			}

			button.dataset.pbBound = '1';
			button.addEventListener('click', async function () {
				const postId = button.getAttribute('data-post-id');
				const nonce = button.getAttribute('data-nonce');
				const row = button.closest('.pb-board__row');

				if (!postId || !nonce || !row) {
					return;
				}

				if (!window.confirm('이 글을 삭제할까요?')) {
					return;
				}

				const originalText = button.textContent;
				button.disabled = true;

				try {
					const response = await fetch('/wp-json/wp/v2/posts/' + encodeURIComponent(postId) + '?force=true', {
						method: 'DELETE',
						headers: {
							'X-WP-Nonce': nonce
						},
						credentials: 'same-origin'
					});

					if (!response.ok) {
						throw new Error('delete_failed');
					}

					row.remove();
				} catch (error) {
					button.disabled = false;
					button.textContent = originalText;
					window.alert('삭제에 실패했습니다. 잠시 후 다시 시도해 주세요.');
				}
			});
		});
	}

	bindRowActions(document);

	if (modalBackdrop) {
		modalBackdrop.addEventListener('click', closeModal);
	}

	if (cancelButton) {
		cancelButton.addEventListener('click', closeModal);
	}

	if (saveButton) {
		saveButton.addEventListener('click', saveEditor);
	}

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && modal && !modal.hidden) {
			closeModal();
		}
	});
})();
</script>
<?php endif; ?>

<?php
wp_reset_postdata();
get_footer();
