<?php
/* Template Name: Góp ý */

get_header();
?>

<main>
  <section class="section">
    <div class="container">
      <div class="contact-page-heading">
        <h1>Góp ý</h1>
        <p>Nếu bạn có điều gì muốn tiệm cải thiện hoặc muốn nhắn nhủ nhẹ nhàng sau khi trải nghiệm, bạn có thể gửi góp ý ẩn danh tại đây nhé.</p>
      </div>

      <?php echo do_shortcode('[cake_shop_feedback_form]'); ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
