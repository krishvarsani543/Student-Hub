<?php
/**
 * Practical 11: Student Management CRUD with Search and Filter
 * Implements SELECT, INSERT, UPDATE, DELETE, Search, Course & Status Filtering, Pagination,
 * CSV Export (Intermediate), and Soft Delete (Advanced) using MySQLi.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";

// 1. Process Flash Messages
$flash = $_SESSION["flash"] ?? null;
unset($_SESSION["flash"]);

// 2. Fetch Query Parameters for Search, Filter, and Pagination
$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");
$page = max(1, (int)($_GET["page"] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// 3. Count Totals for Summary Statistics
$totalRes = $conn->query("SELECT 
    COUNT(*) as total_all,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as total_active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as total_inactive,
    SUM(CASE WHEN status = 'deleted' THEN 1 ELSE 0 END) as total_deleted
    FROM students");
$stats = $totalRes->fetch_assoc();

// 4. Fetch Unique Courses for the Filter Dropdown
$coursesRes = $conn->query("SELECT DISTINCT course FROM students WHERE course IS NOT NULL AND course != '' ORDER BY course ASC");
$coursesList = [];
while ($c = $coursesRes->fetch_assoc()) {
    $coursesList[] = $c["course"];
}

// 5. Build Dynamic MySQLi Prepared Query for Filtered Records
$whereClauses = ["1=1"];
$params = [];
$types  = "";

if ($search !== "") {
    $whereClauses[] = "(name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($courseFilter !== "") {
    $whereClauses[] = "course = ?";
    $params[] = $courseFilter;
    $types .= "s";
}

if ($statusFilter !== "") {
    $whereClauses[] = "status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$whereSql = implode(" AND ", $whereClauses);

// Count matching records for pagination
$countQuery = "SELECT COUNT(*) as filtered_total FROM students WHERE $whereSql";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$filteredTotal = $countStmt->get_result()->fetch_assoc()["filtered_total"] ?? 0;
$countStmt->close();

$totalPages = max(1, ceil($filteredTotal / $limit));

// Fetch paginated results
$dataQuery = "SELECT student_id, name, email, mobile, course, status, role, last_login, created_at 
              FROM students 
              WHERE $whereSql 
              ORDER BY student_id DESC 
              LIMIT ? OFFSET ?";

$paramsWithLimit = $params;
$paramsWithLimit[] = $limit;
$paramsWithLimit[] = $offset;
$typesWithLimit = $types . "ii";

$dataStmt = $conn->prepare($dataQuery);
$dataStmt->bind_param($typesWithLimit, ...$paramsWithLimit);
$dataStmt->execute();
$studentsResult = $dataStmt->get_result();
$students = [];
while ($row = $studentsResult->fetch_assoc()) {
    $students[] = $row;
}
$dataStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Management CRUD (Practical 11)</title>

    <link rel="stylesheet" href="../Practical-3/deshboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .crud-container {
            max-width: 1280px;
            margin: 25px auto;
            padding: 0 20px;
            font-size: 15px;
        }

        /* Flash Message Alert */
        .flash-alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .flash-success { background: #dcfce7; color: #166534; border-left: 5px solid #22c55e; }
        .flash-danger  { background: #fee2e2; color: #991b1b; border-left: 5px solid #ef4444; }
        .flash-warning { background: #fef3c7; color: #92400e; border-left: 5px solid #f59e0b; }
        .flash-info    { background: #e0f2fe; color: #075985; border-left: 5px solid #0284c7; }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-left: 5px solid #123B5D;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card.card-active  { border-left-color: #22c55e; }
        .stat-card.card-inactive{ border-left-color: #94a3b8; }
        .stat-card.card-deleted { border-left-color: #ef4444; }
        .stat-val { font-size: 26px; font-weight: bold; color: #1e293b; }
        .stat-lbl { font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 600; }

        /* Control Panel */
        .control-panel {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }
        .search-filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .input-group {
            position: relative;
            flex: 1 1 240px;
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            background: #fff;
        }
        .input-group input:focus, .input-group select:focus {
            border-color: #123B5D;
            box-shadow: 0 0 0 3px rgba(18, 59, 93, 0.15);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary   { background: #123B5D; color: white; }
        .btn-primary:hover { background: #0c273e; color: white; }
        .btn-success   { background: #16a34a; color: white; }
        .btn-success:hover { background: #15803d; color: white; }
        .btn-secondary { background: #64748b; color: white; }
        .btn-secondary:hover { background: #475569; color: white; }
        .btn-warning   { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; color: white; }
        .btn-danger    { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; color: white; }
        .btn-csv       { background: #0284c7; color: white; }
        .btn-csv:hover { background: #0369a1; color: white; }
        .btn-sm { padding: 5px 10px; font-size: 12px; border-radius: 4px; }

        /* Data Table */
        .table-responsive {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow-x: auto;
        }
        .students-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .students-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 2px solid #e2e8f0;
        }
        .students-table td {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }
        .students-table tr:hover { background: #f8fafc; }

        /* Status Pills */
        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-active   { background: #dcfce7; color: #166534; }
        .status-inactive { background: #f1f5f9; color: #475569; }
        .status-deleted  { background: #fee2e2; color: #991b1b; }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 6px;
        }

        /* Pagination */
        .pagination-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
        .pagination-links {
            display: flex;
            gap: 6px;
        }
        .page-link {
            padding: 6px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            text-decoration: none;
            color: #334155;
            font-weight: 600;
            font-size: 13px;
        }
        .page-link.active {
            background: #123B5D;
            color: white;
            border-color: #123B5D;
        }
        .page-link:hover:not(.active) { background: #f1f5f9; }

        /* Modals */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(3px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-box {
            background: white;
            width: 520px;
            max-width: 95%;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            overflow: hidden;
            animation: zoomIn 0.25s ease-out;
        }
        @keyframes zoomIn {
            from { transform: scale(0.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-header {
            background: #123B5D;
            color: white;
            padding: 16px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-header h3 { margin: 0; font-size: 18px; }
        .modal-close-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }
        .modal-body {
            padding: 22px;
        }
        .form-row {
            margin-bottom: 14px;
        }
        .form-row label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #334155;
            font-size: 13px;
        }
        .form-row input, .form-row select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }
        .form-row input:focus, .form-row select:focus {
            border-color: #123B5D;
            box-shadow: 0 0 0 3px rgba(18, 59, 93, 0.15);
        }
        .modal-footer {
            padding: 14px 22px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>

    <header>
        <div class="header-content">
            <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
                 alt="StudentHub Logo">

            <div class="header-text">
                <h1>StudentHub Portal</h1>
                <p><b>Practical 11: Student Management CRUD with Search & Filter</b></p>
            </div>
        </div>

        <nav>
            <center>
                <a href="index.php">Student Records</a>
                <a href="../Practical-10/login.php">Practical 10 Login</a>
                <a href="../Practical-2/events.html">Events</a>
                <a href="../Practical-2/profile.html">Profile</a>
            </center>
        </nav>
    </header>

    <div class="crud-container">

        <!-- Flash Feedback Message Alert -->
        <?php if ($flash): ?>
            <div class="flash-alert flash-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
                <div>
                    <?php if ($flash['type'] === 'success'): ?>
                        <i class="fa-solid fa-circle-check"></i>
                    <?php elseif ($flash['type'] === 'warning'): ?>
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-circle-xmark"></i>
                    <?php endif; ?>
                    &nbsp;<?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <span style="cursor: pointer;" onclick="this.parentElement.style.display='none';">&times;</span>
            </div>
        <?php endif; ?>

        <!-- Key Stats Display -->
        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= (int)$stats["total_all"] ?></div>
                    <div class="stat-lbl">Total Students</div>
                </div>
                <i class="fa-solid fa-users" style="font-size: 32px; color: #123B5D;"></i>
            </div>
            <div class="stat-card card-active">
                <div>
                    <div class="stat-val" style="color: #16a34a;"><?= (int)$stats["total_active"] ?></div>
                    <div class="stat-lbl">Active Students</div>
                </div>
                <i class="fa-solid fa-user-check" style="font-size: 32px; color: #22c55e;"></i>
            </div>
            <div class="stat-card card-inactive">
                <div>
                    <div class="stat-val" style="color: #64748b;"><?= (int)$stats["total_inactive"] ?></div>
                    <div class="stat-lbl">Inactive</div>
                </div>
                <i class="fa-solid fa-user-slash" style="font-size: 32px; color: #94a3b8;"></i>
            </div>
            <div class="stat-card card-deleted">
                <div>
                    <div class="stat-val" style="color: #ef4444;"><?= (int)$stats["total_deleted"] ?></div>
                    <div class="stat-lbl">Soft Deleted (Adv.)</div>
                </div>
                <i class="fa-solid fa-trash-can-arrow-up" style="font-size: 32px; color: #ef4444;"></i>
            </div>
        </div>

        <!-- Search, Filter & Quick Action Bar -->
        <div class="control-panel">
            <form method="GET" action="index.php" class="search-filter-form">
                <!-- Search by name/email/course -->
                <div class="input-group">
                    <input type="text" name="search" placeholder="Search name, email, course..." 
                           value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Course Filter Dropdown -->
                <div class="input-group" style="flex: 1 1 200px;">
                    <select name="course">
                        <option value="">-- All Courses --</option>
                        <?php foreach ($coursesList as $c): ?>
                            <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>" <?= ($courseFilter === $c ? "selected" : "") ?>>
                                <?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter Dropdown -->
                <div class="input-group" style="flex: 1 1 160px;">
                    <select name="status">
                        <option value="">-- All Statuses --</option>
                        <option value="active" <?= ($statusFilter === "active" ? "selected" : "") ?>>Active</option>
                        <option value="inactive" <?= ($statusFilter === "inactive" ? "selected" : "") ?>>Inactive</option>
                        <option value="deleted" <?= ($statusFilter === "deleted" ? "selected" : "") ?>>Soft Deleted</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <button type="submit" class="btn btn-primary" title="Apply Search & Filters">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>

                <a href="index.php" class="btn btn-secondary" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>

                <!-- Intermediate Extension: CSV Export -->
                <a href="export_csv.php?search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>&status=<?= urlencode($statusFilter) ?>" 
                   class="btn btn-csv" title="Download matching records to CSV">
                    <i class="fa-solid fa-file-csv"></i> Export CSV
                </a>

                <!-- Add Student Button -->
                <button type="button" class="btn btn-success" onclick="openAddModal()" style="margin-left: auto;">
                    <i class="fa-solid fa-user-plus"></i> + Add Student
                </button>
            </form>
        </div>

        <!-- Student Records Data Table -->
        <div class="table-responsive">
            <table class="students-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Student Name</th>
                        <th>University Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th style="text-align: center; width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><strong>#<?= (int)$s["student_id"] ?></strong></td>
                                <td>
                                    <i class="fa-solid fa-circle-user" style="color: #64748b; margin-right: 6px;"></i>
                                    <strong><?= htmlspecialchars($s["name"], ENT_QUOTES, "UTF-8") ?></strong>
                                </td>
                                <td><?= htmlspecialchars($s["email"], ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($s["mobile"] ?? "N/A", ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($s["course"] ?? "N/A", ENT_QUOTES, "UTF-8") ?></td>
                                <td>
                                    <?php if ($s["status"] === "active"): ?>
                                        <span class="status-pill status-active"><i class="fa-solid fa-check"></i> Active</span>
                                    <?php elseif ($s["status"] === "inactive"): ?>
                                        <span class="status-pill status-inactive"><i class="fa-solid fa-pause"></i> Inactive</span>
                                    <?php else: ?>
                                        <span class="status-pill status-deleted"><i class="fa-solid fa-ban"></i> Soft Deleted</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns" style="justify-content: center;">
                                        <!-- View Button -->
                                        <button class="btn btn-sm btn-secondary" title="View Details" 
                                                onclick="openViewModal(<?= (int)$s['student_id'] ?>)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        <button class="btn btn-sm btn-primary" title="Edit Student" 
                                                onclick="openEditModal(<?= (int)$s['student_id'] ?>)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- If Deleted: Show Restore Button -->
                                        <?php if ($s["status"] === "deleted"): ?>
                                            <form method="POST" action="student_action.php" style="display:inline; margin:0;" 
                                                  onsubmit="return confirm('Restore student #<?= (int)$s['student_id'] ?> back to active?');">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="student_id" value="<?= (int)$s['student_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Restore Active">
                                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Delete Button (Triggers Confirm Modal) -->
                                        <button class="btn btn-sm btn-danger" title="Delete Student" 
                                                onclick="openDeleteModal(<?= (int)$s['student_id'] ?>, '<?= htmlspecialchars(addslashes($s['name']), ENT_QUOTES, 'UTF-8') ?>', '<?= $s['status'] ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fa-solid fa-folder-open" style="font-size: 38px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                No student records found matching your search and filter criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination Bar -->
            <div class="pagination-bar">
                <div style="font-size: 13px; color: #64748b;">
                    Showing <strong><?= min($offset + 1, $filteredTotal) ?></strong> to 
                    <strong><?= min($offset + $limit, $filteredTotal) ?></strong> of 
                    <strong><?= (int)$filteredTotal ?></strong> students
                </div>

                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>&status=<?= urlencode($statusFilter) ?>" 
                           class="page-link">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>&status=<?= urlencode($statusFilter) ?>" 
                           class="page-link <?= ($i === $page ? 'active' : '') ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>&status=<?= urlencode($statusFilter) ?>" 
                           class="page-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: ADD STUDENT                                     -->
    <!-- ======================================================== -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fa-solid fa-user-plus"></i> Add New Student</h3>
                <button class="modal-close-btn" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST" action="student_action.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-row">
                        <label for="add_name">Full Name *</label>
                        <input type="text" id="add_name" name="name" placeholder="e.g. Aryan Patel" required>
                    </div>
                    <div class="form-row">
                        <label for="add_email">University Email *</label>
                        <input type="email" id="add_email" name="email" placeholder="e.g. 22dit099@charusat.edu.in" required>
                    </div>
                    <div class="form-row">
                        <label for="add_mobile">Mobile Number</label>
                        <input type="text" id="add_mobile" name="mobile" placeholder="10-digit mobile" maxlength="10">
                    </div>
                    <div class="form-row">
                        <label for="add_course">Course / Department *</label>
                        <select id="add_course" name="course" required>
                            <option value="">-- Select Course --</option>
                            <option value="B.Tech Information Technology">B.Tech Information Technology</option>
                            <option value="B.Tech Computer Engineering">B.Tech Computer Engineering</option>
                            <option value="B.Tech Computer Science">B.Tech Computer Science</option>
                            <option value="B.Tech Artificial Intelligence">B.Tech Artificial Intelligence</option>
                            <option value="B.Tech Civil Engineering">B.Tech Civil Engineering</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="add_status">Account Status</label>
                        <select id="add_status" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: EDIT STUDENT                                    -->
    <!-- ======================================================== -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fa-solid fa-user-pen"></i> Edit Student Record</h3>
                <button class="modal-close-btn" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" action="student_action.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="student_id" value="">
                <div class="modal-body">
                    <div class="form-row">
                        <label for="edit_name">Full Name *</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    <div class="form-row">
                        <label for="edit_email">University Email *</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="form-row">
                        <label for="edit_mobile">Mobile Number</label>
                        <input type="text" id="edit_mobile" name="mobile" maxlength="10">
                    </div>
                    <div class="form-row">
                        <label for="edit_course">Course / Department *</label>
                        <select id="edit_course" name="course" required>
                            <option value="B.Tech Information Technology">B.Tech Information Technology</option>
                            <option value="B.Tech Computer Engineering">B.Tech Computer Engineering</option>
                            <option value="B.Tech Computer Science">B.Tech Computer Science</option>
                            <option value="B.Tech Artificial Intelligence">B.Tech Artificial Intelligence</option>
                            <option value="B.Tech Civil Engineering">B.Tech Civil Engineering</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="edit_status">Account Status</label>
                        <select id="edit_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="deleted">Soft Deleted</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update Record</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 3: VIEW STUDENT DETAILS                            -->
    <!-- ======================================================== -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fa-solid fa-id-card"></i> Student Details</h3>
                <button class="modal-close-btn" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <p style="text-align:center; color:#64748b;">Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 4: CONFIRM DELETE (CONFIRMATION FOR KEY QUESTION #3)-->
    <!-- ======================================================== -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" style="background: #ef4444;">
                <h3><i class="fa-solid fa-triangle-exclamation"></i> Confirm Delete Operation</h3>
                <button class="modal-close-btn" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px; margin-bottom: 12px;">
                    Are you sure you want to delete student: <strong id="deleteStudentName"></strong> (ID: #<span id="deleteStudentId"></span>)?
                </p>
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px; border-radius: 6px; font-size: 13px; color: #991b1b; margin-bottom: 15px;">
                    <strong>Delete Options:</strong>
                    <ul style="margin: 6px 0 0 18px; padding: 0;">
                        <li><strong>Soft Delete (Advanced Extension):</strong> Keeps the student record safely in database but marks status as <code>deleted</code>.</li>
                        <li><strong>Permanent Delete:</strong> Permanently removes the row from the database (cannot be undone).</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer" style="justify-content: space-between;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>

                <div style="display: flex; gap: 8px;">
                    <!-- Soft Delete Form -->
                    <form method="POST" action="student_action.php" style="margin: 0;">
                        <input type="hidden" name="action" value="soft_delete">
                        <input type="hidden" id="softDeleteId" name="student_id" value="">
                        <button type="submit" class="btn btn-warning" title="Soft delete (status = deleted)">
                            <i class="fa-solid fa-eye-slash"></i> Soft Delete
                        </button>
                    </form>

                    <!-- Permanent Delete Form -->
                    <form method="POST" action="student_action.php" style="margin: 0;">
                        <input type="hidden" name="action" value="permanent_delete">
                        <input type="hidden" id="permanentDeleteId" name="student_id" value="">
                        <button type="submit" class="btn btn-danger" title="Hard delete from database">
                            <i class="fa-solid fa-trash-can"></i> Permanent Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Modal Handling & AJAX Record Fetch -->
    <script>
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        function openAddModal() {
            document.getElementById("addModal").style.display = "flex";
        }

        function openEditModal(studentId) {
            fetch(`get_student.php?id=${studentId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        const s = res.data;
                        document.getElementById("edit_id").value = s.student_id;
                        document.getElementById("edit_name").value = s.name;
                        document.getElementById("edit_email").value = s.email;
                        document.getElementById("edit_mobile").value = s.mobile || "";
                        document.getElementById("edit_course").value = s.course || "";
                        document.getElementById("edit_status").value = s.status || "active";
                        document.getElementById("editModal").style.display = "flex";
                    } else {
                        alert(res.message || "Failed to load student details.");
                    }
                })
                .catch(err => alert("Error connecting to server."));
        }

        function openViewModal(studentId) {
            const body = document.getElementById("viewModalBody");
            body.innerHTML = '<p style="text-align:center; color:#64748b;">Loading details...</p>';
            document.getElementById("viewModal").style.display = "flex";

            fetch(`get_student.php?id=${studentId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        const s = res.data;
                        body.innerHTML = `
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold; width: 140px;">Student ID:</td><td style="padding: 8px;">#${s.student_id}</td></tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Full Name:</td><td style="padding: 8px;">${s.name}</td></tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Email:</td><td style="padding: 8px;">${s.email}</td></tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Mobile:</td><td style="padding: 8px;">${s.mobile || 'N/A'}</td></tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Course:</td><td style="padding: 8px;">${s.course || 'N/A'}</td></tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Status:</td><td style="padding: 8px;"><strong>${s.status ? s.status.toUpperCase() : 'ACTIVE'}</strong></td></tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Role:</td><td style="padding: 8px;">${s.role || 'student'}</td></tr>
                                <tr><td style="padding: 8px; font-weight: bold;">Last Login:</td><td style="padding: 8px;">${s.last_login || 'Never'}</td></tr>
                            </table>
                        `;
                    } else {
                        body.innerHTML = `<p style="color: red;">${res.message || 'Student not found.'}</p>`;
                    }
                })
                .catch(err => {
                    body.innerHTML = '<p style="color: red;">Failed to retrieve record.</p>';
                });
        }

        function openDeleteModal(studentId, name, status) {
            document.getElementById("deleteStudentId").innerText = studentId;
            document.getElementById("deleteStudentName").innerText = name;
            document.getElementById("softDeleteId").value = studentId;
            document.getElementById("permanentDeleteId").value = studentId;
            document.getElementById("deleteModal").style.display = "flex";
        }

        // Close modal when clicking outside box
        window.onclick = function(event) {
            if (event.target.classList.contains("modal-overlay")) {
                event.target.style.display = "none";
            }
        };
    </script>
</body>
</html>
