<?php
/* Template Name: Quản lý tiệm */

if (is_user_logged_in() && current_user_can('edit_posts')) {
  wp_enqueue_media();
}

get_header();

$store_notice = '';
$store_notice_type = 'success';

if (!is_user_logged_in()) {
  $store_notice = 'Bạn cần đăng nhập để quản lý tiệm.';
  $store_notice_type = 'error';
} elseif (!current_user_can('edit_posts')) {
  $store_notice = 'Tài khoản này không có quyền quản lý tiệm.';
  $store_notice_type = 'error';
} elseif (!empty($_GET['store_hero_notice'])) {
  $store_hero_notice = sanitize_key(wp_unslash($_GET['store_hero_notice']));

  switch ($store_hero_notice) {
    case 'updated':
      $store_notice = 'Đã cập nhật ảnh hero trang chủ.';
      break;

    case 'removed':
      $store_notice = 'Đã xóa ảnh hero trang chủ.';
      break;

    case 'no_file':
      $store_notice = 'Bạn chưa chọn ảnh mới để lưu.';
      $store_notice_type = 'error';
      break;

    case 'upload_error':
      $store_notice = !empty($_GET['store_hero_message'])
        ? sanitize_text_field(wp_unslash($_GET['store_hero_message']))
        : 'Không thể tải ảnh lên. Vui lòng thử lại.';
      $store_notice_type = 'error';
      break;

    case 'invalid_nonce':
      $store_notice = 'Không thể lưu ảnh vì phiên làm việc không hợp lệ. Vui lòng thử lại.';
      $store_notice_type = 'error';
      break;

    case 'permission_denied':
      $store_notice = 'Tài khoản này không có quyền quản lý tiệm.';
      $store_notice_type = 'error';
      break;

    case 'login_required':
      $store_notice = 'Bạn cần đăng nhập để quản lý tiệm.';
      $store_notice_type = 'error';
      break;
  }
}

$current_hero_image_id = (int) get_option('cake_shop_home_hero_image_id', 0);
$current_hero_image_url = $current_hero_image_id ? wp_get_attachment_image_url($current_hero_image_id, 'large') : '';
?>

<main>
  <section class="section">
    <div class="container">
      <?php
      while (have_posts()) : the_post();
        the_content();
      endwhile;
      ?>

      <?php echo do_shortcode('[cake_shop_admin_dashboard]'); ?>

      <?php if ($store_notice) : ?>
        <div class="store-notice store-notice--<?php echo esc_attr($store_notice_type); ?>">
          <?php echo esc_html($store_notice); ?>
        </div>
      <?php endif; ?>

      <?php if (is_user_logged_in() && current_user_can('edit_posts')) : ?>
        <div class="manager-box manager-box--store manager-box--hero">
          <h2>Ảnh hero trang chủ</h2>
          <p class="store-manager-help">Đây là ảnh lớn nằm bên phải phần mở đầu ở trang chủ.</p>

          <form class="store-manager-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('cake_shop_save_store_hero', 'cake_shop_store_hero_nonce'); ?>

            <label for="home_hero_image">Chọn ảnh mới</label>
            <input type="file" id="home_hero_image" name="home_hero_image" accept="image/*">

            <?php if ($current_hero_image_url) : ?>
              <div class="store-hero-preview">
                <p class="store-hero-preview__label">Ảnh hiện tại</p>
                <img src="<?php echo esc_url($current_hero_image_url); ?>" alt="Ảnh hero hiện tại">
              </div>

              <label class="store-checkbox">
                <input type="checkbox" name="remove_home_hero_image" value="1">
                Xóa ảnh hiện tại và quay về ảnh mặc định
              </label>
            <?php endif; ?>

            <div class="manager-actions">
              <button type="submit" class="btn btn-primary">Lưu ảnh hero</button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
