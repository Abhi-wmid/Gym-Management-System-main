<?php
include('dbcon.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['userId'];
    $userPassword = $_POST['password'];
    $message = '';

    // Function to check user credentials in a table
    function checkCredentials($conn, $table, $userId, $userPassword) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE user_id = ? AND password = ?");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("ss", $userId, $userPassword);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Check members table
    $user = checkCredentials($conn, 'members', $userId, $userPassword);
    if (!$user) {
        // Check staff table
        $user = checkCredentials($conn, 'staffs', $userId, $userPassword);
    }
    // if (!$user) {
    //     // Check trainers table
    //     $user = checkCredentials($conn, 'trainers', $userId, $userPassword);
    // }

    if ($user) {
        $currentDate = date("Y-m-d");
        $currentTime = date("H:i:s");

        // Check if membership is valid (if applicable)
        if (!isset($user['membership_expiry']) || $user['membership_expiry'] >= $currentDate) {
            // Insert attendance record
            $stmt = $conn->prepare("INSERT INTO attendance (user_id, curr_date, curr_time, present) VALUES (?, ?, ?, 1)");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }

            $stmt->bind_param("sss", $userId, $currentDate, $currentTime);
            $stmt->execute();

            $message = 'Your attendance is marked successfully.';
        } else {
            $message = 'Sorry, your membership has expired.';
        }
    } else {
        $message = 'Sorry, wrong user ID or password.';
    }

    $conn->close();

    header("Location: attendence.html?message=" . urlencode($message));
    exit();
}
?>
