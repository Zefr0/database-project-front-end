-- ============================================================
--  IVOR Paine Memorial Hospital Database
--  CS205 Spring 2026 | DDL Script + Initial Data Insertion
--  Based on student ER Map and ERD (draw.io files)
-- ============================================================
--  TABLE CREATION ORDER (respects FK dependencies):
--   1. Ward
--   2. CareUnit
--   3. Bed
--   4. Staff         (supertype)
--   5. Consultant    (subtype of Staff)
--   6. Doctor        (subtype of Staff)
--   7. DoctorTeam    (Doctor <-> Consultant with dateJoined)
--   8. PastExperience (weak entity of Doctor)
--   9. PerformanceGrade (weak entity of Doctor)
--  10. Nurse          (supertype)
--  11. Sister         (subtype of Nurse)
--  12. StaffNurse     (subtype of Nurse, manages CareUnit)
--  13. NonRegisteredNurse (subtype of Nurse)
--  14. Patient
--  15. Complaint
--  16. Treatment
--  17. PatientTreatmentPlan (ternary: Patient+Complaint+Treatment)
-- ============================================================

USE master;
GO

-- Drop and recreate database
IF EXISTS (SELECT name FROM sys.databases WHERE name = 'IVORHospital')
    DROP DATABASE IVORHospital;
GO

CREATE DATABASE IVORHospital;
GO

USE IVORHospital;
GO

-- ============================================================
--  1. WARD
--  PK: wardName
-- ============================================================
CREATE TABLE Ward (
    wardName    VARCHAR(50)     NOT NULL,
    CONSTRAINT PK_Ward PRIMARY KEY (wardName)
);
GO

-- ============================================================
--  2. CARE UNIT
--  PK: unitNo
--  FK: ward -> Ward(wardName)
-- ============================================================
CREATE TABLE CareUnit (
    unitNo      INT             NOT NULL,
    ward        VARCHAR(50)     NOT NULL,
    CONSTRAINT PK_CareUnit PRIMARY KEY (unitNo),
    CONSTRAINT FK_CareUnit_Ward FOREIGN KEY (ward)
        REFERENCES Ward(wardName)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);
GO

-- ============================================================
--  3. BED
--  PK: bedNo
--  FK: ward -> Ward(wardName)
-- ============================================================
CREATE TABLE Bed (
    bedNo       INT             NOT NULL,
    ward        VARCHAR(50)     NOT NULL,
    CONSTRAINT PK_Bed PRIMARY KEY (bedNo),
    CONSTRAINT FK_Bed_Ward FOREIGN KEY (ward)
        REFERENCES Ward(wardName)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);
GO

-- ============================================================
--  4. STAFF  (Supertype for Doctor and Consultant)
--  PK: staffID
-- ============================================================
CREATE TABLE Staff (
    staffID     VARCHAR(10)     NOT NULL,
    name        VARCHAR(100)    NOT NULL,
    CONSTRAINT PK_Staff PRIMARY KEY (staffID)
);
GO

