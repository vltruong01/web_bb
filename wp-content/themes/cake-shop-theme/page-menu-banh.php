<?php get_header(); ?>

<main id="main-content" tabindex="-1">
  <section class="section">
    <div class="container">
      <div class="cake-page-heading">
        <h1>Menu bánh</h1>
        <p>Những mẫu bánh có sẵn hoặc bán trong ngày để bạn dễ lựa chọn. Bạn có thể nhấp vào từng bánh để xem kỹ hơn hình ảnh, thông tin chi tiết.</p>
      </div>

      <form class="cake-search-form cake-search-form--live" data-cake-client-search-form>
        <label class="screen-reader-text" for="cake-search-menu">Tìm bánh trong Menu bánh</label>
        <input
          id="cake-search-menu"
          type="search"
          placeholder="Tìm bánh theo tên..."
          autocomplete="off"
          data-cake-client-search-input
        >
      </form>

      <div class="cake-filter-chips" aria-label="Lọc menu bánh" data-cake-filter-group>
        <button type="button" class="is-active" data-cake-filter="">Tất cả</button>
        <button type="button" data-cake-filter="co-san">Có sẵn</button>
        <button type="button" data-cake-filter="tam-het">Tạm hết</button>
      </div>

      <div class="cards">
        <?php
        $menu_banh_query = new WP_Query([
          'post_type'      => 'cake',
          'posts_per_page' => -1,
          'meta_query'     => [
            [
              'key'     => '_trang_thai_banh',
              'value'   => 'an',
              'compare' => '!=',
            ]
          ],
          'tax_query'      => [
            [
              'taxonomy' => 'cake_category',
              'field'    => 'slug',
              'terms'    => 'menu-banh',
            ]
          ]
        ]);

        if (!empty($menu_banh_query->posts)) {
          $priority_posts = [];
          $normal_posts   = [];

          foreach ($menu_banh_query->posts as $cake_post) {
            $cake_highlight = get_post_meta($cake_post->ID, '_cake_highlight', true);

            if (in_array($cake_highlight, ['moi', 'hot'], true) && count($priority_posts) < 3) {
              $priority_posts[] = $cake_post;
            } else {
              $normal_posts[] = $cake_post;
            }
          }

          $menu_banh_query->posts = array_merge($priority_posts, $normal_posts);
        }


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
            <div class="card" data-cake-search-card data-cake-title="<?php echo esc_attr(wp_strip_all_tags(get_the_title())); ?>" data-cake-status="<?php echo esc_attr($trang_thai_banh); ?>">
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

              <?php if ($highlight_label || $trang_thai_label) : ?>
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
              <?php endif; ?>

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
          echo '<p>Tiệm đang lựa thêm những mẫu bánh xinh cho mục này, bạn ghé lại sau hoặc nhắn tiệm để được gợi ý nhanh nhé.</p>';
        endif;
        ?>
      </div>


      <p class="cake-search-empty" data-cake-search-empty hidden>Không có bánh phù hợp. Bạn thử nhập từ khóa khác nhé.</p>

      <nav class="cake-pagination" aria-label="Phân trang danh sách bánh" data-cake-client-pagination></nav>
    </div>
  </section>
</main>

<script>
(function () {
  const searchInput = document.querySelector('[data-cake-client-search-input]');
  const searchForm = document.querySelector('[data-cake-client-search-form]');
  const cards = Array.from(document.querySelectorAll('[data-cake-search-card]'));
  const emptyMessage = document.querySelector('[data-cake-search-empty]');
  const pagination = document.querySelector('[data-cake-client-pagination]');
  const filterButtons = Array.from(document.querySelectorAll('[data-cake-filter]'));
  const perPage = 24;
  let currentPage = 1;
  let activeStatus = '';

  if (!searchInput || !pagination || !cards.length) {
    if (pagination) pagination.hidden = true;
    return;
  }

  function normalizeText(value) {
    return (value || '')
      .toString()
      .toLocaleLowerCase('vi-VN')
      .trim();
  }

  function getFilteredCards() {
    const keyword = normalizeText(searchInput.value);

    return cards.filter(function (card) {
      const matchesKeyword = !keyword || normalizeText(card.getAttribute('data-cake-title')).includes(keyword);
      const matchesStatus = !activeStatus || card.getAttribute('data-cake-status') === activeStatus;
      return matchesKeyword && matchesStatus;
    });
  }

  function createPageButton(label, page, isCurrent) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'page-numbers' + (isCurrent ? ' current' : '');
    button.textContent = label;
    button.addEventListener('click', function () {
      currentPage = page;
      renderCards();
      const heading = document.querySelector('.cake-page-heading');
      if (heading) {
        heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
    return button;
  }

  function renderPagination(totalPages) {
    pagination.innerHTML = '';

    if (totalPages <= 1) {
      pagination.hidden = true;
      return;
    }

    pagination.hidden = false;

    if (currentPage > 1) {
      pagination.appendChild(createPageButton('‹ Trước', currentPage - 1, false));
    }

    for (let page = 1; page <= totalPages; page++) {
      if (page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1) {
        pagination.appendChild(createPageButton(String(page), page, page === currentPage));
      } else if (page === currentPage - 2 || page === currentPage + 2) {
        const dots = document.createElement('span');
        dots.className = 'page-numbers dots';
        dots.textContent = '…';
        pagination.appendChild(dots);
      }
    }

    if (currentPage < totalPages) {
      pagination.appendChild(createPageButton('Sau ›', currentPage + 1, false));
    }
  }

  function renderCards() {
    const filteredCards = getFilteredCards();
    const keyword = normalizeText(searchInput.value);
    const totalPages = Math.max(1, Math.ceil(filteredCards.length / perPage));

    if (currentPage > totalPages) {
      currentPage = totalPages;
    }

    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const visibleCards = new Set(filteredCards.slice(start, end));

    cards.forEach(function (card) {
      card.style.display = visibleCards.has(card) ? '' : 'none';
    });

    if (emptyMessage) {
      emptyMessage.hidden = !(keyword && filteredCards.length === 0);
    }

    renderPagination(totalPages);
  }

  searchInput.addEventListener('input', function () {
    currentPage = 1;
    renderCards();
  });

  filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      activeStatus = button.getAttribute('data-cake-filter') || '';
      currentPage = 1;
      filterButtons.forEach(function (item) { item.classList.toggle('is-active', item === button); });
      renderCards();
    });
  });

  if (searchForm) {
    searchForm.addEventListener('submit', function (event) {
      event.preventDefault();
    });
  }

  renderCards();
})();
</script>

<?php cake_shop_render_lightbox_markup(); ?>

<?php get_footer(); ?>
