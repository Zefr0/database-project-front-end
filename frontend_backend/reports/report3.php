<?php
// ============================================================
//  Report 3 — Patients and Their Complaints, Treatments & Dates
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R3 — Patient Treatments';
$pageSubtitle = 'A list of patients and their complaints, treatments, and dates of treatment.';
$pageBadge    = 'Report 3';
require_once '../includes/header.php';

$sql = "
    SELECT p.patientNO,
           p.name       AS PatientName,
           c.complainID AS ComplaintCode,
           c.descr      AS Complaint,
           t.treatID    AS TreatmentCode,
           t.descr      AS Treatment,
           ptp.startDate,
           ptp.endDate
    FROM   Patient              p
    JOIN   PatientTreatmentPlan ptp ON p.patientNO    = ptp.patientNO
    JOIN   Complaint            c   ON ptp.complainID = c.complainID
    JOIN   Treatment            t   ON ptp.treatID    = t.treatID
    ORDER  BY p.name, c.complainID, ptp.startDate";

$rows = fetchAll(qry($conn, $sql));
?>

<div class="card mb-5">
  <div class="card-header">
    <i class="bi bi-clipboard2-pulse me-2"></i>Patient Treatment Records
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
              <th>Patient No</th>
              <th>Patient Name</th>
              <th>Complaint Code</th>
              <th>Complaint</th>
              <th>Treatment Code</th>
              <th>Treatment</th>
              <th>Date Started</th>
              <th>Date Ended</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $prevPat = null;
            foreach ($rows as $r):
              $isNew = $r['patientNO'] !== $prevPat;
              $prevPat = $r['patientNO'];
            ?>
            <tr <?= $isNew ? 'style="border-top:2px solid var(--navy);"' : '' ?>>
              <td><?= $isNew ? h($r['patientNO']) : '' ?></td>
              <td><?= $isNew ? '<strong>' . h($r['PatientName']) . '</strong>' : '' ?></td>
              <td><code><?= h($r['ComplaintCode']) ?></code></td>
              <td><?= h($r['Complaint']) ?></td>
              <td><code><?= h($r['TreatmentCode']) ?></code></td>
              <td><?= h($r['Treatment']) ?></td>
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
