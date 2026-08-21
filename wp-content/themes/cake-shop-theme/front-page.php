<?php get_header(); ?>
<?php $store_info = cake_shop_get_store_info(); ?>

<?php
$hero_query = new WP_Query([
  'post_type'      => 'cake',
  'posts_per_page' => 1,
  'meta_query'     => [
    [
      'key'     => '_trang_thai_banh',
      'value'   => 'an',
      'compare' => '!=',
    ]
  ],
  'orderby'        => 'date',
  'order'          => 'DESC',
]);

$hero_image_url = '';
$hero_image_id = (int) get_option('cake_shop_home_hero_image_id', 0);

if ($hero_image_id) {
  $hero_image_url = wp_get_attachment_image_url($hero_image_id, 'large');
}

if (!$hero_image_url && $hero_query->have_posts()) {
  $hero_query->the_post();
  if (has_post_thumbnail()) {
    $hero_image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
  }
  wp_reset_postdata();
}

/**
 * Query ưu tiên bánh có badge Mới/Hot.
 * Nếu không có thì fallback về query cũ.
 */
if (!function_exists('cake_shop_get_home_featured_query')) {
  function cake_shop_get_home_featured_query($category_slug) {
    $highlight_query = new WP_Query([
      'post_type'      => 'cake',
      'posts_per_page' => 3,
      'tax_query'      => [
        [
          'taxonomy' => 'cake_category',
          'field'    => 'slug',
          'terms'    => $category_slug,
        ]
      ],
      'meta_query'     => [
        'relation' => 'AND',
        [
          'key'     => '_trang_thai_banh',
          'value'   => 'an',
          'compare' => '!=',
        ],
        [
          'key'     => '_cake_highlight',
          'value'   => ['moi', 'hot'],
          'compare' => 'IN',
        ]
      ],
      'orderby'        => 'date',
      'order'          => 'DESC',
    ]);

    if ($highlight_query->have_posts()) {
      return $highlight_query;
    }

    return new WP_Query([
      'post_type'      => 'cake',
      'posts_per_page' => 3,
      'tax_query'      => [
        [
          'taxonomy' => 'cake_category',
          'field'    => 'slug',
          'terms'    => $category_slug,
        ]
      ],
      'meta_query'     => [
        [
          'key'     => '_trang_thai_banh',
          'value'   => 'an',
          'compare' => '!=',
        ]
      ],
      'orderby'        => 'date',
      'order'          => 'DESC',
    ]);
  }
}
?>

