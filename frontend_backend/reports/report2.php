<?php
// ============================================================
//  Report 2 — Wards with Sisters, Care Units & Staff Nurses
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R2 — Ward Sisters & Staff Nurses';
$pageSubtitle = 'A list of wards with their respective sisters, care units, and staff nurses in charge.';
$pageBadge    = 'Report 2';
require_once '../includes/header.php';

$sql = "
    SELECT w.wardName,
           n_day.name      AS DaySister,    n_day.nurseID   AS DaySisterID,
           n_night.name    AS NightSister,  n_night.nurseID AS NightSisterID,
           cu.unitNo       AS CareUnit,
           sn_nurse.name   AS StaffNurse,   sn_nurse.nurseID AS StaffNurseID
    FROM   Ward w
    LEFT JOIN Nurse n_day     ON n_day.ward   = w.wardName AND n_day.role   = 'day_sister'
    LEFT JOIN Nurse n_night   ON n_night.ward = w.wardName AND n_night.role = 'night_sister'
    LEFT JOIN CareUnit cu     ON cu.ward      = w.wardName
    LEFT JOIN StaffNurse sn   ON sn.unitManaging = cu.unitNo
    LEFT JOIN Nurse sn_nurse  ON sn_nurse.nurseID = sn.nurseID
    ORDER  BY w.wardName, cu.unitNo";

$rows = fetchAll(qry($conn, $sql));
?>

<div class="card mb-5">
  <div class="card-header">
    <i class="bi bi-building me-2"></i>Ward Overview
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
              <th>Ward</th>
              <th>Day Sister</th>
              <th>Night Sister</th>
              <th>Care Unit No</th>
              <th>Staff Nurse in Charge</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $prevWard = null;
            foreach ($rows as $r):
              $isNew = $r['wardName'] !== $prevWard;
              $prevWard = $r['wardName'];
            ?>
            <tr <?= $isNew ? 'style="border-top:2px solid var(--navy);"' : '' ?>>
              <td><strong><?= $isNew ? h($r['wardName']) : '' ?></strong></td>
              <td><?= $isNew ? h($r['DaySister']) : '' ?></td>
              <td><?= $isNew ? h($r['NightSister']) : '' ?></td>
              <td>Unit <?= h($r['CareUnit']) ?></td>
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
