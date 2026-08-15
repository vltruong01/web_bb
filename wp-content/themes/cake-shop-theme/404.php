<?php get_header(); ?>

<main>
  <section class="section">
    <div class="container">
      <div class="card archive-empty-card">
        <h1>Không tìm thấy trang</h1>
        <p class="cake-excerpt">Trang bạn đang tìm có thể đã bị đổi đường dẫn, xóa hoặc chưa được tạo.</p>
        <p class="cake-excerpt">Bạn có thể quay lại trang chủ, xem menu bánh hoặc liên hệ tiệm để được hỗ trợ nhanh hơn nhé.</p>

        <div class="button-group">
          <a class="btn btn-primary" href="<?php echo esc_url(home_url('/')); ?>">Về trang chủ</a>
          <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/menu-banh')); ?>">Xem menu bánh</a>
          <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/lien-he')); ?>">Liên hệ tiệm</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
