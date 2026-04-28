<?php
/**
 * Template Name: Project B Home
 */
get_header();

$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$query_args = array(
    'post_type' => 'post',
    'posts_per_page' => 20,
    'ignore_sticky_posts' => 1,
    'paged' => $paged,
);

$done_list_term = get_category_by_slug('blog-done-list');
if (!$done_list_term) {
    $done_list_term = get_term_by('name', 'Done List', 'category');
}
if ($done_list_term && !is_wp_error($done_list_term)) {
    $query_args['category__not_in'] = array((int) $done_list_term->term_id);
}

if (function_exists('project_b_can_view_member_private_content') && function_exists('project_b_get_member_private_category_ids') && !project_b_can_view_member_private_content()) {
    $private_ids = project_b_get_member_private_category_ids();
    if (!empty($private_ids)) {
        $existing_excluded = isset($query_args['category__not_in']) ? (array) $query_args['category__not_in'] : array();
        $query_args['category__not_in'] = array_values(array_unique(array_merge($existing_excluded, array_map('intval', $private_ids))));
    }
}

if (function_exists('project_b_can_view_melpin') && function_exists('project_b_get_melpin_category_ids') && !project_b_can_view_melpin()) {
    $melpin_ids = project_b_get_melpin_category_ids();
    if (!empty($melpin_ids)) {
        $existing_excluded = isset($query_args['category__not_in']) ? (array) $query_args['category__not_in'] : array();
        $query_args['category__not_in'] = array_values(array_unique(array_merge($existing_excluded, array_map('intval', $melpin_ids))));
    }
}

$query = new WP_Query($query_args);

if (!function_exists('project_b_home_card_data')) {
    function project_b_home_card_data($post_id)
    {
        $categories = get_the_category($post_id);

        return array(
            'title' => get_the_title($post_id),
            'url' => get_permalink($post_id),
            'image' => function_exists('project_b_get_post_preview_image_url') ? project_b_get_post_preview_image_url($post_id, 'full') : get_the_post_thumbnail_url($post_id, 'full'),
            'date' => get_the_date('Y. m. d', $post_id),
            'subline' => has_excerpt($post_id)
                ? get_the_excerpt($post_id)
                : '',
            'category' => !empty($categories) ? $categories[0]->name : 'Uncategorized',
        );
    }
}
?>

