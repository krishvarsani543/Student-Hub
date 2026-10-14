<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Asynchronous Fetch API CRUD (Practical 13)</title>

    <link rel="stylesheet" href="../Practical-3/deshboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .spa-container {
            max-width: 1280px;
            margin: 25px auto;
            padding: 0 20px;
            font-size: 15px;
        }

        /* Toast Notification Container */
        #toastContainer {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            min-width: 300px;
            max-width: 420px;
            background: white;
            padding: 14px 18px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.18);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 14px;
            animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-left: 5px solid #123B5D;
            transition: opacity 0.3s, transform 0.3s;
        }
        @keyframes slideIn {
            from { transform: translateX(110%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast-success { border-left-color: #22c55e; color: #166534; }
        .toast-danger  { border-left-color: #ef4444; color: #991b1b; }
        .toast-info    { border-left-color: #0284c7; color: #075985; }
        .toast-warning { border-left-color: #f59e0b; color: #92400e; }
        .toast-close {
            margin-left: auto;
            cursor: pointer;
            color: #94a3b8;
            font-size: 18px;
        }
        .toast-close:hover { color: #334155; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 22px;
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
        .stat-val { font-size: 26px; font-weight: bold; color: #1e293b; }
        .stat-lbl { font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 600; }

        /* Controls Bar */
        .control-panel {
            background: white;
            padding: 18px 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: center;
        }
        .search-box-wrapper {
            position: relative;
            flex: 1 1 260px;
        }
        .search-box-wrapper input {
            width: 100%;
            padding: 10px 38px 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            box-sizing: border-box;
        }
        .search-box-wrapper input:focus {
            border-color: #123B5D;
            box-shadow: 0 0 0 3px rgba(18, 59, 93, 0.15);
        }
        .search-spinner {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #0284c7;
            display: none;
            font-size: 14px;
        }
        .filter-select {
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            background: white;
            flex: 1 1 160px;
        }
        .filter-select:focus {
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
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-primary   { background: #123B5D; color: white; }
        .btn-primary:hover:not(:disabled) { background: #0c273e; }
        .btn-success   { background: #16a34a; color: white; }
        .btn-success:hover:not(:disabled) { background: #15803d; }
        .btn-secondary { background: #64748b; color: white; }
        .btn-secondary:hover:not(:disabled) { background: #475569; }
        .btn-danger    { background: #ef4444; color: white; }
        .btn-danger:hover:not(:disabled) { background: #dc2626; }
        .btn-sm { padding: 6px 10px; font-size: 12px; border-radius: 4px; }

        /* Table & States */
        .table-responsive {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow-x: auto;
            position: relative;
            min-height: 250px;
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

        /* Loading Skeleton & Spinner */
        #loadingOverlay {
            display: none;
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(2px);
            z-index: 10;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            color: #123B5D;
            font-weight: 600;
        }
        .spinner {
            width: 36px;
            height: 36px;
            border: 4px solid #cbd5e1;
            border-top-color: #123B5D;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Badges */
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
        .form-row input.is-invalid, .form-row select.is-invalid {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }
        .modal-footer {
            padding: 14px 22px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Live Network Activity Bar (Viva & Testing Aid) */
        .network-bar {
            background: #0f172a;
            color: #94a3b8;
            padding: 10px 18px;
            border-radius: 8px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .network-status-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-200 { background: #166534; color: #86efac; }
        .status-201 { background: #15803d; color: #bbf7d0; }
        .status-404 { background: #991b1b; color: #fca5a5; }
        .status-422 { background: #854d0e; color: #fef08a; }
        .status-500 { background: #7f1d1d; color: #fecaca; }
    </style>
</head>
<body>

    <!-- Floating Toast Notification Container -->
    <div id="toastContainer"></div>

    <header>
        <div class="header-content">
            <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
                 alt="StudentHub Logo">

            <div class="header-text">
                <h1>StudentHub Portal</h1>
                <p><b>Practical 13: Asynchronous Fetch API CRUD & RESTful JSON API</b></p>
            </div>
        </div>

        <nav>
            <center>
                <a href="index.php">Async Events Hub</a>
                <a href="../Practical-12/index.php">Practical 12 (Poster Upload)</a>
                <a href="../Practical-11/index.php">Practical 11 (Student CRUD)</a>
                <a href="../Practical-10/login.php">Practical 10 Login</a>
            </center>
        </nav>
    </header>

    <div class="spa-container">

        <!-- Metrics Cards (Updated Asynchronously) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="stat-val" id="statTotal">-</div>
                    <div class="stat-lbl">Total Events</div>
                </div>
                <i class="fa-solid fa-calendar-days" style="font-size: 32px; color: #123B5D;"></i>
            </div>
            <div class="stat-card card-open">
                <div>
                    <div class="stat-val" id="statOpen" style="color: #16a34a;">-</div>
                    <div class="stat-lbl">Open for Registration</div>
                </div>
                <i class="fa-solid fa-door-open" style="font-size: 32px; color: #22c55e;"></i>
            </div>
            <div class="stat-card card-closed">
                <div>
                    <div class="stat-val" id="statClosed" style="color: #64748b;">-</div>
                    <div class="stat-lbl">Closed Events</div>
                </div>
                <i class="fa-solid fa-lock" style="font-size: 32px; color: #94a3b8;"></i>
            </div>
            <div class="stat-card" style="border-left-color: #0284c7;">
                <div>
                    <div class="stat-val" id="statStatus" style="color: #0284c7; font-size: 16px; margin-top: 6px;">
                        <i class="fa-solid fa-bolt"></i> Live API
                    </div>
                    <div class="stat-lbl">Fetch API Engine</div>
                </div>
                <i class="fa-solid fa-satellite-dish" style="font-size: 32px; color: #0284c7;"></i>
            </div>
        </div>

        <!-- Controls Bar with Debounced Search & Filters -->
        <div class="control-panel">
            <!-- Intermediate Extension: Debounced Search Input -->
            <div class="search-box-wrapper">
                <input type="text" id="searchInput" placeholder="Search events (Debounced 350ms)..." autocomplete="off">
                <i class="fa-solid fa-spinner fa-spin search-spinner" id="searchSpinner"></i>
            </div>

            <!-- Category Filter -->
            <select id="categoryFilter" class="filter-select">
                <option value="">-- All Categories --</option>
                <option value="Technical">Technical</option>
                <option value="Workshop">Workshop</option>
                <option value="Cultural">Cultural</option>
                <option value="Sports">Sports</option>
                <option value="Seminar">Seminar</option>
            </select>

            <!-- Status Filter -->
            <select id="statusFilter" class="filter-select">
                <option value="">-- All Statuses --</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
            </select>

            <!-- Refresh Button -->
            <button type="button" class="btn btn-secondary" onclick="loadEvents()" title="Reload list without refreshing browser">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>

            <!-- + Add Event Button -->
            <button type="button" class="btn btn-success" onclick="openAddModal()" style="margin-left: auto;">
                <i class="fa-solid fa-calendar-plus"></i> + Add Event
            </button>
        </div>

        <!-- Events Records Table with Asynchronous Loading Overlay -->
        <div class="table-responsive">
            <div id="loadingOverlay">
                <div class="spinner"></div>
                <span>Fetching events from API...</span>
            </div>

            <table class="events-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Event Details</th>
                        <th>Category</th>
                        <th>Schedule</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th style="text-align: center; width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <!-- Populated dynamically via JavaScript Fetch API -->
                </tbody>
            </table>
        </div>

        <!-- Live Network Activity Bar for Viva & Screenshots -->
        <div class="network-bar">
            <div>
                <i class="fa-solid fa-network-wired" style="color: #38bdf8;"></i>
                <strong style="color: #f8fafc; margin-left: 6px;">Latest Fetch API Request:</strong>
                <span id="netMethod" style="color: #fbbf24; margin-left: 6px;">GET</span>
                <span id="netUrl" style="color: #cbd5e1; margin-left: 4px;">api.php</span>
            </div>
            <div>
                <span>Status:</span>
                <span id="netStatus" class="network-status-badge status-200">200 OK</span>
                <span id="netLatency" style="color: #64748b; margin-left: 8px;">-- ms</span>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: ADD EVENT                                       -->
    <!-- ======================================================== -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class="fa-solid fa-calendar-plus"></i> Add Event (Fetch POST)</h3>
                <button class="modal-close-btn" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form id="addEventForm" onsubmit="handleCreateEvent(event)">
                <div class="modal-body">
                    <div class="form-row">
                        <label for="add_name">Event Title *</label>
                        <input type="text" id="add_name" name="event_name" placeholder="e.g. Google Cloud Next 2026" required>
                        <div class="invalid-feedback" id="add_err_name"></div>
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
                            <div class="invalid-feedback" id="add_err_cat"></div>
                        </div>
                        <div class="form-row" style="flex: 1;">
                            <label for="add_status">Event Status *</label>
                            <select id="add_status" name="status" required>
                                <option value="open" selected>Open</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <div class="form-row" style="flex: 1;">
                            <label for="add_date">Event Date *</label>
                            <input type="date" id="add_date" name="event_date" required>
                            <div class="invalid-feedback" id="add_err_date"></div>
                        </div>
                        <div class="form-row" style="flex: 1;">
                            <label for="add_time">Event Time</label>
                            <input type="time" id="add_time" name="event_time" value="10:00">
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="add_venue">Venue *</label>
                        <input type="text" id="add_venue" name="venue" placeholder="e.g. Auditorium / Lab 102" required>
                        <div class="invalid-feedback" id="add_err_venue"></div>
                    </div>

                    <div class="form-row">
                        <label for="add_desc">Event Description</label>
                        <textarea id="add_desc" name="description" rows="3" placeholder="Provide agenda or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btnAddSubmit">
                        <i class="fa-solid fa-check"></i> Save Event
                    </button>
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
                <h3><i class="fa-solid fa-pen-to-square"></i> Edit Event (Fetch PUT)</h3>
                <button class="modal-close-btn" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form id="editEventForm" onsubmit="handleUpdateEvent(event)">
                <input type="hidden" id="edit_id" name="event_id">
                <div class="modal-body">
                    <div class="form-row">
                        <label for="edit_name">Event Title *</label>
                        <input type="text" id="edit_name" name="event_name" required>
                        <div class="invalid-feedback" id="edit_err_name"></div>
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
                            <label for="edit_status">Event Status *</label>
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
                            <div class="invalid-feedback" id="edit_err_date"></div>
                        </div>
                        <div class="form-row" style="flex: 1;">
                            <label for="edit_time">Event Time</label>
                            <input type="time" id="edit_time" name="event_time">
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="edit_venue">Venue *</label>
                        <input type="text" id="edit_venue" name="venue" required>
                        <div class="invalid-feedback" id="edit_err_venue"></div>
                    </div>

                    <div class="form-row">
                        <label for="edit_desc">Event Description</label>
                        <textarea id="edit_desc" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnEditSubmit">
                        <i class="fa-solid fa-floppy-disk"></i> Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 3: VIEW EVENT                                      -->
    <!-- ======================================================== -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal-box" style="width: 620px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-circle-info"></i> Event Details (Fetch GET)</h3>
                <button class="modal-close-btn" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewModalContent">
                <p style="text-align:center; color:#64748b;">Loading...</p>
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
                <h3><i class="fa-solid fa-triangle-exclamation"></i> Confirm Delete (Fetch DELETE)</h3>
                <button class="modal-close-btn" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px;">
                    Are you sure you want to delete event: <strong id="deleteEventName"></strong> (ID: #<span id="deleteEventId"></span>)?
                </p>
                <p style="font-size: 13px; color: #dc2626; margin-top: 8px;">
                    This record will be permanently deleted via an asynchronous <code>DELETE</code> API call.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete" onclick="executeDeleteEvent()">
                    <i class="fa-solid fa-trash-can"></i> Confirm Delete
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- JAVASCRIPT: FETCH API CRUD & DEBOUNCE ENGINE             -->
    <!-- ======================================================== -->
    <script>
        // State variables
        let pendingDeleteId = null;
        let debounceTimer = null;

        // Toast Notification System (Key Question #3)
        function showToast(type, message) {
            const container = document.getElementById("toastContainer");
            const toast = document.createElement("div");
            toast.className = `toast toast-${type}`;

            let icon = "fa-circle-check";
            if (type === "danger") icon = "fa-circle-xmark";
            if (type === "warning") icon = "fa-triangle-exclamation";
            if (type === "info") icon = "fa-circle-info";

            toast.innerHTML = `
                <i class="fa-solid ${icon}"></i>
                <span>${message}</span>
                <span class="toast-close" onclick="this.parentElement.remove()">&times;</span>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.transform = "translateX(110%)";
                toast.style.opacity = "0";
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Network Activity Bar Logger
        function logNetwork(method, url, status, latency) {
            document.getElementById("netMethod").innerText = method;
            document.getElementById("netUrl").innerText = url;
            const badge = document.getElementById("netStatus");
            badge.innerText = status;
            badge.className = `network-status-badge status-${status.split(' ')[0]}`;
            document.getElementById("netLatency").innerText = `${latency}ms`;
        }

        // Modal Utilities
        function closeModal(id) {
            document.getElementById(id).style.display = "none";
        }
        function openAddModal() {
            document.getElementById("addEventForm").reset();
            clearValidationErrors("add");
            document.getElementById("addModal").style.display = "flex";
        }

        // Centralized API Request Wrapper with Timing & Status Codes
        async function apiRequest(endpoint, options = {}) {
            const start = performance.now();
            const method = options.method || "GET";

            try {
                const response = await fetch(endpoint, options);
                const latency = Math.round(performance.now() - start);
                logNetwork(method, endpoint, `${response.status} ${response.statusText}`, latency);

                const data = await response.json();
                return { ok: response.ok, status: response.status, data };
            } catch (err) {
                const latency = Math.round(performance.now() - start);
                logNetwork(method, endpoint, "Network Error", latency);
                throw err;
            }
        }

        // 1. READ / LIST EVENTS (GET Request)
        async function loadEvents() {
            const overlay = document.getElementById("loadingOverlay");
            overlay.style.display = "flex";

            const search = document.getElementById("searchInput").value.trim();
            const category = document.getElementById("categoryFilter").value;
            const status = document.getElementById("statusFilter").value;

            const params = new URLSearchParams();
            if (search) params.append("search", search);
            if (category) params.append("category", category);
            if (status) params.append("status", status);

            const url = `api.php?${params.toString()}`;

            try {
                const res = await apiRequest(url, { method: "GET" });
                overlay.style.display = "none";

                if (res.ok && res.data.success) {
                    renderTable(res.data.data.events);
                    updateStats(res.data.data.stats);
                } else {
                    showToast("danger", res.data.message || "Failed to load events.");
                }
            } catch (err) {
                overlay.style.display = "none";
                showToast("danger", "Could not connect to the API server. Please check MySQL/Apache.");
            }
        }

        // Render Events Table Rows
        function renderTable(events) {
            const tbody = document.getElementById("eventsTableBody");

            if (!events || events.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 45px; color: #64748b;">
                            <i class="fa-regular fa-calendar-xmark" style="font-size: 40px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                            No events found matching your search and filter criteria.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = events.map(ev => {
                const catLower = (ev.category || "technical").toLowerCase();
                const descSnippet = ev.description ? (ev.description.length > 55 ? ev.description.substring(0, 55) + '...' : ev.description) : 'No description provided.';
                const isOpen = (ev.status === "open");

                return `
                    <tr>
                        <td><strong>#${ev.event_id}</strong></td>
                        <td>
                            <strong style="color: #0f172a;">${escapeHtml(ev.event_name)}</strong>
                            <small style="display: block; color: #64748b; margin-top: 3px;">${escapeHtml(descSnippet)}</small>
                        </td>
                        <td>
                            <span class="badge-cat cat-${catLower}">${escapeHtml(ev.category)}</span>
                        </td>
                        <td>
                            <div><i class="fa-regular fa-calendar" style="color: #0284c7;"></i> ${formatDate(ev.event_date)}</div>
                            <small style="color: #64748b;"><i class="fa-regular fa-clock"></i> ${ev.event_time ? ev.event_time.substring(0, 5) : '10:00'}</small>
                        </td>
                        <td>
                            <i class="fa-solid fa-location-dot" style="color: #ef4444;"></i>
                            ${escapeHtml(ev.venue)}
                        </td>
                        <td>
                            <span class="status-pill status-${ev.status}">
                                <i class="fa-solid ${isOpen ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
                                ${ev.status.toUpperCase()}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button class="btn btn-sm btn-secondary" title="View Event" onclick="openViewModal(${ev.event_id})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" title="Edit Event" onclick="openEditModal(${ev.event_id})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" title="Delete Event" onclick="openDeleteModal(${ev.event_id}, '${escapeJs(ev.event_name)}')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join("");
        }

        // Update Stat Cards
        function updateStats(stats) {
            if (!stats) return;
            document.getElementById("statTotal").innerText = stats.total ?? 0;
            document.getElementById("statOpen").innerText = stats.open ?? 0;
            document.getElementById("statClosed").innerText = stats.closed ?? 0;
        }

        // 2. CREATE EVENT (POST Request)
        async function handleCreateEvent(e) {
            e.preventDefault();
            clearValidationErrors("add");

            const btn = document.getElementById("btnAddSubmit");
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            const payload = {
                event_name:  document.getElementById("add_name").value.trim(),
                category:    document.getElementById("add_cat").value,
                status:      document.getElementById("add_status").value,
                event_date:  document.getElementById("add_date").value,
                event_time:  document.getElementById("add_time").value,
                venue:       document.getElementById("add_venue").value.trim(),
                description: document.getElementById("add_desc").value.trim(),
            };

            try {
                const res = await apiRequest("api.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });

                btn.innerHTML = originalText;
                btn.disabled = false;

                if (res.status === 201 && res.data.success) {
                    closeModal("addModal");
                    showToast("success", res.data.message);
                    loadEvents(); // Asynchronous refresh
                } else if (res.status === 422 && res.data.errors) {
                    showValidationErrors("add", res.data.errors);
                    showToast("danger", res.data.message || "Please fix validation errors.");
                } else {
                    showToast("danger", res.data.message || "Failed to create event.");
                }
            } catch (err) {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast("danger", "Network error occurred while creating event.");
            }
        }

        // 3. EDIT / UPDATE EVENT (GET single + PUT Request)
        async function openEditModal(id) {
            clearValidationErrors("edit");

            try {
                const res = await apiRequest(`api.php?id=${id}`, { method: "GET" });
                if (res.ok && res.data.success) {
                    const ev = res.data.data;
                    document.getElementById("edit_id").value = ev.event_id;
                    document.getElementById("edit_name").value = ev.event_name;
                    document.getElementById("edit_cat").value = ev.category;
                    document.getElementById("edit_status").value = ev.status;
                    document.getElementById("edit_date").value = ev.event_date;
                    document.getElementById("edit_time").value = ev.event_time ? ev.event_time.substring(0, 5) : "10:00";
                    document.getElementById("edit_venue").value = ev.venue;
                    document.getElementById("edit_desc").value = ev.description || "";

                    document.getElementById("editModal").style.display = "flex";
                } else {
                    showToast("danger", res.data.message || "Failed to fetch event details.");
                }
            } catch (err) {
                showToast("danger", "Error loading event for editing.");
            }
        }

        async function handleUpdateEvent(e) {
            e.preventDefault();
            clearValidationErrors("edit");

            const id = document.getElementById("edit_id").value;
            const btn = document.getElementById("btnEditSubmit");
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
            btn.disabled = true;

            const payload = {
                event_name:  document.getElementById("edit_name").value.trim(),
                category:    document.getElementById("edit_cat").value,
                status:      document.getElementById("edit_status").value,
                event_date:  document.getElementById("edit_date").value,
                event_time:  document.getElementById("edit_time").value,
                venue:       document.getElementById("edit_venue").value.trim(),
                description: document.getElementById("edit_desc").value.trim(),
            };

            try {
                const res = await apiRequest(`api.php?id=${id}`, {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });

                btn.innerHTML = originalText;
                btn.disabled = false;

                if (res.status === 200 && res.data.success) {
                    closeModal("editModal");
                    showToast("success", res.data.message);
                    loadEvents(); // Asynchronous refresh without page reload
                } else if (res.status === 422 && res.data.errors) {
                    showValidationErrors("edit", res.data.errors);
                    showToast("danger", res.data.message || "Validation failed.");
                } else {
                    showToast("danger", res.data.message || "Failed to update event.");
                }
            } catch (err) {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast("danger", "Network error occurred while updating event.");
            }
        }

        // 4. VIEW EVENT DETAILS (GET Request)
        async function openViewModal(id) {
            const content = document.getElementById("viewModalContent");
            content.innerHTML = '<p style="text-align:center; color:#64748b;"><i class="fa-solid fa-spinner fa-spin"></i> Loading details...</p>';
            document.getElementById("viewModal").style.display = "flex";

            try {
                const res = await apiRequest(`api.php?id=${id}`, { method: "GET" });
                if (res.ok && res.data.success) {
                    const ev = res.data.data;
                    const catLower = (ev.category || "technical").toLowerCase();
                    content.innerHTML = `
                        <h2 style="color: #123B5D; margin: 0 0 10px 0;">${escapeHtml(ev.event_name)}</h2>
                        <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                            <span class="badge-cat cat-${catLower}">${escapeHtml(ev.category)}</span>
                            <span class="status-pill status-${ev.status}">${ev.status.toUpperCase()}</span>
                        </div>
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 14px;">
                            <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold; width: 140px;">Event ID:</td><td style="padding: 8px;">#${ev.event_id}</td></tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Date & Time:</td><td style="padding: 8px;">${ev.event_date} at ${ev.event_time}</td></tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px; font-weight: bold;">Venue:</td><td style="padding: 8px;">${escapeHtml(ev.venue)}</td></tr>
                            <tr><td style="padding: 8px; font-weight: bold;">Created:</td><td style="padding: 8px;">${ev.created_at || 'N/A'}</td></tr>
                        </table>
                        <div style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <strong style="display: block; margin-bottom: 6px; color: #334155;">Description:</strong>
                            <p style="margin: 0; color: #475569; white-space: pre-line;">${escapeHtml(ev.description || 'No description provided.')}</p>
                        </div>
                    `;
                } else {
                    content.innerHTML = `<p style="color: red;">${res.data.message || 'Event not found.'}</p>`;
                }
            } catch (err) {
                content.innerHTML = '<p style="color: red;">Error retrieving event details.</p>';
            }
        }

        // 5. DELETE EVENT (DELETE Request)
        function openDeleteModal(id, name) {
            pendingDeleteId = id;
            document.getElementById("deleteEventId").innerText = id;
            document.getElementById("deleteEventName").innerText = name;
            document.getElementById("deleteModal").style.display = "flex";
        }

        async function executeDeleteEvent() {
            if (!pendingDeleteId) return;

            const btn = document.getElementById("btnConfirmDelete");
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
            btn.disabled = true;

            try {
                const res = await apiRequest(`api.php?id=${pendingDeleteId}`, { method: "DELETE" });

                btn.innerHTML = originalText;
                btn.disabled = false;

                if (res.status === 200 && res.data.success) {
                    closeModal("deleteModal");
                    showToast("warning", res.data.message);
                    loadEvents(); // Asynchronous refresh
                } else {
                    showToast("danger", res.data.message || "Failed to delete event.");
                }
            } catch (err) {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast("danger", "Network error occurred while deleting event.");
            }
        }

        // ========================================================
        // INTERMEDIATE EXTENSION: DEBOUNCED SEARCH
        // ========================================================
        const searchInput = document.getElementById("searchInput");
        const searchSpinner = document.getElementById("searchSpinner");

        searchInput.addEventListener("input", function() {
            searchSpinner.style.display = "inline-block";
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                searchSpinner.style.display = "none";
                loadEvents();
            }, 350); // 350ms Debounce Delay
        });

        document.getElementById("categoryFilter").addEventListener("change", loadEvents);
        document.getElementById("statusFilter").addEventListener("change", loadEvents);

        // Validation UI Helpers
        function clearValidationErrors(prefix) {
            document.querySelectorAll(`[id^="${prefix}_err_"]`).forEach(el => {
                el.innerText = "";
                el.style.display = "none";
            });
            document.querySelectorAll(`#${prefix}Modal input, #${prefix}Modal select`).forEach(el => {
                el.classList.remove("is-invalid");
            });
        }

        function showValidationErrors(prefix, errors) {
            for (const [field, msg] of Object.entries(errors)) {
                let errEl = null;
                let inputEl = null;
                if (field === "event_name") { errEl = document.getElementById(`${prefix}_err_name`); inputEl = document.getElementById(`${prefix}_name`); }
                if (field === "category")   { errEl = document.getElementById(`${prefix}_err_cat`);  inputEl = document.getElementById(`${prefix}_cat`); }
                if (field === "event_date") { errEl = document.getElementById(`${prefix}_err_date`); inputEl = document.getElementById(`${prefix}_date`); }
                if (field === "venue")      { errEl = document.getElementById(`${prefix}_err_venue`);inputEl = document.getElementById(`${prefix}_venue`); }

                if (errEl) {
                    errEl.innerText = msg;
                    errEl.style.display = "block";
                }
                if (inputEl) {
                    inputEl.classList.add("is-invalid");
                }
            }
        }

        // HTML & JS Escaping
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]);
        }
        function escapeJs(str) {
            if (!str) return '';
            return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }
        function formatDate(dStr) {
            if (!dStr) return '';
            const parts = dStr.split('-');
            if (parts.length === 3) {
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return `${parts[2]} ${months[parseInt(parts[1])-1]}, ${parts[0]}`;
            }
            return dStr;
        }

        // Close on outside click
        window.onclick = function(e) {
            if (e.target.classList.contains("modal-overlay")) {
                e.target.style.display = "none";
            }
        };

        // Initialize on page load
        document.addEventListener("DOMContentLoaded", loadEvents);
    </script>
</body>
</html>
