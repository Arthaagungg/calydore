<?php
require_once __DIR__ . '/../../database/koneksi.php';

?>
<style>
    .sidebar-link {
        text-decoration: none;
    }
</style>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/dasboard-admin/property/css/sidebar.css">
    <link href="<?php echo BASE_URL; ?>/assets/src/bootstrap-5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/src/lineicons5/regular-icon-font-free/lineicons.css" rel="stylesheet" />

</head>
<!-- Sidebar -->
<aside id="sidebar" class="sidebar-toggle ">
    <div class="sidebar-logo">
        <a class="sidebar-link" href="../../index.php">calydore</a>
    </div>
    <!-- Sidebar Navigation -->
    <ul class="sidebar-nav p-0">
        <li class="sidebar-header">
            Tools & Components
        </li>
        <li class="sidebar-item">
            <a href="index-admin.php" class="sidebar-link">
                <i class="lni lni-user"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="dashboard-icons.php" class="sidebar-link">
                <i class="lni lni-user"></i>
                <span>Dashboard Icons</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="dashboard-assets.php" class="sidebar-link">
                <i class="lni lni-user"></i>
                <span>Dashboard assets</span>
            </a>
        </li>
        <li class="sidebar-header">
            Dashboard Katalog
        </li>
        <li class="sidebar-item">
            <a href="dashboard-villa.php" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>Villa</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="dashboard-villa-kamar.php" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>Villa Kamar</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="dashboard-glamping.php" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>Glamping</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="dashboard-outbound.php" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>Outbound</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="dashboard-catering.php" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>Catering</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="dashboard-testimoni.php" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>testimoni</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="dashboard-hotel.php" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>hotel</span>
            </a>
        </li>
    </ul>
    <!-- Sidebar Navigation Ends -->
    <div class="sidebar-footer">
        <a href="../logout-admin.php" class="sidebar-link">
            <i class="lni lni-exit"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
<!-- Sidebar Ends -->