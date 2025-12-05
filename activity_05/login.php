<?php
session_start();
require 'config.php';

if (isset($_COOKIE['user_id']) && !isset($_POST['email'])) {
    $user_id = $_COOKIE['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'student') {
            header("Location: student-dashboard.php");
        } elseif ($user['role'] == 'faculty') {
            header("Location: faculty-dashboard.php");
        } else {
            header("Location: intern-dashboard.php");
        }
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $selected_role = $_POST['role'];
    
    if (empty($email) || empty($password) || empty($selected_role)) {
        echo json_encode(['success' => false, 'message' => 'All fields required']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $selected_role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            
            if (isset($_POST['remember'])) {
                setcookie('user_id', $user['id'], time() + (30 * 24 * 60 * 60), '/');
            }
            
            echo json_encode(['success' => true, 'username' => $user['fullname'], 'user_id' => $user['id'], 'role' => $user['role']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Wrong password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    $stmt->close();
} else {
    header("Location: login.html");
}
?>
