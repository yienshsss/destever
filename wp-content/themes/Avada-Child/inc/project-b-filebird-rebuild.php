<?php

if ( ! function_exists( 'project_b_filebird_post_taxonomy' ) ) {
	function project_b_filebird_post_taxonomy() {
		if ( class_exists( '\FileBird\Addons\PostType\Models\Main' ) ) {
			return \FileBird\Addons\PostType\Models\Main::getTaxonomyName( 'post' );
		}

		return 'fbv_pt_tax_post';
	}
}

if ( ! function_exists( 'project_b_filebird_rebuild_token' ) ) {
	function project_b_filebird_rebuild_token() {
		return hash_hmac( 'sha256', 'project-b-filebird-rebuild', AUTH_SALT . NONCE_SALT );
	}
}

if ( ! function_exists( 'project_b_filebird_folder_aliases' ) ) {
	function project_b_filebird_folder_aliases() {
		return array(
			'BLOG'       => 'Blog',
			'REVIEW'     => 'Review',
			'TRAVEL'     => 'Travel',
			'PROS'       => 'Pros',
			'LOG'        => 'Log',
			'GAME'       => '게임',
			'OC'         => 'OC',
			'OC COUPLES' => '자캐 커플',
			'자캐커플'      => '자캐 커플',
		);
	}
}

if ( ! function_exists( 'project_b_normalize_folder_label' ) ) {
	function project_b_normalize_folder_label( $label ) {
		$label = trim( preg_replace( '/\s+/u', ' ', (string) $label ) );
		$alias = project_b_filebird_folder_aliases();

		return $alias[ $label ] ?? $label;
	}
}

if ( ! function_exists( 'project_b_filebird_path_key' ) ) {
	function project_b_filebird_path_key( $segments ) {
		$normalized = array_map( 'project_b_normalize_folder_label', array_values( array_filter( (array) $segments ) ) );

		return implode( ' > ', $normalized );
	}
}

