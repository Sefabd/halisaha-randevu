<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SahaNet PRO - Profesyonel Halı Saha Randevu & Abonman Platformu</title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom UEFA Glassmorphism CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- UEFA Header / Top Navigation -->
<header class="uefa-header py-3 mb-4">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <!-- Brand Logo -->
            <a href="index.php" class="d-flex align-items-center text-decoration-none gap-2">
                <div class="brand-badge fs-5">
                    <i class="fa-solid fa-futbol me-1"></i> SahaNet
                </div>
                <span class="fs-4 fw-extrabold text-white brand-font">PRO</span>
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 rounded-pill ms-2 fs-7">
                    <i class="fa-solid fa-circle text-success me-1 fs-8"></i> Canlı Lig
                </span>
            </a>

            <!-- Right Quick Actions -->
            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-md-flex align-items-center gap-2 text-muted fs-7">
                    <i class="fa-solid fa-location-dot text-primary"></i>
                    <span>İstanbul / Kadıköy Saha Kompleksi</span>
                </div>
                <button class="btn btn-uefa d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#reservationModal" onclick="prepareAddModal()">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span class="d-none d-sm-inline">Yeni Randevu</span>
                </button>
            </div>
        </div>
    </div>
</header>
