<?php $store_info = cake_shop_get_store_info(); ?>
<footer class="site-footer">
  <div class="container">
    <div class="site-footer__grid">
      <div class="site-footer__col site-footer__col--brand">
        <h2 class="site-footer__title"><?php echo esc_html($store_info['shop_name']); ?></h2>
        <p class="site-footer__text">Tiệm luôn chuẩn bị những mẫu bánh xinh xắn, dễ chọn và sẵn sàng lắng nghe để bạn đặt bánh thật nhanh qua Zalo, Messenger hoặc điện thoại.</p>
      </div>


      <div class="site-footer__col">
        <h3>Liên hệ</h3>
        <ul class="site-footer__contact">
          <li><strong>Điện thoại:</strong> <a href="<?php echo esc_attr(cake_shop_get_phone_href($store_info['phone'])); ?>"><?php echo esc_html($store_info['phone']); ?></a></li>
          <li><strong>Giờ mở cửa:</strong> <?php echo esc_html($store_info['opening_hours']); ?></li>
          <li><strong>Địa chỉ:</strong> <?php echo esc_html($store_info['address']); ?></li>
          <li><a href="<?php echo esc_url($store_info['zalo_link']); ?>" target="_blank" rel="noopener noreferrer">Nhắn Zalo</a></li>
          <li><a href="<?php echo esc_url($store_info['facebook_link']); ?>" target="_blank" rel="noopener noreferrer">Nhắn Messenger</a></li>
        </ul>
      </div>
    </div>

    <div class="site-footer__bottom">
      <p>© <?php echo date('Y'); ?> <?php echo esc_html($store_info['shop_name']); ?>.</p>
    </div>
  </div>
</footer>

<div class="floating-contact">
  <a class="floating-contact__item" href="<?php echo esc_url($store_info['zalo_link']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Zalo">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/zalo.png" alt="Zalo">
  </a>

  <a class="floating-contact__item" href="<?php echo esc_url($store_info['facebook_link']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Messenger">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/messenger.png" alt="Messenger">
  </a>

  <a class="floating-contact__item" href="<?php echo esc_attr(cake_shop_get_phone_href($store_info['phone'])); ?>" aria-label="Gọi điện">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/phone.png" alt="Gọi điện">
  </a>
</div>

<?php wp_footer(); ?>
</body>
</html>
