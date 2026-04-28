<?php
/**
 * Large feature card for homepage bands.
 *
 * @var array $args
 */

$post_obj = $args['post'] ?? get_post();

if (!$post_obj instanceof WP_Post) {
    return;
}

$post_id = $post_obj->ID;
$thumb = function_exists('project_b_get_post_preview_image_url') ? project_b_get_post_preview_image_url($post_id, 'full') : get_the_post_thumbnail_url($post_id, 'full');
$title = get_the_title($post_id);
$permalink = get_permalink($post_id);
$categories = get_the_category($post_id);
$category_name = !empty($categories) ? $categories[0]->name : __('Uncategorized', 'Avada');
$excerpt = has_excerpt($post_id) ? get_the_excerpt($post_id) : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 18);
?>
<article class="project-b-card project-b-card--feature">
    <a class="project-b-card__media-link" href="<?php echo esc_url($permalink); ?>">
        <?php if ($thumb) : ?>
            <img class="project-b-card__media" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>">
        <?php else : ?>
            <span class="project-b-card__fallback"></span>
        <?php endif; ?>
    </a>
    <div class="project-b-card__overlay"></div>
    <div class="project-b-card__content">
        <span class="project-b-card__kicker"><?php echo esc_html($category_name); ?></span>
        <h2 class="project-b-card__title">
            <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
        </h2>
        <p class="project-b-card__excerpt"><?php echo esc_html($excerpt); ?></p>
        <div class="project-b-card__meta"><?php echo esc_html(get_the_date('Y. m. d', $post_id)); ?></div>
    </div>
</article>