if ( ! function_exists( 'project_b_filebird_expected_structure_map' ) ) {
	function project_b_filebird_expected_structure_map() {
		$paths = array(
			array( '블로그', 'blog', 'Blog' ),
			array( '블로그 > 잡상 노트', 'blog-notes', '잡상노트' ),
			array( '블로그 > 일상', 'blog-daily', '일상' ),
			array( '블로그 > 캐나다 워홀', 'canada-working-holiday', '캐나다 워홀' ),
			array( '블로그 > Done List', 'blog-done-list', 'Done List' ),
			array( '리뷰', 'review', 'Review' ),
			array( '리뷰 > 맛집', 'food', '맛집' ),
			array( '리뷰 > 음식', 'review-food-backup', '음식' ),
			array( '리뷰 > 영화/드라마', 'movie-drama', '영화 / 드라마' ),
			array( '리뷰 > 전시', 'exhibition', '전시' ),
			array( '리뷰 > 책', 'book', '책' ),
			array( '리뷰 > 책 > 웹소설', 'book-webnovel', '웹소설' ),
			array( '리뷰 > 책 > 웹툰', 'book-webtoon', '웹툰' ),
			array( '리뷰 > 책 > 일반 소설', 'book-fiction', '일반 소설' ),
			array( '리뷰 > 책 > 실용서', 'book-practical', '실용서' ),
			array( '리뷰 > 게임', 'game', '게임', true ),
			array( '리뷰 > 게임 > FF14', 'ff14', 'FF14' ),
			array( '리뷰 > 게임 > Sims', 'sims', 'Sims' ),
			array( '리뷰 > 게임 > 기타', 'game-etc', '기타' ),
			array( '리뷰 > IT', 'it', 'IT' ),
			array( '여행', 'travel', 'Travel' ),
			array( '여행 > 2025', 'travel-2025', '2025' ),
			array( '여행 > 2024', 'travel-2024', '2024' ),
			array( '여행 > 2023', 'travel-2023', '2023' ),
			array( '글', 'pros', 'Pros' ),
			array( '글 > 커미션 / 리퀘', 'commission-request', '커미션 / 리퀘' ),
			array( '글 > 개인작', 'personal-work', '개인작' ),
			array( '글 > 연재', 'serial', '연재', true ),
			array( '글 > 연재 > 붉은 바다', 'crimson-ocean', '붉은바다' ),
			array( '글 > 연재 > 미노스의 공장', 'minos-factory', '미노스의 공장' ),
			array( '글 > 연재 > 왕비실전', 'iamqueen', '왕비실전' ),
			array( '글 > 연재 > 샐러맨더', 'salamander', '샐러맨더' ),
			array( '글 > 연재 > 새아버지 최면 수업', 'stepfather-hypnosis', '새아버지 최면수업' ),
			array( '글 > 연재 > 기사단장 사용법: 마물 함락 편', 'knight-captain-monster-siege', '기사단장 사용법: 마물 함락 편' ),
			array( '글 > 연재 > 촉수형 난임 치료 연구소', 'tentacle-fertility-lab', '촉수형 난임 치료 연구소' ),
			array( '그림', 'log', 'Log' ),
			array( '그림 > 개인 연성', 'personal-creation-log', '개인 연성' ),
			array( '그림 > 그림 연습', 'art-study', '그림 연습' ),
			array( '그림 > 낙서', 'scribble', '낙서' ),
			array( '그림 > 자캐 픽크루 백업', 'oc-picrew-backup', '자캐 픽크루 백업' ),
			array( '그림 > 커미션', 'art-commission', '커미션' ),
			array( '자캐', 'oc', 'OC', true ),
			array( '자캐 > 자캐 커플', 'oc-couples', '자캐 커플', true ),
			array( '자캐 > 자캐 커플 > 언릴', 'unril', '언릴', true ),
			array( '자캐 > 자캐 커플 > 언릴 > 언릴 글', 'unril-text', '글' ),
			array( '자캐 > 자캐 커플 > 언릴 > 언릴 그림', 'unril-art', '그림' ),
			array( '자캐 > 자캐 커플 > 언릴 > 언릴 그림 > 그림', 'unril-commission', '커미션' ),
			array( '자캐 > 자캐 커플 > 언릴 > 언릴 그림 > 픽크루', 'unril-picrew', '픽크루' ),
			array( '자캐 > 자캐 커플 > 언릴 > 언릴 그림 > FF14', 'unril-ff14', 'FF14' ),
			array( '자캐 > 자캐 커플 > 언릴 > 언릴 썰', 'unril-chat', '썰' ),
			array( '자캐 > 자캐 커플 > 멜핀', 'melpin', '멜핀', true ),
			array( '자캐 > 자캐 커플 > 멜핀 > 멜핀 글', 'melpin-writing', '글' ),
			array( '자캐 > 자캐 커플 > 멜핀 > 멜핀 그림', 'melpin-art', '그림' ),
			array( '자캐 > 자캐 커플 > 멜핀 > 멜핀 그림 > 그림', 'melpin-commission', '커미션' ),
			array( '자캐 > 자캐 커플 > 멜핀 > 멜핀 그림 > 픽크루', 'melpin-picrew', '픽크루' ),
			array( '자캐 > 자캐 커플 > 멜핀 > 멜핀 썰', 'melpin-chat', '썰' ),
			array( '자캐 > 자캐 커플 > 켈든', 'kelden', '켈든', true ),
			array( '자캐 > 자캐 커플 > 켈든 > 켈든 글', 'kelden-text', '글' ),
			array( '자캐 > 자캐 커플 > 켈든 > 켈든 그림', 'kelden-art', '그림' ),
			array( '자캐 > 자캐 커플 > 켈든 > 켈든 썰', 'kelden-chat', '썰' ),
			array( '자캐 > 커뮤 로그 백업', 'commu-log-backup', '커뮤 로그 백업' ),
			array( '자캐 > 커뮤 로그 백업 > 더 테이커: 솔트', 'commu-the-taker-salt', '더 테이커: 솔트' ),
			array( '자캐 > 커뮤 로그 백업 > 밴드커: 메르마스', 'commu-bandc-mermas', '밴드커: 메르마스' ),
			array( '자캐 > 그 외', 'oc-etc', '그 외' ),
		);

		$map = array();

		foreach ( $paths as $row ) {
			$map[ $row[0] ] = array(
				'slug'         => $row[1],
				'name'         => $row[2] ?? '',
				'landing_page' => ! empty( $row[3] ),
			);
		}

		return $map;
	}
}

if ( ! function_exists( 'project_b_generate_category_slug' ) ) {
	function project_b_generate_category_slug( $path_labels, $parent_slug = '' ) {
		$label = project_b_normalize_folder_label( end( $path_labels ) );
		$slug  = sanitize_title( $label );

		if ( '' !== $slug ) {
			return $parent_slug ? $parent_slug . '-' . $slug : $slug;
		}

		$hash = substr( md5( project_b_filebird_path_key( $path_labels ) ), 0, 8 );

		return $parent_slug ? $parent_slug . '-' . $hash : 'folder-' . $hash;
	}
}

