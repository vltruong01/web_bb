<?php get_header(); ?>
<?php $store_info = cake_shop_get_store_info(); ?>
<?php $map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($store_info['address']); ?>
<?php $map_embed_url = 'https://www.google.com/maps?q=' . rawurlencode($store_info['address']) . '&output=embed'; ?>

<main id="main-content" tabindex="-1">
  <section class="section">
    <div class="container">
      <div class="contact-page-heading">
        <h1>Liên hệ</h1>
        <p>Nếu bạn cần tư vấn nhanh hoặc muốn đặt bánh, cứ chọn cách liên hệ thuận tiện nhất bên dưới nhé, tiệm luôn rất vui khi được hỗ trợ bạn.</p>
      </div>

      <section class="contact-quick" aria-labelledby="contact-quick-title">
        <div>
          <p class="contact-quick__eyebrow">Đặt bánh nhanh</p>
          <h2 id="contact-quick-title">Liên hệ nhanh</h2>
          <p>Nhắn Zalo để tiệm phản hồi nhanh về mẫu bánh, giá và thời gian nhận.</p>
        </div>

        <div class="contact-quick__actions">
          <a class="btn btn-primary" href="<?php echo esc_url($store_info['zalo_link']); ?>" target="_blank" rel="noopener noreferrer">Nhắn Zalo</a>
          <a class="btn btn-secondary" href="<?php echo esc_url($store_info['facebook_link']); ?>" target="_blank" rel="noopener noreferrer">Nhắn Messenger</a>
          <a class="btn btn-secondary" href="<?php echo esc_attr(cake_shop_get_phone_href($store_info['phone'])); ?>">Gọi <?php echo esc_html($store_info['phone']); ?></a>
        </div>
      </section>

      <div class="contact-page-grid">
        <div class="card">
          <h2>Giờ mở cửa</h2>
          <p class="cake-excerpt"><?php echo esc_html($store_info['opening_hours']); ?></p>
        </div>

        <div class="card">
          <h2>Cách đặt bánh với tiệm</h2>
          <p class="cake-excerpt">
            Bạn chỉ cần gửi tên bánh, ảnh mẫu hoặc vài thông tin bạn mong muốn qua Zalo, Messenger hay điện thoại, tiệm sẽ dựa vào đó để tư vấn nhanh và gợi ý mẫu phù hợp cho bạn.
          </p>
        </div>

        <div class="card">
          <h2>Lưu ý nhỏ với bánh kem</h2>
          <p class="cake-excerpt">
            Với bánh kem, bạn nên nhắn trước một chút để tiệm có thời gian chuẩn bị mẫu, kích thước và thời gian giao hoặc nhận bánh thật phù hợp với nhu cầu của mình.
          </p>
        </div>

        <div class="card">
          <h2>Góp ý ẩn danh</h2>
          <p class="cake-excerpt">
            Nếu bạn muốn góp ý để tiệm phục vụ tốt hơn, bạn có thể gửi góp ý ẩn danh tại đây. Tiệm sẽ đọc và ghi nhận tất cả góp ý từ khách hàng.
          </p>
          <a class="btn btn-primary" href="<?php echo esc_url(home_url('/gop-y')); ?>">Gửi góp ý</a>
        </div>
      </div>

      <div class="contact-location-grid">
        <div class="card contact-address-card">
          <h2>Địa chỉ tiệm</h2>
          <p class="cake-excerpt"><?php echo esc_html($store_info['address']); ?></p>
          <a class="btn btn-secondary" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener noreferrer">Mở Google Maps</a>
        </div>

        <div class="card contact-delivery-card">
          <h2>Giao bánh</h2>
          <p class="cake-excerpt"><strong>Khu vực:</strong> <?php echo esc_html($store_info['delivery_areas']); ?></p>
          <p class="cake-excerpt"><?php echo esc_html($store_info['delivery_note']); ?></p>
        </div>
      </div>

      <section class="contact-map" aria-labelledby="contact-map-title">
        <div class="contact-map__heading">
          <h2 id="contact-map-title">Bản đồ đến tiệm</h2>
          <p>Chọn chỉ đường để mở bản đồ trên điện thoại.</p>
        </div>
        <iframe src="<?php echo esc_url($map_embed_url); ?>" title="Bản đồ đến <?php echo esc_attr($store_info['shop_name']); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </section>
    </div>
  </section>
</main>

<?php get_footer(); ?>
