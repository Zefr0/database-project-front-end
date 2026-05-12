<?php
require_once 'config/db.php';
$base         = '';
$pageTitle    = 'Hospital Dashboard';
$pageSubtitle = 'CS205 Database Lab — Milestone 3 · Spring 2026';
$pageBadge    = 'Hospital Management System';
require_once 'includes/header.php';

// Quick stats
$stats = [];
$counts = [
    'Patients'    => 'SELECT COUNT(*) AS n FROM Patient',
    'Doctors'     => 'SELECT COUNT(*) AS n FROM Doctor',
    'Consultants' => 'SELECT COUNT(*) AS n FROM Consultant',
    'Nurses'      => 'SELECT COUNT(*) AS n FROM Nurse',
    'Wards'       => 'SELECT COUNT(*) AS n FROM Ward',
    'Treatments'  => 'SELECT COUNT(*) AS n FROM PatientTreatmentPlan',
];
foreach ($counts as $label => $sql) {
    $s = sqlsrv_query($conn, $sql);
    $row = $s ? sqlsrv_fetch_array($s, SQLSRV_FETCH_ASSOC) : ['n' => '—'];
    $stats[$label] = $row['n'];
}
?>

<!-- ── Stats Row ── -->
<div class="row g-3 mb-4">
  <?php
  $icons = ['Patients'=>'person-fill','Doctors'=>'person-badge','Consultants'=>'award',
            'Nurses'=>'heart-pulse','Wards'=>'building','Treatments'=>'clipboard2-pulse'];
  foreach ($stats as $label => $val): ?>
  <div class="col-6 col-md-4 col-lg-2">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <i class="bi bi-<?= $icons[$label] ?>" style="font-size:1.6rem;color:var(--navy);"></i>
        <div style="font-size:1.8rem;font-weight:800;color:var(--navy);line-height:1.2;margin-top:4px;">
          <?= h($val) ?>
        </div>
        <div style="font-size:.78rem;color:var(--muted);font-weight:600;text-transform:uppercase;
                    letter-spacing:.06em;"><?= $label ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Forms Section ── -->
<div class="dashboard-section mb-4">
  <h5><i class="bi bi-file-text me-2"></i>Manual Record Forms</h5>
  <div class="row g-3">

    <div class="col-md-4">
      <a class="dash-card" href="forms/patient_form.php">
        <div class="icon"><i class="bi bi-person-lines-fill"></i></div>
        <div>
          <div class="title">Patient Record</div>
          <div class="desc">Look up a patient's demographics, assigned doctor, consultant, and full medical history by Patient No.</div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a class="dash-card" href="forms/ward_form.php">
        <div class="icon"><i class="bi bi-building"></i></div>
        <div>
          <div class="title">Ward Record</div>
          <div class="desc">Select a ward to view its sisters, care units, staff nurses, and all patients currently admitted.</div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a class="dash-card" href="forms/doctor_form.php">
        <div class="icon"><i class="bi bi-person-badge"></i></div>
        <div>
          <div class="title">Doctor Record</div>
          <div class="desc">Enter a Staff No to view a doctor's position, consultant, past experience, and performance grade history.</div>
        </div>
      </a>
    </div>

  </div>
</div>

<!-- ── Reports Section ── -->
<div class="dashboard-section mb-5">
  <h5><i class="bi bi-bar-chart-line me-2"></i>SQL Query Reports</h5>
  <div class="row g-3">
    <?php
    $reports = [
        1  => ['Consultant Teams',              'List of consultants and the doctors in their team.'],
        2  => ['Ward Sisters & Staff Nurses',   'Wards with sisters, care units, and staff nurses in charge.'],
        3  => ['Patient Treatments',            'Patients with their complaints, treatments, and treatment dates.'],
        4  => ['Junior Housemen',               'JH doctors with their patients and the patient\'s care-unit staff nurse.'],
        5  => ['Unique Specialties',            'Consultants who hold a specialty shared by no other consultant.'],
        6  => ['Complaints & Doctor Experience','Complaints, treatments given, and the treating doctor\'s experience history.'],
        7  => ['Multi-Complaint Patients',      'Patients with more than one complaint and their treatments.'],
        8  => ['Grouped by Treatment',          'Patients grouped by treatment within each complaint.'],
        9  => ['Doctor Performance History',    'Performance grade history for a specific doctor.'],
        10 => ['Full Patient Details',          'Complete medical details for a particular patient.'],
        11 => ['Treatments by Date Range',      'Treatments given for a complaint between two specified dates.'],
        12 => ['Staff Position Count',          'All staff positions in the hospital with a count of staff per position.'],
    ];
    foreach ($reports as $n => $info): ?>
    <div class="col-md-6 col-lg-3">
      <a class="dash-card" href="reports/report<?= $n ?>.php">
        <div class="icon report" style="min-width:42px;">
          <span style="font-size:.85rem;font-weight:800;">R<?= $n ?></span>
        </div>
        <div>
          <div class="title"><?= htmlspecialchars($info[0]) ?></div>
          <div class="desc"><?= htmlspecialchars($info[1]) ?></div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
