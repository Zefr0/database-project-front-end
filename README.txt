============================================================
 IVOR PAINE MEMORIAL HOSPITAL — Milestone 3 Setup Guide
 CS205 Spring 2026 | Group: 24I-0809 | 24I-0531 | 24I-0682
============================================================

PREREQUISITES
--------------
1. SQL Server (any edition) must be running with the IVORHospital
   database already created using the Milestone 2 DDL script.

2. PHP 8.x must be installed. Recommended: XAMPP (includes Apache + PHP).
   Download: https://www.apachefriends.org/download.html

3. Microsoft PHP Driver for SQL Server (sqlsrv extension) must be installed.

------------------------------------------------------------
STEP 1 — Install XAMPP
------------------------------------------------------------
- Download and install XAMPP for Windows (PHP 8.x version).
- Default install path: C:\xampp

------------------------------------------------------------
STEP 2 — Install the sqlsrv PHP Extension
------------------------------------------------------------
a) Download Microsoft PHP Driver for SQL Server from:
   https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server

b) Choose the correct DLL for your PHP version (e.g. php_sqlsrv_83_ts_x64.dll
   for PHP 8.3 Thread Safe 64-bit).

c) Copy both files to your PHP extensions folder:
   C:\xampp\php\ext\
     - php_sqlsrv_8X_ts_x64.dll
     - php_pdo_sqlsrv_8X_ts_x64.dll

d) Edit C:\xampp\php\php.ini — add these two lines:
   extension=php_sqlsrv_8X_ts_x64.dll
   extension=php_pdo_sqlsrv_8X_ts_x64.dll

e) Also install Microsoft ODBC Driver 18 for SQL Server:
   https://learn.microsoft.com/en-us/sql/connect/odbc/download-odbc-driver-for-sql-server

------------------------------------------------------------
STEP 3 — Configure the Database Connection
------------------------------------------------------------
Open hospital/config/db.php and verify:
   $serverName = "localhost";          // or ".\SQLEXPRESS" if using Express
   $database   = "IVORHospital";

If SQL Server uses a named instance (e.g. SQLEXPRESS), change to:
   $serverName = "localhost\SQLEXPRESS";

The connection uses Windows Authentication (no username/password needed).

------------------------------------------------------------
STEP 4 — Place Project Files
------------------------------------------------------------
Copy the entire "hospital" folder to XAMPP's web root:
   C:\xampp\htdocs\hospital\

------------------------------------------------------------
STEP 5 — Start XAMPP and Open the App
------------------------------------------------------------
a) Open XAMPP Control Panel and start Apache.
b) Open your browser and go to:
   http://localhost/hospital/

You should see the IVOR Paine Memorial Hospital dashboard.

------------------------------------------------------------
TROUBLESHOOTING
------------------------------------------------------------
- "sqlsrv_connect() undefined": The sqlsrv extension is not loaded.
  Check php.ini and make sure the DLL files are in the ext folder.

- "Connection failed": SQL Server is not running, or the server name
  is wrong. Try ".\SQLEXPRESS" or "localhost\MSSQLSERVER".

- "Database not found": Run the Milestone 2 DDL script first to create
  and populate the IVORHospital database.

- Restart Apache after any php.ini changes.
============================================================
