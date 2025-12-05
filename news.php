<?php

$servername = "rm-d9j40fh5e5ny3vr23zo.mysql.ap-southeast-5.rds.aliyuncs.com";
$username = "pk_credit_core_pro_user";
$password = "qie7rjuzn5nvs35t3znhdk7lamsgh0@";
$dbname = "pk_credit_admin";

$dsn = "mysql:host=$servername;dbname=$dbname";

try {
    // Create PDO instance
    $conn = new PDO($dsn, $username, $password);
    // Set PDO error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Set the number of results per page
$resultsPerPage = 9;

// Get the current page or set default to 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;

// Calculate the starting row for the query
$startRow = ($page - 1) * $resultsPerPage;

// Query to count total records
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM content WHERE enabled = :enabled AND parentId = :parentId");
$totalStmt->bindValue(":enabled", 2);
$totalStmt->bindValue(":parentId", 10);
$totalStmt->execute();
$totalResults = $totalStmt->fetchColumn();

// Calculate total pages
$totalPages = ceil($totalResults / $resultsPerPage);

// Main query to fetch the results with pagination
$stmt = $conn->prepare("SELECT id, title, titleImg01 AS image, publishedAt, content FROM content WHERE enabled = :enabled AND parentId = :parentId ORDER BY sortNum DESC LIMIT :startRow, :resultsPerPage");
$stmt->bindValue(":enabled", 2);
$stmt->bindValue(":parentId", 10);
$stmt->bindValue(":startRow", $startRow, PDO::PARAM_INT);
$stmt->bindValue(":resultsPerPage", $resultsPerPage, PDO::PARAM_INT);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$maxVisiblePages = 5; // Maximum number of visible page links
$startPage = max(1, $page - floor($maxVisiblePages / 2));
$endPage = min($totalPages, $startPage + $maxVisiblePages - 1);

if ($endPage - $startPage + 1 < $maxVisiblePages) {
    $startPage = max(1, $endPage - $maxVisiblePages + 1);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pilih Kredit</title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self' https: data: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https:; img-src 'self' https: http: data: blob:; font-src 'self' https: data:;" />

    <!-- Favicons -->
    <link href="assets/img/logo_2.png" rel="icon" />
    <link href="assets/img/logo_2.png" rel="apple-touch-icon" />

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="assets/vendor/aos/aos.css" rel="stylesheet" />
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet" />
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet" />

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet" />
</head>

<body class="index-page scrolled">

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
        <section id="news" class="news section">
            <div class="container">
                <div class="section-title text-center mb-5" data-aos="fade-up">
                    <h2 data-i18n="news_header">Berita & Informasi</h2>
                    <p data-i18n="news_subheader">Dapatkan update terbaru dari Pilih Kredit</p>
                </div>
                <div class="row gy-4">
                    <?php foreach ($results as $row): ?>
                        <div class="col-md-6 col-lg-4">
                            <a href="content.php?id=<?php echo $row['id']; ?>" class="card-link">
                                <div class="card h-100 border-0 shadow-sm card-hover" style="border-radius: 18px;">
                                    <img src="<?php echo htmlspecialchars(str_replace('http://', 'https://', $row['image'])); ?>" class="card-img-top"
                                        alt="<?php echo htmlspecialchars($row['title']); ?>"
                                        style="height: 220px; object-fit: cover; border-radius: 18px 18px 0 0;">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <h5 class="card-title mb-2" style="font-weight:700;">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </h5>
                                        <p class="card-text text-muted small mb-2">
                                            <i class="bi bi-calendar-event"></i>
                                            <?php echo date('d F Y', strtotime($row['publishedAt'])); ?>
                                        </p>
                                        <span class="btn btn-outline-primary btn-sm align-self-start"
                                            data-i18n="view">Lihat</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination Controls -->
                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination justify-content-center custom-pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link rounded-circle prev-next d-flex align-items-center justify-content-center"
                                    href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link rounded-circle d-flex align-items-center justify-content-center"
                                    href="?page=1">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link rounded-circle ellipsis">…</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link rounded-circle d-flex align-items-center justify-content-center"
                                    href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link rounded-circle ellipsis">…</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link rounded-circle d-flex align-items-center justify-content-center"
                                    href="?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link rounded-circle prev-next d-flex align-items-center justify-content-center"
                                    href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
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
                            <a href="html.index#hero" class="footer-link"><i class="bi bi-chevron-right"></i>
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
    <!-- Bootstrap JS and dependencies -->
    <script src="./assets/js/jquery.min.js"></script>
    <!-- Popper.js is already included in bootstrap.bundle.min.js -->

    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>

    <script type="text/javascript">
        // Function to fetch language data
        async function fetchLanguageData(lang) {
            const response = await fetch(`languages/${lang}.json`);
            return response.json();
        }

        // Function to set the language preference
        function setLanguagePreference(lang) {
            localStorage.setItem("language", lang);
        }

        // Function to update content based on selected language
        function updateContent(langData) {
            document.querySelectorAll("[data-i18n]").forEach((element) => {
                const key = element.getAttribute("data-i18n");
                element.textContent = langData[key];
            });
        }

        // Function to change language
        async function changeLanguage(lang) {
            await setLanguagePreference(lang);

            const langData = await fetchLanguageData(lang);
            updateContent(langData);
        }

        // Call updateContent() on page load
        window.addEventListener("DOMContentLoaded", async () => {
            const userPreferredLanguage = localStorage.getItem("language") || "id";
            const langData = await fetchLanguageData(userPreferredLanguage);
            updateContent(langData);
        });

        function openContent(id) {
            window.location.href = 'content.php?id=' + id;
        }
    </script>

</body>

</html>