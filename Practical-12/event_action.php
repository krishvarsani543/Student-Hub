<?php
/**
 * Practical 12: Event Action Controller
 * Handles Add, Edit, Delete, Poster Upload/Replacement, Status Toggle, and Audit Logging.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/upload_helper.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$action = $_POST["action"] ?? "";

function redirectFlash(string $type, string $message): void
{
    $_SESSION["flash"] = [
        "type"    => $type, // success, danger, warning, info
        "message" => $message
    ];
    header("Location: index.php");
    exit;
}

// ==========================================
// 1. ADD EVENT (CREATE)
// ==========================================
if ($action === "add") {
    $eventName   = trim($_POST["event_name"] ?? "");
    $category    = trim($_POST["category"] ?? "Technical");
    $eventDate   = trim($_POST["event_date"] ?? "");
    $eventTime   = trim($_POST["event_time"] ?? "10:00:00");
    $venue       = trim($_POST["venue"] ?? "");
    $status      = trim($_POST["status"] ?? "open");
    $description = trim($_POST["description"] ?? "");

    // Validation
    if ($eventName === "" || $eventDate === "" || $venue === "") {
        redirectFlash("danger", "Failed to add event: Event Title, Date, and Venue are required fields.");
    }

    // Process Poster Upload if provided
    $posterFilename = null;
    if (isset($_FILES["poster"]) && $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = validateAndUploadPoster($_FILES["poster"]);
        if (!$uploadResult["success"]) {
            redirectFlash("danger", $uploadResult["error"]);
        }
        $posterFilename = $uploadResult["filename"];
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO events (event_name, description, category, event_date, event_time, venue, status, poster)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$eventName, $description, $category, $eventDate, $eventTime, $venue, $status, $posterFilename]);
        $newEventId = (int)$pdo->lastInsertId();

        // Advanced Extension: Audit Logging
        $auditDetails = "Created event '{$eventName}' [Category: {$category}, Date: {$eventDate}]";
        if ($posterFilename) {
            $auditDetails .= " with poster file '{$posterFilename}'";
        }
        recordEventAudit($pdo, $newEventId, "CREATE", $auditDetails);

        redirectFlash("success", "Success! Event '{$eventName}' (ID: #{$newEventId}) was created successfully.");

    } catch (PDOException $e) {
        // If DB fails, remove uploaded poster
        if ($posterFilename) {
            deletePosterFile($posterFilename);
        }
        redirectFlash("danger", "Database error while adding event: " . $e->getMessage());
    }
}

// ==========================================
// 2. EDIT EVENT (UPDATE)
// ==========================================
elseif ($action === "edit") {
    $eventId     = (int)($_POST["event_id"] ?? 0);
    $eventName   = trim($_POST["event_name"] ?? "");
    $category    = trim($_POST["category"] ?? "Technical");
    $eventDate   = trim($_POST["event_date"] ?? "");
    $eventTime   = trim($_POST["event_time"] ?? "10:00:00");
    $venue       = trim($_POST["venue"] ?? "");
    $status      = trim($_POST["status"] ?? "open");
    $description = trim($_POST["description"] ?? "");
    $removePoster = isset($_POST["remove_poster"]) && $_POST["remove_poster"] === "1";

    if ($eventId <= 0 || $eventName === "" || $eventDate === "" || $venue === "") {
        redirectFlash("danger", "Failed to update event: Event ID, Title, Date, and Venue are required.");
    }

    // Fetch existing event
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? LIMIT 1");
    $stmt->execute([$eventId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        redirectFlash("danger", "Event not found for editing.");
    }

    $currentPoster = $existing["poster"];
    $finalPoster   = $currentPoster;

    // Handle poster removal checkbox
    if ($removePoster && $currentPoster) {
        deletePosterFile($currentPoster);
        $finalPoster = null;
    }

    // Handle new poster upload
    if (isset($_FILES["poster"]) && $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = validateAndUploadPoster($_FILES["poster"]);
        if (!$uploadResult["success"]) {
            redirectFlash("danger", $uploadResult["error"]);
        }

        // Delete old poster if new one uploaded successfully
        if ($currentPoster) {
            deletePosterFile($currentPoster);
        }
        $finalPoster = $uploadResult["filename"];
    }

    try {
        $updateStmt = $pdo->prepare("
            UPDATE events
            SET event_name = ?, description = ?, category = ?, event_date = ?, event_time = ?, venue = ?, status = ?, poster = ?
            WHERE event_id = ?
        ");
        $updateStmt->execute([$eventName, $description, $category, $eventDate, $eventTime, $venue, $status, $finalPoster, $eventId]);

        // Audit Log
        $auditDetails = "Updated event '{$eventName}' (ID: #{$eventId}).";
        if ($finalPoster !== $currentPoster) {
            $auditDetails .= " Poster updated: " . ($finalPoster ? $finalPoster : "Removed");
        }
        recordEventAudit($pdo, $eventId, "UPDATE", $auditDetails);

        redirectFlash("success", "Success! Event record for '{$eventName}' (ID: #{$eventId}) was updated successfully.");

    } catch (PDOException $e) {
        redirectFlash("danger", "Database error while updating event: " . $e->getMessage());
    }
}

// ==========================================
// 3. DELETE EVENT (DELETE)
// ==========================================
elseif ($action === "delete") {
    $eventId = (int)($_POST["event_id"] ?? 0);

    if ($eventId <= 0) {
        redirectFlash("danger", "Invalid event ID for deletion.");
    }

    // Fetch event for file cleanup & audit
    $stmt = $pdo->prepare("SELECT event_name, poster FROM events WHERE event_id = ? LIMIT 1");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        redirectFlash("danger", "Event not found to delete.");
    }

    try {
        // Delete record from database
        $delStmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
        $delStmt->execute([$eventId]);

        // Delete poster file from disk
        if (!empty($event["poster"])) {
            deletePosterFile($event["poster"]);
        }

        // Audit Log
        recordEventAudit($pdo, $eventId, "DELETE", "Permanently deleted event '{$event['event_name']}' (ID: #{$eventId}) and cleaned up assets.");

        redirectFlash("warning", "Deleted! Event '{$event['event_name']}' (ID: #{$eventId}) was permanently removed.");

    } catch (PDOException $e) {
        redirectFlash("danger", "Database error while deleting event: " . $e->getMessage());
    }
}

// ==========================================
// 4. TOGGLE STATUS (OPEN / CLOSED)
// ==========================================
elseif ($action === "toggle_status") {
    $eventId = (int)($_POST["event_id"] ?? 0);

    if ($eventId <= 0) {
        redirectFlash("danger", "Invalid event ID.");
    }

    $stmt = $pdo->prepare("SELECT event_name, status FROM events WHERE event_id = ? LIMIT 1");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        $newStatus = ($event["status"] === "open") ? "closed" : "open";
        $upd = $pdo->prepare("UPDATE events SET status = ? WHERE event_id = ?");
        $upd->execute([$newStatus, $eventId]);

        recordEventAudit($pdo, $eventId, "STATUS_CHANGE", "Toggled status of '{$event['event_name']}' to '{$newStatus}'");
        redirectFlash("info", "Status for '{$event['event_name']}' changed to " . strtoupper($newStatus) . ".");
    } else {
        redirectFlash("danger", "Event not found.");
    }
}

else {
    redirectFlash("danger", "Unknown action requested.");
}
