<?php get_header(); ?>

<main>
  <section class="section">
    <div class="container">
      <div class="cake-page-heading">
        <?php if (is_search()) : ?>
          <h1>Kết quả tìm kiếm</h1>
          <p>Những nội dung phù hợp với từ khóa “<?php echo esc_html(get_search_query()); ?>”.</p>
        <?php elseif (is_archive()) : ?>
          <h1><?php the_archive_title(); ?></h1>
          <?php if (get_the_archive_description()) : ?>
            <p><?php echo wp_kses_post(get_the_archive_description()); ?></p>
          <?php else : ?>
            <p>Những nội dung mới nhất được tiệm cập nhật trong chuyên mục này.</p>
          <?php endif; ?>
        <?php else : ?>
          <h1>Nội dung mới nhất</h1>
          <p>Các bài viết và cập nhật mới nhất từ tiệm.</p>
        <?php endif; ?>
      </div>

      <?php if (have_posts()) : ?>
        <div class="cards archive-cards">
          <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('card archive-card'); ?>>
              <?php if (has_post_thumbnail()) : ?>
                <a class="cake-thumb" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
                  <?php the_post_thumbnail('large'); ?>
                </a>
              <?php endif; ?>

              <h3>
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              </h3>

              <?php if (get_post_type() === 'cake') : ?>
                <?php $gia_tham_khao = get_post_meta(get_the_ID(), '_gia_tham_khao', true); ?>
                <?php if ($gia_tham_khao) : ?>
                  <p class="cake-price"><?php echo esc_html(cake_shop_format_price($gia_tham_khao)); ?></p>
                <?php endif; ?>
              <?php endif; ?>

              <p class="cake-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>
            </article>
          <?php endwhile; ?>
        </div>

        <div class="cake-pagination">
          <?php
          the_posts_pagination([
            'mid_size'           => 1,
            'prev_text'          => '← Trang trước',
            'next_text'          => 'Trang sau →',
            'screen_reader_text' => 'Phân trang',
          ]);
          ?>
        </div>
      <?php else : ?>
        <div class="card archive-empty-card">
          <h3>Chưa có nội dung phù hợp</h3>
          <p class="cake-excerpt">Bạn có thể quay lại trang chủ hoặc xem menu bánh của tiệm nhé.</p>
          <div class="button-group">
            <a class="btn btn-primary" href="<?php echo esc_url(home_url('/')); ?>">Về trang chủ</a>
            <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/menu-banh')); ?>">Xem menu bánh</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
