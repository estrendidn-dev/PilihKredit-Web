<?php

// Load database configuration
$dbConfig = require __DIR__ . '/config/database_config.php';

$host = $dbConfig['host'];
$database = $dbConfig['database'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];
$charset = $dbConfig['charset'] ?? 'utf8mb4';

// Create PDO connection
$dsn = "mysql:host=$host;dbname=$database;charset=$charset";
try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get the content ID from the URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    // Prepare and execute the query to fetch content by ID
    $stmt = $conn->prepare("SELECT title, content, publishedAt, titleImg01 AS image FROM content WHERE id = :id AND enabled = 2");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the content
    $content = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$content) {
        die("Content not found or disabled.");
    }
} else {
    die("Invalid content ID.");
}

// Function to remove unwanted tags, including <img>
function cleanContent($html)
{
    // Remove <head> tag and its content
    $html = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $html);

    // Remove <meta> tags
    $html = preg_replace('/<meta[^>]*>/is', '', $html);

    // Remove <style> tags and their content
    $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);

    // Remove <img> tags
    $html = preg_replace('/<img[^>]*>/is', '', $html);

    return $html;
}

// Clean the content, removing <img> tags
$cleanedContent = cleanContent($content['content']);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Pilih Kredit</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self' https: data: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https:; img-src 'self' https: http: data: blob:; font-src 'self' https: data:;">

    <!-- Favicons -->
    <link href="assets/img/logo_2.png" rel="icon">
    <link href="assets/img/logo_2.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

</head>

