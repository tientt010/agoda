<header>
    <nav>
        <div class="container">
            <a href="<?php echo SITE_URL; ?>" class="brand">Hotel Booking</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Xin chào, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="<?php echo SITE_URL; ?>/user/profile">Tài khoản</a>
                    <a href="<?php echo SITE_URL; ?>/logout">Đăng xuất</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login">Đăng nhập</a>
                    <a href="<?php echo SITE_URL; ?>/register">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>