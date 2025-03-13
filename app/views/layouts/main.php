<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Thêm meta tags kiểm soát cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Hotel Booking' : 'Hotel Booking'; ?></title>

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

    <!-- Custom CSS - thêm version parameter cho đảm bảo không bị cache -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/auth.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <!-- Luôn sử dụng container-fluid để đảm bảo chiều rộng tối đa -->
    <main class="container-fluid">
        <?php echo $content; ?>
    </main>

    <?php require_once __DIR__ . '/footer.php'; ?>

    <!-- Bootstrap JS và Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>