<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content">Đi tới nội dung chính</a>
<?php $store_info = cake_shop_get_store_info(); ?>

<header class="site-header" data-site-header>
  <div class="container site-header__inner">
    <div class="site-branding">
      <h1 class="site-title">
        <a href="<?php echo esc_url(home_url('/')); ?>">
          <?php echo esc_html(cake_shop_get_store_field('shop_name', get_bloginfo('name'))); ?>
        </a>
      </h1>
    </div>

    <button class="site-menu-toggle" type="button" aria-controls="site-navigation" aria-expanded="false" data-menu-toggle>
      <span class="screen-reader-text">Mở menu điều hướng</span>
      <span class="site-menu-toggle__icon" aria-hidden="true"><span></span><span></span><span></span></span>
    </button>

    <nav class="site-nav" id="site-navigation" aria-label="Điều hướng chính" data-site-nav>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/')); ?>"<?php echo cake_shop_get_nav_link_attributes(); ?>>Trang chủ</a></li>
        <li><a href="<?php echo esc_url(home_url('/menu-banh')); ?>"<?php echo cake_shop_get_nav_link_attributes('menu-banh'); ?>>Menu bánh</a></li>
        <li><a href="<?php echo esc_url(home_url('/banh-kem')); ?>"<?php echo cake_shop_get_nav_link_attributes('banh-kem'); ?>>Bánh kem</a></li>
        <li><a href="<?php echo esc_url(home_url('/lien-he')); ?>"<?php echo cake_shop_get_nav_link_attributes('lien-he'); ?>>Liên hệ</a></li>
        <?php if (!is_user_logged_in() || !current_user_can('edit_posts')) : ?>
          <li><a href="<?php echo esc_url(home_url('/gop-y')); ?>"<?php echo cake_shop_get_nav_link_attributes('gop-y'); ?>>Góp ý</a></li>
        <?php endif; ?>

        <?php if (is_user_logged_in() && current_user_can('edit_posts')) : ?>
          <li class="nav-user-menu">
            <button class="nav-user-toggle" type="button" aria-label="Tài khoản quản trị" aria-expanded="false" aria-controls="nav-user-dropdown" data-user-menu-toggle>
              <span></span>
              <span></span>
              <span></span>
            </button>

            <div class="nav-user-dropdown" id="nav-user-dropdown">
              <a href="<?php echo esc_url(home_url('/quan-ly-banh')); ?>">Trang quản lý bánh</a>
              <a href="<?php echo esc_url(home_url('/quan-ly-tiem')); ?>">Quản lý tiệm</a>
              <a href="<?php echo esc_url(home_url('/gop-y-khach-hang')); ?>">Góp ý khách hàng</a>
              <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Đăng xuất</a>
            </div>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>
