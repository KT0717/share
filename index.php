<?php
$parent_category_slug = 'about';
$child_category_slug = isset($_GET['child_category']) ? sanitize_text_field($_GET['child_category']) : '';
$tag_slug = isset($_GET['tag']) ? sanitize_text_field($_GET['tag']) : '';

// `about` カテゴリの ID を取得
$parent_category = get_category_by_slug($parent_category_slug);
$parent_category_id = $parent_category ? $parent_category->term_id : 0;

// クエリの条件を設定
$args = [
    'post_type'      => 'post',
    'posts_per_page' => 10,
    'category__in'   => [$parent_category_id],
];

// 子カテゴリが選択されている場合
if ($child_category_slug) {
    $child_category = get_category_by_slug($child_category_slug);
    if ($child_category) {
        $args['category__in'][] = $child_category->term_id;
    }
}

// タグが選択されている場合
if ($tag_slug) {
    $args['tag'] = $tag_slug;
}

// クエリを実行
$query = new WP_Query($args);
?>

<?php if ($query->have_posts()) : ?>
    <ul>
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <li>
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                <!-- 子カテゴリの絞り込みリンク -->
                <div>
                    子カテゴリ:
                    <?php
                    $categories = get_the_category();
                    foreach ($categories as $category) {
                        if ($category->parent == $parent_category_id) {
                            $child_url = add_query_arg('child_category', $category->slug, get_permalink(get_option('page_for_posts')));
                            if ($tag_slug) {
                                $child_url = add_query_arg('tag', $tag_slug, $child_url);
                            }
                            echo '<a href="' . esc_url($child_url) . '">' . esc_html($category->name) . '</a> ';
                        }
                    }
                    ?>
                </div>

                <!-- タグの絞り込みリンク -->
                <div>
                    タグ:
                    <?php
                    $tags = get_the_tags();
                    if ($tags) {
                        foreach ($tags as $tag) {
                            $tag_url = add_query_arg('tag', $tag->slug, get_permalink(get_option('page_for_posts')));
                            if ($child_category_slug) {
                                $tag_url = add_query_arg('child_category', $child_category_slug, $tag_url);
                            }
                            echo '<a href="' . esc_url($tag_url) . '">' . esc_html($tag->name) . '</a> ';
                        }
                    }
                    ?>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p>該当する記事がありません。</p>
<?php endif; ?>
