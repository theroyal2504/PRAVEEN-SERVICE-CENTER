<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'bike_management_system';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Function to check login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Function to check staff
function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'staff';
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to check admin authorization for master entries
function checkAdminAuth() {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
    if (!isAdmin()) {
        $_SESSION['error'] = "Access denied. Admin only area.";
        redirect('dashboard.php');
    }
}

// Function to check staff authorization for operations
function checkStaffAuth() {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
    // Staff can access but with limited permissions
}
// Add this function to config.php
function generateInvoiceNumber($conn) {
    // Get current month in YYYYMM format
    $current_month = date('Ym');
    
    // Check if month has changed
    $result = mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key = 'invoice_month'");
    $stored_month = mysqli_fetch_assoc($result)['setting_value'];
    
    // Get prefix
    $result = mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key = 'invoice_prefix'");
    $prefix = mysqli_fetch_assoc($result)['setting_value'];
    
    // Get last number
    $result = mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key = 'invoice_last_number'");
    $last_number = intval(mysqli_fetch_assoc($result)['setting_value']);
    
    // If month changed, reset counter
    if ($stored_month != $current_month) {
        $next_number = 1;
        mysqli_query($conn, "UPDATE system_settings SET setting_value = '$current_month' WHERE setting_key = 'invoice_month'");
    } else {
        $next_number = $last_number + 1;
    }
    
    // Update last number
    mysqli_query($conn, "UPDATE system_settings SET setting_value = '$next_number' WHERE setting_key = 'invoice_last_number'");
    
    // Format: PREFIX-YYYYMM-00001
    $invoice_number = $prefix . '-' . $current_month . '-' . str_pad($next_number, 5, '0', STR_PAD_LEFT);
    
    return $invoice_number;
}
?>