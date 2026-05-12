<?php
// ============================================================
//  Report 8 — Patients Grouped by Treatment within Complaint
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R8 — Grouped by Treatment';
$pageSubtitle = 'A list of patients grouped by treatment within each complaint.';
$pageBadge    = 'Report 8';
require_once '../includes/header.php';

$sql = "
    SELECT c.complainID AS ComplaintCode,
           c.descr      AS Complaint,
           t.treatID    AS TreatmentCode,
           t.descr      AS Treatment,
           p.patientNO,
           p.name       AS PatientName,
           ptp.startDate,
           ptp.endDate
    FROM   PatientTreatmentPlan ptp
    JOIN   Patient   p ON ptp.patientNO   = p.patientNO
    JOIN   Complaint c ON ptp.complainID  = c.complainID
    JOIN   Treatment t ON ptp.treatID     = t.treatID
    ORDER  BY c.descr, t.descr, p.name";

$rows = fetchAll(qry($conn, $sql));
?>

<div class="card mb-5">
  <div class="card-header">
    <i class="bi bi-collection me-2"></i>Patients Grouped by Treatment within Complaint
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
              <th>Patient No</th>
              <th>Patient Name</th>
              <th>Date Started</th>
              <th>Date Ended</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $prevComplaint  = null;
            $prevTreatment  = null;
            foreach ($rows as $r):
              $newComplaint = $r['ComplaintCode'] !== $prevComplaint;
              $newTreatment = $newComplaint || ($r['TreatmentCode'] !== $prevTreatment);
              $prevComplaint = $r['ComplaintCode'];
              $prevTreatment = $r['TreatmentCode'];

              $border = $newComplaint ? 'border-top:3px solid var(--navy);'
                      : ($newTreatment ? 'border-top:2px solid var(--border);' : '');
            ?>
            <tr style="<?= $border ?>">
              <td>
                <?= $newComplaint ? '<code><strong>' . h($r['ComplaintCode']) . '</strong></code>' : '' ?>
              </td>
              <td>
                <?= $newComplaint ? '<strong>' . h($r['Complaint']) . '</strong>' : '' ?>
              </td>
              <td>
                <?= $newTreatment ? '<code>' . h($r['TreatmentCode']) . '</code>' : '' ?>
              </td>
              <td>
                <?= $newTreatment ? h($r['Treatment']) : '' ?>
              </td>
              <td><?= h($r['patientNO']) ?></td>
              <td><?= h($r['PatientName']) ?></td>
              <td><?= h($r['startDate']) ?></td>
              <td>
                <?php if ($r['endDate'] === null): ?>
                  <span class="ongoing">Ongoing</span>
                <?php else: ?>
                  <span class="ended"><?= h($r['endDate']) ?></span>
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
