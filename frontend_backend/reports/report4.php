<?php
// ============================================================
//  Report 4 — Junior Housemen, Their Patients & Staff Nurse
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R4 — Junior Housemen';
$pageSubtitle = 'A list of junior housemen, their patients, and the staff nurse for each patient\'s care unit.';
$pageBadge    = 'Report 4';
require_once '../includes/header.php';

$sql = "
    SELECT s_doc.name       AS DoctorName,
           d.staffID        AS DoctorID,
           p.patientNO,
           p.name           AS PatientName,
           p.careUnit,
           sn_nurse.name    AS StaffNurse,
           sn_nurse.nurseID AS StaffNurseID
    FROM   Doctor     d
    JOIN   Staff      s_doc    ON d.staffID        = s_doc.staffID
    JOIN   Patient    p        ON p.primaryDoctor  = d.staffID
    JOIN   StaffNurse sn       ON sn.unitManaging  = p.careUnit
    JOIN   Nurse      sn_nurse ON sn_nurse.nurseID = sn.nurseID
    WHERE  d.position = 'jh'
    ORDER  BY s_doc.name, p.name";

$rows = fetchAll(qry($conn, $sql));
?>

<div class="card mb-5">
  <div class="card-header">
    <i class="bi bi-person-badge me-2"></i>Junior Housemen &amp; Their Patients
    <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;"><?= count($rows) ?> rows</span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($rows)): ?>
      <div class="no-results">No junior housemen with assigned patients found.</div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Doctor ID</th>
              <th>Junior Houseman</th>
              <th>Patient No</th>
              <th>Patient Name</th>
              <th>Care Unit</th>
              <th>Staff Nurse (Care Unit)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $prevDoc = null;
            foreach ($rows as $r):
              $isNew = $r['DoctorID'] !== $prevDoc;
              $prevDoc = $r['DoctorID'];
            ?>
            <tr <?= $isNew ? 'style="border-top:2px solid var(--navy);"' : '' ?>>
              <td><?= $isNew ? '<code>' . h($r['DoctorID']) . '</code>' : '' ?></td>
              <td><?= $isNew ? '<strong>' . h($r['DoctorName']) . '</strong>' : '' ?></td>
              <td><?= h($r['patientNO']) ?></td>
              <td><?= h($r['PatientName']) ?></td>
              <td>Unit <?= h($r['careUnit']) ?></td>
              <td><?= h($r['StaffNurse']) ?> <small class="text-muted">(<?= h($r['StaffNurseID']) ?>)</small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