<body class="index-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <img src="assets/img/logo_filled.png" alt="Pilih Kredit Logo" width="270" height="400" />
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li>
                        <a href="index.html#hero" class="nav-link" data-i18n="home_nav"></a>
                    </li>
                    <li>
                        <a href="index.html#features" class="nav-link" data-i18n="feature_nav"></a>
                    </li>
                    <li><a href="index.html#faq" class="nav-link" data-i18n="faq_nav"></a></li>
                    <li>
                        <a href="index.html#about" class="nav-link" data-i18n="aboutus_nav"></a>
                    </li>
                    <li>
                        <a href="news.php" class="active nav-link" data-i18n="news_nav"></a>
                    </li>
                    <li class="language-selector">
                        <img class="indonesia flag-icon" src="assets/img/flag-indo.png" height="28" width="28"
                            onclick="changeLanguage('id')" alt="Indonesia" />
                        <img class="english flag-icon" src="assets/img/flag-usa.png" height="28" width="28"
                            onclick="changeLanguage('en')" alt="English" />
                    </li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </header>

    <main class="main">
        <section id="content-news" class="content-news d-flex align-items-center justify-content-center"
            style="padding-top:120px; padding-bottom:40px; min-height:60vh;">
            <div class="container content-container" style="max-width:700px;">
                <!-- Back Button -->
                <div class="mb-4 d-flex justify-content-start">
                    <a href="javascript:history.back()"
                        class="btn btn-back btn-lg rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;">
                        <i class="bi bi-arrow-left" style="font-size:1.5rem;"></i>
                    </a>
                </div>

                <!-- Title and Meta Information -->
                <h1 class="content-title mb-3 text-center"><?php echo htmlspecialchars($content['title']); ?></h1>
                <div class="text-muted mb-4 text-center">
                    <span class="author"><?php echo htmlspecialchars($content['author']); ?></span>
                    <span class="date"><?php echo date('d F Y', strtotime($content['publishedAt'])); ?></span>
                </div>

                <!-- Image and Content -->
                <div class="row justify-content-center">
                    <div class="col-12">
                        <?php if (!empty($content['image'])): ?>
                            <img src="<?php echo htmlspecialchars(str_replace('http://', 'https://', $content['image'])); ?>"
                                class="img-fluid rounded content-image mb-4 mx-auto d-block"
                                alt="<?php echo htmlspecialchars($content['title']); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <div class="content-text">
                            <?php echo $cleanedContent; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer id="footer" class="footer footer-background">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h3 class="footer-title mb-4">PT. Estrend Teknologi Digital</h3>

                    <div class="footer-address mb-4">
                        <p class="mb-2">
                            <i class="bi bi-geo-alt-fill"></i> Soho Capital, 19th Floor
                        </p>
                        <p class="mb-0">
                            Jalan Letjend S Parman kav 28, RT.3/RW.5, Tj. Duren Sel., Kec.
                            Grogol petamburan, Kota Jakarta Barat 11470
                        </p>
                    </div>

                    <!-- Map image and link -->
                    <div class="footer-map mb-4">
                        <a href="https://maps.app.goo.gl/Kkg3nk6FMwU4TS6r6" target="_blank" rel="noopener">
                            <img src="assets/img/map.png" alt="Soho Capital Map" style="
                    width: 100%;
                    max-width: 260px;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0, 98, 204, 0.1);
                    margin-bottom: 8px;
                  " />
                            <div style="
                    font-size: 14px;
                    color: #fff;
                    text-decoration: underline;
                    display: inline-block;
                    margin-top: 2px;
                  "></div>
                        </a>
                    </div>

                    <div class="footer-contact mb-4">
                        <p class="mb-2">
                            <i class="bi bi-envelope-fill"></i>
                            <a href="mailto:cs@pilihkredit.id" class="footer-link">cs@pilihkredit.id</a>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-whatsapp"></i>
                            <a href="https://api.whatsapp.com/send?phone=6285117767799" target="_blank"
                                class="footer-link">085117767799</a>
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h3 class="footer-title mb-4" data-i18n="useful_links">
                        Useful Links
                    </h3>
                    <ul class="footer-links">
                        <li>
                            <a href="index.html#hero" class="footer-link"><i class="bi bi-chevron-right"></i>
                                <span data-i18n="home_nav"></span></a>
                        </li>
                        <li>
                            <a href="index.html#features" class="footer-link"><i class="bi bi-chevron-right"></i>
                                <span data-i18n="feature_nav"></span></a>
                        </li>
                        <li>
                            <a href="index.html#faq" class="footer-link"><i class="bi bi-chevron-right"></i>
                                <span data-i18n="faq_nav"></span></a>
                        </li>
                        <li>
                            <a href="index.html#about" class="footer-link"><i class="bi bi-chevron-right"></i>
                                <span data-i18n="aboutus_nav"></span></a>
                        </li>
                        <li>
                            <a href="news.php" class="footer-link"><i class="bi bi-chevron-right"></i>
                                <span data-i18n="news_nav"></span></a>
                        </li>
                        <li>
                            <a href="./Privasi/ketentuan.html" class="footer-link"><i class="bi bi-chevron-right"></i>
                                <span data-i18n="terms_and_conditions"></span></a>
                        </li>
                        <li>
                            <a href="./Privasi/privasi.html" class="footer-link"><i class="bi bi-chevron-right"></i>
                                <span data-i18n="privacy_policy"></span></a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-4 mb-4 mb-md-0">
                    <h3 class="footer-title mb-4" data-i18n="download_app">
                        Download App
                    </h3>
                    <div class="download-badges mb-4">
                        <a href="https://play.google.com/store/apps/details?id=com.pilihid.kreditid.pilihkredit"
                            target="_blank" class="d-inline-block mb-2 me-3">
                            <img src="assets/img/google.png" height="60" width="180" alt="Google Play"
                                class="download-badge" />
                        </a>
                        <a class="d-inline-block mb-2">
                            <img src="assets/img/apple.png" height="60" width="180" alt="App Store"
                                class="download-badge" />
                        </a>
                    </div>
                </div>
            </div>

            <div class="social-media-bar text-center mb-4">
                <h4 class="mb-3" data-i18n="follow_us">Follow Us</h4>
                <div class="social-links d-flex justify-content-center gap-3">
                    <a href="https://www.facebook.com/PilihKredit.id/" target="_blank" class="social-btn"><i
                            class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/pilihkredit.id/" target="_blank" class="social-btn"><i
                            class="bi bi-instagram"></i></a>
                    <!-- <a href="#" class="social-btn"><i class="bi bi-twitter-x"></i></a> -->
                    <a href="https://www.linkedin.com/company/pilihkredit/" target="_blank" class="social-btn"><i
                            class="bi bi-linkedin"></i></a>
                    <a href="https://api.whatsapp.com/send?phone=6285117767799" target="_blank" class="social-btn"><i
                            class="bi bi-whatsapp"></i></a>
                </div>
            </div>

            <div class="attention-section py-4">
                <h3 class="text-center mb-4" data-i18n="attention_header"></h3>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="attention-list">
                            <li data-i18n="attention1"></li>
                            <li data-i18n="attention2"></li>
                            <li data-i18n="attention3"></li>
                            <li data-i18n="attention4"></li>
                            <li data-i18n="attention5"></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="attention-list">
                            <li data-i18n="attention6"></li>
                            <li data-i18n="attention7"></li>
                            <li data-i18n="attention8"></li>
                            <li data-i18n="attention9"></li>
                            <li data-i18n="attention10"></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="copyright-section text-center py-4">
                <div class="mb-3">
                    <a href="./Privasi/ketentuan.html" target="_blank" class="footer-link mx-2"
                        data-i18n="terms_and_conditions"></a>
                    <span class="separator">|</span>
                    <a href="./Privasi/privasi.html" target="_blank" class="footer-link mx-2"
                        data-i18n="privacy_policy"></a>
                </div>
                <p class="mb-0">
                    <span data-i18n="footer1"></span>
                    <span data-i18n="footer2"></span>
                    <span>&copy; 2025</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
    <link href="./assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>