<?php
// ============================================================
//  Report 11 — Treatments for a Complaint Between Two Dates
//  Inputs: Complaint ID, From Date, To Date
//  Ordered by treatment
// ============================================================
require_once '../config/db.php';
$base         = '../';
$pageTitle    = 'R11 — Treatments by Date Range';
$pageSubtitle = 'Treatments given for a particular complaint between two specified dates, ordered by treatment.';
$pageBadge    = 'Report 11';
require_once '../includes/header.php';

// Load complaints for dropdown
$complaintStmt = qry($conn, "SELECT complainID, descr FROM Complaint ORDER BY complainID");
$complaints    = fetchAll($complaintStmt);

$submitted = false;
$rows      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_POST['complaint_id'])
    && !empty($_POST['from_date'])
    && !empty($_POST['to_date'])) {

    $submitted   = true;
    $complainID  = trim($_POST['complaint_id']);
    $fromDate    = trim($_POST['from_date']);
    $toDate      = trim($_POST['to_date']);

    if ($fromDate > $toDate) {
        echo '<div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                "From Date" must be on or before "To Date".
              </div>';
        $submitted = false;
    } else {
        $sql = "
            SELECT p.patientNO,
                   p.name       AS PatientName,
                   c.complainID AS ComplaintCode,
                   c.descr      AS Complaint,
                   t.treatID    AS TreatmentCode,
                   t.descr      AS Treatment,
                   ptp.startDate,
                   ptp.endDate
            FROM   PatientTreatmentPlan ptp
            JOIN   Patient   p ON ptp.patientNO   = p.patientNO
            JOIN   Complaint c ON ptp.complainID  = c.complainID
            JOIN   Treatment t ON ptp.treatID     = t.treatID
            WHERE  ptp.complainID = ?
              AND  ptp.startDate >= ?
              AND  ptp.startDate <= ?
            ORDER  BY t.descr, ptp.startDate";

        $stmt = qry($conn, $sql, [
            [$complainID, SQLSRV_PARAM_IN],
            [$fromDate,   SQLSRV_PARAM_IN],
            [$toDate,     SQLSRV_PARAM_IN],
        ]);
        if ($stmt) $rows = fetchAll($stmt);
    }
}
?>

<!-- ── Search Form ── -->
<div class="search-card">
  <form method="POST" class="row g-3 align-items-end">
    <div class="col-md-4">
      <label for="complaint_id">Complaint</label>
      <select id="complaint_id" name="complaint_id" class="form-select">
        <option value="">— Select Complaint —</option>
        <?php foreach ($complaints as $c): ?>
          <option value="<?= h($c['complainID']) ?>"
            <?= (isset($_POST['complaint_id']) && $_POST['complaint_id'] === $c['complainID']) ? 'selected' : '' ?>>
            <?= h($c['complainID']) ?> — <?= h($c['descr']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <label for="from_date">From Date</label>
      <input type="date" id="from_date" name="from_date" class="form-control"
             value="<?= isset($_POST['from_date']) ? h($_POST['from_date']) : '2026-01-01' ?>">
    </div>
    <div class="col-auto">
      <label for="to_date">To Date</label>
      <input type="date" id="to_date" name="to_date" class="form-control"
             value="<?= isset($_POST['to_date']) ? h($_POST['to_date']) : '2026-12-31' ?>">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn-search">
        <i class="bi bi-search me-1"></i>Search
      </button>
    </div>
    <?php if ($submitted): ?>
    <div class="col-auto">
      <a href="report11.php" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
    <?php endif; ?>
  </form>
</div>

<?php if (!$submitted): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Select a complaint and specify a date range, then click <strong>Search</strong> to list treatments.
  </div>
<?php elseif ($submitted): ?>

  <div class="card mb-5">
    <div class="card-header">
      <i class="bi bi-calendar-range me-2"></i>
      Treatments for
      <strong>
        <?php
          foreach ($complaints as $c) {
              if ($c['complainID'] === ($_POST['complaint_id'] ?? '')) {
                  echo h($c['complainID'] . ' — ' . $c['descr']);
              }
          }
        ?>
      </strong>
      between
      <?= h($_POST['from_date'] ?? '') ?> and <?= h($_POST['to_date'] ?? '') ?>
      <span class="badge bg-light text-dark ms-2" style="font-size:.75rem;"><?= count($rows) ?> rows</span>
    </div>
    <div class="card-body p-0">
      <?php if (empty($rows)): ?>
        <div class="no-results">
          No treatments found for this complaint in the specified date range.
        </div>
      <?php else: ?>
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
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
              $prevTrt = null;
              foreach ($rows as $r):
                $isNew = $r['TreatmentCode'] !== $prevTrt;
                $prevTrt = $r['TreatmentCode'];
              ?>
              <tr <?= $isNew ? 'style="border-top:2px solid var(--navy);"' : '' ?>>
                <td><?= $isNew ? '<code><strong>' . h($r['TreatmentCode']) . '</strong></code>' : '' ?></td>
                <td><?= $isNew ? '<strong>' . h($r['Treatment']) . '</strong>' : '' ?></td>
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

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