if ( ! function_exists( 'project_b_build_filebird_folder_tree' ) ) {
	function project_b_build_filebird_folder_tree() {
		global $wpdb;

		$taxonomy = project_b_filebird_post_taxonomy();
		$rows     = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT terms.term_id, terms.name, tax.parent, COALESCE(ord_meta.meta_value, '0') AS ord
				FROM {$wpdb->terms} AS terms
				INNER JOIN {$wpdb->term_taxonomy} AS tax
					ON tax.term_id = terms.term_id
				LEFT JOIN {$wpdb->termmeta} AS ord_meta
					ON ord_meta.term_id = terms.term_id
					AND ord_meta.meta_key = 'fbv_tax_order'
				WHERE tax.taxonomy = %s
				ORDER BY tax.parent ASC, CAST(COALESCE(ord_meta.meta_value, '0') AS UNSIGNED) ASC, terms.name ASC",
				$taxonomy
			),
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return array(
				'taxonomy' => $taxonomy,
				'nodes'    => array(),
			);
		}

		$terms_by_parent = array();

		foreach ( $rows as $row ) {
			$parent = (int) $row['parent'];

			if ( ! isset( $terms_by_parent[ $parent ] ) ) {
				$terms_by_parent[ $parent ] = array();
			}

			$terms_by_parent[ $parent ][] = (object) array(
				'term_id' => (int) $row['term_id'],
				'name'    => $row['name'],
				'parent'  => $parent,
				'ord'     => (int) $row['ord'],
			);
		}

		$expected_map = project_b_filebird_expected_structure_map();

		$build_nodes = function ( $parent_id, $ancestors ) use ( &$build_nodes, $terms_by_parent, $expected_map ) {
			$nodes = array();

			foreach ( $terms_by_parent[ $parent_id ] ?? array() as $term ) {
				$display_name = project_b_normalize_folder_label( $term->name );
				$path_labels  = array_merge( $ancestors, array( $display_name ) );
				$path_key     = project_b_filebird_path_key( $path_labels );
				$expected     = $expected_map[ $path_key ] ?? null;
				$parent_slug  = isset( $expected_map[ project_b_filebird_path_key( $ancestors ) ]['slug'] ) ? $expected_map[ project_b_filebird_path_key( $ancestors ) ]['slug'] : '';

				$nodes[] = array(
					'filebird_term_id' => (int) $term->term_id,
					'name'             => ! empty( $expected['name'] ) ? $expected['name'] : $display_name,
					'path_labels'      => $path_labels,
					'path_key'         => $path_key,
					'slug'             => $expected['slug'] ?? project_b_generate_category_slug( $path_labels, $parent_slug ),
					'landing_page'     => ! empty( $expected['landing_page'] ),
					'children'         => $build_nodes( (int) $term->term_id, $path_labels ),
				);
			}

			return $nodes;
		};

		return array(
			'taxonomy' => $taxonomy,
			'nodes'    => $build_nodes( 0, array() ),
		);
	}
}

if ( ! function_exists( 'project_b_sync_category_node_from_filebird' ) ) {
	function project_b_sync_category_node_from_filebird( $node, $parent_id, &$report, $dry_run = true ) {
		$slug      = $node['slug'];
		$name      = $node['name'];
		$term_id   = 0;
		$created   = false;
		$term      = get_category_by_slug( $slug );

		if ( $term instanceof WP_Term ) {
			$term_id = (int) $term->term_id;

			if ( ! $dry_run ) {
				wp_update_term(
					$term_id,
					'category',
					array(
						'name'   => $name,
						'parent' => (int) $parent_id,
						'slug'   => $slug,
					)
				);
			}
		} elseif ( ! $dry_run ) {
			$created_term = wp_insert_term(
				$name,
				'category',
				array(
					'slug'   => $slug,
					'parent' => (int) $parent_id,
				)
			);

			if ( ! is_wp_error( $created_term ) ) {
				$term_id = (int) $created_term['term_id'];
				$created = true;
			}
		}

		$report['categories'][] = array(
			'action'           => $created ? 'created' : ( $term_id ? 'updated' : 'planned' ),
			'filebird_term_id' => $node['filebird_term_id'],
			'name'             => $name,
			'slug'             => $slug,
			'parent_id'        => (int) $parent_id,
			'category_id'      => (int) $term_id,
			'path'             => $node['path_key'],
		);

		if ( $term_id && ! $dry_run ) {
			update_term_meta( $term_id, 'project_b_filebird_source_term_id', (int) $node['filebird_term_id'] );
			update_term_meta( $term_id, 'project_b_filebird_source_path', $node['path_key'] );

			if ( $node['landing_page'] ) {
				update_term_meta( $term_id, 'landing_page_recommended', '1' );
			}
		}

		$node['category_id'] = $term_id;

		foreach ( $node['children'] as $child ) {
			project_b_sync_category_node_from_filebird( $child, $term_id, $report, $dry_run );
		}
	}
}

