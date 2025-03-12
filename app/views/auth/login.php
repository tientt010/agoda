<div class="login-container">
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

            <form method="POST" action="<?php echo SITE_URL; ?>/login">
                <div class="inputBx">
                    <input type="text" name="login_id" required
                        placeholder="Email / Số điện thoại">
                </div>

                <div class="inputBx">
                    <input type="password" name="password" required
                        placeholder="Mật khẩu">
                </div>

                <div class="inputBx">
                    <input type="submit" value="Đăng nhập">
                </div>

                <div class="login-links">
                    <a href="<?php echo SITE_URL; ?>/forgot-password">Quên mật khẩu?</a>
                    <a href="<?php echo SITE_URL; ?>/register">Đăng ký</a>
                </div>
            </form>
        </div>
    </div>
</div>