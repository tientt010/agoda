<div class="login-container row">
    <!-- Registration form column (left) - 1/3 width -->
    <div class="form-column">
        <div class="ring">
            <i style="--clr:#00ff0a"></i>
            <i style="--clr:#ff0057"></i>
            <i style="--clr:#fffd44"></i>

            <div class="register-form animate__animated animate__fadeIn">
                <h2>Đăng ký</h2>

                <?php if ($error = $this->getFlash('error')): ?>
                    <div class="alert alert-danger animate__animated animate__shake">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo SITE_URL; ?>/register">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="inputBx">
                                <input type="text" name="full_name" required
                                    placeholder="Họ và tên"
                                    value="<?php echo isset($data['full_name']) ? $data['full_name'] : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="inputBx">
                                <input type="email" name="email" required
                                    placeholder="Email"
                                    value="<?php echo isset($data['email']) ? $data['email'] : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="inputBx">
                        <input type="text" name="username" required
                            placeholder="Tên đăng nhập"
                            value="<?php echo isset($data['username']) ? $data['username'] : ''; ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="inputBx">
                                <input type="password" name="password" required
                                    placeholder="Mật khẩu">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="inputBx">
                                <input type="password" name="password_confirmation" required
                                    placeholder="Xác nhận mật khẩu">
                            </div>
                        </div>
                    </div>

                    <div class="inputBx">
                        <input type="tel" name="phone"
                            placeholder="Số điện thoại"
                            value="<?php echo isset($data['phone']) ? $data['phone'] : ''; ?>">
                    </div>

                    <div class="inputBx">
                        <input type="submit" value="Đăng ký">
                    </div>

                    <div class="login-links">
                        <a href="<?php echo SITE_URL; ?>/login">Đã có tài khoản? Đăng nhập</a>
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
            <p class="brand-tagline">Đăng ký để trải nghiệm dịch vụ đặt phòng tốt nhất</p>

            <!-- Thêm một số nội dung quảng cáo -->
            <div class="brand-features">
                <div class="feature-item">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Đăng ký miễn phí, không phí ẩn</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-gift-fill"></i>
                    <span>Nhận ưu đãi độc quyền cho thành viên</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-lightning-fill"></i>
                    <span>Đặt phòng nhanh chóng với 1-click</span>
                </div>
            </div>
        </div>
    </div>
</div>