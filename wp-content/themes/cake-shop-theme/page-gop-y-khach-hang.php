<?php
/* Template Name: Góp ý khách hàng */

get_header();
?>

<main>
  <section class="section">
    <div class="container">
      <div class="contact-page-heading">
        <h1>Góp ý khách hàng</h1>
        <p>Xem toàn bộ góp ý ẩn danh mà khách hàng đã gửi.</p>
      </div>

      <?php echo do_shortcode('[cake_shop_feedback_admin]'); ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
