<?php
// ============================================================
//  Form 3 — Doctor Record
//  Input: Staff No (Doctor ID e.g. D001)
//  Output: Doctor header + Past Experience + Performance Grades
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'Doctor Record';
$pageSubtitle = 'Enter a Staff Number (e.g. D001) to view the doctor\'s record and performance history.';
$pageBadge    = 'Form 3';
require_once '../includes/header.php';

$submitted  = false;
$doctor     = null;
$experience = [];
$grades     = [];
$notFound   = false;

// Position labels
$posLabels = [
    'student' => 'Student',
    'jh'      => 'Junior Houseman',
    'sh'      => 'Senior Houseman',
    'ar'      => 'Assistant Registrar',
    'r'       => 'Registrar',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_no'])) {
    $submitted = true;
    $staffNo   = strtoupper(trim($_POST['staff_no']));

    if ($staffNo === '') {
        echo '<div class="alert alert-warning">Please enter a Staff Number.</div>';
        $submitted = false;
    } else {
        // ── Doctor header ──────────────────────────────────────
        $sql_doc = "
            SELECT d.staffID, s.name AS DoctorName, d.position,
                   s_con.name AS ConsultantName, con.staffID AS ConsultantID,
                   con.specialty
            FROM   Doctor     d
            JOIN   Staff      s     ON d.staffID    = s.staffID
            LEFT JOIN Consultant con   ON d.consultant = con.staffID
            LEFT JOIN Staff    s_con   ON con.staffID  = s_con.staffID
            WHERE  d.staffID = ?";
        $stmt = qry($conn, $sql_doc, [[$staffNo, SQLSRV_PARAM_IN]]);
        if ($stmt) {
            $doctor = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$doctor) $notFound = true;
        }

        if ($doctor) {
            // ── Past Experience ────────────────────────────────
            $sql_exp = "
                SELECT expID, dateStart, dateEnd, position, establishment
                FROM   PastExperience
                WHERE  doctorID = ?
                ORDER  BY dateStart";
            $stmt2 = qry($conn, $sql_exp, [[$staffNo, SQLSRV_PARAM_IN]]);
            if ($stmt2) $experience = fetchAll($stmt2);

            // ── Performance Grades ─────────────────────────────
            $sql_gr = "
                SELECT perfID, grade, dat
                FROM   PerformanceGrade
                WHERE  doctorID = ?
                ORDER  BY dat";
            $stmt3 = qry($conn, $sql_gr, [[$staffNo, SQLSRV_PARAM_IN]]);
            if ($stmt3) $grades = fetchAll($stmt3);
        }
    }
}
?>

<!-- ── Search Form ── -->
<div class="search-card">
  <form method="POST" class="row g-3 align-items-end">
    <div class="col-auto">
      <label for="staff_no">Staff Number</label>
      <input type="text" id="staff_no" name="staff_no" class="form-control"
             placeholder="e.g. D001"
             value="<?= isset($_POST['staff_no']) ? h($_POST['staff_no']) : '' ?>"
             style="width:160px;" maxlength="10">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn-search">
        <i class="bi bi-search me-1"></i>Look Up Doctor
      </button>
    </div>
    <?php if ($submitted && $doctor): ?>
    <div class="col-auto">
      <a href="doctor_form.php" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
    <?php endif; ?>
  </form>
  <p class="text-muted mt-2 mb-0" style="font-size:.82rem;">
    Doctor IDs are in the format <code>D001</code>–<code>D015</code>.
    Use the <a href="../reports/report1.php">Consultant Teams report</a> to find doctor IDs.
  </p>
</div>

<?php if (!$submitted): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Enter a Doctor Staff Number above and click <strong>Look Up Doctor</strong> to view
    their record, past experience, and performance grade history.
  </div>
<?php endif; ?>

