<div class="home-page">
    <!-- Hero Section -->
    <section class="hero mb-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 mb-4 animate__animated animate__fadeInLeft">
                        Tìm và đặt khách sạn hoàn hảo
                    </h1>
                    <p class="lead mb-4 animate__animated animate__fadeInLeft animate__delay-1s">
                        Khám phá các điểm đến tuyệt vời với mức giá tốt nhất
                    </p>
                </div>
                <div class="col-md-6 animate__animated animate__fadeInRight">
                    <!-- Search Form -->
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="<?php echo SITE_URL; ?>" method="GET">
                                <div class="mb-3">
                                    <label class="form-label">Điểm đến</label>
                                    <input type="text" name="city" class="form-control"
                                        value="<?php echo $filters['city']; ?>"
                                        placeholder="Nhập thành phố, khu vực">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nhận phòng</label>
                                        <input type="date" name="check_in" class="form-control"
                                            value="<?php echo $filters['check_in']; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Trả phòng</label>
                                        <input type="date" name="check_out" class="form-control"
                                            value="<?php echo $filters['check_out']; ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Số khách</label>
                                        <select name="guests" class="form-select">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <option value="<?php echo $i; ?>"
                                                    <?php echo $filters['guests'] == $i ? 'selected' : ''; ?>>
                                                    <?php echo $i; ?> khách
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Xếp hạng</label>
                                        <select name="star_rating" class="form-select">
                                            <option value="">Tất cả</option>
                                            <?php for ($i = 5; $i >= 3; $i--): ?>
                                                <option value="<?php echo $i; ?>"
                                                    <?php echo $filters['star_rating'] == $i ? 'selected' : ''; ?>>
                                                    <?php echo $i; ?> sao
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Tìm kiếm
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Cities -->
    <section class="popular-cities mb-5">
        <div class="container">
            <h2 class="section-title mb-4">Điểm đến phổ biến</h2>
            <div class="row">
                <?php foreach ($popularCities as $city): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card city-card animate__animated animate__fadeIn">
                            <img src="<?php echo $city['image']; ?>" class="card-img-top" alt="<?php echo $city['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $city['name']; ?></h5>
                                <p class="card-text"><?php echo $city['hotel_count']; ?> khách sạn</p>
                                <a href="<?php echo SITE_URL . '?city=' . urlencode($city['name']); ?>"
                                    class="btn btn-outline-primary">Khám phá</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Hotels -->
    <section class="featured-hotels mb-5">
        <div class="container">
            <h2 class="section-title mb-4">Khách sạn nổi bật</h2>
            <div class="row">
                <?php foreach ($featuredHotels as $hotel): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card hotel-card animate__animated animate__fadeIn">
                            <img src="<?php echo $hotel['image']; ?>" class="card-img-top" alt="<?php echo $hotel['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $hotel['name']; ?></h5>
                                <div class="hotel-rating mb-2">
                                    <?php for ($i = 0; $i < $hotel['star_rating']; $i++): ?>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text"><?php echo $hotel['address']; ?></p>
                                <p class="price">Từ <?php echo number_format($hotel['min_price']); ?> VND/đêm</p>
                                <a href="<?php echo SITE_URL . '/hotels/' . $hotel['id']; ?>"
                                    class="btn btn-primary">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Promotions -->
    <section class="promotions mb-5">
        <div class="container">
            <h2 class="section-title mb-4">Ưu đãi đặc biệt</h2>
            <div class="row">
                <?php foreach ($promotions as $promo): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card promo-card animate__animated animate__fadeIn">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $promo['title']; ?></h5>
                                <p class="card-text"><?php echo $promo['description']; ?></p>
                                <div class="promo-code">
                                    Mã: <strong><?php echo $promo['code']; ?></strong>
                                </div>
                                <div class="promo-expires">
                                    Hết hạn: <?php echo date('d/m/Y', strtotime($promo['expires'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>