if ( ! function_exists( 'project_b_collect_category_id_map_from_report' ) ) {
	function project_b_collect_category_id_map_from_report( $report ) {
		$map = array();

		foreach ( $report['categories'] ?? array() as $row ) {
			if ( ! empty( $row['filebird_term_id'] ) && ! empty( $row['category_id'] ) ) {
				$map[ (int) $row['filebird_term_id'] ] = (int) $row['category_id'];
			}
		}

		return $map;
	}
}

if ( ! function_exists( 'project_b_assign_posts_from_filebird_categories' ) ) {
	function project_b_assign_posts_from_filebird_categories( $filebird_taxonomy, $category_map, &$report, $dry_run = true ) {
		global $wpdb;

		$post_ids = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$default_category = (int) get_option( 'default_category' );

		foreach ( $post_ids as $post_id ) {
			$folder_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT tax.term_id
					FROM {$wpdb->term_relationships} AS rel
					INNER JOIN {$wpdb->term_taxonomy} AS tax
						ON tax.term_taxonomy_id = rel.term_taxonomy_id
					WHERE rel.object_id = %d
						AND tax.taxonomy = %s",
					$post_id,
					$filebird_taxonomy
				)
			);

			if ( empty( $folder_ids ) ) {
				$report['posts']['skipped'][] = array(
					'post_id' => (int) $post_id,
					'title'   => get_the_title( $post_id ),
					'reason'  => 'no_filebird_folder',
				);
				continue;
			}

			$category_ids = array_values(
				array_unique(
					array_filter(
						array_map(
							function ( $folder_id ) use ( $category_map ) {
								return $category_map[ (int) $folder_id ] ?? 0;
							},
							$folder_ids
						)
					)
				)
			);

			$assignment_source = 'filebird';

			if ( empty( $category_ids ) ) {
				$category_ids = project_b_resolve_xe_fallback_category_ids( $post_id );
				$assignment_source = 'xe_fallback';
			}

			if ( empty( $category_ids ) ) {
				$report['posts']['skipped'][] = array(
					'post_id' => (int) $post_id,
					'title'   => get_the_title( $post_id ),
					'reason'  => empty( $folder_ids ) ? 'no_filebird_folder' : 'folder_not_mapped',
				);
				continue;
			}

			if ( ! $dry_run ) {
				wp_set_post_categories( $post_id, $category_ids, false );

				if ( $default_category && in_array( $default_category, $category_ids, true ) && count( $category_ids ) > 1 ) {
					wp_remove_object_terms( $post_id, array( $default_category ), 'category' );
				}
			}

			$report['posts']['assigned'][] = array(
				'post_id'       => (int) $post_id,
				'title'         => get_the_title( $post_id ),
				'source'        => $assignment_source,
				'filebird_ids'  => array_map( 'intval', $folder_ids ),
				'category_ids'  => array_map( 'intval', $category_ids ),
			);
		}
	}
}

if ( ! function_exists( 'project_b_get_xe_reclassification_context' ) ) {
	function project_b_get_xe_reclassification_context() {
		static $context = null;

		if ( null !== $context ) {
			return $context;
		}

		$context = array(
			'module_mid_by_srl' => array(),
			'categories'        => array(),
		);

		$sql_file = trailingslashit( ABSPATH ) . 'antiB.sql';
		$lib_file = trailingslashit( ABSPATH ) . 'xe_migration_lib.php';

		if ( ! file_exists( $sql_file ) || ! file_exists( $lib_file ) ) {
			return $context;
		}

		require_once $lib_file;

		$modules    = xe_parse_modules( $sql_file );
		$categories = xe_parse_categories( $sql_file );

		foreach ( $modules as $module_srl => $module ) {
			$context['module_mid_by_srl'][ (int) $module_srl ] = (string) $module['mid'];
		}

		$context['categories'] = $categories;

		return $context;
	}
}