<?php if ($submitted): ?>

  <?php if ($notFound): ?>
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-circle me-2"></i>
      No doctor found with Staff No <strong><?= h($_POST['staff_no']) ?></strong>.
      Note: Consultant IDs (C001–C010) are not doctor records.
    </div>

  <?php elseif ($doctor): ?>

    <!-- ════════════════════════════════════════
         DOCTOR RECORD HEADER
         ════════════════════════════════════════ -->
    <div class="card mb-4">
      <div class="card-header">
        <div>IVOR PAINE MEMORIAL HOSPITAL &mdash; DOCTOR RECORD</div>
        <div class="card-subtitle">Staff No: <?= h($doctor['staffID']) ?></div>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item">
            <label>Staff No</label>
            <span><?= h($doctor['staffID']) ?></span>
          </div>
          <div class="info-item">
            <label>Doctor Name</label>
            <span><?= h($doctor['DoctorName']) ?></span>
          </div>
          <div class="info-item">
            <label>Position</label>
            <span>
              <span class="badge-pos"><?= h($posLabels[$doctor['position']] ?? $doctor['position']) ?></span>
              <small class="text-muted ms-1">(<?= h($doctor['position']) ?>)</small>
            </span>
          </div>
          <div class="info-item">
            <label>Consultant ID</label>
            <span><?= h($doctor['ConsultantID']) ?></span>
          </div>
          <div class="info-item">
            <label>Consultant Name</label>
            <span><?= h($doctor['ConsultantName']) ?></span>
          </div>
          <div class="info-item">
            <label>Specialty</label>
            <span><?= h($doctor['specialty']) ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-5">

      <!-- ══════════════════════════════════
           PAST EXPERIENCE TABLE
           ══════════════════════════════════ -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            <i class="bi bi-briefcase me-2"></i>Past Experience
            <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;">
              <?= count($experience) ?> record<?= count($experience) !== 1 ? 's' : '' ?>
            </span>
          </div>
          <div class="card-body p-0">
            <?php if (empty($experience)): ?>
              <div class="no-results">
                <i class="bi bi-briefcase fs-1"></i><br>
                No past experience records found.
              </div>
            <?php else: ?>
              <div class="table-wrapper">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Establishment</th>
                      <th>Position</th>
                      <th>Date Start</th>
                      <th>Date End</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($experience as $exp): ?>
                    <tr>
                      <td><?= h($exp['expID']) ?></td>
                      <td><?= h($exp['establishment']) ?></td>
                      <td>
                        <span class="badge-pos">
                          <?= h($posLabels[$exp['position']] ?? $exp['position']) ?>
                        </span>
                      </td>
                      <td><?= h($exp['dateStart']) ?></td>
                      <td>
                        <?php if ($exp['dateEnd'] === null): ?>
                          <span class="ongoing">Current</span>
                        <?php else: ?>
                          <span class="ended"><?= h($exp['dateEnd']) ?></span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════
           PERFORMANCE GRADES TABLE
           ══════════════════════════════════ -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            <i class="bi bi-star me-2"></i>Performance Grade History
            <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;">
              <?= count($grades) ?> record<?= count($grades) !== 1 ? 's' : '' ?>
            </span>
          </div>
          <div class="card-body p-0">
            <?php if (empty($grades)): ?>
              <div class="no-results">
                <i class="bi bi-star fs-1"></i><br>
                No performance grades recorded yet.
              </div>
            <?php else: ?>
              <div class="table-wrapper">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Grade</th>
                      <th>Date Assessed</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($grades as $g): ?>
                    <?php
                      $grade = $g['grade'];
                      $color = str_starts_with($grade, 'A') ? '#27ae60'
                             : (str_starts_with($grade, 'B') ? '#2980b9'
                             : '#c0392b');
                    ?>
                    <tr>
                      <td><?= h($g['perfID']) ?></td>
                      <td>
                        <span style="font-weight:800;font-size:1.1rem;color:<?= $color ?>;">
                          <?= h($grade) ?>
                        </span>
                      </td>
                      <td><?= h($g['dat']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div><!-- /row -->

  <?php endif; ?>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
