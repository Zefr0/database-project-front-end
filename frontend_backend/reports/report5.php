<?php
// ============================================================
//  Report 5 — Consultants with a Unique Specialty
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R5 — Unique Specialties';
$pageSubtitle = 'A list of consultants who hold a specialty not shared by any other consultant.';
$pageBadge    = 'Report 5';
require_once '../includes/header.php';

$sql = "
    SELECT con.staffID      AS ConsultantID,
           s.name           AS ConsultantName,
           con.specialty
    FROM   Consultant con
    JOIN   Staff      s   ON con.staffID = s.staffID
    WHERE  con.specialty IN (
        SELECT specialty
        FROM   Consultant
        GROUP  BY specialty
        HAVING COUNT(*) = 1
    )
    ORDER  BY con.specialty";

$rows = fetchAll(qry($conn, $sql));
?>

<div class="card mb-5">
  <div class="card-header">
    <i class="bi bi-award me-2"></i>Consultants with Unique Specialties
    <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;"><?= count($rows) ?> found</span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($rows)): ?>
      <div class="no-results">All specialties are shared by more than one consultant.</div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Consultant ID</th>
              <th>Consultant Name</th>
              <th>Specialty (Unique)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
              <td><code><?= h($r['ConsultantID']) ?></code></td>
              <td><?= h($r['ConsultantName']) ?></td>
              <td>
                <span style="color:var(--success);font-weight:600;">
                  <i class="bi bi-check-circle me-1"></i><?= h($r['specialty']) ?>
                </span>
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