if ( ! function_exists( 'project_b_resolve_xe_fallback_category_ids' ) ) {
	function project_b_resolve_xe_fallback_category_ids( $post_id ) {
		$context      = project_b_get_xe_reclassification_context();
		$module_srl   = (int) get_post_meta( $post_id, 'xe_module_srl', true );
		$category_srl = (int) get_post_meta( $post_id, 'xe_category_srl', true );
		$mid          = $context['module_mid_by_srl'][ $module_srl ] ?? '';
		$post         = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return array();
		}

		$slugs = project_b_resolve_xe_target_slugs( $mid, $category_srl, $context['categories'], $post );

		if ( empty( $slugs ) ) {
			return array();
		}

		$term_ids = array();

		foreach ( $slugs as $slug ) {
			$term = get_category_by_slug( $slug );

			if ( $term instanceof WP_Term ) {
				$term_ids[] = (int) $term->term_id;
			}
		}

		return array_values( array_unique( array_filter( $term_ids ) ) );
	}
}

if ( ! function_exists( 'project_b_resolve_xe_target_slugs' ) ) {
	function project_b_resolve_xe_target_slugs( $mid, $category_srl, $categories, $post ) {
		$mid    = trim( (string) $mid );
		$direct = array(
			'food'         => array( 'food' ),
			'movie'        => array( 'movie-drama' ),
			'book'         => array( 'book' ),
			'secretbook'   => array( 'book' ),
			'ff14'         => array( 'ff14' ),
			'sims'         => array( 'sims' ),
			'game_etc'     => array( 'game-etc' ),
			'commission'   => array( 'commission-request' ),
			'Ectwritings'  => array( 'personal-work' ),
			'shortprose'   => array( 'personal-work' ),
			'ocwriting'    => array( 'personal-work' ),
			'Crimsonocean' => array( 'crimson-ocean' ),
			'minos_factory'=> array( 'minos-factory' ),
			'Iamqueen'     => array( 'iamqueen' ),
			'salamander'   => array( 'salamander' ),
			'stepfa'       => array( 'stepfather-hypnosis' ),
			'drawingself'  => array( 'personal-creation-log' ),
			'study'        => array( 'art-study' ),
			'scribe'       => array( 'scribble' ),
			'OCpicrew'     => array( 'oc-picrew-backup' ),
			'commu'        => array( 'commu-log-backup' ),
			'YRS'          => array( 'unril-chat' ),
			'YRW'          => array( 'unril-text' ),
			'YRD'          => array( 'unril-art' ),
			'YRcom'        => array( 'unril-commission' ),
			'YRff14'       => array( 'unril-ff14' ),
			'YRpicrew'     => array( 'unril-picrew' ),
			'ppwrite'      => array( 'melpin-writing' ),
			'pppic'        => array( 'melpin-art' ),
			'pppicrew'     => array( 'melpin-picrew' ),
			'OCC_ECT_CHAT' => array( 'oc-etc' ),
			'OCC_ECT_TEXT' => array( 'oc-etc' ),
			'OCC_ECT_PIC'  => array( 'oc-etc' ),
			'occ_ect'      => array( 'oc-etc' ),
			'occw'         => array( 'oc-etc' ),
		);

		if ( isset( $direct[ $mid ] ) ) {
			return $direct[ $mid ];
		}

		if ( 'travel' === $mid ) {
			$year = substr( (string) $post->post_date, 0, 4 );

			if ( in_array( $year, array( '2025', '2024', '2023' ), true ) ) {
				return array( 'travel-' . $year );
			}

			return array( 'travel' );
		}

		if ( in_array( $mid, array( 'blog', 'done', 'personalw' ), true ) ) {
			$title_and_body = function_exists( 'mb_strtolower' )
				? mb_strtolower( wp_strip_all_tags( $post->post_title . ' ' . $post->post_content ) )
				: strtolower( wp_strip_all_tags( $post->post_title . ' ' . $post->post_content ) );

			if ( false !== strpos( $title_and_body, '캐나다' ) || false !== strpos( $title_and_body, 'canada' ) || false !== strpos( $title_and_body, 'working holiday' ) ) {
				return array( 'canada-working-holiday' );
			}

			if ( 'done' === $mid ) {
				return array( 'blog-done-list' );
			}

			return array( 'blog-notes' );
		}

		if ( $category_srl > 0 && isset( $categories[ $category_srl ] ) ) {
			$category_title = trim( (string) $categories[ $category_srl ]['title'] );
			$category_map   = array(
				'라이언' => array( 'oc-etc' ),
				'베릴'  => array( 'oc-etc' ),
				'언릴'  => array( 'unril' ),
				'네핀'  => array( 'melpin' ),
				'샴펠'  => array( 'melpin' ),
				'멜핀'  => array( 'melpin' ),
				'켈든'  => array( 'kelden' ),
				'짤방'  => array( 'oc-etc' ),
			);

			if ( isset( $category_map[ $category_title ] ) ) {
				return $category_map[ $category_title ];
			}
		}

		return array();
	}
}

