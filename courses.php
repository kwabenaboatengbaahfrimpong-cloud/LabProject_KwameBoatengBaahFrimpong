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
    
    if ($action == 'create' && $_SESSION['role'] == 'faculty') {
        $course_name = trim($_POST['course_name']);
        $course_code = trim($_POST['course_code']);
        $faculty_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, faculty_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $course_name, $course_code, $faculty_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course created']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create course']);
        }
        $stmt->close();
    }
    
    if ($action == 'my_courses') {
        if ($_SESSION['role'] == 'faculty') {
            $stmt = $conn->prepare("SELECT * FROM courses WHERE faculty_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            echo json_encode(['success' => true, 'courses' => $courses]);
        } else if ($_SESSION['role'] == 'student') {
            $stmt = $conn->prepare("SELECT c.* FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = ? AND e.status = 'approved'");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            echo json_encode(['success' => true, 'courses' => $courses]);
        }
    }
    
    if ($action == 'search') {
        $search = trim($_POST['search']);
        $stmt = $conn->prepare("SELECT c.*, u.fullname as faculty_name FROM courses c JOIN users u ON c.faculty_id = u.id WHERE c.course_name LIKE ? OR c.course_code LIKE ?");
        $search_term = "%$search%";
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        echo json_encode(['success' => true, 'courses' => $courses]);
    }
}
?>
