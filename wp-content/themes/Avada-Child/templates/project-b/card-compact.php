<?php
/**
 * Compact card for homepage bands.
 *
 * @var array $args
 */

$post_obj = $args['post'] ?? get_post();

if (!$post_obj instanceof WP_Post) {
    return;
}

$post_id = $post_obj->ID;
$thumb = function_exists('project_b_get_post_preview_image_url') ? project_b_get_post_preview_image_url($post_id, 'large') : get_the_post_thumbnail_url($post_id, 'large');
$title = get_the_title($post_id);
$permalink = get_permalink($post_id);
$categories = get_the_category($post_id);
$category_name = !empty($categories) ? $categories[0]->name : __('Uncategorized', 'Avada');
?>
<article class="project-b-card project-b-card--compact">
    <a class="project-b-card__media-link" href="<?php echo esc_url($permalink); ?>">
        <?php if ($thumb) : ?>
            <img class="project-b-card__media" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>">
        <?php else : ?>
            <span class="project-b-card__fallback"></span>
        <?php endif; ?>
    </a>
    <div class="project-b-card__compact-body">
        <span class="project-b-card__kicker"><?php echo esc_html($category_name); ?></span>
        <h3 class="project-b-card__compact-title">
            <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
        </h3>
        <div class="project-b-card__meta"><?php echo esc_html(get_the_date('Y. m. d', $post_id)); ?></div>
    </div>
</article>
