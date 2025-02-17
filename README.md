`about` カテゴリ配下の記事を取得し、記事に紐づいている子カテゴリの絞り込みリンクを生成し、さらにタグの絞り込みリンクを作成し、カテゴリとタグの絞り込みを連動させる方法を解説します。  

---

## **1. 絞り込み用の URL 設計**  
クエリパラメータを活用して、以下のような URL でフィルタリングできるようにします。  
```
example.com/about/?child_category=slug&tag=slug
```
- `child_category=slug` → 子カテゴリを絞り込み  
- `tag=slug` → タグを絞り込み  

---

## **2. `about` 配下の記事を取得**  
```php
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
```

---

## **3. 解説**
1. **`about` カテゴリの ID を取得**  
   `get_category_by_slug('about')` を使って、`about` の ID を取得します。  
2. **クエリ条件を作成**  
   - `category__in` に `about` の ID を指定  
   - `child_category` のクエリパラメータがある場合、その子カテゴリの ID も追加  
   - `tag` のクエリパラメータがある場合、それも追加  
3. **記事一覧をループ処理**  
   - `the_permalink()` を使って記事へのリンクを生成  
4. **子カテゴリの絞り込みリンクを生成**  
   - `get_the_category()` で記事に紐づくカテゴリを取得  
   - `about` の子カテゴリのみ抽出し、`child_category` のパラメータを持つ URL を作成  
   - 既に `tag` が選択されている場合、それも保持したまま URL を作成  
5. **タグの絞り込みリンクを生成**  
   - `get_the_tags()` で記事に紐づくタグを取得  
   - `tag` のパラメータを持つ URL を作成  
   - 既に `child_category` が選択されている場合、それも保持  

---

## **4. 使い方**
1. `about` カテゴリの投稿一覧ページにアクセス（例: `example.com/about/`）  
2. **子カテゴリのリンクをクリック** → `example.com/about/?child_category=example-child` に遷移  
3. **タグのリンクをクリック** → `example.com/about/?child_category=example-child&tag=example-tag` に遷移  
4. **別のタグをクリック** → `tag` のパラメータが更新される  

---

## **5. まとめ**
- **`about` カテゴリの投稿を取得**  
- **記事に紐づく子カテゴリを取得し、絞り込みリンクを生成**  
- **記事に紐づくタグを取得し、絞り込みリンクを生成**  
- **カテゴリとタグのフィルタを連動させる**  

この方法なら `form` タグを使わずに、シンプルなリンククリックだけでフィルタリングできます！
