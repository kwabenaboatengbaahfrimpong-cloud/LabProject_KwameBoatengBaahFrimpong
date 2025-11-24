<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'request' && $_SESSION['role'] == 'student') {
        $course_id = $_POST['course_id'];
        $student_id = $_SESSION['user_id'];
        
        $check = $conn->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
        $check->bind_param("ii", $student_id, $course_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Already requested']);
        } else {
            $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("ii", $student_id, $course_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Request sent']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Request failed']);
            }
        }
    }
    
    if ($action == 'pending' && $_SESSION['role'] == 'faculty') {
        $stmt = $conn->prepare("SELECT e.*, u.fullname, c.course_name FROM enrollments e JOIN users u ON e.student_id = u.id JOIN courses c ON e.course_id = c.id WHERE c.faculty_id = ? AND e.status = 'pending'");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        echo json_encode(['success' => true, 'requests' => $requests]);
    }
    
    if ($action == 'approve' && $_SESSION['role'] == 'faculty') {
        $enrollment_id = $_POST['enrollment_id'];
        $stmt = $conn->prepare("UPDATE enrollments SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $enrollment_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Request approved']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve']);
        }
    }
    
    if ($action == 'reject' && $_SESSION['role'] == 'faculty') {
        $enrollment_id = $_POST['enrollment_id'];
        $stmt = $conn->prepare("UPDATE enrollments SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $enrollment_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Request rejected']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject']);
        }
    }
}
?>
