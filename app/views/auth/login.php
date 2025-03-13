<div class="login-container row">
    <!-- Login form column (left) - 1/3 width -->
    <div class="form-column">
        <div class="ring">
            <i style="--clr:#00ff0a"></i>
            <i style="--clr:#ff0057"></i>
            <i style="--clr:#fffd44"></i>

            <div class="login-form animate__animated animate__fadeIn">
                <h2>Đăng nhập</h2>

                <?php if ($error = $this->getFlash('error')): ?>
                    <div class="alert alert-danger animate__animated animate__shake">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success = $this->getFlash('success')): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo SITE_URL; ?>/login">
                    <div class="inputBx">
                        <input type="text" name="login_id" placeholder="Email hoặc tên đăng nhập" required>
                    </div>
                    <div class="inputBx">
                        <input type="password" name="password" placeholder="Mật khẩu" required>
                    </div>
                    <div class="inputBx">
                        <input type="submit" value="Đăng nhập">
                    </div>
                    <div class="login-links">
                        <a href="<?php echo SITE_URL; ?>/forgot-password">Quên mật khẩu?</a>
                        <a href="<?php echo SITE_URL; ?>/register">Tạo tài khoản</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Brand logo column (right) - 2/3 width -->
    <div class="brand-column">
        <div class="brand-content">
            <img src="<?php echo SITE_URL; ?>/public/images/logo/logo.png" alt="Hotel Booking Logo" class="brand-logo">
            <h1 class="brand-title">LITTLE GONE</h1>
            <p class="brand-tagline">Đặt phòng khách sạn dễ dàng, nhanh chóng và tiện lợi</p>

            <!-- Thêm một số nội dung quảng cáo -->
            <div class="brand-features">
                <div class="feature-item">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Hơn 1 triệu khách sạn trên toàn thế giới</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-star-fill"></i>
                    <span>Giá tốt nhất được đảm bảo</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-shield-check"></i>
                    <span>Đặt phòng an toàn và bảo mật</span>
                </div>
            </div>
        </div>
    </div>
</div>