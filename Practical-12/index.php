<?php
/**
 * Practical 12: Event Management CRUD with Poster Upload and File Validation
 * Features:
 * - Full CRUD: Add, View, Edit, Delete events with poster images
 * - Server-side file validation (MIME, size max 2MB, extension, image integrity)
 * - Intermediate Extension: Event category and open/closed filters
 * - Advanced Extension: Image renaming, upload directory protection (.htaccess), audit logging
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";

// 1. Process Flash Messages
$flash = $_SESSION["flash"] ?? null;
unset($_SESSION["flash"]);

// 2. Query Filters
$search = trim($_GET["search"] ?? "");
$categoryFilter = trim($_GET["category"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");

// 3. Stats Summary
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_events,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_events,
        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_events,
        COUNT(DISTINCT category) as total_categories
    FROM events
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// 4. Fetch Unique Categories for Filter
$catStmt = $pdo->query("SELECT DISTINCT category FROM events WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// 5. Build Dynamic Filter Query
$where = ["1=1"];
$params = [];

if ($search !== "") {
    $where[] = "(event_name LIKE ? OR venue LIKE ? OR description LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($categoryFilter !== "") {
    $where[] = "category = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter !== "") {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

$sql = "SELECT * FROM events WHERE " . implode(" AND ", $where) . " ORDER BY event_date ASC, event_time ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Event Management CRUD (Practical 12)</title>

    <link rel="stylesheet" href="../Practical-3/deshboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .event-container {
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
        .stat-card.card-open  { border-left-color: #22c55e; }
        .stat-card.card-closed{ border-left-color: #94a3b8; }
        .stat-card.card-cat   { border-left-color: #8b5cf6; }
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
        .btn-purple    { background: #7c3aed; color: white; }
        .btn-purple:hover { background: #6d28d9; color: white; }
        .btn-sm { padding: 6px 10px; font-size: 12px; border-radius: 4px; }

        /* Events Table */
        .table-responsive {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow-x: auto;
        }
        .events-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .events-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 2px solid #e2e8f0;
        }
        .events-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
        }
        .events-table tr:hover { background: #f8fafc; }

        /* Poster Thumbnail */
        .poster-thumb {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: transform 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .poster-thumb:hover { transform: scale(1.08); }
        .poster-placeholder {
            width: 55px;
            height: 55px;
            border-radius: 8px;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Pills */
        .badge-cat {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .cat-technical { background: #dbeafe; color: #1e40af; }
        .cat-workshop  { background: #dcfce7; color: #166534; }
        .cat-cultural  { background: #f3e8ff; color: #6b21a8; }
        .cat-sports    { background: #ffedd5; color: #9a3412; }
        .cat-seminar   { background: #e0e7ff; color: #3730a3; }

        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-open   { background: #dcfce7; color: #166534; }
        .status-closed { background: #f1f5f9; color: #475569; }

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
            width: 580px;
            max-width: 95%;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            overflow: hidden;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
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
            overflow-y: auto;
        }
        .form-row { margin-bottom: 14px; }
        .form-row label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #334155;
            font-size: 13px;
        }
        .form-row input, .form-row select, .form-row textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            background: #fff;
        }
        .form-row input:focus, .form-row select:focus, .form-row textarea:focus {
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
        .upload-tip {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            display: block;
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
                <p><b>Practical 12: Event Management CRUD with Poster Upload & File Validation</b></p>
            </div>
        </div>

        <nav>
            <center>
                <a href="index.php">Manage Events</a>
                <a href="../Practical-11/index.php">Student CRUD (Practical 11)</a>
                <a href="../Practical-10/login.php">Practical 10 Login</a>
                <a href="../Practical-2/events.html">Student Events View</a>
            </center>
        </nav>
    </header>

    <div class="event-container">

        <!-- Flash Feedback Alert -->
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

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= (int)$stats["total_events"] ?></div>
                    <div class="stat-lbl">Total Events</div>
                </div>
                <i class="fa-solid fa-calendar-days" style="font-size: 32px; color: #123B5D;"></i>
            </div>
            <div class="stat-card card-open">
                <div>
                    <div class="stat-val" style="color: #16a34a;"><?= (int)$stats["open_events"] ?></div>
                    <div class="stat-lbl">Open for Registration</div>
                </div>
                <i class="fa-solid fa-door-open" style="font-size: 32px; color: #22c55e;"></i>
            </div>
            <div class="stat-card card-closed">
                <div>
                    <div class="stat-val" style="color: #64748b;"><?= (int)$stats["closed_events"] ?></div>
                    <div class="stat-lbl">Closed / Concluded</div>
                </div>
                <i class="fa-solid fa-lock" style="font-size: 32px; color: #94a3b8;"></i>
            </div>
            <div class="stat-card card-cat">
                <div>
                    <div class="stat-val" style="color: #7c3aed;"><?= (int)$stats["total_categories"] ?></div>
                    <div class="stat-lbl">Categories</div>
                </div>
                <i class="fa-solid fa-layer-group" style="font-size: 32px; color: #8b5cf6;"></i>
            </div>
        </div>

        <!-- Search, Filter & Quick Action Bar -->
        <div class="control-panel">
            <form method="GET" action="index.php" class="search-filter-form">
                <!-- Search title / venue -->
                <div class="input-group">
                    <input type="text" name="search" placeholder="Search event title, venue, details..." 
                           value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Intermediate Extension: Category Filter -->
                <div class="input-group" style="flex: 1 1 180px;">
                    <select name="category">
                        <option value="">-- All Categories --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>" <?= ($categoryFilter === $cat ? 'selected' : '') ?>>
                                <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Intermediate Extension: Open/Closed Status Filter -->
                <div class="input-group" style="flex: 1 1 150px;">
                    <select name="status">
                        <option value="">-- All Statuses --</option>
                        <option value="open" <?= ($statusFilter === 'open' ? 'selected' : '') ?>>Open</option>
                        <option value="closed" <?= ($statusFilter === 'closed' ? 'selected' : '') ?>>Closed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" title="Apply Search & Filters">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>

                <a href="index.php" class="btn btn-secondary" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>

                <!-- Advanced Extension: Audit Trail Viewer -->
                <button type="button" class="btn btn-purple" onclick="openAuditModal()" title="View audit log of all event modifications">
                    <i class="fa-solid fa-clock-rotate-left"></i> Audit Logs
                </button>

                <!-- Add Event Button -->
                <button type="button" class="btn btn-success" onclick="openAddModal()" style="margin-left: auto;">
                    <i class="fa-solid fa-calendar-plus"></i> + Add Event
                </button>
            </form>
        </div>

        <!-- Events Records Table -->
        <div class="table-responsive">
            <table class="events-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Poster</th>
                        <th>Event Details</th>
                        <th>Category</th>
                        <th>Schedule</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th style="text-align: center; width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($events) > 0): ?>
                        <?php foreach ($events as $ev): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($ev["poster"]) && file_exists(__DIR__ . "/uploads/posters/" . $ev["poster"])): ?>
                                        <img src="uploads/posters/<?= htmlspecialchars($ev['poster'], ENT_QUOTES, 'UTF-8') ?>" 
                                             class="poster-thumb" alt="Poster" 
                                             onclick="openViewModal(<?= (int)$ev['event_id'] ?>)" title="Click to view">
                                    <?php else: ?>
                                        <div class="poster-placeholder" title="No poster uploaded">
                                            <i class="fa-regular fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($ev["event_name"], ENT_QUOTES, "UTF-8") ?></strong>
                                    <small style="display: block; color: #64748b; margin-top: 3px;">
                                        <?= htmlspecialchars(mb_strimwidth($ev["description"] ?? "No description", 0, 60, "..."), ENT_QUOTES, "UTF-8") ?>
                                    </small>
                                </td>
                                <td>
                                    <?php
                                        $catLower = strtolower($ev["category"]);
                                        $badgeClass = "cat-" . $catLower;
                                    ?>
                                    <span class="badge-cat <?= $badgeClass ?>">
                                        <?= htmlspecialchars($ev["category"], ENT_QUOTES, "UTF-8") ?>
                                    </span>
                                </td>
                                <td>
                                    <div><i class="fa-regular fa-calendar" style="color: #0284c7;"></i> <?= date("d M, Y", strtotime($ev["event_date"])) ?></div>
                                    <small style="color: #64748b;"><i class="fa-regular fa-clock"></i> <?= date("h:i A", strtotime($ev["event_time"])) ?></small>
                                </td>
                                <td>
                                    <i class="fa-solid fa-location-dot" style="color: #ef4444;"></i>
                                    <?= htmlspecialchars($ev["venue"], ENT_QUOTES, "UTF-8") ?>
                                </td>
                                <td>
                                    <?php if ($ev["status"] === "open"): ?>
                                        <span class="status-pill status-open"><i class="fa-solid fa-circle-check"></i> Open</span>
                                    <?php else: ?>
                                        <span class="status-pill status-closed"><i class="fa-solid fa-circle-xmark"></i> Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <!-- View Button -->
                                        <button class="btn btn-sm btn-secondary" title="View Details & Poster" 
                                                onclick="openViewModal(<?= (int)$ev['event_id'] ?>)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        <button class="btn btn-sm btn-primary" title="Edit Event & Poster" 
                                                onclick="openEditModal(<?= (int)$ev['event_id'] ?>)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- Quick Status Toggle -->
                                        <form method="POST" action="event_action.php" style="margin: 0; display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Toggle Open/Closed status">
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                            </button>
                                        </form>

                                        <!-- Delete Button (Triggers Confirm Modal) -->
                                        <button class="btn btn-sm btn-danger" title="Delete Event" 
                                                onclick="openDeleteModal(<?= (int)$ev['event_id'] ?>, '<?= htmlspecialchars(addslashes($ev['event_name']), ENT_QUOTES, 'UTF-8') ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fa-regular fa-calendar-xmark" style="font-size: 38px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                No events found matching your search and filter criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: ADD EVENT                                       -->
    <!-- ======================================================== -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fa-solid fa-calendar-plus"></i> Add New Event</h3>
                <button class="modal-close-btn" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST" action="event_action.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-row">
                        <label for="add_name">Event Title *</label>
                        <input type="text" id="add_name" name="event_name" placeholder="e.g. AI Innovation Summit 2026" required>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <div class="form-row" style="flex: 1;">
                            <label for="add_cat">Category *</label>
                            <select id="add_cat" name="category" required>
                                <option value="Technical" selected>Technical</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Cultural">Cultural</option>
                                <option value="Sports">Sports</option>
                                <option value="Seminar">Seminar</option>
                            </select>
                        </div>
                        <div class="form-row" style="flex: 1;">
                            <label for="add_status">Event Status *</label>
                            <select id="add_status" name="status" required>
                                <option value="open" selected>Open (Accepting Registrations)</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <div class="form-row" style="flex: 1;">
                            <label for="add_date">Event Date *</label>
                            <input type="date" id="add_date" name="event_date" required>
                        </div>
                        <div class="form-row" style="flex: 1;">
                            <label for="add_time">Event Time</label>
                            <input type="time" id="add_time" name="event_time" value="10:00">
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="add_venue">Venue *</label>
                        <input type="text" id="add_venue" name="venue" placeholder="e.g. Auditorium / Lab 102, CSPIT" required>
                    </div>

                    <div class="form-row">
                        <label for="add_desc">Event Description</label>
                        <textarea id="add_desc" name="description" rows="3" placeholder="Provide event schedule, rules, or details..."></textarea>
                    </div>

                    <!-- File Upload with Server-Side Validation -->
                    <div class="form-row">
                        <label for="add_poster">
                            <i class="fa-solid fa-file-image"></i> Event Poster Upload (Optional)
                        </label>
                        <input type="file" id="add_poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
                        <small class="upload-tip">
                            <i class="fa-solid fa-circle-info"></i> Allowed: JPG, PNG, WEBP. Max size: 2MB. Validated on server.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Save Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: EDIT EVENT                                      -->
    <!-- ======================================================== -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fa-solid fa-pen-to-square"></i> Edit Event</h3>
                <button class="modal-close-btn" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" action="event_action.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="event_id" value="">
                <div class="modal-body">
                    <div class="form-row">
                        <label for="edit_name">Event Title *</label>
                        <input type="text" id="edit_name" name="event_name" required>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <div class="form-row" style="flex: 1;">
                            <label for="edit_cat">Category *</label>
                            <select id="edit_cat" name="category" required>
                                <option value="Technical">Technical</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Cultural">Cultural</option>
                                <option value="Sports">Sports</option>
                                <option value="Seminar">Seminar</option>
                            </select>
                        </div>
                        <div class="form-row" style="flex: 1;">
                            <label for="edit_status">Status *</label>
                            <select id="edit_status" name="status" required>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <div class="form-row" style="flex: 1;">
                            <label for="edit_date">Event Date *</label>
                            <input type="date" id="edit_date" name="event_date" required>
                        </div>
                        <div class="form-row" style="flex: 1;">
                            <label for="edit_time">Event Time</label>
                            <input type="time" id="edit_time" name="event_time">
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="edit_venue">Venue *</label>
                        <input type="text" id="edit_venue" name="venue" required>
                    </div>

                    <div class="form-row">
                        <label for="edit_desc">Event Description</label>
                        <textarea id="edit_desc" name="description" rows="3"></textarea>
                    </div>

                    <!-- Current Poster & Replacement Option -->
                    <div class="form-row" id="edit_poster_preview_box" style="display: none; background: #f8fafc; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <label>Current Poster:</label>
                        <div style="display: flex; align-items: center; gap: 12px; margin-top: 4px;">
                            <img id="edit_poster_img" src="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;" alt="Current Poster">
                            <div>
                                <label style="font-weight: normal; font-size: 13px; color: #dc2626; cursor: pointer;">
                                    <input type="checkbox" name="remove_poster" value="1"> Remove current poster
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="edit_poster">Replace / Upload Poster Image</label>
                        <input type="file" id="edit_poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
                        <small class="upload-tip">Max 2MB (JPG, PNG, WEBP). Leave empty to retain current poster.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 3: VIEW EVENT DETAILS                              -->
    <!-- ======================================================== -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal-box" style="width: 650px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-circle-info"></i> Event Details</h3>
                <button class="modal-close-btn" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <p style="text-align: center; color: #64748b;">Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 4: CONFIRM DELETE                                  -->
    <!-- ======================================================== -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" style="background: #ef4444;">
                <h3><i class="fa-solid fa-triangle-exclamation"></i> Confirm Delete Event</h3>
                <button class="modal-close-btn" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px;">
                    Are you sure you want to permanently delete event: <strong id="deleteEventName"></strong>?
                </p>
                <p style="font-size: 13px; color: #dc2626; margin-top: 8px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> This action cannot be undone. Any uploaded poster file will also be permanently deleted from the server storage.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                <form method="POST" action="event_action.php" style="margin: 0;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="deleteEventId" name="event_id" value="">
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash-can"></i> Confirm Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 5: AUDIT LOGS (ADVANCED EXTENSION)                 -->
    <!-- ======================================================== -->
    <div id="auditModal" class="modal-overlay">
        <div class="modal-box" style="width: 800px;">
            <div class="modal-header" style="background: #7c3aed;">
                <h3><i class="fa-solid fa-clock-rotate-left"></i> Event Audit Trail (Advanced Extension)</h3>
                <button class="modal-close-btn" onclick="closeModal('auditModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 15px;">
                <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                    Tracks all CREATE, UPDATE, DELETE, and STATUS changes with timestamps and IP logging.
                </p>
                <div id="auditLogContent" style="max-height: 400px; overflow-y: auto;">
                    <p style="text-align: center; color: #64748b;">Loading logs...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('auditModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Client-side JavaScript -->
    <script>
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        function openAddModal() {
            document.getElementById("addModal").style.display = "flex";
        }

        function openEditModal(eventId) {
            fetch(`get_event.php?id=${eventId}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === "success") {
                        const ev = res.data;
                        document.getElementById("edit_id").value = ev.event_id;
                        document.getElementById("edit_name").value = ev.event_name;
                        document.getElementById("edit_cat").value = ev.category || "Technical";
                        document.getElementById("edit_status").value = ev.status || "open";
                        document.getElementById("edit_date").value = ev.event_date;
                        document.getElementById("edit_time").value = ev.event_time ? ev.event_time.substring(0, 5) : "10:00";
                        document.getElementById("edit_venue").value = ev.venue;
                        document.getElementById("edit_desc").value = ev.description || "";

                        const previewBox = document.getElementById("edit_poster_preview_box");
                        if (ev.poster) {
                            document.getElementById("edit_poster_img").src = "uploads/posters/" + ev.poster;
                            previewBox.style.display = "block";
                        } else {
                            previewBox.style.display = "none";
                        }

                        document.getElementById("editModal").style.display = "flex";
                    } else {
                        alert(res.message || "Failed to load event.");
                    }
                })
                .catch(err => alert("Error connecting to server."));
        }

        function openViewModal(eventId) {
            const body = document.getElementById("viewModalBody");
            body.innerHTML = '<p style="text-align: center; color: #64748b;">Loading details...</p>';
            document.getElementById("viewModal").style.display = "flex";

            fetch(`get_event.php?id=${eventId}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === "success") {
                        const ev = res.data;
                        let posterHtml = '';
                        if (ev.poster) {
                            posterHtml = `
                                <div style="text-align: center; margin-bottom: 15px;">
                                    <img src="uploads/posters/${ev.poster}" style="max-width: 100%; max-height: 280px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);" alt="Event Poster">
                                </div>
                            `;
                        } else {
                            posterHtml = `
                                <div style="background: #f1f5f9; padding: 25px; border-radius: 8px; text-align: center; color: #64748b; margin-bottom: 15px;">
                                    <i class="fa-regular fa-image" style="font-size: 32px; display: block; margin-bottom: 6px;"></i>
                                    No poster uploaded for this event
                                </div>
                            `;
                        }

                        body.innerHTML = `
                            ${posterHtml}
                            <h2 style="color: #123B5D; margin: 0 0 10px 0;">${ev.event_name}</h2>
                            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                <span class="badge-cat cat-${ev.category ? ev.category.toLowerCase() : 'technical'}">${ev.category}</span>
                                <span class="status-pill status-${ev.status}">${ev.status.toUpperCase()}</span>
                            </div>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 15px;">
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold; width: 140px;">Date & Time:</td><td style="padding: 8px;">${ev.event_date} at ${ev.event_time}</td></tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Venue:</td><td style="padding: 8px;">${ev.venue}</td></tr>
                                <tr><td style="padding: 8px; font-weight: bold;">Created At:</td><td style="padding: 8px;">${ev.created_at || 'N/A'}</td></tr>
                            </table>
                            <div style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <strong style="display: block; margin-bottom: 6px; color: #334155;">Description:</strong>
                                <p style="margin: 0; color: #475569; white-space: pre-line;">${ev.description || 'No description provided.'}</p>
                            </div>
                        `;
                    } else {
                        body.innerHTML = `<p style="color: red;">${res.message || 'Event not found.'}</p>`;
                    }
                })
                .catch(err => {
                    body.innerHTML = '<p style="color: red;">Failed to retrieve event record.</p>';
                });
        }

        function openDeleteModal(eventId, eventName) {
            document.getElementById("deleteEventId").value = eventId;
            document.getElementById("deleteEventName").innerText = eventName;
            document.getElementById("deleteModal").style.display = "flex";
        }

        function openAuditModal() {
            const container = document.getElementById("auditLogContent");
            container.innerHTML = '<p style="text-align: center; color: #64748b;">Loading logs...</p>';
            document.getElementById("auditModal").style.display = "flex";

            fetch("get_audit_logs.php")
                .then(r => r.json())
                .then(res => {
                    if (res.status === "success" && res.data.length > 0) {
                        let rows = res.data.map(log => `
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 8px; font-size: 12px; color: #64748b; white-space: nowrap;">${log.created_at}</td>
                                <td style="padding: 8px;">
                                    <span style="font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 4px; background: #e2e8f0; color: #334155;">
                                        ${log.action}
                                    </span>
                                </td>
                                <td style="padding: 8px; font-size: 13px;">${log.details}</td>
                                <td style="padding: 8px; font-size: 12px; color: #64748b;">${log.performed_by} (${log.ip_address})</td>
                            </tr>
                        `).join('');

                        container.innerHTML = `
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f1f5f9; text-align: left; font-size: 12px; color: #475569;">
                                        <th style="padding: 8px;">Timestamp</th>
                                        <th style="padding: 8px;">Action</th>
                                        <th style="padding: 8px;">Details</th>
                                        <th style="padding: 8px;">User / IP</th>
                                    </tr>
                                </thead>
                                <tbody>${rows}</tbody>
                            </table>
                        `;
                    } else {
                        container.innerHTML = '<p style="text-align:center; color:#64748b;">No audit logs recorded yet.</p>';
                    }
                })
                .catch(err => {
                    container.innerHTML = '<p style="color:red; text-align:center;">Failed to load audit logs.</p>';
                });
        }

        // Close on outside click
        window.onclick = function(e) {
            if (e.target.classList.contains("modal-overlay")) {
                e.target.style.display = "none";
            }
        };
    </script>
</body>
</html>
