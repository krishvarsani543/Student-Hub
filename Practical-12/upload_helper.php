<?php
/**
 * Practical 12: Secure Poster Upload & Validation Helper
 * Features:
 * - Strict MIME type verification using finfo
 * - Allowed extension check (jpg, jpeg, png, webp)
 * - Maximum file size validation (2MB limit)
 * - Image integrity validation using getimagesize()
 * - Secure random file renaming (Advanced Extension)
 */

function validateAndUploadPoster(array $file): array
{
    // If no file was uploaded
    if (!isset($file["error"]) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return ["success" => true, "filename" => null];
    }

    // Check for upload errors
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => "The uploaded poster exceeds server upload_max_filesize.",
            UPLOAD_ERR_FORM_SIZE  => "The uploaded poster exceeds the maximum size allowed by the form.",
            UPLOAD_ERR_PARTIAL    => "The poster was only partially uploaded. Please try again.",
            UPLOAD_ERR_NO_TMP_DIR => "Server temporary directory is missing.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write poster file to disk.",
            UPLOAD_ERR_EXTENSION  => "A server PHP extension stopped the file upload."
        ];
        return [
            "success" => false,
            "error"   => $errorMessages[$file["error"]] ?? "Unknown error during file upload."
        ];
    }

    $tmpPath = $file["tmp_name"];
    $fileSize = $file["size"];
    $origName = $file["name"];

    // 1. File Size Validation: 2MB limit (2,097,152 bytes)
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($fileSize > $maxSize) {
        $sizeInMb = round($fileSize / (1024 * 1024), 2);
        return [
            "success" => false,
            "error"   => "Poster upload rejected: File size ({$sizeInMb}MB) exceeds the 2MB limit."
        ];
    }

    // 2. Extension Validation
    $extension = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowedExtensions = ["jpg", "jpeg", "png", "webp"];
    if (!in_array($extension, $allowedExtensions, true)) {
        return [
            "success" => false,
            "error"   => "Poster upload rejected: Invalid file extension (.'{$extension}'). Only JPG, JPEG, PNG, and WEBP formats are allowed."
        ];
    }

    // 3. Server-side MIME Type Verification (finfo)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowedMimes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    if (!in_array($mimeType, $allowedMimes, true)) {
        return [
            "success" => false,
            "error"   => "Poster upload rejected: Invalid MIME type ('{$mimeType}'). Upload must be a valid image."
        ];
    }

    // 4. Image Integrity Check (Prevents disguised PHP scripts / polyglots)
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        return [
            "success" => false,
            "error"   => "Poster upload rejected: The uploaded file is corrupted or not a valid image."
        ];
    }

    // 5. Advanced Extension: Cryptographically Secure Random File Renaming
    // Format: poster_YYYYMMDD_HHMMSS_[random_12_hex].[ext]
    $randomHex = bin2hex(random_bytes(6));
    $newFilename = "poster_" . date("Ymd_His") . "_" . $randomHex . "." . $extension;

    $uploadDir = __DIR__ . "/uploads/posters";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . "/" . $newFilename;

    if (!move_uploaded_file($tmpPath, $destination)) {
        return [
            "success" => false,
            "error"   => "Failed to save uploaded poster file to the destination folder."
        ];
    }

    return [
        "success"  => true,
        "filename" => $newFilename
    ];
}

/**
 * Helper to delete old poster from disk when replaced or deleted
 */
function deletePosterFile(?string $filename): void
{
    if (empty($filename)) {
        return;
    }

    // Sanitize filename to avoid directory traversal
    $cleanFilename = basename($filename);
    $path = __DIR__ . "/uploads/posters/" . $cleanFilename;

    if (file_exists($path) && is_file($path)) {
        @unlink($path);
    }
}
