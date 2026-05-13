<?php
declare(strict_types=1);

function renderLayoutStart(string $pageTitle, string $activeMenu, string $subTitle = 'AMC / Domain & Hosting Management'): void
{
    $menu = [
        'dashboard' => ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'href' => 'dashboard.php'],
        'clients' => ['label' => 'Manage Clients', 'icon' => 'fas fa-users', 'href' => 'clients.php'],
        'groups' => ['label' => 'View Clients', 'icon' => 'fas fa-layer-group', 'href' => 'groups.php'],
        'renewal' => ['label' => 'Renewals', 'icon' => 'fas fa-calendar-check', 'href' => 'renewal.php'],
        'billing' => ['label' => 'Billing', 'icon' => 'fas fa-credit-card', 'href' => 'billing.php'],
    ];
    $toastMessage = trim((string) ($_GET['toast'] ?? ''));
    $toastType = (string) ($_GET['toast_type'] ?? 'success');
    if (!in_array($toastType, ['success', 'error', 'info'], true)) {
        $toastType = 'success';
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <title><?= esc($pageTitle) ?> | AMC Admin Suite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        /* Fallback font in case Google Fonts fails to load */
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .font-fallback { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
    </style>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #0f172a; overflow-x: hidden; }
        .app-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(145deg, #0a2b3e 0%, #0a1c2a 100%);
            color: #e2e8f0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
            z-index: 20;
        }
        .sidebar-brand { padding: 28px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand h2 { font-size: 1.65rem; background: linear-gradient(135deg, #fff, #9ad0db); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .nav-menu { padding: 20px 16px; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 18px;
            margin: 6px 0;
            border-radius: 16px;
            font-weight: 500;
            color: #cbd5e6;
            text-decoration: none;
            transition: all 0.2s;
        }
        .nav-item:hover, .nav-item.active { background: #2c6e7a; color: white; }
        .nav-item i { width: 24px; }
        .main-content { flex: 1; background: #f8fafc; min-width: 0; }
        .top-header {
            background: white;
            padding: 18px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0px !important;
        }
        .page-title h1 { font-size: 1.65rem; font-weight: 600; }
        .page-title p { margin-top: 4px; color: #64748b; }
        .top-actions { display: flex; align-items: center; gap: 10px; }
        .logout-btn {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 8px 14px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover { background: #fee2e2; }
        .dashboard-container { padding: 28px 32px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: white; border-radius: 28px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #eef2ff; }
        .stat-number { font-size: 2.2rem; font-weight: 700; color: #0f2b3d; }
        .stat-label { color: #4b5563; font-size: 0.85rem; margin-top: 8px; }
        .card { background: white; border-radius: 24px; padding: 20px; border: 1px solid #eef2ff; }
        .table-wrap { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 24px; overflow: hidden; }
        .data-table th, .data-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #eef2ff; vertical-align: top; }
        .data-table th { background: #f8fafc; font-weight: 600; color: #1e293b; white-space: nowrap; }
        .badge { padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-expiring { background: #fff3e3; color: #b45309; }
        .row-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; gap: 12px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 6px; color: #1e293b; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 24px;
            font-family: inherit;
            background: white;
        }
        .btn-primary, .btn-secondary {
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary { background: #1f6e7c; color: white; }
        .btn-secondary { background: #e2e8f0; color: #1e293b; }
        .action-links { display: flex; gap: 8px; flex-wrap: wrap; }
        .action-links a { text-decoration: none; font-size: 0.85rem; color: #1f6e7c; }
        .icon-action,
        .icon-action-btn {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid #dbe4f1;
            background: #f8fafc;
            color: #1f6e7c;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .icon-action-btn { padding: 0; }
        .icon-action:hover,
        .icon-action-btn:hover {
            background: #e6f0f7;
            border-color: #bfd3e6;
        }
        .icon-danger { color: #c0392b; }
        .icon-toggle { color: #7c5a14; }
        .inline-muted { color: #64748b; font-size: 0.85rem; }
        .service-block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 10px 12px; margin: 8px 0; }
        .pagination {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .pagination a, .pagination span {
            min-width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid #dbe4f1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #1f6e7c;
            padding: 0 12px;
            font-size: 0.85rem;
            background: #f8fafc;
        }
        .pagination .active {
            background: #1f6e7c;
            color: #fff;
            border-color: #1f6e7c;
        }
        .toast-wrap {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 5000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            min-width: 260px;
            max-width: 360px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid #dbe4f1;
            background: #fff;
            color: #1e293b;
            box-shadow: 0 10px 22px rgba(2, 6, 23, 0.12);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast.success { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
        .toast.error { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
        .toast.info { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
        .toast-close {
            margin-left: auto;
            border: none;
            background: transparent;
            cursor: pointer;
            color: inherit;
            font-size: 0.95rem;
        }
        @media (max-width: 900px) {
            .app-wrapper { flex-direction: column; }
            .admin-sidebar { width: 100%; height: auto; position: relative; }
            .top-header, .dashboard-container { padding: 18px; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-brand"><h2><i class="fas fa-globe"></i> Kho Social</h2></div>
        <nav class="nav-menu">
            <?php foreach ($menu as $key => $item): ?>
                <a class="nav-item <?= $key === $activeMenu ? 'active' : '' ?>" href="<?= esc($item['href']) ?>">
                    <i class="<?= esc($item['icon']) ?>"></i> <?= esc($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <main class="main-content">
        <div class="top-header">
            <div class="page-title">
                <h1><?= esc($pageTitle) ?></h1>
                <p><?= esc($subTitle) ?></p>
            </div>
            <div class="top-actions">
                <a href="index.php" class="logout-btn" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="dashboard-container">
    <?php
}

function renderLayoutEnd(): void
{
    $toastMessage = trim((string) ($_GET['toast'] ?? ''));
    $toastType = (string) ($_GET['toast_type'] ?? 'success');
    if (!in_array($toastType, ['success', 'error', 'info'], true)) {
        $toastType = 'success';
    }
    ?>
        </div>
    </main>
</div>
<?php if ($toastMessage !== ''): ?>
    <div class="toast-wrap" id="toastWrap">
        <div class="toast <?= esc($toastType) ?>" id="appToast">
            <i class="fas <?= $toastType === 'success' ? 'fa-circle-check' : ($toastType === 'error' ? 'fa-circle-xmark' : 'fa-circle-info') ?>"></i>
            <span><?= esc($toastMessage) ?></span>
            <button type="button" class="toast-close" id="toastClose"><i class="fas fa-times"></i></button>
        </div>
    </div>
<?php endif; ?>
<script>
    (function () {
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function () {
                localStorage.removeItem('amc_admin_logged');
                localStorage.removeItem('amc_admin_email');
                sessionStorage.removeItem('amc_admin_logged');
                sessionStorage.removeItem('amc_admin_email');
            });
        }

        const appToast = document.getElementById('appToast');
        const toastClose = document.getElementById('toastClose');
        if (appToast) {
            const closeToast = function () {
                appToast.style.display = 'none';
            };
            if (toastClose) {
                toastClose.addEventListener('click', closeToast);
            }
            setTimeout(closeToast, 3500);
        }
    })();
</script>
</body>
</html>
    <?php
}
