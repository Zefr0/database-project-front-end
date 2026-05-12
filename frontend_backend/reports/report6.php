<?php
// ============================================================
//  Report 6 — Complaints, Treatments & Doctor's Experience History
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R6 — Complaints & Doctor Experience';
$pageSubtitle = 'A list of complaints, treatments given for each complaint, and the experience history of the treating doctor.';
$pageBadge    = 'Report 6';
require_once '../includes/header.php';

$sql = "
    SELECT DISTINCT
           c.complainID    AS ComplaintCode,
           c.descr         AS Complaint,
           t.treatID       AS TreatmentCode,
           t.descr         AS Treatment,
           s.name          AS DoctorName,
           d.staffID       AS DoctorID,
           pe.expID,
           pe.establishment,
           pe.position     AS PastPosition,
           pe.dateStart,
           pe.dateEnd
    FROM   PatientTreatmentPlan ptp
    JOIN   Complaint            c   ON ptp.complainID  = c.complainID
    JOIN   Treatment            t   ON ptp.treatID     = t.treatID
    JOIN   Patient              p   ON ptp.patientNO   = p.patientNO
    JOIN   Doctor               d   ON p.primaryDoctor = d.staffID
    JOIN   Staff                s   ON d.staffID       = s.staffID
    LEFT JOIN PastExperience    pe  ON pe.doctorID     = d.staffID
    ORDER  BY c.complainID, t.treatID, s.name, pe.dateStart";

$rows = fetchAll(qry($conn, $sql));

$posLabels = [
    'student' => 'Student', 'jh' => 'Junior Houseman',
    'sh' => 'Senior Houseman', 'ar' => 'Assistant Registrar', 'r' => 'Registrar',
];
?>

<div class="card mb-5">
  <div class="card-header">
    <i class="bi bi-journal-medical me-2"></i>Complaints, Treatments &amp; Treating Doctor Experience
    <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;"><?= count($rows) ?> rows</span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($rows)): ?>
      <div class="no-results">No data found.</div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Complaint Code</th>
              <th>Complaint</th>
              <th>Treatment Code</th>
              <th>Treatment</th>
              <th>Treating Doctor</th>
              <th>Past Establishment</th>
              <th>Past Position</th>
              <th>From</th>
              <th>To</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $prevKey = null;
            foreach ($rows as $r):
              $key = $r['ComplaintCode'] . '|' . $r['TreatmentCode'] . '|' . $r['DoctorID'];
              $isNew = $key !== $prevKey;
              $prevKey = $key;
            ?>
            <tr <?= $isNew ? 'style="border-top:2px solid var(--border);"' : '' ?>>
              <td><?= $isNew ? '<code>' . h($r['ComplaintCode']) . '</code>' : '' ?></td>
              <td><?= $isNew ? h($r['Complaint']) : '' ?></td>
              <td><?= $isNew ? '<code>' . h($r['TreatmentCode']) . '</code>' : '' ?></td>
              <td><?= $isNew ? h($r['Treatment']) : '' ?></td>
              <td><?= $isNew ? h($r['DoctorName']) : '' ?></td>
              <td><?= h($r['establishment']) ?></td>
              <td>
                <?php if ($r['PastPosition']): ?>
                  <span class="badge-pos"><?= h($posLabels[$r['PastPosition']] ?? $r['PastPosition']) ?></span>
                <?php else: ?>
                  <em class="text-muted">—</em>
                <?php endif; ?>
              </td>
              <td><?= h($r['dateStart']) ?></td>
              <td>
                <?php if ($r['dateEnd'] === null && $r['expID'] !== null): ?>
                  <span class="ongoing">Current</span>
                <?php else: ?>
                  <?= h($r['dateEnd']) ?>
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

<?php require_once '../includes/footer.php'; ?>
