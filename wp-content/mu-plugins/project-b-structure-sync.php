<?php
/**
 * Plugin Name: Project B Structure Sync
 * Description: Keeps critical menu structure in sync with the codebase.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function project_b_structure_sync_normalize_label( $label ) {
	$label = trim( wp_strip_all_tags( (string) $label ) );
	$label = strtolower( $label );
	return preg_replace( '/[\s\-_]+/u', '', $label );
}

function project_b_structure_sync_blog_items() {
	if ( function_exists( 'project_b_get_blog_menu_items' ) ) {
		return project_b_get_blog_menu_items();
	}

	return array(
		array(
			'label' => '전체',
			'type'  => 'category',
			'slug'  => 'blog',
			'url'   => function_exists( 'project_b_menu_url' ) ? project_b_menu_url( 'category', 'blog' ) : home_url( '/blog/' ),
		),
		array(
			'label' => '잡상노트',
			'type'  => 'category',
			'slug'  => 'blog-notes',
			'url'   => function_exists( 'project_b_menu_url' ) ? project_b_menu_url( 'category', 'blog-notes' ) : home_url( '/category/blog-notes/' ),
		),
		array(
			'label' => '일상',
			'type'  => 'category',
			'slug'  => 'blog-daily',
			'url'   => function_exists( 'project_b_menu_url' ) ? project_b_menu_url( 'category', 'blog-daily' ) : home_url( '/category/blog-daily/' ),
		),
		array(
			'label' => '캐나다 워홀',
			'type'  => 'category',
			'slug'  => 'canada-working-holiday',
			'url'   => function_exists( 'project_b_menu_url' ) ? project_b_menu_url( 'category', 'canada-working-holiday' ) : home_url( '/category/canada-working-holiday/' ),
		),
		array(
			'label' => 'Done List',
			'type'  => 'category',
			'slug'  => 'blog-done-list',
			'url'   => function_exists( 'project_b_menu_url' ) ? project_b_menu_url( 'category', 'blog-done-list' ) : home_url( '/category/blog-done-list/' ),
		),
	);
}

function project_b_structure_sync_reset_menu_item_visibility( $menu_item_id ) {
	delete_post_meta( $menu_item_id, 'menu-item-um_nav_public' );
	delete_post_meta( $menu_item_id, 'menu-item-um_nav_roles' );
}

function project_b_structure_sync_find_blog_parent( $menu_items ) {
	foreach ( $menu_items as $menu_item ) {
		if ( (int) $menu_item->menu_item_parent !== 0 ) {
			continue;
		}

		if ( 'blog' === project_b_structure_sync_normalize_label( $menu_item->title ) ) {
			return $menu_item;
		}
	}

	return null;
}

function project_b_structure_sync_menu_blog_children( $menu_id ) {
	$menu_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

	if ( empty( $menu_items ) ) {
		return false;
	}

	$blog_parent = project_b_structure_sync_find_blog_parent( $menu_items );

	if ( ! $blog_parent ) {
		return false;
	}

	$desired_items = project_b_structure_sync_blog_items();
	$existing      = array();

	foreach ( $menu_items as $menu_item ) {
		if ( (int) $menu_item->menu_item_parent !== (int) $blog_parent->ID ) {
			continue;
		}

		$existing[ project_b_structure_sync_normalize_label( $menu_item->title ) ] = $menu_item;
	}

	foreach ( $desired_items as $index => $desired_item ) {
		$normalized = project_b_structure_sync_normalize_label( $desired_item['label'] );
		$order      = ( (int) $blog_parent->menu_order ) + $index + 1;
		$item_data  = array(
			'menu-item-title'     => $desired_item['label'],
			'menu-item-url'       => $desired_item['url'],
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => (int) $blog_parent->ID,
			'menu-item-type'      => 'custom',
			'menu-item-position'  => $order,
		);

		if ( isset( $existing[ $normalized ] ) ) {
			$menu_item_id = wp_update_nav_menu_item( $menu_id, $existing[ $normalized ]->ID, $item_data );

			if ( ! is_wp_error( $menu_item_id ) ) {
				project_b_structure_sync_reset_menu_item_visibility( (int) $existing[ $normalized ]->ID );
			}

			continue;
		}

		$menu_item_id = wp_update_nav_menu_item( $menu_id, 0, $item_data );

		if ( ! is_wp_error( $menu_item_id ) ) {
			project_b_structure_sync_reset_menu_item_visibility( (int) $menu_item_id );
		}
	}

	return true;
}

function project_b_structure_sync_run() {
	if ( ! function_exists( 'wp_get_nav_menus' ) ) {
		return;
	}

	$menus = wp_get_nav_menus();

	foreach ( $menus as $menu ) {
		project_b_structure_sync_menu_blog_children( $menu->term_id );
	}
}

add_action( 'admin_init', 'project_b_structure_sync_run', 20 );
