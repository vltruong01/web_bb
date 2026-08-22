<?php get_header(); $shop_info = cake_shop_get_shop_info(); ?>

<main id="main-content" tabindex="-1">
  <section class="section cake-detail-section">
    <div class="container">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

        <?php
        $gia_tham_khao    = get_post_meta(get_the_ID(), '_gia_tham_khao', true);
        $trang_thai_banh  = get_post_meta(get_the_ID(), '_trang_thai_banh', true);
        $trang_thai_label = cake_shop_get_trang_thai_label($trang_thai_banh);
        $cake_details = cake_shop_get_cake_detail_fields(get_the_ID());
        $gallery_urls = cake_shop_get_gallery_image_urls(get_the_ID(), 'large');
        $gallery_thumbs = array_slice($gallery_urls, 1);
        $gallery_json = esc_attr(wp_json_encode($gallery_urls));

        $status_class = '';
        if ($trang_thai_banh === 'co-san') {
          $status_class = 'status-badge--available';
        } elseif ($trang_thai_banh === 'nhan-dat-truoc') {
          $status_class = 'status-badge--preorder';
        } elseif ($trang_thai_banh === 'tam-het') {
          $status_class = 'status-badge--soldout';
        }
        ?>

        <div class="cake-detail">
          <?php
          $detail_term_slugs = wp_get_post_terms(get_the_ID(), 'cake_category', ['fields' => 'slugs']);
          $detail_category_links = [
            'menu-banh' => ['label' => 'Menu bánh', 'url' => home_url('/menu-banh')],
            'banh-kem' => ['label' => 'Bánh kem', 'url' => home_url('/banh-kem')],
          ];
          $detail_category_slug = !is_wp_error($detail_term_slugs) && !empty($detail_term_slugs) ? $detail_term_slugs[0] : '';
          ?>
          <nav class="cake-breadcrumb" aria-label="Điều hướng phân cấp">
            <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a>
            <?php if (isset($detail_category_links[$detail_category_slug])) : ?>
              <span aria-hidden="true">/</span>
              <a href="<?php echo esc_url($detail_category_links[$detail_category_slug]['url']); ?>"><?php echo esc_html($detail_category_links[$detail_category_slug]['label']); ?></a>
            <?php endif; ?>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?php the_title(); ?></span>
          </nav>

          <div class="cake-detail__header">
            <p class="cake-detail__eyebrow">Thông tin bánh</p>
            <h1><?php the_title(); ?></h1>
          </div>

        <?php if (has_post_thumbnail()) : ?>
          <div class="cake-thumb cake-detail__image">
            <?php the_post_thumbnail('large', ['alt' => get_the_title(), 'fetchpriority' => 'high', 'decoding' => 'async']); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($gallery_thumbs)) : ?>
          <div class="cake-detail__gallery" aria-label="Ảnh khác của bánh">
            <?php foreach ($gallery_thumbs as $index => $gallery_url) : ?>
              <button
                type="button"
                class="cake-detail__gallery-item cake-popup-trigger"
                data-gallery="<?php echo $gallery_json; ?>"
                data-lightbox-mode="image-only"
                data-title="<?php echo esc_attr(get_the_title()); ?>"
                data-excerpt="<?php echo esc_attr(get_the_excerpt()); ?>"
                data-price="<?php echo esc_attr(cake_shop_format_price($gia_tham_khao)); ?>"
                data-start-index="<?php echo esc_attr($index + 1); ?>"
                aria-label="Xem ảnh <?php echo esc_attr($index + 2); ?> của <?php echo esc_attr(get_the_title()); ?>"
              >
                <img src="<?php echo esc_url($gallery_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($trang_thai_label) : ?>
          <span class="status-badge <?php echo esc_attr($status_class); ?>">
            <?php echo esc_html($trang_thai_label); ?>
          </span>
        <?php endif; ?>

        <?php if ($trang_thai_banh === 'tam-het') : ?>
          <p class="cake-availability-notice">Mẫu này đang tạm hết. Nhắn tiệm để được gợi ý mẫu tương tự hoặc thời gian có bánh lại.</p>
        <?php elseif ($trang_thai_banh === 'nhan-dat-truoc') : ?>
          <p class="cake-availability-notice">Mẫu này cần chuẩn bị trước. Nhắn tiệm để chốt thời gian nhận bánh phù hợp.</p>
        <?php endif; ?>

        <?php if ($gia_tham_khao) : ?>
          <p class="cake-price"><?php echo esc_html(cake_shop_format_price($gia_tham_khao)); ?></p>
        <?php endif; ?>

        <?php if ($cake_details['size'] || $cake_details['serving'] || $cake_details['preorder']) : ?>
          <dl class="cake-detail__facts">
            <?php if ($cake_details['size']) : ?><div><dt>Kích thước</dt><dd><?php echo esc_html($cake_details['size']); ?></dd></div><?php endif; ?>
            <?php if ($cake_details['serving']) : ?><div><dt>Khẩu phần</dt><dd><?php echo esc_html($cake_details['serving']); ?></dd></div><?php endif; ?>
            <?php if ($cake_details['preorder']) : ?><div><dt>Đặt trước</dt><dd><?php echo esc_html($cake_details['preorder']); ?></dd></div><?php endif; ?>
          </dl>
        <?php endif; ?>

        <div class="cake-excerpt cake-detail__content">
          <?php the_content(); ?>
        </div>

        <div class="button-group cake-detail__actions">
          <a class="btn btn-primary" href="<?php echo esc_url(cake_shop_get_contact_url('zalo')); ?>" target="_blank" rel="noopener noreferrer">Nhắn Zalo</a>
          <a class="btn btn-secondary" href="<?php echo esc_url(cake_shop_get_contact_url('messenger')); ?>" target="_blank" rel="noopener noreferrer">Nhắn Messenger</a>
          <a class="btn btn-secondary" href="<?php echo esc_url(cake_shop_get_contact_url('phone')); ?>">Gọi cho tiệm</a>
        </div>

        </div>

        <?php
        $related_query_args = [
          'post_type'      => 'cake',
          'posts_per_page' => 3,
          'post__not_in'   => [get_the_ID()],
          'meta_query'     => [
            [
              'key'     => '_trang_thai_banh',
              'value'   => 'an',
              'compare' => '!=',
            ],
          ],
          'orderby'        => 'date',
          'order'          => 'DESC',
        ];

        if ($detail_category_slug) {
          $related_query_args['tax_query'] = [
            [
              'taxonomy' => 'cake_category',
              'field'    => 'slug',
              'terms'    => $detail_category_slug,
            ],
          ];
        }

        $related_cakes = new WP_Query($related_query_args);
        if (!empty($related_cakes->posts)) {
          $related_status_order = [
            'co-san' => 0,
            'nhan-dat-truoc' => 1,
            'tam-het' => 2,
          ];

          usort($related_cakes->posts, function ($first_cake, $second_cake) use ($related_status_order) {
            $first_status = get_post_meta($first_cake->ID, '_trang_thai_banh', true);
            $second_status = get_post_meta($second_cake->ID, '_trang_thai_banh', true);
            $first_order = $related_status_order[$first_status] ?? 3;
            $second_order = $related_status_order[$second_status] ?? 3;

            if ($first_order === $second_order) {
              return strcmp($second_cake->post_date, $first_cake->post_date);
            }

            return $first_order <=> $second_order;
          });
        }
        ?>

        <?php if ($related_cakes->have_posts()) : ?>
          <section class="cake-related" aria-labelledby="cake-related-title">
            <div class="cake-related__heading">
              <div>
                <p class="cake-related__eyebrow">Khám phá thêm</p>
                <h2 id="cake-related-title">Mẫu bánh khác</h2>
              </div>
              <?php if (isset($detail_category_links[$detail_category_slug])) : ?>
                <a class="cake-card__detail-action" href="<?php echo esc_url($detail_category_links[$detail_category_slug]['url']); ?>">Xem tất cả</a>
              <?php endif; ?>
            </div>

            <div class="cards">
              <?php while ($related_cakes->have_posts()) : $related_cakes->the_post(); ?>
                <?php
                $related_price = get_post_meta(get_the_ID(), '_gia_tham_khao', true);
                $related_status = get_post_meta(get_the_ID(), '_trang_thai_banh', true);
                $related_status_label = cake_shop_get_trang_thai_label($related_status);
                $related_status_class = $related_status === 'co-san' ? 'status-badge--available' : ($related_status === 'nhan-dat-truoc' ? 'status-badge--preorder' : ($related_status === 'tam-het' ? 'status-badge--soldout' : ''));
                ?>
                <article class="card">
                  <a class="cake-related__image-link" href="<?php echo esc_url(get_permalink()); ?>" aria-label="Xem <?php echo esc_attr(get_the_title()); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                      <div class="cake-thumb">
                        <?php the_post_thumbnail('medium_large', ['alt' => get_the_title(), 'loading' => 'lazy', 'decoding' => 'async']); ?>
                      </div>
                    <?php endif; ?>
                  </a>

                  <?php if ($related_status_label) : ?>
                    <div class="cake-card-badges">
                      <span class="status-badge <?php echo esc_attr($related_status_class); ?>"><?php echo esc_html($related_status_label); ?></span>
                    </div>
                  <?php endif; ?>

                  <h3><a class="cake-card__detail-link" href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h3>
                  <?php if ($related_price) : ?><p class="cake-price"><?php echo esc_html(cake_shop_format_price($related_price)); ?></p><?php endif; ?>
                  <a class="cake-card__detail-action" href="<?php echo esc_url(get_permalink()); ?>">Xem chi tiết</a>
                </article>
              <?php endwhile; ?>
            </div>
          </section>
          <?php wp_reset_postdata(); ?>
        <?php endif; ?>
      <?php endwhile; endif; ?>
    </div>
  </section>
</main>

<?php cake_shop_render_lightbox_markup(); ?>
<?php get_footer(); ?>
