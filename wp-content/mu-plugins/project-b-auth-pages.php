<?php
/**
 * Plugin Name: Project B Auth Pages
 * Description: Recreates and routes the custom login, register, and password reset pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PROJECT_B_AUTH_PAGES_VERSION = '2026-04-28.1';

function project_b_auth_page_specs() {
	$login_url          = home_url( '/login/' );
	$register_url       = home_url( '/register/' );
	$password_reset_url = home_url( '/password-reset/' );

	return array(
		'login'          => array(
			'title'   => '로그인',
			'content' => '[fusion_builder_container type="flex" hundred_percent="no" hundred_percent_height="no" min_height="100vh" flex_align_items="center" flex_justify_content="center" class="project-b-auth project-b-auth--login"][fusion_builder_row][fusion_builder_column type="1_1" class="project-b-auth__card"][fusion_title title_type="text" content_align="center" size="1"]로그인[/fusion_title][fusion_login show_labels="no" show_placeholders="yes" show_remember_me="yes" button_fullwidth="yes" register_link="' . esc_url( $register_url ) . '" lost_password_link="' . esc_url( $password_reset_url ) . '" redirection_link="' . esc_url( home_url( '/' ) ) . '"][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]',
		),
		'register'       => array(
			'title'   => '회원가입',
			'content' => '[fusion_builder_container type="flex" hundred_percent="no" hundred_percent_height="no" min_height="100vh" flex_align_items="center" flex_justify_content="center" class="project-b-auth project-b-auth--register"][fusion_builder_row][fusion_builder_column type="1_1" class="project-b-auth__card"][fusion_title title_type="text" content_align="center" size="1"]회원가입[/fusion_title][fusion_register show_labels="no" show_placeholders="yes" button_fullwidth="yes" register_note="가입 확인 메일이 발송됩니다." redirection_link="' . esc_url( $login_url ) . '"][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]',
		),
		'password-reset' => array(
			'title'   => '비밀번호 찾기',
			'content' => '[fusion_builder_container type="flex" hundred_percent="no" hundred_percent_height="no" min_height="100vh" flex_align_items="center" flex_justify_content="center" class="project-b-auth project-b-auth--password-reset"][fusion_builder_row][fusion_builder_column type="1_1" class="project-b-auth__card"][fusion_title title_type="text" content_align="center" size="1"]비밀번호 찾기[/fusion_title][fusion_lost_password show_labels="no" show_placeholders="yes" button_fullwidth="yes" redirection_link="' . esc_url( $login_url ) . '"][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]',
		),
	);
}

function project_b_auth_find_page_by_path_in_any_status( $slug ) {
	$pages = get_posts(
		array(
			'name'           => $slug,
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	return ! empty( $pages ) ? (int) $pages[0] : 0;
}

function project_b_auth_ensure_pages() {
	foreach ( project_b_auth_page_specs() as $slug => $spec ) {
		$page_id        = project_b_auth_find_page_by_path_in_any_status( $slug );
		$stored_version = $page_id > 0 ? (string) get_post_meta( $page_id, '_project_b_auth_page_version', true ) : '';

		$page_data = array(
			'post_title'   => $spec['title'],
			'post_name'    => $slug,
			'post_content' => $spec['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);

		if ( $page_id > 0 ) {
			$page_data['ID'] = $page_id;

			if ( PROJECT_B_AUTH_PAGES_VERSION !== $stored_version ) {
				wp_update_post( wp_slash( $page_data ) );
			}
		} else {
			$page_id = wp_insert_post( wp_slash( $page_data ) );
		}

		if ( ! is_wp_error( $page_id ) && $page_id > 0 ) {
			update_post_meta( $page_id, '_project_b_auth_page', $slug );
			update_post_meta( $page_id, '_project_b_auth_page_version', PROJECT_B_AUTH_PAGES_VERSION );
		}
	}
}
add_action( 'admin_init', 'project_b_auth_ensure_pages', 30 );

function project_b_auth_page_url( $slug ) {
	$page = get_page_by_path( $slug );

	return $page ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

function project_b_auth_login_url( $login_url, $redirect, $force_reauth ) {
	$url = project_b_auth_page_url( 'login' );

	if ( $redirect ) {
		$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
	}

	if ( $force_reauth ) {
		$url = add_query_arg( 'reauth', '1', $url );
	}

	return $url;
}
add_filter( 'login_url', 'project_b_auth_login_url', 10, 3 );

function project_b_auth_register_url( $register_url ) {
	return project_b_auth_page_url( 'register' );
}
add_filter( 'register_url', 'project_b_auth_register_url' );

function project_b_auth_lostpassword_url( $lostpassword_url, $redirect ) {
	$url = project_b_auth_page_url( 'password-reset' );

	if ( $redirect ) {
		$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
	}

	return $url;
}
add_filter( 'lostpassword_url', 'project_b_auth_lostpassword_url', 10, 2 );

function project_b_auth_page_styles() {
	if ( ! is_page( array( 'login', 'register', 'password-reset' ) ) ) {
		return;
	}

	wp_register_style( 'project-b-auth-pages', false, array(), '1.0.0' );
	wp_enqueue_style( 'project-b-auth-pages' );
	wp_add_inline_style(
		'project-b-auth-pages',
		'body.page .project-b-auth{background:#f3f2eb;color:#050505}.project-b-auth__card{max-width:440px;margin:0 auto!important;padding:48px 36px!important}.project-b-auth .fusion-title h1,.project-b-auth .fusion-title h2,.project-b-auth .fusion-title h3{color:#050505!important;font-size:42px!important;font-weight:900!important;letter-spacing:-.08em!important}.project-b-auth .fusion-login-box{background:transparent!important;border:0!important;box-shadow:none!important}.project-b-auth input[type=text],.project-b-auth input[type=email],.project-b-auth input[type=password]{height:52px!important;border:3px solid #050505!important;border-radius:0!important;background:#f3f2eb!important;color:#050505!important;font-size:17px!important;font-weight:800!important}.project-b-auth input::placeholder{color:#767676!important;opacity:1}.project-b-auth button,.project-b-auth input[type=submit]{width:100%!important;border:3px solid #050505!important;border-radius:999px!important;background:#050505!important;color:#f3f2eb!important;font-size:16px!important;font-weight:900!important;letter-spacing:-.04em!important}.project-b-auth a{color:#050505!important;font-weight:900!important;text-decoration:none!important}.project-b-auth a:hover{color:#e78645!important}.project-b-auth .fusion-login-additional-content,.project-b-auth .fusion-login-links{font-size:14px!important;text-align:center!important}'
	);
}
add_action( 'wp_enqueue_scripts', 'project_b_auth_page_styles', 30 );
