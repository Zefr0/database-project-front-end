<?php
// ============================================================
//  Form 1 — Patient Record
//  Input: Patient No
//  Output: Patient header + Medical History table
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'Patient Record';
$pageSubtitle = 'Enter a Patient Number to view their full record and medical history.';
$pageBadge    = 'Form 1';
require_once '../includes/header.php';

$submitted  = false;
$patient    = null;
$history    = [];
$notFound   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_no'])) {
    $submitted = true;
    $pno = trim($_POST['patient_no']);

    if ($pno === '' || !ctype_digit($pno)) {
        echo '<div class="alert alert-warning mt-0 mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Please enter a valid numeric Patient Number.
              </div>';
        $submitted = false;
    } else {
        $pno = (int)$pno;

        // ── Patient header query ──────────────────────────────
        $sql_patient = "
            SELECT p.patientNO, p.name AS PatientName, p.dob, p.doA,
                   d.staffID  AS DoctorID,  s_doc.name AS DoctorName,
                   s_con.name AS ConsultantName,   con.staffID AS ConsultantID
            FROM   Patient p
            JOIN   Doctor     d     ON p.primaryDoctor = d.staffID
            JOIN   Staff      s_doc ON d.staffID       = s_doc.staffID
            LEFT JOIN Consultant con     ON d.consultant    = con.staffID
            LEFT JOIN Staff      s_con   ON con.staffID     = s_con.staffID
            WHERE  p.patientNO = ?";

        $stmt = qry($conn, $sql_patient, [[$pno, SQLSRV_PARAM_IN]]);
        if ($stmt) {
            $patient = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$patient) $notFound = true;
        }

        // ── Medical History query ─────────────────────────────
        if ($patient) {
            $sql_history = "
                SELECT ptp.complainID  AS ComplaintCode,
                       comp.descr      AS ComplaintDesc,
                       ptp.treatID     AS TreatmentCode,
                       t.descr         AS TreatmentDesc,
                       s_doc.name      AS Doctor,
                       ptp.startDate,
                       ptp.endDate
                FROM   PatientTreatmentPlan ptp
                JOIN   Complaint comp ON ptp.complainID  = comp.complainID
                JOIN   Treatment t    ON ptp.treatID     = t.treatID
                JOIN   Patient   p    ON ptp.patientNO   = p.patientNO
                JOIN   Doctor    d    ON p.primaryDoctor = d.staffID
                JOIN   Staff  s_doc   ON d.staffID       = s_doc.staffID
                WHERE  ptp.patientNO = ?
                ORDER  BY ptp.startDate";
            $stmt2 = qry($conn, $sql_history, [[$pno, SQLSRV_PARAM_IN]]);
            if ($stmt2) $history = fetchAll($stmt2);
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
        <i class="bi bi-search me-1"></i>Look Up Patient
      </button>
    </div>
    <?php if ($submitted && $patient): ?>
    <div class="col-auto">
      <a href="patient_form.php" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
    <?php endif; ?>
  </form>
</div>

<?php if ($submitted): ?>

  <?php if ($notFound): ?>
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-circle me-2"></i>
      No patient found with Patient No <strong><?= h($_POST['patient_no']) ?></strong>.
    </div>

  <?php elseif ($patient): ?>

    <!-- ════════════════════════════════════════════════════
         PATIENT RECORD HEADER  (matches manual record form)
         ════════════════════════════════════════════════════ -->
    <div class="card mb-4">
      <div class="card-header">
        <div>IVOR PAINE MEMORIAL HOSPITAL &mdash; PATIENT RECORD</div>
        <div class="card-subtitle">Patient No: <?= h($patient['patientNO']) ?></div>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item">
            <label>Patient No</label>
            <span><?= h($patient['patientNO']) ?></span>
          </div>
          <div class="info-item">
            <label>Patient Name</label>
            <span><?= h($patient['PatientName']) ?></span>
          </div>
          <div class="info-item">
            <label>Date of Birth</label>
            <span><?= h($patient['dob']) ?></span>
          </div>
          <div class="info-item">
            <label>Date of Admission</label>
            <span><?= h($patient['doA']) ?></span>
          </div>
          <div class="info-item">
            <label>Doctor No</label>
            <span><?= h($patient['DoctorID']) ?></span>
          </div>
          <div class="info-item">
            <label>Doctor Name</label>
            <span><?= h($patient['DoctorName']) ?></span>
          </div>
          <div class="info-item">
            <label>Consultant</label>
            <span><?= h($patient['ConsultantName']) ?></span>
          </div>
          <div class="info-item">
            <label>Consultant ID</label>
            <span><?= h($patient['ConsultantID']) ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════
         MEDICAL HISTORY TABLE
         ══════════════════════════════════════════════ -->
    <div class="card mb-5">
      <div class="card-header">
        <i class="bi bi-clipboard2-pulse me-2"></i>Medical History
        <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;">
          <?= count($history) ?> record<?= count($history) !== 1 ? 's' : '' ?>
        </span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($history)): ?>
          <div class="no-results">
            <i class="bi bi-clipboard2 fs-1"></i><br>
            No treatment records found for this patient.
          </div>
        <?php else: ?>
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Complaint Code</th>
                  <th>Complaint</th>
                  <th>Treatment Code</th>
                  <th>Treatment</th>
                  <th>Doctor</th>
                  <th>Date Treatment Started</th>
                  <th>Date Treatment Ended</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($history as $row): ?>
                <tr>
                  <td><code><?= h($row['ComplaintCode']) ?></code></td>
                  <td><?= h($row['ComplaintDesc']) ?></td>
                  <td><code><?= h($row['TreatmentCode']) ?></code></td>
                  <td><?= h($row['TreatmentDesc']) ?></td>
                  <td><?= h($row['Doctor']) ?></td>
                  <td><?= h($row['startDate']) ?></td>
                  <td>
                    <?php if ($row['endDate'] === null): ?>
                      <span class="ongoing">Ongoing</span>
                    <?php else: ?>
                      <span class="ended"><?= h($row['endDate']) ?></span>
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
<?php else: ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Enter a Patient Number above and click <strong>Look Up Patient</strong> to view the patient record.
    Patient numbers range from <strong>1 to 30</strong> in the current database.
  </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