-- ============================================================
--  5. CONSULTANT  (Subtype of Staff)
--  PK/FK: staffID -> Staff(staffID)
-- ============================================================
CREATE TABLE Consultant (
    staffID     VARCHAR(10)     NOT NULL,
    specialty   VARCHAR(100)    NOT NULL,
    CONSTRAINT PK_Consultant PRIMARY KEY (staffID),
    CONSTRAINT FK_Consultant_Staff FOREIGN KEY (staffID)
        REFERENCES Staff(staffID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
GO

-- ============================================================
--  6. DOCTOR  (Subtype of Staff)
--  PK/FK: staffID -> Staff(staffID)
--  position: student | jh | sh | ar | r
-- ============================================================
CREATE TABLE Doctor (
    staffID     VARCHAR(10)     NOT NULL,
    position    VARCHAR(20)     NOT NULL,
    consultant  VARCHAR(10)     NULL,   -- FK -> Consultant added after DoctorTeam pattern
    CONSTRAINT PK_Doctor PRIMARY KEY (staffID),
    CONSTRAINT FK_Doctor_Staff FOREIGN KEY (staffID)
        REFERENCES Staff(staffID),
    CONSTRAINT CK_Doctor_Position CHECK (position IN ('student','jh','sh','ar','r'))
);
GO

-- ============================================================
--  7. DOCTOR TEAM  (Relationship: Consultant leads team of Doctors)
--  PK: (doctor, consultant)
--  FK: doctor -> Doctor, consultant -> Consultant
--  Attribute: dateJoined
-- ============================================================
CREATE TABLE DoctorTeam (
    doctor      VARCHAR(10)     NOT NULL,
    consultant  VARCHAR(10)     NOT NULL,
    dateJoined  DATE            NOT NULL,
    CONSTRAINT PK_DoctorTeam PRIMARY KEY (doctor, consultant),
    CONSTRAINT FK_DoctorTeam_Doctor FOREIGN KEY (doctor)
        REFERENCES Doctor(staffID),
    CONSTRAINT FK_DoctorTeam_Consultant FOREIGN KEY (consultant)
        REFERENCES Consultant(staffID)
);
GO

-- ============================================================
--  8. PAST EXPERIENCE  (Weak entity of Doctor)
--  PK: (doctorID, expID)
--  Attributes: dateStart, dateJoin (end date), position, establishment
-- ============================================================
CREATE TABLE PastExperience (
    doctorID        VARCHAR(10)     NOT NULL,
    expID           INT             NOT NULL,
    dateStart       DATE            NOT NULL,
    dateEnd         DATE            NULL,
    position        VARCHAR(50)     NOT NULL,
    establishment   VARCHAR(150)    NOT NULL,
    CONSTRAINT PK_PastExperience PRIMARY KEY (doctorID, expID),
    CONSTRAINT FK_PastExp_Doctor FOREIGN KEY (doctorID)
        REFERENCES Doctor(staffID)
        ON DELETE CASCADE
);
GO

-- ============================================================
--  9. PERFORMANCE GRADE  (Weak entity of Doctor)
--  PK: (doctorID, perfID)
--  Attributes: grade, date
-- ============================================================
CREATE TABLE PerformanceGrade (
    doctorID    VARCHAR(10)     NOT NULL,
    perfID      INT             NOT NULL,
    grade       VARCHAR(5)      NOT NULL,
    dat         DATE            NOT NULL,
    CONSTRAINT PK_PerformanceGrade PRIMARY KEY (doctorID, perfID),
    CONSTRAINT FK_PerfGrade_Doctor FOREIGN KEY (doctorID)
        REFERENCES Doctor(staffID)
        ON DELETE CASCADE
);
GO

-- ============================================================
--  10. NURSE  (Supertype)
--  PK: nurseID
--  FK: ward -> Ward
--  role: day_sister | night_sister | staff_nurse | non_registered
-- ============================================================
CREATE TABLE Nurse (
    nurseID     VARCHAR(10)     NOT NULL,
    name        VARCHAR(100)    NOT NULL,
    role        VARCHAR(20)     NOT NULL,
    ward        VARCHAR(50)     NOT NULL,
    CONSTRAINT PK_Nurse PRIMARY KEY (nurseID),
    CONSTRAINT FK_Nurse_Ward FOREIGN KEY (ward)
        REFERENCES Ward(wardName)
        ON UPDATE CASCADE,
    CONSTRAINT CK_Nurse_Role CHECK (role IN ('day_sister','night_sister','staff_nurse','non_registered'))
);
GO

-- ============================================================
--  11. SISTER  (Subtype of Nurse)
--  PK/FK: nurseID -> Nurse
--  time: 'day' or 'night'
--  FK: ward -> Ward
-- ============================================================
CREATE TABLE Sister (
    nurseID     VARCHAR(10)     NOT NULL,
    time        VARCHAR(5)      NOT NULL,
    ward        VARCHAR(50)     NOT NULL,
    CONSTRAINT PK_Sister PRIMARY KEY (nurseID),
    CONSTRAINT FK_Sister_Nurse FOREIGN KEY (nurseID)
        REFERENCES Nurse(nurseID)
        ON DELETE CASCADE,
    CONSTRAINT FK_Sister_Ward FOREIGN KEY (ward)
        REFERENCES Ward(wardName)
        ON UPDATE CASCADE,
    CONSTRAINT CK_Sister_Time CHECK (time IN ('day','night'))
);
GO

-- ============================================================
--  12. STAFF NURSE  (Subtype of Nurse)
--  PK/FK: nurseID -> Nurse
--  unitManaging -> CareUnit (1:1 manages relationship)
-- ============================================================
CREATE TABLE StaffNurse (
    nurseID         VARCHAR(10)     NOT NULL,
    unitManaging    INT             NOT NULL,
    CONSTRAINT PK_StaffNurse PRIMARY KEY (nurseID),
    CONSTRAINT FK_StaffNurse_Nurse FOREIGN KEY (nurseID)
        REFERENCES Nurse(nurseID)
        ON DELETE CASCADE,
    CONSTRAINT FK_StaffNurse_CareUnit FOREIGN KEY (unitManaging)
        REFERENCES CareUnit(unitNo),
    CONSTRAINT UQ_StaffNurse_Unit UNIQUE (unitManaging)  -- 1:1 manages
);
GO

-- ============================================================
--  13. NON-REGISTERED NURSE  (Subtype of Nurse)
--  PK/FK: nurseID -> Nurse
--  unitAssigned -> CareUnit (assigned to care unit)
-- ============================================================
CREATE TABLE NonRegisteredNurse (
    nurseID         VARCHAR(10)     NOT NULL,
    unitAssigned    INT             NOT NULL,
    CONSTRAINT PK_NonRegNurse PRIMARY KEY (nurseID),
    CONSTRAINT FK_NonRegNurse_Nurse FOREIGN KEY (nurseID)
        REFERENCES Nurse(nurseID)
        ON DELETE CASCADE,
    CONSTRAINT FK_NonRegNurse_CareUnit FOREIGN KEY (unitAssigned)
        REFERENCES CareUnit(unitNo)
);
GO

-- ============================================================
--  14. PATIENT
--  PK: patientNO
--  FK: careUnit -> CareUnit, bed -> Bed, primaryDoctor -> Doctor
-- ============================================================
CREATE TABLE Patient (
    patientNO       INT             NOT NULL,
    name            VARCHAR(100)    NOT NULL,
    dob             DATE            NOT NULL,
    doA             DATE            NOT NULL,
    careUnit        INT             NOT NULL,
    bed             INT             NOT NULL,
    primaryDoctor   VARCHAR(10)     NOT NULL,
    CONSTRAINT PK_Patient PRIMARY KEY (patientNO),
    CONSTRAINT FK_Patient_CareUnit FOREIGN KEY (careUnit)
        REFERENCES CareUnit(unitNo),
    CONSTRAINT FK_Patient_Bed FOREIGN KEY (bed)
        REFERENCES Bed(bedNo),
    CONSTRAINT FK_Patient_Doctor FOREIGN KEY (primaryDoctor)
        REFERENCES Doctor(staffID),
    CONSTRAINT UQ_Patient_Bed UNIQUE (bed)  -- one patient per bed
);
GO

-- ============================================================
--  15. COMPLAINT
--  PK: complainID
-- ============================================================
CREATE TABLE Complaint (
    complainID  VARCHAR(10)     NOT NULL,
    descr       VARCHAR(255)    NOT NULL,
    CONSTRAINT PK_Complaint PRIMARY KEY (complainID)
);
GO

-- ============================================================
--  16. TREATMENT
--  PK: treatID
-- ============================================================
CREATE TABLE Treatment (
    treatID     VARCHAR(10)     NOT NULL,
    descr       VARCHAR(255)    NOT NULL,
    CONSTRAINT PK_Treatment PRIMARY KEY (treatID)
);
GO

-- ============================================================
--  17. PATIENT TREATMENT PLAN  (Ternary associative entity)
--  PK: (patientNO, complainID, treatID)
--  FK: patientNO -> Patient, complainID -> Complaint, treatID -> Treatment
--  Attributes: startDate, endDate (nullable = ongoing)
-- ============================================================
CREATE TABLE PatientTreatmentPlan (
    patientNO   INT             NOT NULL,
    complainID  VARCHAR(10)     NOT NULL,
    treatID     VARCHAR(10)     NOT NULL,
    startDate   DATE            NOT NULL,
    endDate     DATE            NULL,
    CONSTRAINT PK_PTP PRIMARY KEY (patientNO, complainID, treatID),
    CONSTRAINT FK_PTP_Patient FOREIGN KEY (patientNO)
        REFERENCES Patient(patientNO),
    CONSTRAINT FK_PTP_Complaint FOREIGN KEY (complainID)
        REFERENCES Complaint(complainID),
    CONSTRAINT FK_PTP_Treatment FOREIGN KEY (treatID)
        REFERENCES Treatment(treatID),
    CONSTRAINT CK_PTP_Dates CHECK (endDate IS NULL OR endDate >= startDate)
);
GO


-- ============================================================
-- ============================================================
--   INITIAL DATA INSERTION
--   Requirements: 30+ patients, 10+ doctors, 15+ nurses
--   All other tables: 10-20 records minimum
-- ============================================================
-- ============================================================

-- ============================================================
-- WARD (10 records)
-- ============================================================
INSERT INTO Ward (wardName) VALUES
('Orthopedic'),
('Geriatric'),
('Cardiology'),
('Neurology'),
('Pediatrics'),
('Oncology'),
('General Surgery'),
('Maternity'),
('Dermatology'),
('Psychiatry');
GO

-- ============================================================
-- CARE UNIT (20 records — 2 per ward)
-- ============================================================
INSERT INTO CareUnit (unitNo, ward) VALUES
(1,  'Orthopedic'),
(2,  'Orthopedic'),
(3,  'Geriatric'),
(4,  'Geriatric'),
(5,  'Cardiology'),
(6,  'Cardiology'),
(7,  'Neurology'),
(8,  'Neurology'),
(9,  'Pediatrics'),
(10, 'Pediatrics'),
(11, 'Oncology'),
(12, 'Oncology'),
(13, 'General Surgery'),
(14, 'General Surgery'),
(15, 'Maternity'),
(16, 'Maternity'),
(17, 'Dermatology'),
(18, 'Dermatology'),
(19, 'Psychiatry'),
(20, 'Psychiatry');
GO

-- ============================================================
-- BED (60 records — 6 per ward)
-- ============================================================
INSERT INTO Bed (bedNo, ward) VALUES
(1,  'Orthopedic'),(2,  'Orthopedic'),(3,  'Orthopedic'),
(4,  'Orthopedic'),(5,  'Orthopedic'),(6,  'Orthopedic'),
(7,  'Geriatric'), (8,  'Geriatric'), (9,  'Geriatric'),
(10, 'Geriatric'), (11, 'Geriatric'), (12, 'Geriatric'),
(13, 'Cardiology'),(14, 'Cardiology'),(15, 'Cardiology'),
(16, 'Cardiology'),(17, 'Cardiology'),(18, 'Cardiology'),
(19, 'Neurology'), (20, 'Neurology'), (21, 'Neurology'),
(22, 'Neurology'), (23, 'Neurology'), (24, 'Neurology'),
(25, 'Pediatrics'),(26, 'Pediatrics'),(27, 'Pediatrics'),
(28, 'Pediatrics'),(29, 'Pediatrics'),(30, 'Pediatrics'),
(31, 'Oncology'),  (32, 'Oncology'),  (33, 'Oncology'),
(34, 'Oncology'),  (35, 'Oncology'),  (36, 'Oncology'),
(37, 'General Surgery'),(38, 'General Surgery'),(39, 'General Surgery'),
(40, 'General Surgery'),(41, 'General Surgery'),(42, 'General Surgery'),
(43, 'Maternity'), (44, 'Maternity'), (45, 'Maternity'),
(46, 'Maternity'), (47, 'Maternity'), (48, 'Maternity'),
(49, 'Dermatology'),(50, 'Dermatology'),(51, 'Dermatology'),
(52, 'Dermatology'),(53, 'Dermatology'),(54, 'Dermatology'),
(55, 'Psychiatry'),(56, 'Psychiatry'),(57, 'Psychiatry'),
(58, 'Psychiatry'),(59, 'Psychiatry'),(60, 'Psychiatry');
GO

-- ============================================================
-- STAFF  (25 records: 10 consultants + 15 doctors, all are Staff)
-- ============================================================
INSERT INTO Staff (staffID, name) VALUES
-- Consultants
('C001','Dr. Aisha Malik'),
('C002','Dr. Omar Farooq'),
('C003','Dr. Sana Hussain'),
('C004','Dr. Tariq Mehmood'),
('C005','Dr. Nadia Baig'),
('C006','Dr. Faisal Qureshi'),
('C007','Dr. Rida Zaman'),
('C008','Dr. Hassan Iqbal'),
('C009','Dr. Zara Nawaz'),
('C010','Dr. Bilal Chaudhry'),
-- Doctors
('D001','Dr. Ali Raza'),
('D002','Dr. Sara Khan'),
('D003','Dr. Hamza Shah'),
('D004','Dr. Ayesha Javed'),
('D005','Dr. Usman Ghani'),
('D006','Dr. Fareeha Noor'),
('D007','Dr. Kamran Siddiqui'),
('D008','Dr. Mehwish Alam'),
('D009','Dr. Adnan Butt'),
('D010','Dr. Rabia Khalid'),
('D011','Dr. Imran Mirza'),
('D012','Dr. Sobia Rashid'),
('D013','Dr. Waqas Ahmad'),
('D014','Dr. Hina Saleem'),
('D015','Dr. Zubair Anwar');
GO

-- ============================================================
-- CONSULTANT  (10 records)
-- ============================================================
INSERT INTO Consultant (staffID, specialty) VALUES
('C001','Orthopedics'),
('C002','Geriatrics'),
('C003','Cardiology'),
('C004','Neurology'),
('C005','Pediatrics'),
('C006','Oncology'),
('C007','General Surgery'),
('C008','Obstetrics'),
('C009','Dermatology'),
('C010','Psychiatry');
GO

-- ============================================================
-- DOCTOR  (15 records)
-- position: student | jh | sh | ar | r
-- ============================================================
INSERT INTO Doctor (staffID, position, consultant) VALUES
('D001','r',   'C001'),
('D002','ar',  'C001'),
('D003','sh',  'C002'),
('D004','jh',  'C002'),
('D005','r',   'C003'),
('D006','ar',  'C003'),
('D007','sh',  'C004'),
('D008','jh',  'C004'),
('D009','r',   'C005'),
('D010','ar',  'C006'),
('D011','sh',  'C007'),
('D012','jh',  'C007'),
('D013','r',   'C008'),
('D014','ar',  'C009'),
('D015','student','C010');
GO

-- ============================================================
-- DOCTOR TEAM  (15 records — matches Doctor.consultant)
-- ============================================================
INSERT INTO DoctorTeam (doctor, consultant, dateJoined) VALUES
('D001','C001','2018-03-15'),
('D002','C001','2019-07-01'),
('D003','C002','2017-11-20'),
('D004','C002','2021-01-10'),
('D005','C003','2016-06-05'),
('D006','C003','2020-09-15'),
('D007','C004','2019-03-22'),
('D008','C004','2022-04-01'),
('D009','C005','2015-08-30'),
('D010','C006','2018-12-12'),
('D011','C007','2020-02-14'),
('D012','C007','2021-11-11'),
('D013','C008','2017-05-25'),
('D014','C009','2019-10-08'),
('D015','C010','2023-06-01');
GO

-- ============================================================
-- PAST EXPERIENCE  (20 records, ~1-2 per doctor)
-- ============================================================
INSERT INTO PastExperience (doctorID, expID, dateStart, dateEnd, position, establishment) VALUES
('D001',1,'2014-01-01','2016-06-30','jh','Shifa International Hospital'),
('D001',2,'2016-07-01','2018-02-28','sh','Pakistan Institute of Medical Sciences'),
('D002',1,'2016-01-01','2019-06-30','jh','Aga Khan Hospital Karachi'),
('D003',1,'2013-03-01','2017-10-31','sh','Mayo Hospital Lahore'),
('D004',1,'2019-01-01','2020-12-31','student','Rawalpindi Medical University'),
('D005',1,'2010-06-01','2014-05-31','jh','Liaquat National Hospital'),
('D005',2,'2014-06-01','2016-05-31','sh','CMH Rawalpindi'),
('D006',1,'2017-09-01','2020-08-31','jh','Holy Family Hospital'),
('D007',1,'2015-02-01','2019-03-31','ar','Benazir Bhutto Hospital'),
('D008',1,'2020-05-01','2021-12-31','student','Foundation University Islamabad'),
('D009',1,'2008-01-01','2012-12-31','jh','Services Hospital Lahore'),
('D009',2,'2013-01-01','2015-07-31','sh','DHQ Teaching Hospital'),
('D010',1,'2015-03-01','2018-11-30','jh','Jinnah Hospital Lahore'),
('D011',1,'2016-08-01','2020-01-31','sh','Pakistan Medical Center'),
('D012',1,'2019-06-01','2021-10-31','jh','Islamabad Diagnostic Centre'),
('D013',1,'2012-01-01','2017-04-30','ar','Lady Reading Hospital'),
('D014',1,'2017-01-01','2019-09-30','jh','Fatima Memorial Hospital'),
('D015',1,'2022-01-01',NULL,'student','Rawalpindi Medical University'),
('D006',2,'2021-01-01','2022-12-31','sh','Maroof International Hospital'),
('D007',2,'2019-04-01','2021-01-31','sh','Quaid-e-Azam International Hospital');
GO

-- ============================================================
-- PERFORMANCE GRADE  (20 records)
-- ============================================================
INSERT INTO PerformanceGrade (doctorID, perfID, grade, dat) VALUES
('D001',1,'A',  '2019-06-30'),
('D001',2,'A+', '2019-12-31'),
('D002',1,'B',  '2020-06-30'),
('D002',2,'B+', '2020-12-31'),
('D003',1,'A',  '2018-06-30'),
('D003',2,'A',  '2018-12-31'),
('D004',1,'C',  '2021-12-31'),
('D005',1,'A+', '2017-06-30'),
('D005',2,'A+', '2017-12-31'),
('D006',1,'B+', '2021-06-30'),
('D007',1,'A',  '2020-06-30'),
('D007',2,'A+', '2020-12-31'),
('D008',1,'C+', '2023-06-30'),
('D009',1,'A+', '2016-06-30'),
('D009',2,'A+', '2016-12-31'),
('D010',1,'B',  '2019-06-30'),
('D011',1,'A',  '2021-06-30'),
('D012',1,'B+', '2022-06-30'),
('D013',1,'A+', '2018-06-30'),
('D014',1,'B',  '2020-06-30');
GO

-- ============================================================
-- NURSE  (15 nurses: 10 sisters + 20 care nurses in supertype)
-- Total: 15 Sister entries + 10 StaffNurse + 10 NonRegistered = 35 nurses
-- Inserting 35 in Nurse supertype
-- ============================================================

-- Day and Night Sisters (10 sisters, 1 day + 1 night per 5 wards for brevity)
INSERT INTO Nurse (nurseID, name, role, ward) VALUES
('N001','Fatima Zahra',      'day_sister',    'Orthopedic'),
('N002','Amna Bibi',         'night_sister',  'Orthopedic'),
('N003','Sana Gul',          'day_sister',    'Geriatric'),
('N004','Hira Baig',         'night_sister',  'Geriatric'),
('N005','Zainab Akhtar',     'day_sister',    'Cardiology'),
('N006','Noor Fatima',       'night_sister',  'Cardiology'),
('N007','Maryam Riaz',       'day_sister',    'Neurology'),
('N008','Iqra Hussain',      'night_sister',  'Neurology'),
('N009','Rabia Malik',       'day_sister',    'Pediatrics'),
('N010','Lubna Shahid',      'night_sister',  'Pediatrics'),
-- Staff Nurses (1 per care unit, units 1-10)
('N011','Ayesha Tariq',      'staff_nurse',   'Orthopedic'),
('N012','Sadia Perveen',     'staff_nurse',   'Orthopedic'),
('N013','Bushra Naz',        'staff_nurse',   'Geriatric'),
('N014','Rukhsana Bibi',     'staff_nurse',   'Geriatric'),
('N015','Sumera Gul',        'staff_nurse',   'Cardiology'),
('N016','Tahira Nasim',      'staff_nurse',   'Cardiology'),
('N017','Farzana Khan',      'staff_nurse',   'Neurology'),
('N018','Gulshan Ara',       'staff_nurse',   'Neurology'),
('N019','Parveen Akhtar',    'staff_nurse',   'Pediatrics'),
('N020','Nasreen Bibi',      'staff_nurse',   'Pediatrics'),
-- Non-Registered Nurses (units 1-10, 1 per unit)
('N021','Kiran Shahbaz',     'non_registered','Orthopedic'),
('N022','Uzma Iqbal',        'non_registered','Orthopedic'),
('N023','Saima Rafiq',       'non_registered','Geriatric'),
('N024','Fouzia Bibi',       'non_registered','Geriatric'),
('N025','Asma Khalil',       'non_registered','Cardiology'),
('N026','Shagufta Naz',      'non_registered','Cardiology'),
('N027','Mehnaz Begum',      'non_registered','Neurology'),
('N028','Riffat Javed',      'non_registered','Neurology'),
('N029','Samina Parveen',    'non_registered','Pediatrics'),
('N030','Zara Saleem',       'non_registered','Pediatrics'),
-- Extra nurses for Oncology and others
('N031','Hina Anwar',        'day_sister',    'Oncology'),
('N032','Madiha Imran',      'night_sister',  'Oncology'),
('N033','Shazia Zafar',      'staff_nurse',   'Oncology'),
('N034','Nazish Rehman',     'staff_nurse',   'Oncology'),
('N035','Ammara Gill',       'non_registered','Oncology');
GO

-- ============================================================
-- SISTER  (subtype, 12 records)
-- ============================================================
INSERT INTO Sister (nurseID, time, ward) VALUES
('N001','day',   'Orthopedic'),
('N002','night', 'Orthopedic'),
('N003','day',   'Geriatric'),
('N004','night', 'Geriatric'),
('N005','day',   'Cardiology'),
('N006','night', 'Cardiology'),
('N007','day',   'Neurology'),
('N008','night', 'Neurology'),
('N009','day',   'Pediatrics'),
('N010','night', 'Pediatrics'),
('N031','day',   'Oncology'),
('N032','night', 'Oncology');
GO

-- ============================================================
-- STAFF NURSE  (subtype, 10 records — 1 per care unit 1-10)
-- ============================================================
INSERT INTO StaffNurse (nurseID, unitManaging) VALUES
('N011',1),
('N012',2),
('N013',3),
('N014',4),
('N015',5),
('N016',6),
('N017',7),
('N018',8),
('N019',9),
('N020',10);
GO

-- ============================================================
-- NON-REGISTERED NURSE  (subtype, 11 records)
-- ============================================================
INSERT INTO NonRegisteredNurse (nurseID, unitAssigned) VALUES
('N021',1),
('N022',2),
('N023',3),
('N024',4),
('N025',5),
('N026',6),
('N027',7),
('N028',8),
('N029',9),
('N030',10),
('N035',11);
GO

-- ============================================================
-- COMPLAINT  (15 records)
-- ============================================================
INSERT INTO Complaint (complainID, descr) VALUES
('CMP001','Fractured Femur'),
('CMP002','Lower Back Pain'),
('CMP003','Hip Replacement Required'),
('CMP004','Congestive Heart Failure'),
('CMP005','Atrial Fibrillation'),
('CMP006','Hypertension'),
('CMP007','Alzheimers Disease'),
('CMP008','Stroke'),
('CMP009','Epilepsy'),
('CMP010','Asthma'),
('CMP011','Pneumonia'),
('CMP012','Breast Cancer'),
('CMP013','Appendicitis'),
('CMP014','Gestational Diabetes'),
('CMP015','Severe Depression');
GO

-- ============================================================
-- TREATMENT  (15 records)
-- ============================================================
INSERT INTO Treatment (treatID, descr) VALUES
('TRT001','Physiotherapy'),
('TRT002','Surgical Fixation'),
('TRT003','Pain Management'),
('TRT004','Cardiac Medication'),
('TRT005','Defibrillation'),
('TRT006','Antihypertensive Drugs'),
('TRT007','Cognitive Therapy'),
('TRT008','Thrombolysis'),
('TRT009','Anticonvulsant Therapy'),
('TRT010','Bronchodilators'),
('TRT011','Antibiotics'),
('TRT012','Chemotherapy'),
('TRT013','Surgical Removal'),
('TRT014','Insulin Therapy'),
('TRT015','Antidepressants');
GO

-- ============================================================
-- PATIENT  (30 records)
-- ============================================================
INSERT INTO Patient (patientNO, name, dob, doA, careUnit, bed, primaryDoctor) VALUES
(1,  'Ahmed Raza',          '1985-04-12', '2026-01-05', 1,  1,  'D001'),
(2,  'Bilal Nawaz',         '1990-07-23', '2026-01-10', 1,  2,  'D001'),
(3,  'Farida Bibi',         '1952-11-03', '2026-01-15', 2,  3,  'D002'),
(4,  'Ghulam Rasool',       '1948-02-28', '2026-01-20', 2,  4,  'D002'),
(5,  'Hafsa Tariq',         '1966-09-17', '2026-01-25', 3,  7,  'D003'),
(6,  'Irfan Ullah',         '1971-12-30', '2026-02-01', 3,  8,  'D003'),
(7,  'Jawad Ali',           '1979-05-14', '2026-02-05', 4,  9,  'D004'),
(8,  'Kulsoom Begum',       '1945-03-22', '2026-02-10', 4,  10, 'D004'),
(9,  'Laila Gul',           '2001-08-09', '2026-02-12', 5,  13, 'D005'),
(10, 'Muhammad Asif',       '1982-06-16', '2026-02-15', 5,  14, 'D005'),
(11, 'Nadia Shahid',        '1995-01-29', '2026-02-18', 6,  15, 'D006'),
(12, 'Omar Hayat',          '1973-10-11', '2026-02-20', 6,  16, 'D006'),
(13, 'Palwasha Gul',        '1988-04-07', '2026-03-01', 7,  19, 'D007'),
(14, 'Qamar Zaman',         '1960-12-25', '2026-03-05', 7,  20, 'D007'),
(15, 'Rehana Perveen',      '1958-07-04', '2026-03-08', 8,  21, 'D008'),
(16, 'Saad Hussain',        '1999-09-13', '2026-03-10', 8,  22, 'D008'),
(17, 'Tayyaba Naz',         '2010-03-19', '2026-03-12', 9,  25, 'D009'),
(18, 'Uzair Khan',          '2015-06-02', '2026-03-15', 9,  26, 'D009'),
(19, 'Vinay Kumar',         '2018-11-27', '2026-03-17', 10, 27, 'D009'),
(20, 'Waseem Akbar',        '2008-01-05', '2026-03-20', 10, 28, 'D009'),
(21, 'Xiomara Abbas',       '1963-05-31', '2026-03-22', 1,  5,  'D001'),
(22, 'Yasmin Bibi',         '1977-08-17', '2026-03-25', 2,  6,  'D003'),
(23, 'Zahid Mehmood',       '1955-02-14', '2026-03-28', 3,  11, 'D005'),
(24, 'Ameer Hamza',         '1991-04-20', '2026-04-01', 4,  12, 'D007'),
(25, 'Baseera Khatoon',     '1946-10-06', '2026-04-03', 5,  17, 'D005'),
(26, 'Chaudhry Kamran',     '1984-03-11', '2026-04-05', 6,  18, 'D006'),
(27, 'Durre Shahwar',       '1970-07-29', '2026-04-07', 7,  23, 'D007'),
(28, 'Ehtisham Baig',       '1935-12-03', '2026-04-09', 8,  24, 'D008'),
(29, 'Farrukh Tashkentov',  '1993-09-22', '2026-04-11', 9,  29, 'D009'),
(30, 'Gul Meena',           '2012-05-15', '2026-04-13', 10, 30, 'D009');
GO

-- ============================================================
-- PATIENT TREATMENT PLAN  (30 records)
-- ============================================================
INSERT INTO PatientTreatmentPlan (patientNO, complainID, treatID, startDate, endDate) VALUES
(1,  'CMP001','TRT002','2026-01-06', '2026-02-10'),
(1,  'CMP001','TRT001','2026-02-11', NULL),
(2,  'CMP002','TRT003','2026-01-11', '2026-02-20'),
(3,  'CMP002','TRT001','2026-01-16', NULL),
(4,  'CMP003','TRT002','2026-01-21', '2026-03-10'),
(5,  'CMP004','TRT004','2026-01-26', NULL),
(6,  'CMP005','TRT005','2026-02-02', '2026-02-28'),
(6,  'CMP006','TRT006','2026-02-02', NULL),
(7,  'CMP006','TRT006','2026-02-06', NULL),
(8,  'CMP004','TRT004','2026-02-11', NULL),
(9,  'CMP010','TRT010','2026-02-13', '2026-03-01'),
(10, 'CMP004','TRT004','2026-02-16', NULL),
(11, 'CMP006','TRT006','2026-02-19', NULL),
(12, 'CMP005','TRT005','2026-02-21', '2026-03-15'),
(13, 'CMP007','TRT007','2026-03-02', NULL),
(14, 'CMP008','TRT008','2026-03-06', '2026-04-01'),
(15, 'CMP007','TRT007','2026-03-09', NULL),
(16, 'CMP009','TRT009','2026-03-11', NULL),
(17, 'CMP010','TRT010','2026-03-13', '2026-04-05'),
(18, 'CMP011','TRT011','2026-03-16', '2026-03-30'),
(19, 'CMP010','TRT010','2026-03-18', NULL),
(20, 'CMP011','TRT011','2026-03-21', '2026-04-10'),
(21, 'CMP002','TRT003','2026-03-23', NULL),
(22, 'CMP003','TRT002','2026-03-26', '2026-04-20'),
(23, 'CMP004','TRT004','2026-03-29', NULL),
(24, 'CMP008','TRT008','2026-04-02', NULL),
(25, 'CMP006','TRT006','2026-04-04', NULL),
(26, 'CMP012','TRT012','2026-04-06', NULL),
(28, 'CMP004','TRT004','2026-04-10', NULL),
(30, 'CMP010','TRT010','2026-04-14', NULL);
GO

-- ============================================================
-- END OF SCRIPT
-- ============================================================
PRINT 'Database IVORHospital created and populated successfully.';
GO
