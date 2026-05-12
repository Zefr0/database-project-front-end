<?php
// ============================================================
//  Report 1 — Consultants and the Doctors in Their Team
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R1 — Consultant Teams';
$pageSubtitle = 'A list of consultants and the doctors in their team.';
$pageBadge    = 'Report 1';
require_once '../includes/header.php';

$sql = "
    SELECT s_con.name     AS ConsultantName,
           con.staffID    AS ConsultantID,
           con.specialty,
           s_doc.name     AS DoctorName,
           d.staffID      AS DoctorID,
           d.position,
           dt.dateJoined
    FROM   Consultant con
    JOIN   Staff      s_con ON con.staffID  = s_con.staffID
    JOIN   DoctorTeam dt    ON con.staffID  = dt.consultant
    JOIN   Doctor     d     ON dt.doctor    = d.staffID
    JOIN   Staff      s_doc ON d.staffID    = s_doc.staffID
    ORDER  BY s_con.name, dt.dateJoined";

$rows = fetchAll(qry($conn, $sql));

$posLabels = [
    'student' => 'Student', 'jh' => 'Junior Houseman',
    'sh' => 'Senior Houseman', 'ar' => 'Assistant Registrar', 'r' => 'Registrar',
];
?>

<div class="card mb-5">
  <div class="card-header">
    <i class="bi bi-people-fill me-2"></i>Consultant Teams
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
              <th>Consultant ID</th>
              <th>Consultant Name</th>
              <th>Specialty</th>
              <th>Doctor ID</th>
              <th>Doctor Name</th>
              <th>Position</th>
              <th>Date Joined Team</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $prevConsultant = null;
            foreach ($rows as $r):
              $isNew = $r['ConsultantID'] !== $prevConsultant;
              $prevConsultant = $r['ConsultantID'];
            ?>
            <tr <?= $isNew ? 'style="border-top:2px solid var(--navy);"' : '' ?>>
              <td><code><?= h($r['ConsultantID']) ?></code></td>
              <td><strong><?= h($r['ConsultantName']) ?></strong></td>
              <td><?= h($r['specialty']) ?></td>
              <td><code><?= h($r['DoctorID']) ?></code></td>
              <td><?= h($r['DoctorName']) ?></td>
              <td><span class="badge-pos"><?= h($posLabels[$r['position']] ?? $r['position']) ?></span></td>
              <td><?= h($r['dateJoined']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
