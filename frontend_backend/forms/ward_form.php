<?php
// ============================================================
//  Form 2 — Ward Record
//  Input: Ward Name (dropdown)
//  Output: Ward header (sisters, nurses) + Patient Information table
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'Ward Record';
$pageSubtitle = 'Select a ward to view its nursing staff and current patient list.';
$pageBadge    = 'Form 2';
require_once '../includes/header.php';

// Load ward list for dropdown
$wardStmt = qry($conn, "SELECT wardName FROM Ward ORDER BY wardName");
$wards    = fetchAll($wardStmt);

$submitted  = false;
$wardInfo   = null;
$staffNurses    = [];
$nonRegNurses   = [];
$patients       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ward_name'])) {
    $submitted = true;
    $wardName  = trim($_POST['ward_name']);

    // ── Ward header: day sister, night sister ──────────────
    $sql_ward = "
        SELECT w.wardName,
               n_day.name   AS DaySister,   n_day.nurseID   AS DaySisterID,
               n_night.name AS NightSister, n_night.nurseID AS NightSisterID
        FROM   Ward w
        LEFT JOIN Nurse n_day   ON n_day.ward   = w.wardName AND n_day.role   = 'day_sister'
        LEFT JOIN Nurse n_night ON n_night.ward = w.wardName AND n_night.role = 'night_sister'
        WHERE  w.wardName = ?";
    $stmt = qry($conn, $sql_ward, [[$wardName, SQLSRV_PARAM_IN]]);
    if ($stmt) $wardInfo = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // ── Staff nurses on this ward ──────────────────────────
    $sql_sn = "
        SELECT n.nurseID, n.name, sn.unitManaging
        FROM   Nurse n
        JOIN   StaffNurse sn ON sn.nurseID = n.nurseID
        WHERE  n.ward = ?
        ORDER  BY sn.unitManaging";
    $stmt2 = qry($conn, $sql_sn, [[$wardName, SQLSRV_PARAM_IN]]);
    if ($stmt2) $staffNurses = fetchAll($stmt2);

    // ── Non-registered nurses on this ward ────────────────
    $sql_nr = "
        SELECT n.nurseID, n.name, nr.unitAssigned
        FROM   Nurse n
        JOIN   NonRegisteredNurse nr ON nr.nurseID = n.nurseID
        WHERE  n.ward = ?
        ORDER  BY nr.unitAssigned";
    $stmt3 = qry($conn, $sql_nr, [[$wardName, SQLSRV_PARAM_IN]]);
    if ($stmt3) $nonRegNurses = fetchAll($stmt3);

    // ── Patient information table ──────────────────────────
    $sql_pat = "
        SELECT p.patientNO, p.name AS PatientName,
               p.careUnit, p.bed,
               s_con.name AS Consultant,
               p.doA      AS DateAdmitted
        FROM   Patient    p
        JOIN   CareUnit   cu    ON p.careUnit    = cu.unitNo
        JOIN   Doctor     d     ON p.primaryDoctor = d.staffID
        LEFT JOIN Consultant con   ON d.consultant = con.staffID
        LEFT JOIN Staff    s_con   ON con.staffID  = s_con.staffID
        WHERE  cu.ward = ?
        ORDER  BY p.careUnit, p.patientNO";
    $stmt4 = qry($conn, $sql_pat, [[$wardName, SQLSRV_PARAM_IN]]);
    if ($stmt4) $patients = fetchAll($stmt4);
}
?>