<main id="main-content" tabindex="-1">
  <section class="hero hero--with-banner">
    <div class="container hero__grid">
      <div class="hero__content">
        <p class="hero__eyebrow">Tiệm bánh tại Phú Quốc</p>
        <h1>Những chiếc bánh ngọt ngào cho từng khoảnh khắc</h1>
        <p>
          Chào mừng bạn ghé <?php echo esc_html($store_info['shop_name']); ?>. Ở đây luôn có những mẫu bánh bán trong ngày và bánh kem nhận làm theo yêu cầu, để bạn dễ dàng chọn một chiếc bánh thật xinh cho dịp mình cần. Bạn có thể xem mẫu ngay trên website rồi nhắn Zalo, Messenger hoặc gọi điện, tiệm sẽ hỗ trợ thật nhanh.
        </p>

        <div class="button-group">
          <a class="btn btn-primary" href="<?php echo esc_url(home_url('/menu-banh')); ?>">Xem Menu bánh</a>
          <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/banh-kem')); ?>">Xem Bánh kem</a>
        </div>
      </div>

      <div class="hero__banner">
        <?php if ($hero_image_url) : ?>
          <img src="<?php echo esc_url($hero_image_url); ?>" alt="Bánh nổi bật tại <?php echo esc_attr($store_info['shop_name']); ?>" fetchpriority="high" decoding="async">
        <?php else : ?>
          <div class="hero__banner-placeholder"><?php echo esc_html($store_info['shop_name']); ?></div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="section home-products" aria-labelledby="home-products-title">
    <div class="container">
      <h2 id="home-products-title">Tiệm bánh mì, bánh ngọt và bánh kem Hồng Lập</h2>
      <p>Hồng Lập phục vụ bánh mì và bánh ngọt mỗi ngày, đồng thời nhận làm bánh kem theo yêu cầu tại Phú Quốc. Xem mẫu bánh, tham khảo giá và liên hệ đặt bánh trực tiếp với tiệm.</p>
    </div>
  </section>

  <section class="section home-intro">
    <div class="container">
      <h2>Vì sao nhiều khách yêu mến <?php echo esc_html($store_info['shop_name']); ?></h2>
      <p>Từ những chiếc bánh ngọt dùng hằng ngày đến bánh kem cho sinh nhật, kỷ niệm hay những dịp đặc biệt, tiệm luôn muốn mọi thứ thật dễ chọn, dễ đặt và đủ ấm áp để bạn cảm thấy yên tâm ngay từ lần đầu ghé xem.</p>

      <div class="cards info-cards">
        <div class="card">
          <h3>Luôn có bánh xinh mỗi ngày</h3>
          <p class="cake-excerpt">Bạn có thể xem nhanh những mẫu bánh đang có sẵn và nhắn tiệm giữ bánh trong ngày nếu gặp đúng mẫu mình thích.</p>
        </div>

        <div class="card">
          <h3>Nhận làm bánh kem theo yêu cầu</h3>
          <p class="cake-excerpt">Bạn có thể chọn mẫu có sẵn trên website rồi nhắn thêm mong muốn về màu sắc, kiểu trang trí hay lời chúc để tiệm chuẩn bị chiếc bánh thật vừa ý.</p>
        </div>

        <div class="card">
          <h3>Liên hệ nhẹ nhàng, đặt bánh thật nhanh</h3>
          <p class="cake-excerpt">Chỉ cần bấm vào biểu tượng liên hệ nổi là bạn đã có thể gọi điện, nhắn Zalo hoặc Messenger để được tiệm tư vấn nhanh chóng.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section home-catalog">
    <div class="container">
      <h2>Menu bánh nổi bật</h2>
      <p>Một vài mẫu bánh có sẵn hoặc bán trong ngày để bạn ghé xem nhanh và chọn cho mình chiếc bánh phù hợp.</p>

      <div class="cards">
        <?php
        $menu_banh_query = cake_shop_get_home_featured_query('menu-banh');

        if ($menu_banh_query->have_posts()) :
          while ($menu_banh_query->have_posts()) : $menu_banh_query->the_post();

            $gia_tham_khao    = get_post_meta(get_the_ID(), '_gia_tham_khao', true);
            $trang_thai_banh  = get_post_meta(get_the_ID(), '_trang_thai_banh', true);
            $trang_thai_label = cake_shop_get_trang_thai_label($trang_thai_banh);

            $highlight_value  = get_post_meta(get_the_ID(), '_cake_highlight', true);
            $highlight_label  = cake_shop_get_highlight_label($highlight_value);
            $highlight_class  = cake_shop_get_highlight_class($highlight_value);

            $status_class = '';
            if ($trang_thai_banh === 'co-san') {
              $status_class = 'status-badge--available';
            } elseif ($trang_thai_banh === 'tam-het') {
              $status_class = 'status-badge--soldout';
            }

            $gallery_urls = cake_shop_get_gallery_image_urls(get_the_ID(), 'large');
            $gallery_json = esc_attr(wp_json_encode($gallery_urls));
            $excerpt_text = get_the_excerpt();
        ?>
            <div class="card">
              <?php if (!empty($gallery_urls)) : ?>
                <div class="cake-thumb cake-thumb--clickable">
                  <img
                    src="<?php echo esc_url($gallery_urls[0]); ?>"
                    alt="<?php echo esc_attr(get_the_title()); ?>"
                    loading="lazy"
                    decoding="async"
                    class="cake-popup-trigger"
                    data-gallery="<?php echo $gallery_json; ?>"
                    data-title="<?php echo esc_attr(get_the_title()); ?>"
                    data-excerpt="<?php echo esc_attr($excerpt_text); ?>"
                    data-price="<?php echo esc_attr(cake_shop_format_price($gia_tham_khao)); ?>"
                    data-detail-url="<?php echo esc_url(get_permalink()); ?>"
                  >
                </div>
              <?php endif; ?>

              <div class="cake-card-badges">
                <?php if ($highlight_label) : ?>
                  <span class="cake-highlight-badge <?php echo esc_attr($highlight_class); ?>">
                    <?php echo esc_html($highlight_label); ?>
                  </span>
                <?php endif; ?>

                <?php if ($trang_thai_label) : ?>
                  <span class="status-badge <?php echo esc_attr($status_class); ?>">
                    <?php echo esc_html($trang_thai_label); ?>
                  </span>
                <?php endif; ?>
              </div>

              <h3><a class="cake-card__detail-link" href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h3>

              <?php if ($gia_tham_khao) : ?>
                <p class="cake-price"><?php echo esc_html(cake_shop_format_price($gia_tham_khao)); ?></p>
              <?php endif; ?>

              <a class="cake-card__detail-action" href="<?php echo esc_url(get_permalink()); ?>">Xem chi tiết</a>

            </div>
        <?php
          endwhile;
          wp_reset_postdata();
        else :
          echo '<p>Tiệm đang cập nhật thêm bánh cho mục này, bạn ghé lại sau một chút nhé.</p>';
        endif;
        ?>
      </div>

      <div style="margin-top: 20px;">
        <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/menu-banh')); ?>">Xem tất cả Menu bánh</a>
      </div>
    </div>
  </section>

  <section class="section home-catalog home-catalog--cream">
    <div class="container">
      <h2>Bánh kem nổi bật</h2>
      <p>Một vài mẫu bánh kem nhận đặt và một số mẫu có sẵn để bạn dễ tham khảo trước khi nhắn tiệm.</p>

      <div class="cards">
        <?php
        $banh_kem_query = cake_shop_get_home_featured_query('banh-kem');

        if ($banh_kem_query->have_posts()) :
          while ($banh_kem_query->have_posts()) : $banh_kem_query->the_post();

            $gia_tham_khao    = get_post_meta(get_the_ID(), '_gia_tham_khao', true);
            $trang_thai_banh  = get_post_meta(get_the_ID(), '_trang_thai_banh', true);
            $trang_thai_label = cake_shop_get_trang_thai_label($trang_thai_banh);

            $highlight_value  = get_post_meta(get_the_ID(), '_cake_highlight', true);
            $highlight_label  = cake_shop_get_highlight_label($highlight_value);
            $highlight_class  = cake_shop_get_highlight_class($highlight_value);

            $status_class = '';
            if ($trang_thai_banh === 'co-san') {
              $status_class = 'status-badge--available';
            } elseif ($trang_thai_banh === 'nhan-dat-truoc') {
              $status_class = 'status-badge--preorder';
            }

            $gallery_urls = cake_shop_get_gallery_image_urls(get_the_ID(), 'large');
            $gallery_json = esc_attr(wp_json_encode($gallery_urls));
            $excerpt_text = get_the_excerpt();
        ?>
            <div class="card">
              <?php if (!empty($gallery_urls)) : ?>
                <div class="cake-thumb cake-thumb--clickable">
                  <img
                    src="<?php echo esc_url($gallery_urls[0]); ?>"
                    alt="<?php echo esc_attr(get_the_title()); ?>"
                    loading="lazy"
                    decoding="async"
                    class="cake-popup-trigger"
                    data-gallery="<?php echo $gallery_json; ?>"
                    data-title="<?php echo esc_attr(get_the_title()); ?>"
                    data-excerpt="<?php echo esc_attr($excerpt_text); ?>"
                    data-price="<?php echo esc_attr(cake_shop_format_price($gia_tham_khao)); ?>"
                    data-detail-url="<?php echo esc_url(get_permalink()); ?>"
                  >
                </div>
              <?php endif; ?>

              <div class="cake-card-badges">
                <?php if ($highlight_label) : ?>
                  <span class="cake-highlight-badge <?php echo esc_attr($highlight_class); ?>">
                    <?php echo esc_html($highlight_label); ?>
                  </span>
                <?php endif; ?>

                <?php if ($trang_thai_label) : ?>
                  <span class="status-badge <?php echo esc_attr($status_class); ?>">
                    <?php echo esc_html($trang_thai_label); ?>
                  </span>
                <?php endif; ?>
              </div>

              <h3><a class="cake-card__detail-link" href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h3>

              <?php if ($gia_tham_khao) : ?>
                <p class="cake-price"><?php echo esc_html(cake_shop_format_price($gia_tham_khao)); ?></p>
              <?php endif; ?>

              <a class="cake-card__detail-action" href="<?php echo esc_url(get_permalink()); ?>">Xem chi tiết</a>

            </div>
        <?php
          endwhile;
          wp_reset_postdata();
        else :
          echo '<p>Tiệm đang cập nhật thêm mẫu bánh kem xinh cho bạn, ghé lại sau nhé.</p>';
        endif;
        ?>
      </div>

      <div style="margin-top: 20px;">
        <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/banh-kem')); ?>">Xem tất cả Bánh kem</a>
      </div>
    </div>
  </section>

  <section class="section section--soft" aria-labelledby="order-steps-title">
    <div class="container">
      <h2 id="order-steps-title">Đặt bánh thật đơn giản</h2>
      <p>Chỉ cần gửi mẫu bạn thích và thời gian mong muốn, tiệm sẽ tư vấn nhanh về size, giá và thời gian chuẩn bị.</p>

      <div class="cards info-cards order-steps">
        <div class="card">
          <h3>1. Chọn mẫu bánh</h3>
          <p class="cake-excerpt">Xem menu hoặc bánh kem, bấm xem chi tiết để tham khảo thông tin và ảnh mẫu.</p>
        </div>
        <div class="card">
          <h3>2. Nhắn tiệm</h3>
          <p class="cake-excerpt">Gửi tên bánh, ngày nhận mong muốn và những thay đổi bạn cần qua Zalo, Messenger hoặc điện thoại.</p>
        </div>
        <div class="card">
          <h3>3. Xác nhận đơn</h3>
          <p class="cake-excerpt">Tiệm phản hồi về mẫu, giá và thời gian nhận để bạn yên tâm trước khi chuẩn bị bánh.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section home-contact">
    <div class="container">
      <h2>Liên hệ với tiệm</h2>
      <p>Nếu bạn muốn đặt bánh hoặc cần tiệm tư vấn nhanh, mọi cách liên hệ đều có sẵn ở đây để bạn tiện nhắn bất cứ lúc nào.</p>

      <div class="cards contact-cards">
        <div class="card">
          <h3>Đặt bánh thật nhanh</h3>
          <p class="cake-excerpt">Bạn có thể bấm vào biểu tượng liên hệ nổi hoặc vào trang liên hệ để chọn cách nhắn tiệm thuận tiện nhất.</p>
          <a class="btn btn-primary" href="<?php echo esc_url(home_url('/lien-he')); ?>">Xem trang liên hệ</a>
        </div>

        <div class="card">
          <h3>Ghé tiệm</h3>
          <p class="cake-excerpt"><?php echo esc_html($store_info['address']); ?></p>
        </div>
      </div>
    </div>
  </section>
</main>

<?php cake_shop_render_lightbox_markup(); ?>

<?php get_footer(); ?>
