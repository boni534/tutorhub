<?php
// includes/functions.php - Global utility functions

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function create_notification($conn, $user_id, $message, $type = 'general') {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, notification_type) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $message, $type);
    $stmt->execute();
    $stmt->close();
}

function format_taka($amount) {
    return '৳' . number_format($amount, 2);
}

function get_status_badge($status) {
    $status_clean = strtolower(str_replace(' ', '-', $status));
    return "<span class='badge badge-{$status_clean}'>" . htmlspecialchars($status) . "</span>";
}