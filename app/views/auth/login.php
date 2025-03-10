<div class="auth-container">
    <div class="auth-form animate__animated animate__fadeIn">
        <div class="card">
            <div class="card-header text-center bg-primary text-white">
                <h3 class="mb-0">Đăng nhập</h3>
            </div>
            <div class="card-body">
                <?php if ($error = $this->getFlash('error')): ?>
                    <div class="alert alert-danger animate__animated animate__shake">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo SITE_URL; ?>/login" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập / Email / Số điện thoại</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="login_id" class="form-control" required
                                placeholder="Nhập tên đăng nhập, email hoặc số điện thoại">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3 btn-hover">Đăng nhập</button>

                    <div class="text-center">
                        <a href="<?php echo SITE_URL; ?>/register" class="btn btn-link btn-hover">Đăng ký tài khoản mới</a>
                        <a href="<?php echo SITE_URL; ?>/forgot-password" class="btn btn-link btn-hover">Quên mật khẩu?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>