<style>
    .pb-home {
        background: #f3efe4;
        color: #111;
    }

    .pb-home *,
    .pb-home *::before,
    .pb-home *::after {
        box-sizing: border-box;
    }

    .pb-home a {
        color: inherit;
        text-decoration: none;
    }

    .pb-home-stream {
        width: 100%;
    }

    .pb-home-band {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(420px, 1fr);
        align-items: start;
        background: #111;
    }

    .pb-home-band--reverse {
        grid-template-columns: minmax(420px, 1fr) minmax(0, 1fr);
    }

    .pb-home-band--reverse .pb-home-feature {
        order: 2;
    }

    .pb-home-band--reverse .pb-home-grid {
        order: 1;
    }

    .pb-home-feature {
        position: sticky;
        top: 0;
        align-self: start;
        height: 100vh;
        overflow: hidden;
        background: #111;
    }

    .pb-home-feature__link {
        display: block;
        width: 100%;
        height: 100vh;
        min-height: 100vh;
        position: relative;
    }

    .pb-home-feature__image {
        width: 100%;
        height: 100%;
        min-height: 100vh;
        object-fit: cover;
        display: block;
    }

    .pb-home-feature__fallback {
        width: 100%;
        height: 100%;
        min-height: 100vh;
        display: block;
        background:
            linear-gradient(180deg, rgba(40, 40, 40, 0.05), rgba(10, 10, 10, 0.8)),
            linear-gradient(135deg, #2d2d2d, #929292);
    }

    .pb-home-feature__shade {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.04) 35%, rgba(0, 0, 0, 0.78) 100%);
    }

    .pb-home-feature__content {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 2;
        padding: 54px 50px 44px;
        color: #fff;
    }

    .pb-home-kicker {
        display: inline-block;
        margin-bottom: 14px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .pb-home-feature__title {
        display: block;
        max-width: 10.5ch;
        margin: 0 0 14px;
        font-size: clamp(2.4rem, 4vw, 4.5rem);
        line-height: 1.02;
        font-weight: 900;
        letter-spacing: -0.04em;
        word-break: keep-all;
    }

    .pb-home-feature__subline {
        display: block;
        max-width: 28rem;
        margin: 0 0 24px;
        font-size: 1rem;
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.88);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pb-home-meta {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .pb-home-grid {
        position: relative;
        min-height: 100vh;
        padding: clamp(28px, 3.2vw, 44px) clamp(20px, 2.4vw, 32px) clamp(28px, 3.2vw, 44px);
        overflow: hidden;
        background:
            radial-gradient(circle at top left, rgba(255, 255, 255, 0.26), transparent 32%),
            linear-gradient(180deg, #d9ff2d 0%, #cbfb27 100%);
    }

    .pb-home-grid::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.12), transparent 46%),
            linear-gradient(315deg, rgba(0, 0, 0, 0.05), transparent 38%);
        pointer-events: none;
    }

    .pb-home-grid__stage {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 26px 18px;
        align-items: start;
    }

    .pb-home-grid__column {
        display: flex;
        flex-direction: column;
        gap: 26px;
    }

    .pb-home-grid__column--right {
        padding-top: 42px;
    }

    .pb-home-compact {
        width: 100%;
        display: flex;
        flex-direction: column;
        background: transparent;
        min-height: 0;
    }

    .pb-home-compact__media {
        display: block;
        width: 100%;
        aspect-ratio: 0.88 / 1;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.2);
        position: relative;
    }

    .pb-home-compact__media img,
    .pb-home-compact__fallback {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        transition: transform 0.45s ease;
    }

    .pb-home-compact:hover .pb-home-compact__media img {
        transform: scale(1.05);
    }

    .pb-home-compact__fallback {
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.28), rgba(0, 0, 0, 0.12)),
            linear-gradient(135deg, #1f1f1f, #ababab);
    }

    .pb-home-compact__body {
        padding: 18px 0 0;
    }

    .pb-home-compact__title {
        margin: 0 0 10px;
        font-size: clamp(1.45rem, 1.75vw, 2.25rem);
        line-height: 1.14;
        font-weight: 900;
        letter-spacing: -0.03em;
        word-break: keep-all;
    }

    .pb-home-compact__kicker {
        position: absolute;
        left: 16px;
        bottom: 14px;
        z-index: 2;
        margin: 0;
        color: #fff;
        text-shadow: 0 1px 10px rgba(0, 0, 0, 0.35);
    }

    .pb-home-compact__subline {
        margin: 0 0 18px;
        font-size: 0.98rem;
        line-height: 1.5;
        color: rgba(17, 17, 17, 0.82);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .pb-home-feature .pb-home-kicker {
        font-size: 16px;
        margin-bottom: 18px;
        letter-spacing: 0.12em;
    }

    .pb-home-feature .pb-home-meta {
        font-size: 17px;
        letter-spacing: 0.06em;
    }

    .pb-home-compact .pb-home-meta {
        font-size: 14px;
        letter-spacing: 0.06em;
        color: rgba(17, 17, 17, 0.92);
    }

    .pb-home-pagination {
        display: flex;
        justify-content: center;
        gap: 6px;
        padding: 24px 20px 40px;
        background: #111;
    }

    .pb-home-pagination .page-numbers {
        min-width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        color: rgba(255, 255, 255, 0.75);
        font-size: 12px;
        font-weight: 700;
    }

    .pb-home-pagination .page-numbers.current,
    .pb-home-pagination .page-numbers:hover {
        background: #dfff1f;
        color: #111;
    }

    @media (max-width: 1100px) {
        .pb-home-band,
        .pb-home-band--reverse {
            grid-template-columns: 1fr;
            min-height: auto;
        }

        .pb-home-band--reverse .pb-home-feature,
        .pb-home-band--reverse .pb-home-grid {
            order: initial;
        }

        .pb-home-feature,
        .pb-home-feature__link,
        .pb-home-feature__image,
        .pb-home-feature__fallback,
        .pb-home-grid {
            min-height: auto;
        }

        .pb-home-feature {
            position: relative;
            top: auto;
            height: auto;
        }

        .pb-home-feature__image,
        .pb-home-feature__fallback {
            aspect-ratio: 4 / 5;
        }

        .pb-home-feature__link {
            height: auto;
            min-height: auto;
        }

        .pb-home-grid__stage {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pb-home-grid__column--right {
            padding-top: 0;
        }
    }

    @media (max-width: 720px) {
        .pb-home-grid {
            padding: 34px 20px 44px;
        }

        .pb-home-grid__stage {
            grid-template-columns: 1fr;
            gap: 28px;
        }

        .pb-home-grid__column {
            gap: 28px;
        }

        .pb-home-grid__column--right {
            padding-top: 0;
        }

        .pb-home-feature__content {
            padding: 30px 22px 26px;
        }

        .pb-home-feature__title,
        .pb-home-feature__subline {
            max-width: none;
        }

        .pb-home-compact__media {
            aspect-ratio: 4 / 3;
        }

        .pb-home-compact__title {
            font-size: 1.7rem;
        }

        .pb-home-feature .pb-home-kicker {
            font-size: 13px;
        }

        .pb-home-feature .pb-home-meta {
            font-size: 15px;
        }
    }
</style>

<main class="pb-home">
    <?php if ($query->have_posts()) : ?>
        <div class="pb-home-stream">
            <?php foreach (array_chunk($query->posts, 5) as $index => $chunk) : ?>
                <?php
                $feature = $chunk[0];
                $feature_data = project_b_home_card_data($feature->ID);
                $subs = array_slice($chunk, 1);
                $reverse = $index % 2 === 1;
                ?>
                <section class="pb-home-band<?php echo $reverse ? ' pb-home-band--reverse' : ''; ?>">
                    <article class="pb-home-feature">
                        <a class="pb-home-feature__link" href="<?php echo esc_url($feature_data['url']); ?>">
                            <?php if ($feature_data['image']) : ?>
                                <img class="pb-home-feature__image" src="<?php echo esc_url($feature_data['image']); ?>" alt="<?php echo esc_attr($feature_data['title']); ?>">
                            <?php else : ?>
                                <span class="pb-home-feature__fallback"></span>
                            <?php endif; ?>
                            <span class="pb-home-feature__shade"></span>
                            <span class="pb-home-feature__content">
                                <span class="pb-home-kicker"><?php echo esc_html($feature_data['category']); ?></span>
                                <span class="pb-home-feature__title"><?php echo esc_html($feature_data['title']); ?></span>
                                <?php if (!empty($feature_data['subline'])) : ?>
                                    <span class="pb-home-feature__subline"><?php echo esc_html($feature_data['subline']); ?></span>
                                <?php endif; ?>
                                <span class="pb-home-meta"><?php echo esc_html($feature_data['date']); ?></span>
                            </span>
                        </a>
                    </article>

                    <div class="pb-home-grid">
                        <div class="pb-home-grid__stage">
                            <?php
                            $left_cards = array();
                            $right_cards = array();

                            foreach ($subs as $sub_index => $sub_post) {
                                if (0 === $sub_index % 2) {
                                    $left_cards[] = $sub_post;
                                } else {
                                    $right_cards[] = $sub_post;
                                }
                            }
                            ?>

                            <div class="pb-home-grid__column pb-home-grid__column--left">
                                <?php foreach ($left_cards as $sub_post) : ?>
                                    <?php $sub = project_b_home_card_data($sub_post->ID); ?>
                                    <article class="pb-home-compact">
                                        <a class="pb-home-compact__media" href="<?php echo esc_url($sub['url']); ?>">
                                            <?php if ($sub['image']) : ?>
                                                <img src="<?php echo esc_url($sub['image']); ?>" alt="<?php echo esc_attr($sub['title']); ?>">
                                            <?php else : ?>
                                                <span class="pb-home-compact__fallback"></span>
                                            <?php endif; ?>
                                            <span class="pb-home-kicker pb-home-compact__kicker"><?php echo esc_html($sub['category']); ?></span>
                                        </a>
                                        <div class="pb-home-compact__body">
                                            <h3 class="pb-home-compact__title">
                                                <a href="<?php echo esc_url($sub['url']); ?>"><?php echo esc_html($sub['title']); ?></a>
                                            </h3>
                                            <?php if (!empty($sub['subline'])) : ?>
                                                <div class="pb-home-compact__subline"><?php echo esc_html($sub['subline']); ?></div>
                                            <?php endif; ?>
                                            <div class="pb-home-meta"><?php echo esc_html($sub['date']); ?></div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <div class="pb-home-grid__column pb-home-grid__column--right">
                                <?php foreach ($right_cards as $sub_post) : ?>
                                    <?php $sub = project_b_home_card_data($sub_post->ID); ?>
                                    <article class="pb-home-compact">
                                        <a class="pb-home-compact__media" href="<?php echo esc_url($sub['url']); ?>">
                                            <?php if ($sub['image']) : ?>
                                                <img src="<?php echo esc_url($sub['image']); ?>" alt="<?php echo esc_attr($sub['title']); ?>">
                                            <?php else : ?>
                                                <span class="pb-home-compact__fallback"></span>
                                            <?php endif; ?>
                                            <span class="pb-home-kicker pb-home-compact__kicker"><?php echo esc_html($sub['category']); ?></span>
                                        </a>
                                        <div class="pb-home-compact__body">
                                            <h3 class="pb-home-compact__title">
                                                <a href="<?php echo esc_url($sub['url']); ?>"><?php echo esc_html($sub['title']); ?></a>
                                            </h3>
                                            <?php if (!empty($sub['subline'])) : ?>
                                                <div class="pb-home-compact__subline"><?php echo esc_html($sub['subline']); ?></div>
                                            <?php endif; ?>
                                            <div class="pb-home-meta"><?php echo esc_html($sub['date']); ?></div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <nav class="pb-home-pagination" aria-label="<?php esc_attr_e('Posts', 'Avada'); ?>">
            <?php
            echo wp_kses_post(
                paginate_links(
                    array(
                        'total' => $query->max_num_pages,
                        'current' => $paged,
                        'mid_size' => 1,
                        'prev_text' => '&lsaquo;',
                        'next_text' => '&rsaquo;',
                    )
                )
            );
            ?>
        </nav>
    <?php endif; ?>
</main>

<?php
wp_reset_postdata();
get_footer();