if ( ! function_exists( 'project_b_cleanup_obsolete_categories' ) ) {
	function project_b_cleanup_obsolete_categories( $keep_ids, &$report, $dry_run = true ) {
		$default_category = (int) get_option( 'default_category' );
		$keep_ids         = array_values( array_unique( array_filter( array_map( 'intval', (array) $keep_ids ) ) ) );

		if ( $default_category ) {
			$keep_ids[] = $default_category;
		}

		$categories = get_categories(
			array(
				'hide_empty' => false,
			)
		);

		foreach ( $categories as $category ) {
			if ( in_array( (int) $category->term_id, $keep_ids, true ) ) {
				continue;
			}

			$report['deleted_categories'][] = array(
				'term_id' => (int) $category->term_id,
				'name'    => $category->name,
				'slug'    => $category->slug,
			);

			if ( ! $dry_run ) {
				wp_delete_category( (int) $category->term_id );
			}
		}
	}
}

if ( ! function_exists( 'project_b_ensure_landing_pages_from_config' ) ) {
	function project_b_ensure_landing_pages_from_config( &$report, $dry_run = true ) {
		$config = array(
			'game'       => array( 'title' => 'GAME' ),
			'serial'     => array( 'title' => 'SERIAL' ),
			'oc'         => array( 'title' => 'OC' ),
			'oc-couples' => array( 'title' => 'OC COUPLES' ),
			'unril'      => array( 'title' => '언릴' ),
			'melpin'     => array( 'title' => '멜핀' ),
			'kelden'     => array( 'title' => '켈든' ),
		);

		foreach ( $config as $slug => $entry ) {
			$page = get_page_by_path( $slug );
			$data = array(
				'post_title'  => $entry['title'],
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
			);

			if ( $page instanceof WP_Post ) {
				$report['pages'][] = array(
					'action' => 'updated',
					'slug'   => $slug,
					'title'  => $entry['title'],
					'page_id'=> (int) $page->ID,
				);

				if ( ! $dry_run ) {
					$data['ID'] = (int) $page->ID;
					wp_update_post( $data );
				}

				continue;
			}

			$report['pages'][] = array(
				'action' => 'created',
				'slug'   => $slug,
				'title'  => $entry['title'],
				'page_id'=> 0,
			);

			if ( ! $dry_run ) {
				$page_id = wp_insert_post( $data );
				$report['pages'][ count( $report['pages'] ) - 1 ]['page_id'] = (int) $page_id;
			}
		}
	}
}

