<?php
// ============================================================
//  Report 9 — Performance History for a Particular Doctor
//  Input: Doctor ID
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R9 — Doctor Performance History';
$pageSubtitle = 'Enter a Doctor ID to view their full performance grade history.';
$pageBadge    = 'Report 9';
require_once '../includes/header.php';

$submitted = false;
$doctor    = null;
$grades    = [];
$notFound  = false;

$posLabels = [
    'student' => 'Student', 'jh' => 'Junior Houseman',
    'sh' => 'Senior Houseman', 'ar' => 'Assistant Registrar', 'r' => 'Registrar',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['doctor_id'])) {
    $submitted = true;
    $docID     = strtoupper(trim($_POST['doctor_id']));

    $sql_doc = "
        SELECT d.staffID, s.name AS DoctorName, d.position,
               s_con.name AS ConsultantName, con.specialty
        FROM   Doctor     d
        JOIN   Staff      s     ON d.staffID    = s.staffID
        LEFT JOIN Consultant con   ON d.consultant = con.staffID
        LEFT JOIN Staff    s_con   ON con.staffID  = s_con.staffID
        WHERE  d.staffID = ?";
    $stmt = qry($conn, $sql_doc, [[$docID, SQLSRV_PARAM_IN]]);
    if ($stmt) {
        $doctor = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (!$doctor) $notFound = true;
    }

    if ($doctor) {
        $sql_gr = "
            SELECT perfID, grade, dat
            FROM   PerformanceGrade
            WHERE  doctorID = ?
            ORDER  BY dat";
        $stmt2 = qry($conn, $sql_gr, [[$docID, SQLSRV_PARAM_IN]]);
        if ($stmt2) $grades = fetchAll($stmt2);
    }
}
?>

<!-- ── Search Form ── -->
<div class="search-card">
  <form method="POST" class="row g-3 align-items-end">
    <div class="col-auto">
      <label for="doctor_id">Doctor Staff ID</label>
      <input type="text" id="doctor_id" name="doctor_id" class="form-control"
             placeholder="e.g. D001"
             value="<?= isset($_POST['doctor_id']) ? h($_POST['doctor_id']) : '' ?>"
             style="width:160px;" maxlength="10">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn-search">
        <i class="bi bi-search me-1"></i>View Performance
      </button>
    </div>
    <?php if ($submitted): ?>
    <div class="col-auto">
      <a href="report9.php" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
    <?php endif; ?>
  </form>
  <p class="text-muted mt-2 mb-0" style="font-size:.82rem;">
    Doctor IDs range from <code>D001</code> to <code>D015</code>.
  </p>
</div>

<?php if (!$submitted): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Enter a Doctor Staff ID and click <strong>View Performance</strong> to see their grade history.
  </div>
<?php endif; ?>

<?php if ($submitted): ?>
  <?php if ($notFound): ?>
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-circle me-2"></i>
      No doctor found with ID <strong><?= h($_POST['doctor_id']) ?></strong>.
    </div>
  <?php elseif ($doctor): ?>

    <!-- Doctor info card -->
    <div class="card mb-4">
      <div class="card-header">
        <i class="bi bi-person-badge me-2"></i>Doctor Profile
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item"><label>Staff ID</label><span><?= h($doctor['staffID']) ?></span></div>
          <div class="info-item"><label>Name</label><span><?= h($doctor['DoctorName']) ?></span></div>
          <div class="info-item">
            <label>Position</label>
            <span><span class="badge-pos"><?= h($posLabels[$doctor['position']] ?? $doctor['position']) ?></span></span>
          </div>
          <div class="info-item"><label>Consultant</label><span><?= h($doctor['ConsultantName']) ?></span></div>
          <div class="info-item"><label>Specialty</label><span><?= h($doctor['specialty']) ?></span></div>
        </div>
      </div>
    </div>

    <!-- Performance grades -->
    <div class="card mb-5">
      <div class="card-header">
        <i class="bi bi-star me-2"></i>Performance Grade History
        <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;">
          <?= count($grades) ?> grade<?= count($grades) !== 1 ? 's' : '' ?>
        </span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($grades)): ?>
          <div class="no-results">No performance grades recorded for this doctor.</div>
        <?php else: ?>
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr><th>#</th><th>Grade</th><th>Date Assessed</th><th>Period</th></tr>
              </thead>
              <tbody>
                <?php foreach ($grades as $i => $g):
                  $grade = $g['grade'];
                  $color = str_starts_with($grade, 'A') ? 'var(--success)'
                         : (str_starts_with($grade, 'B') ? 'var(--accent)' : 'var(--danger)');
                ?>
                <tr>
                  <td><?= h($g['perfID']) ?></td>
                  <td>
                    <span style="font-size:1.3rem;font-weight:800;color:<?= $color ?>;">
                      <?= h($grade) ?>
                    </span>
                  </td>
                  <td><?= h($g['dat']) ?></td>
                  <td style="color:var(--muted);font-size:.82rem;">
                    <?php
                      if ($g['dat'] instanceof DateTime) {
                          $m = (int)$g['dat']->format('m');
                          echo $m <= 6 ? 'H1 (Jan–Jun)' : 'H2 (Jul–Dec)';
                          echo ' ' . $g['dat']->format('Y');
                      }
                    ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php endif; ?>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
