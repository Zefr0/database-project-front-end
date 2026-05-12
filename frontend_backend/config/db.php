<?php
// ============================================================
//  IVOR Paine Memorial Hospital — Database Connection
//  Uses Microsoft sqlsrv extension with Windows Authentication
// ============================================================

$serverName = "localhost\\SQLEXPRESS";        // Change to "localhost\SQLEXPRESS" if needed
$database   = "IVORHospital";

$connectionInfo = [
    "Database"             => $database,
    "TrustServerCertificate" => true,
    "CharacterSet"         => "UTF-8",
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    $errors = sqlsrv_errors();
    $msg = "";
    foreach ($errors as $e) {
        $msg .= "[SQLSTATE {$e['SQLSTATE']}] {$e['message']}<br>";
    }
    die("
    <div style='font-family:Arial;padding:30px;background:#fff3cd;border:1px solid #ffc107;border-radius:6px;margin:20px;'>
        <h3 style='color:#856404;'>&#9888; Database Connection Failed</h3>
        <p style='color:#533f03;'>$msg</p>
        <hr>
        <p style='color:#533f03;font-size:13px;'>
            Check that:<br>
            &bull; SQL Server is running<br>
            &bull; The <code>sqlsrv</code> PHP extension is enabled in php.ini<br>
            &bull; The IVORHospital database exists (run the Milestone 2 DDL script)<br>
            &bull; The server name in <code>config/db.php</code> is correct (try <code>localhost\\SQLEXPRESS</code>)
        </p>
    </div>");
}

// -------------------------------------------------------
//  Helper: format a date value returned by sqlsrv
//  sqlsrv returns dates as PHP DateTime objects
// -------------------------------------------------------
function fmt(?DateTime $d, string $fallback = 'Ongoing'): string {
    if ($d === null) return $fallback;
    return $d->format('d/m/Y');
}

// -------------------------------------------------------
//  Helper: run a parameterized query and return stmt
// -------------------------------------------------------
function qry($conn, string $sql, array $params = []) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        $msg = "";
        foreach ($errors as $e) {
            $msg .= "[{$e['SQLSTATE']}] {$e['message']}<br>";
        }
        echo "<div class='alert alert-danger'><strong>Query Error:</strong><br>$msg</div>";
        return false;
    }
    return $stmt;
}

// -------------------------------------------------------
//  Helper: fetch all rows as associative array
// -------------------------------------------------------
function fetchAll($stmt): array {
    $rows = [];
    if ($stmt === false) return $rows;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

// -------------------------------------------------------
//  Helper: escape output for HTML display
// -------------------------------------------------------
function h($val): string {
    if ($val === null)              return '<em class="text-muted">—</em>';
    if ($val instanceof DateTime)  return $val->format('d/m/Y');
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}
