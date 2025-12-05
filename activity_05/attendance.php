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
    
    // Faculty actions
    if ($action == 'create_session' && ($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'intern')) {
        $course_id = $_POST['course_id'];
        $session_date = $_POST['session_date'];
        
        // Check if user is authorized to create session for this course
        $check_stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND faculty_id = ?");
        $check_stmt->bind_param("ii", $course_id, $_SESSION['user_id']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Not authorized to create session for this course']);
            exit();
        }
        
        // Generate a random 6-digit code
        $session_code = rand(100000, 999999);
        
        // Insert session
        $stmt = $conn->prepare("INSERT INTO attendance_sessions (course_id, session_date, session_code) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $course_id, $session_date, $session_code);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Session created', 'session_code' => $session_code]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create session']);
        }
        $stmt->close();
    }
    
    if ($action == 'get_sessions' && ($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'intern')) {
        $course_id = $_POST['course_id'];
        
        // Check if user is authorized to view sessions for this course
        $check_stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND faculty_id = ?");
        $check_stmt->bind_param("ii", $course_id, $_SESSION['user_id']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Not authorized to view sessions for this course']);
            exit();
        }
        
        // Get sessions
        $stmt = $conn->prepare("SELECT * FROM attendance_sessions WHERE course_id = ? ORDER BY session_date DESC");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
        echo json_encode(['success' => true, 'sessions' => $sessions]);
        $stmt->close();
    }
    
    if ($action == 'mark_attendance' && ($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'intern')) {
        $session_id = $_POST['session_id'];
        $student_id = $_POST['student_id'];
        $status = $_POST['status']; // present, absent, late
        
        // Check if session exists and belongs to user's course
        $check_stmt = $conn->prepare("SELECT s.id FROM attendance_sessions s JOIN courses c ON s.course_id = c.id WHERE s.id = ? AND c.faculty_id = ?");
        $check_stmt->bind_param("ii", $session_id, $_SESSION['user_id']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Not authorized to mark attendance for this session']);
            exit();
        }
        
        // Update or insert attendance record
        $stmt = $conn->prepare("INSERT INTO attendance_records (session_id, student_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
        $stmt->bind_param("iiss", $session_id, $student_id, $status, $status);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Attendance marked']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark attendance']);
        }
        $stmt->close();
    }
    
    if ($action == 'get_attendance_report' && ($_SESSION['role'] == 'faculty' || $_SESSION['role'] == 'intern')) {
        $course_id = $_POST['course_id'];
        
        // Check if user is authorized to view report for this course
        $check_stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND faculty_id = ?");
        $check_stmt->bind_param("ii", $course_id, $_SESSION['user_id']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Not authorized to view report for this course']);
            exit();
        }
        
        // Get attendance report
        $stmt = $conn->prepare("SELECT u.fullname, u.id as student_id, COUNT(ar.id) as total_sessions, 
                               SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                               SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
                               SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count
                               FROM users u 
                               JOIN enrollments e ON u.id = e.student_id 
                               LEFT JOIN attendance_records ar ON u.id = ar.student_id 
                               LEFT JOIN attendance_sessions s ON ar.session_id = s.id 
                               WHERE e.course_id = ? AND e.status = 'approved' 
                               GROUP BY u.id, u.fullname");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $report = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['total_sessions'] > 0) {
                $row['attendance_percentage'] = round(($row['present_count'] + $row['late_count']) / $row['total_sessions'] * 100, 2);
            } else {
                $row['attendance_percentage'] = 0;
            }
            $report[] = $row;
        }
        echo json_encode(['success' => true, 'report' => $report]);
        $stmt->close();
    }
    
    // Student actions
    if ($action == 'submit_attendance_code' && $_SESSION['role'] == 'student') {
        $session_code = $_POST['session_code'];
        
        // Find session with this code
        $stmt = $conn->prepare("SELECT s.id, s.course_id FROM attendance_sessions s WHERE s.session_code = ?");
        $stmt->bind_param("s", $session_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid attendance code']);
            exit();
        }
        
        $session = $result->fetch_assoc();
        $session_id = $session['id'];
        $course_id = $session['course_id'];
        
        // Check if student is enrolled in this course
        $check_stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ? AND status = 'approved'");
        $check_stmt->bind_param("ii", $_SESSION['user_id'], $course_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course']);
            exit();
        }
        
        // Mark student as present
        $update_stmt = $conn->prepare("INSERT INTO attendance_records (session_id, student_id, status) VALUES (?, ?, 'present') ON DUPLICATE KEY UPDATE status = 'present'");
        $update_stmt->bind_param("ii", $session_id, $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Attendance marked successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark attendance']);
        }
        $update_stmt->close();
    }
    
    if ($action == 'get_my_attendance' && $_SESSION['role'] == 'student') {
        $course_id = $_POST['course_id'];
        
        // Check if student is enrolled in this course
        $check_stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ? AND status = 'approved'");
        $check_stmt->bind_param("ii", $_SESSION['user_id'], $course_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course']);
            exit();
        }
        
        // Get student's attendance records for this course
        $stmt = $conn->prepare("SELECT s.session_date, ar.status FROM attendance_records ar 
                               JOIN attendance_sessions s ON ar.session_id = s.id 
                               WHERE ar.student_id = ? AND s.course_id = ? 
                               ORDER BY s.session_date ASC");
        $stmt->bind_param("ii", $_SESSION['user_id'], $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        // Get total sessions and attendance percentage
        $total_stmt = $conn->prepare("SELECT COUNT(*) as total_sessions, 
                                     SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                                     SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count
                                     FROM attendance_sessions s 
                                     LEFT JOIN attendance_records ar ON s.id = ar.session_id AND ar.student_id = ?
                                     WHERE s.course_id = ?");
        $total_stmt->bind_param("ii", $_SESSION['user_id'], $course_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $summary = $total_result->fetch_assoc();
        
        if ($summary['total_sessions'] > 0) {
            $summary['attendance_percentage'] = round(($summary['present_count'] + $summary['late_count']) / $summary['total_sessions'] * 100, 2);
        } else {
            $summary['attendance_percentage'] = 0;
        }
        
        echo json_encode(['success' => true, 'records' => $records, 'summary' => $summary]);
        $stmt->close();
        $total_stmt->close();
    }
}
?>