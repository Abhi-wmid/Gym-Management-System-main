<?php
session_start();
include('dbcon.php');

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($con, $_POST['userId']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $password = md5($password);

    $query_member = "SELECT * FROM members WHERE username='$username' AND password='$password'";
    // $query_staff = "SELECT * FROM staffs WHERE username='$username' AND password='$password'";

    $result_member = mysqli_query($con, $query_member);
    // $result_staff = mysqli_query($con, $query_staff);

    if (!$result_member) {
        die("Query failed: " . mysqli_error($con));
    }

    // if (!$result_staff) {
    //     die("Query failed: " . mysqli_error($con));
    // }

    if (mysqli_num_rows($result_member) > 0) {
        $row = mysqli_fetch_assoc($result_member);
        $user_id = $row['id'];
        $message = "Member attendance recorded successfully.";
    // } elseif (mysqli_num_rows($result_staff) > 0) {
    //     $row = mysqli_fetch_assoc($result_staff);
    //     $user_id = $row['id'];
    //     $message = "Staff attendance recorded successfully.";
    // 
    } 
    else {
        $message = "Invalid Username or Password.";
    }

    if (isset($user_id)) {
        $curr_date = date('Y-m-d');
        $curr_time = date('H:i:s');
        $present = 1; // Assuming 1 means present
        $attendance_query = "INSERT INTO attendance (user_id, curr_date, curr_time, present) VALUES ('$user_id', '$curr_date', '$curr_time', '$present')";

        if (!mysqli_query($con, $attendance_query)) {
            die("Attendance record failed: " . mysqli_error($con));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 500px;
            width: 100%;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #007BFF;
        }
        .entry-options {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .entry-option {
            flex: 1;
            padding: 10px;
            margin: 0 5px;
            border: 2px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .entry-option:hover {
            border-color: #007BFF;
        }
        .entry-option.selected {
            background-color: #007BFF;
            color: #fff;
            border-color: #007BFF;
        }
        #scanning-card img {
            max-width: 100%;
            height: auto;
            margin-top: 20px;
        }
        #scanning-card, #user-form {
            display: none;
        }
        form {
            text-align: left;
        }
        form div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 20px;
            border-radius: 5px;
            color: #fff;
            background-color: #007BFF;
            z-index: 1000;
            display: none;
        }
        .message.error {
            background-color: #e74c3c;
        }
        .blur {
            filter: blur(5px);
        }
        #homeButton {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            text-decoration: none;
        }
        #homeButton:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <a id="homeButton" href="index.php">Home Page</a>
    <div class="message" id="messageBox"><?php echo $message; ?></div>
    <div class="container" id="mainContent">
        <h1>Make Your Entry</h1>
        <div class="entry-options">
            <div class="entry-option selected" id="scanCardOption">Scan Your Card</div>
            <div class="entry-option" id="enterDetailsOption">Enter Your Details</div>
        </div>
        <div id="scanning-card">
            <img src="./id_scan.gif" alt="Scanning Card">
        </div>
        <div id="user-form">
            <form action="" method="post">
                <div>
                    <label for="userId">User ID:</label>
                    <input type="text" id="userId" name="userId" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <button type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scanCardOption = document.getElementById('scanCardOption');
            const enterDetailsOption = document.getElementById('enterDetailsOption');
            const scanningCardDiv = document.getElementById('scanning-card');
            const userFormDiv = document.getElementById('user-form');
            const messageBox = document.getElementById('messageBox');
            const mainContent = document.getElementById('mainContent');

            function updateDisplay() {
                if (scanCardOption.classList.contains('selected')) {
                    scanningCardDiv.style.display = 'block';
                    userFormDiv.style.display = 'none';
                } else {
                    scanningCardDiv.style.display = 'none';
                    userFormDiv.style.display = 'block';
                }
            }

            scanCardOption.addEventListener('click', function() {
                scanCardOption.classList.add('selected');
                enterDetailsOption.classList.remove('selected');
                updateDisplay();
            });

            enterDetailsOption.addEventListener('click', function() {
                enterDetailsOption.classList.add('selected');
                scanCardOption.classList.remove('selected');
                updateDisplay();
            });

            // Initialize display
            updateDisplay();

            // Display the message if present
            if (messageBox.innerText.trim() !== '') {
                messageBox.style.display = 'block';
                mainContent.classList.add('blur');
                
                // Hide the message and remove blur on click anywhere
                document.addEventListener('click', function hideMessage() {
                    messageBox.style.display = 'none';
                    mainContent.classList.remove('blur');
                    document.removeEventListener('click', hideMessage);
                });
            }

            // Auto-redirect to home page after 10 seconds of inactivity
            let inactivityTime = function() {
                let time;
                window.onload = resetTimer;
                document.onmousemove = resetTimer;
                document.onkeypress = resetTimer;
                document.ontouchstart = resetTimer;

                function redirectToHomePage() {
                    window.location.href = 'index.php';
                }

                function resetTimer() {
                    clearTimeout(time);
                    time = setTimeout(redirectToHomePage, 10000);
                }
            };
            inactivityTime();
        });
    </script>
</body>
</html>
