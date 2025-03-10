<div class="auth-container">
    <div class="auth-form animate__animated animate__fadeIn">
        <div class="card">
            <div class="card-header text-center bg-primary text-white">
                <h3 class="mb-0">Đăng ký tài khoản</h3>
            </div>
            <div class="card-body">
                <?php if ($error = $this->getFlash('error')): ?>
                    <div class="alert alert-danger animate__animated animate__shake">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo SITE_URL; ?>/register" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="full_name" class="form-control" required
                                value="<?php echo isset($data['full_name']) ? $data['full_name'] : ''; ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required
                                value="<?php echo isset($data['email']) ? $data['email'] : ''; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" name="username" class="form-control" required
                            value="<?php echo isset($data['username']) ? $data['username'] : ''; ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword2">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" name="phone" class="form-control"
                            value="<?php echo isset($data['phone']) ? $data['phone'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="3"><?php echo isset($data['address']) ? $data['address'] : ''; ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3 btn-hover">Đăng ký</button>

                    <div class="text-center">
                        <a href="<?php echo SITE_URL; ?>/login" class="btn btn-link btn-hover">Đã có tài khoản? Đăng nhập</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>