<!-- ── Search Form ── -->
<div class="search-card">
  <form method="POST" class="row g-3 align-items-end">
    <div class="col-auto">
      <label for="ward_name">Ward Name</label>
      <select id="ward_name" name="ward_name" class="form-select" style="min-width:220px;">
        <option value="">— Select a Ward —</option>
        <?php foreach ($wards as $w): ?>
          <option value="<?= h($w['wardName']) ?>"
            <?= (isset($_POST['ward_name']) && $_POST['ward_name'] === $w['wardName']) ? 'selected' : '' ?>>
            <?= h($w['wardName']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn-search">
        <i class="bi bi-search me-1"></i>View Ward Record
      </button>
    </div>
    <?php if ($submitted): ?>
    <div class="col-auto">
      <a href="ward_form.php" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
    <?php endif; ?>
  </form>
</div>

<?php if (!$submitted): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Select a ward from the dropdown above and click <strong>View Ward Record</strong> to display
    nursing staff and patient information.
  </div>
<?php endif; ?>

<?php if ($submitted && $wardInfo): ?>

  <!-- ════════════════════════════════════════
       WARD RECORD HEADER
       ════════════════════════════════════════ -->
  <div class="card mb-4">
    <div class="card-header">
      <div>IVOR PAINE MEMORIAL HOSPITAL &mdash; WARD RECORD</div>
      <div class="card-subtitle">Ward: <?= h($wardInfo['wardName']) ?></div>
    </div>
    <div class="card-body">
      <div class="info-grid mb-4">
        <div class="info-item">
          <label>Ward Name</label>
          <span><?= h($wardInfo['wardName']) ?></span>
        </div>
        <div class="info-item">
          <label>Day Sister</label>
          <span>
            <?= $wardInfo['DaySister'] ? h($wardInfo['DaySister']) . ' <small class="text-muted">(' . h($wardInfo['DaySisterID']) . ')</small>' : '<em class="text-muted">None assigned</em>' ?>
          </span>
        </div>
        <div class="info-item">
          <label>Night Sister</label>
          <span>
            <?= $wardInfo['NightSister'] ? h($wardInfo['NightSister']) . ' <small class="text-muted">(' . h($wardInfo['NightSisterID']) . ')</small>' : '<em class="text-muted">None assigned</em>' ?>
          </span>
        </div>
      </div>

      <div class="row g-4">
        <!-- Staff Nurses -->
        <div class="col-md-6">
          <h6 class="fw-bold mb-2" style="color:var(--navy);font-size:.85rem;">
            <i class="bi bi-person-check me-1"></i>Staff Nurses
            <span class="badge bg-secondary ms-1" style="font-size:.7rem;"><?= count($staffNurses) ?></span>
          </h6>
          <?php if (empty($staffNurses)): ?>
            <p class="text-muted fst-italic small">No staff nurses assigned.</p>
          <?php else: ?>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr><th>ID</th><th>Name</th><th>Unit Managing</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($staffNurses as $n): ?>
                  <tr>
                    <td><code><?= h($n['nurseID']) ?></code></td>
                    <td><?= h($n['name']) ?></td>
                    <td>Unit <?= h($n['unitManaging']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Non-Registered Nurses -->
        <div class="col-md-6">
          <h6 class="fw-bold mb-2" style="color:var(--navy);font-size:.85rem;">
            <i class="bi bi-person me-1"></i>Non-Registered Nurses
            <span class="badge bg-secondary ms-1" style="font-size:.7rem;"><?= count($nonRegNurses) ?></span>
          </h6>
          <?php if (empty($nonRegNurses)): ?>
            <p class="text-muted fst-italic small">No non-registered nurses assigned.</p>
          <?php else: ?>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr><th>ID</th><th>Name</th><th>Unit Assigned</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($nonRegNurses as $n): ?>
                  <tr>
                    <td><code><?= h($n['nurseID']) ?></code></td>
                    <td><?= h($n['name']) ?></td>
                    <td>Unit <?= h($n['unitAssigned']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════════════
       PATIENT INFORMATION TABLE
       ════════════════════════════════════════ -->
  <div class="card mb-5">
    <div class="card-header">
      <i class="bi bi-people me-2"></i>Patient Information
      <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;">
        <?= count($patients) ?> patient<?= count($patients) !== 1 ? 's' : '' ?>
      </span>
    </div>
    <div class="card-body p-0">
      <?php if (empty($patients)): ?>
        <div class="no-results">
          <i class="bi bi-person-x fs-1"></i><br>
          No patients currently admitted to this ward.
        </div>
      <?php else: ?>
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Patient No</th>
                <th>Patient Name</th>
                <th>Care Unit</th>
                <th>Bed No</th>
                <th>Consultant</th>
                <th>Date Admitted</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($patients as $p): ?>
              <tr>
                <td><?= h($p['patientNO']) ?></td>
                <td><?= h($p['PatientName']) ?></td>
                <td>Unit <?= h($p['careUnit']) ?></td>
                <td><?= h($p['bed']) ?></td>
                <td><?= h($p['Consultant']) ?></td>
                <td><?= h($p['DateAdmitted']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