if ( ! function_exists( 'project_b_run_filebird_category_rebuild' ) ) {
	function project_b_run_filebird_category_rebuild( $dry_run = true ) {
		$tree = project_b_build_filebird_folder_tree();

		$report = array(
			'dry_run'    => (bool) $dry_run,
			'taxonomy'   => $tree['taxonomy'],
			'diagnostics'=> array(
				'fbv_enabled_posttype' => get_option( 'fbv_enabled_posttype', '' ),
				'post_taxonomy_exists' => taxonomy_exists( 'fbv_pt_tax_post' ),
				'page_taxonomy_exists' => taxonomy_exists( 'fbv_pt_tax_page' ),
				'post_taxonomy_terms'  => taxonomy_exists( 'fbv_pt_tax_post' ) ? wp_count_terms( array( 'taxonomy' => 'fbv_pt_tax_post', 'hide_empty' => false ) ) : null,
				'page_taxonomy_terms'  => taxonomy_exists( 'fbv_pt_tax_page' ) ? wp_count_terms( array( 'taxonomy' => 'fbv_pt_tax_page', 'hide_empty' => false ) ) : null,
			),
			'roots'      => array_map(
				function ( $node ) {
					return array(
						'filebird_term_id' => $node['filebird_term_id'],
						'name'             => $node['name'],
						'slug'             => $node['slug'],
						'path'             => $node['path_key'],
					);
				},
				$tree['nodes']
			),
			'categories'         => array(),
			'pages'              => array(),
			'deleted_categories' => array(),
			'posts'              => array(
				'assigned' => array(),
				'skipped'  => array(),
			),
		);

		if ( empty( $tree['nodes'] ) ) {
			global $wpdb;

			$report['diagnostics']['fbv_table_folders'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}fbv" );
			$report['diagnostics']['fbv_table_examples'] = $wpdb->get_results( "SELECT id, name, parent FROM {$wpdb->prefix}fbv ORDER BY parent ASC, ord ASC, id ASC LIMIT 20", ARRAY_A );
			$report['diagnostics']['raw_post_taxonomy_terms'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", 'fbv_pt_tax_post' ) );
			$report['diagnostics']['raw_post_taxonomy_examples'] = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT terms.term_id, terms.name, tax.parent
					FROM {$wpdb->terms} AS terms
					INNER JOIN {$wpdb->term_taxonomy} AS tax
						ON tax.term_id = terms.term_id
					WHERE tax.taxonomy = %s
					ORDER BY tax.parent ASC, terms.name ASC
					LIMIT 20",
					'fbv_pt_tax_post'
				),
				ARRAY_A
			);
			$report['error'] = 'filebird_post_folders_not_found';
			return $report;
		}

		foreach ( $tree['nodes'] as $node ) {
			project_b_sync_category_node_from_filebird( $node, 0, $report, $dry_run );
		}

		$category_map = project_b_collect_category_id_map_from_report( $report );

		project_b_assign_posts_from_filebird_categories( $tree['taxonomy'], $category_map, $report, $dry_run );
		project_b_ensure_landing_pages_from_config( $report, $dry_run );
		project_b_cleanup_obsolete_categories( array_values( $category_map ), $report, $dry_run );

		if ( ! $dry_run ) {
			flush_rewrite_rules( false );
		}

		return $report;
	}
}

if ( ! function_exists( 'project_b_handle_filebird_rebuild_request' ) ) {
	function project_b_handle_filebird_rebuild_request() {
		if ( empty( $_GET['project_b_tool'] ) || 'filebird-rebuild' !== sanitize_key( wp_unslash( $_GET['project_b_tool'] ) ) ) {
			return;
		}

		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

		if ( ! hash_equals( project_b_filebird_rebuild_token(), $token ) ) {
			status_header( 403 );
			header( 'Content-Type: application/json; charset=utf-8' );
			echo wp_json_encode(
				array(
					'error' => 'forbidden',
				),
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			);
			exit;
		}

		$mode = isset( $_GET['mode'] ) ? sanitize_key( wp_unslash( $_GET['mode'] ) ) : 'report';

		if ( 'postdebug' === $mode ) {
			$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
			$result  = array(
				'post_id'         => $post_id,
				'title'           => get_the_title( $post_id ),
				'xe_document_srl' => get_post_meta( $post_id, 'xe_document_srl', true ),
				'xe_module_srl'   => get_post_meta( $post_id, 'xe_module_srl', true ),
				'xe_category_srl' => get_post_meta( $post_id, 'xe_category_srl', true ),
				'current_terms'   => wp_get_post_categories( $post_id ),
				'filebird_terms'  => array(),
				'fallback_terms'  => project_b_resolve_xe_fallback_category_ids( $post_id ),
			);

			global $wpdb;
			$result['filebird_terms'] = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT tax.term_id
					FROM {$wpdb->term_relationships} AS rel
					INNER JOIN {$wpdb->term_taxonomy} AS tax
						ON tax.term_taxonomy_id = rel.term_taxonomy_id
					WHERE rel.object_id = %d
						AND tax.taxonomy = %s",
					$post_id,
					project_b_filebird_post_taxonomy()
				)
			);
		} else {
			$dry_run = 'run' !== $mode;
			$result  = project_b_run_filebird_category_rebuild( $dry_run );
		}

		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode(
			$result,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);
		exit;
	}
}
add_action( 'init', 'project_b_handle_filebird_rebuild_request', 1 );
