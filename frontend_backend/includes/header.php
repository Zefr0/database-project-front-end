<?php
// Compute base path from the actually-running script's location relative
// to the application root (the directory containing index.php).
$appRoot   = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$scriptDir = str_replace('\\', '/', dirname(realpath($_SERVER['SCRIPT_FILENAME'])));
$rel       = ltrim(substr($scriptDir, strlen($appRoot)), '/');
$depth     = $rel === '' ? 0 : substr_count($rel, '/') + 1;
$base      = str_repeat('../', $depth);

// Title and subtitle can be set before including this file
$pageTitle    = $pageTitle    ?? 'IVOR Paine Memorial Hospital';
$pageSubtitle = $pageSubtitle ?? '';
$pageBadge    = $pageBadge    ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> — IVOR Paine Memorial Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>

<!-- ═══════════════ NAVBAR ═══════════════ -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid px-4">

    <a class="navbar-brand" href="<?= $base ?>index.php">
      <i class="bi bi-hospital" style="font-size:1.5rem;"></i>
      <span>
        IVOR Paine Memorial Hospital
        <span class="brand-sub">CS205 Database Lab — Spring 2026</span>
      </span>
    </a>

    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto gap-1">

        <!-- Home -->
        <li class="nav-item">
          <a class="nav-link" href="<?= $base ?>index.php">
            <i class="bi bi-house-door me-1"></i>Dashboard
          </a>
        </li>

        <!-- Forms dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-file-text me-1"></i>Forms
          </a>
          <ul class="dropdown-menu">
            <li><span class="dropdown-item text-uppercase fw-bold"
                      style="font-size:.72rem;color:rgba(255,255,255,.45);cursor:default;">
                Manual Record Forms</span></li>
            <li>
              <a class="dropdown-item" href="<?= $base ?>forms/patient_form.php">
                <i class="bi bi-person-lines-fill me-2"></i>Patient Record
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= $base ?>forms/ward_form.php">
                <i class="bi bi-building me-2"></i>Ward Record
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= $base ?>forms/doctor_form.php">
                <i class="bi bi-person-badge me-2"></i>Doctor Record
              </a>
            </li>
          </ul>
        </li>

        <!-- Reports dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bar-chart-line me-1"></i>Reports
          </a>
          <ul class="dropdown-menu">
            <li><span class="dropdown-item text-uppercase fw-bold"
                      style="font-size:.72rem;color:rgba(255,255,255,.45);cursor:default;">
                SQL Query Reports</span></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report1.php">R1 — Consultant Teams</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report2.php">R2 — Ward Sisters &amp; Staff Nurses</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report3.php">R3 — Patient Treatments</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report4.php">R4 — Junior Housemen</a></li>
            <li><div class="dropdown-divider"></div></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report5.php">R5 — Unique Specialties</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report6.php">R6 — Complaints &amp; Doctor Experience</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report7.php">R7 — Multi-Complaint Patients</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report8.php">R8 — Grouped by Treatment</a></li>
            <li><div class="dropdown-divider"></div></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report9.php">R9 — Doctor Performance History</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report10.php">R10 — Full Patient Details</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report11.php">R11 — Treatments by Date Range</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>reports/report12.php">R12 — Staff Position Count</a></li>
          </ul>
        </li>

      </ul>
    </div>
  </div>
</nav>
<!-- ════════════════════════════════════════ -->

<?php if ($pageTitle !== 'Dashboard'): ?>
<div class="page-banner">
  <div class="container-fluid px-4">
    <?php if ($pageBadge): ?>
      <span class="badge-form"><?= htmlspecialchars($pageBadge) ?></span>
    <?php endif; ?>
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    <?php if ($pageSubtitle): ?>
      <p><?= htmlspecialchars($pageSubtitle) ?></p>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="container-fluid px-4">
