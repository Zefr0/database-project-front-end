<?php
// ============================================================
//  Report 12 — Staff Positions & Count per Position
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R12 — Staff Position Count';
$pageSubtitle = 'A list of all positions held by staff in the hospital, with a count of staff in each position.';
$pageBadge    = 'Report 12';
require_once '../includes/header.php';

$sql = "
    SELECT position   AS StaffRole,
           COUNT(*)   AS StaffCount,
           'Doctor'   AS Category
    FROM   Doctor
    GROUP  BY position

    UNION ALL

    SELECT 'Consultant (Senior)'  AS StaffRole,
           COUNT(*)               AS StaffCount,
           'Medical Staff'        AS Category
    FROM   Consultant

    UNION ALL

    SELECT role        AS StaffRole,
           COUNT(*)    AS StaffCount,
           'Nurse'     AS Category
    FROM   Nurse
    GROUP  BY role

    ORDER  BY Category, StaffRole";

$rows = fetchAll(qry($conn, $sql));

$posLabels = [
    'student'        => 'Student',
    'jh'             => 'Junior Houseman',
    'sh'             => 'Senior Houseman',
    'ar'             => 'Assistant Registrar',
    'r'              => 'Registrar',
    'day_sister'     => 'Day Sister',
    'night_sister'   => 'Night Sister',
    'staff_nurse'    => 'Staff Nurse',
    'non_registered' => 'Non-Registered Nurse',
];

$total = array_sum(array_column($rows, 'StaffCount'));
?>

<div class="card mb-5">
  <div class="card-header">
    <i class="bi bi-bar-chart-fill me-2"></i>Staff Position Summary
    <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;">
      Total Staff: <?= $total ?>
    </span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($rows)): ?>
      <div class="no-results">No data found.</div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Category</th>
              <th>Position / Role</th>
              <th>Staff Code</th>
              <th>Count</th>
              <th style="width:200px;">Distribution</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $prevCat = null;
            foreach ($rows as $r):
              $isNew  = $r['Category'] !== $prevCat;
              $prevCat = $r['Category'];
              $pct    = $total > 0 ? round(($r['StaffCount'] / $total) * 100, 1) : 0;
              $label  = $posLabels[$r['StaffRole']] ?? $r['StaffRole'];
            ?>
            <tr <?= $isNew ? 'style="border-top:2px solid var(--navy);"' : '' ?>>
              <td>
                <?= $isNew ? '<span style="font-weight:700;color:var(--navy);">' . h($r['Category']) . '</span>' : '' ?>
              </td>
              <td><?= h($label) ?></td>
              <td><code><?= h($r['StaffRole']) ?></code></td>
              <td>
                <span style="font-size:1.1rem;font-weight:700;color:var(--navy);">
                  <?= h($r['StaffCount']) ?>
                </span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="flex:1;background:#e0e7ef;border-radius:4px;height:8px;">
                    <div style="width:<?= $pct ?>%;background:var(--accent);height:8px;border-radius:4px;"></div>
                  </div>
                  <span style="font-size:.78rem;color:var(--muted);min-width:36px;"><?= $pct ?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <!-- Total row -->
            <tr style="background:#f0f4f8;border-top:2px solid var(--navy);font-weight:700;">
              <td colspan="3" style="text-align:right;padding-right:16px;color:var(--navy);">TOTAL</td>
              <td style="font-size:1.2rem;color:var(--navy);"><?= $total ?></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
