<?php
// ============================================================
//  Report 10 — Full Medical Details for a Particular Patient
//  Input: Patient No
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R10 — Full Patient Details';
$pageSubtitle = 'Enter a Patient Number to view complete medical details.';
$pageBadge    = 'Report 10';
require_once '../includes/header.php';

$submitted  = false;
$patient    = null;
$treatments = [];
$notFound   = false;

$posLabels = [
    'student' => 'Student', 'jh' => 'Junior Houseman',
    'sh' => 'Senior Houseman', 'ar' => 'Assistant Registrar', 'r' => 'Registrar',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_no'])) {
    $submitted = true;
    $pno = trim($_POST['patient_no']);

    if ($pno === '' || !ctype_digit($pno)) {
        echo '<div class="alert alert-warning">Please enter a valid numeric Patient Number.</div>';
        $submitted = false;
    } else {
        $pno = (int)$pno;

        // Full patient info
        $sql_pat = "
            SELECT p.patientNO,
                   p.name       AS PatientName,
                   p.dob,
                   p.doA,
                   cu.unitNo    AS CareUnit,
                   cu.ward      AS Ward,
                   p.bed        AS BedNo,
                   s_doc.name   AS PrimaryDoctor,
                   d.staffID    AS DoctorID,
                   d.position,
                   s_con.name   AS Consultant,
                   con.staffID  AS ConsultantID,
                   con.specialty
            FROM   Patient      p
            JOIN   CareUnit     cu    ON p.careUnit      = cu.unitNo
            JOIN   Doctor       d     ON p.primaryDoctor = d.staffID
            JOIN   Staff        s_doc ON d.staffID       = s_doc.staffID
            LEFT JOIN Consultant con    ON d.consultant  = con.staffID
            LEFT JOIN Staff      s_con  ON con.staffID   = s_con.staffID
            WHERE  p.patientNO = ?";
        $stmt = qry($conn, $sql_pat, [[$pno, SQLSRV_PARAM_IN]]);
        if ($stmt) {
            $patient = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$patient) $notFound = true;
        }

        if ($patient) {
            $sql_tr = "
                SELECT c.complainID,
                       c.descr      AS Complaint,
                       t.treatID,
                       t.descr      AS Treatment,
                       ptp.startDate,
                       ptp.endDate
                FROM   PatientTreatmentPlan ptp
                JOIN   Complaint c ON ptp.complainID = c.complainID
                JOIN   Treatment t ON ptp.treatID    = t.treatID
                WHERE  ptp.patientNO = ?
                ORDER  BY ptp.startDate";
            $stmt2 = qry($conn, $sql_tr, [[$pno, SQLSRV_PARAM_IN]]);
            if ($stmt2) $treatments = fetchAll($stmt2);
        }
    }
}
?>

<!-- ── Search Form ── -->
<div class="search-card">
  <form method="POST" class="row g-3 align-items-end">
    <div class="col-auto">
      <label for="patient_no">Patient Number</label>
      <input type="number" id="patient_no" name="patient_no" class="form-control"
             placeholder="e.g. 1"
             value="<?= isset($_POST['patient_no']) ? h($_POST['patient_no']) : '' ?>"
             min="1" style="width:180px;">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn-search">
        <i class="bi bi-search me-1"></i>View Full Details
      </button>
    </div>
    <?php if ($submitted && $patient): ?>
    <div class="col-auto">
      <a href="report10.php" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
    <?php endif; ?>
  </form>
  <p class="text-muted mt-2 mb-0" style="font-size:.82rem;">
    Patient numbers range from <code>1</code> to <code>30</code>.
  </p>
</div>

<?php if (!$submitted): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Enter a Patient Number above to view their complete medical details.
  </div>
<?php endif; ?>

<?php if ($submitted): ?>
  <?php if ($notFound): ?>
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-circle me-2"></i>
      No patient found with Patient No <strong><?= h($_POST['patient_no']) ?></strong>.
    </div>
  <?php elseif ($patient): ?>

    <!-- ── Patient Details ── -->
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header"><i class="bi bi-person-fill me-2"></i>Patient Information</div>
          <div class="card-body">
            <div class="info-grid" style="grid-template-columns:1fr 1fr;">
              <div class="info-item"><label>Patient No</label><span><?= h($patient['patientNO']) ?></span></div>
              <div class="info-item"><label>Name</label><span><?= h($patient['PatientName']) ?></span></div>
              <div class="info-item"><label>Date of Birth</label><span><?= h($patient['dob']) ?></span></div>
              <div class="info-item"><label>Date Admitted</label><span><?= h($patient['doA']) ?></span></div>
              <div class="info-item"><label>Ward</label><span><?= h($patient['Ward']) ?></span></div>
              <div class="info-item"><label>Care Unit</label><span>Unit <?= h($patient['CareUnit']) ?></span></div>
              <div class="info-item"><label>Bed No</label><span><?= h($patient['BedNo']) ?></span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header"><i class="bi bi-person-badge me-2"></i>Medical Staff</div>
          <div class="card-body">
            <div class="info-grid" style="grid-template-columns:1fr 1fr;">
              <div class="info-item"><label>Doctor ID</label><span><code><?= h($patient['DoctorID']) ?></code></span></div>
              <div class="info-item"><label>Primary Doctor</label><span><?= h($patient['PrimaryDoctor']) ?></span></div>
              <div class="info-item">
                <label>Position</label>
                <span><span class="badge-pos"><?= h($posLabels[$patient['position']] ?? $patient['position']) ?></span></span>
              </div>
              <div class="info-item"><label>Consultant ID</label><span><code><?= h($patient['ConsultantID']) ?></code></span></div>
              <div class="info-item"><label>Consultant</label><span><?= h($patient['Consultant']) ?></span></div>
              <div class="info-item"><label>Specialty</label><span><?= h($patient['specialty']) ?></span></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Treatment Plan ── -->
    <div class="card mb-5">
      <div class="card-header">
        <i class="bi bi-clipboard2-pulse me-2"></i>Treatment Plan
        <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;">
          <?= count($treatments) ?> record<?= count($treatments) !== 1 ? 's' : '' ?>
        </span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($treatments)): ?>
          <div class="no-results">No treatment records for this patient.</div>
        <?php else: ?>
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Complaint Code</th>
                  <th>Complaint</th>
                  <th>Treatment Code</th>
                  <th>Treatment</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($treatments as $t): ?>
                <tr>
                  <td><code><?= h($t['complainID']) ?></code></td>
                  <td><?= h($t['Complaint']) ?></td>
                  <td><code><?= h($t['treatID']) ?></code></td>
                  <td><?= h($t['Treatment']) ?></td>
                  <td><?= h($t['startDate']) ?></td>
                  <td><?= $t['endDate'] === null ? '—' : h($t['endDate']) ?></td>
                  <td>
                    <?php if ($t['endDate'] === null): ?>
                      <span class="badge" style="background:var(--success);color:#fff;">Ongoing</span>
                    <?php else: ?>
                      <span class="badge" style="background:var(--muted);color:#fff;">Completed</span>
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

  <?php endif; ?>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
