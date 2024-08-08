<?php
session_start();
include('dbcon.php');
$subscriptionType = '';
// Function to calculate remaining time
function calculateRemainingTime($endDate) {
    $currentDate = new DateTime();
    $endDate = new DateTime($endDate);
    if ($endDate < $currentDate) {
        return "Expired";
    }
    $interval = $currentDate->diff($endDate);
    return $interval->format('%a days remaining');
}

// Check if subscription info is stored in session
if (isset($_SESSION['subscription_info'])) {
    $subscription_info = $_SESSION['subscription_info'];
} else {
    // Global subscription check if not in session
    $query = mysqli_query($con, "SELECT * FROM Subscription ORDER BY Subscription_ID DESC LIMIT 1");

    if ($query) {
        $subscription_info = mysqli_fetch_array($query);
        $_SESSION['subscription_info'] = $subscription_info; // Store in session
    } else {
        die("Database query failed: " . mysqli_error($con));
    }
}

// Calculate remaining time
$remaining_time = isset($subscription_info['End_Date']) ? calculateRemainingTime($subscription_info['End_Date']) : "No active subscription";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_license'])) {
    $license_number = mysqli_real_escape_string($con, $_POST['license_number']);
    
    // Query to check against all possible license number columns
    $query = mysqli_query($con, "SELECT * FROM Subscription WHERE 
        Subscription_Lic_Number_Trial = '$license_number' OR 
        Subscription_Lic_Number_1_Month = '$license_number' OR 
        Subscription_Lic_Number_3_Month = '$license_number' OR
        Subscription_Lic_Number_6_Month = '$license_number'");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_array($query);
        
        // Determine the subscription type and calculate new end date
        $subscriptionType = '';
        $new_end_date = '';
        
        if (!empty($row['Subscription_Lic_Number_Trial']) && $row['Subscription_Lic_Number_Trial'] === $license_number) {
            $subscriptionType = 'Trial';
            $new_end_date = (new DateTime())->modify("+2 minutes")->format('Y-m-d H:i:s');
        } elseif (!empty($row['Subscription_Lic_Number_1_Month']) && $row['Subscription_Lic_Number_1_Month'] === $license_number) {
            $subscriptionType = '1 Month';
            $new_end_date = (new DateTime())->modify("+1 month")->format('Y-m-d H:i:s');
        } elseif (!empty($row['Subscription_Lic_Number_3_Month']) && $row['Subscription_Lic_Number_3_Month'] === $license_number) {
            $subscriptionType = '3 Months';
            $new_end_date = (new DateTime())->modify("+3 months")->format('Y-m-d H:i:s');
        } elseif (!empty($row['Subscription_Lic_Number_6_Month']) && $row['Subscription_Lic_Number_6_Month'] === $license_number) {
            $subscriptionType = '6 Months';
            $new_end_date = (new DateTime())->modify("+6 months")->format('Y-m-d H:i:s');
        }

        // Update the End_Date in the Subscription table
        if (!empty($new_end_date)) {
            $update_query = "UPDATE Subscription SET End_Date = '$new_end_date' WHERE Subscription_ID = " . $row['Subscription_ID'];
            $update_result = mysqli_query($con, $update_query);

            if ($update_result) {
                $remaining_time = calculateRemainingTime($new_end_date);
                $_SESSION['subscription_info'] = $row; // Update session with new subscription info
                echo "<script>alert('Subscription updated successfully.');</script>";
            } else {
                echo "<script>alert('Failed to update subscription.');</script>";
            }
        } else {
            echo "<script>alert('Entered wrong license number, please fill it again.');</script>";
        }
    } else {
        echo "<script>alert('Entered wrong license number, please fill it again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Gym System Admin</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="css/matrix-style.css" />
    <link rel="stylesheet" href="css/matrix-login.css" />
    <link href="font-awesome/css/fontawesome.css" rel="stylesheet" />
    <link href="font-awesome/css/all.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <style>
        /* Custom styles for the buttons */
        .top-corner-button {
            position: absolute;
            top: 10px;
            padding: 10px 20px;
            z-index: 999;
        }
        .left-corner {
            left: 10px;
        }
        .right-corner {
            right: 10px;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
        }
        .blur-background {
            filter: blur(5px);
        }
    </style>
</head>

<body>
    <div class="top-corner-button left-corner">
        <button id="attendanceButton" class="btn btn-primary">Attendance</button>
    </div>
    <div class="top-corner-button right-corner">
        <button id="subscriptionButton" class="btn btn-primary">
            Subscription (<?php echo $remaining_time; ?>)
        </button>
    </div>

    <div id="mainContent">
        <div id="loginbox">
            <form id="loginform" method="POST" class="form-vertical" action="#">
                <div class="control-group normal_text"> <h3><img src="img/icontest3.png" alt="Logo" /></h3></div>
                <div class="control-group">
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lg"><i class="fas fa-user-circle"></i></span><input type="text" name="user" placeholder="Username" required/>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_ly"><i class="fas fa-lock"></i></span><input type="password" name="pass" placeholder="Password" required />
                        </div>
                    </div>
                </div>
                <div class="form-actions center">
                    <button type="submit" class="btn btn-block btn-large btn-info" title="Log In" name="login" value="Admin Login">Admin Login</button>
                </div>
            </form>
            <?php
            if (isset($_POST['login'])) {
                $username = mysqli_real_escape_string($con, $_POST['user']);
                $password = mysqli_real_escape_string($con, $_POST['pass']);

                $password = md5($password);

                $query = mysqli_query($con, "SELECT * FROM admin WHERE password='$password' and username='$username'");
                $row = mysqli_fetch_array($query);
                $num_row = mysqli_num_rows($query);

                if ($num_row > 0) {
                    $_SESSION['user_id'] = $row['user_id'];

                    // Check subscription status
                    if ($remaining_time === "No active subscription" || $remaining_time === "Expired") {
                        echo "<div class='alert alert-warning alert-dismissible' role='alert'>
                            Your subscription has expired or is inactive. Please renew or buy a new subscription.
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                        </div>";
                    } else {
                        header('location:admin/index.php');
                    }
                } else {
                    echo "<div class='alert alert-danger alert-dismissible' role='alert'>
                        Invalid Username and Password
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>";
                }
            }
            ?>
            <div class="pull-left">
                <a href="customer/index.php"><h6>Customer Login</h6></a>
            </div>

            <div class="pull-right">
                <a href="staff/index.php"><h6>Staff Login</h6></a>
            </div>
        </div>
    </div>

    <!-- Subscription Modal -->
    <div id="subscriptionModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subscription Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($remaining_time === "No active subscription" || $remaining_time === "Expired"): ?>
                        <p>You have no active subscription Plan. Please buy a subscription plan or if already purchased enter the license number.</p>
                        <button class="btn btn-primary" id="buyNowButton">Buy Now</button>
                        <button class="btn btn-secondary" id="enterLicNumberButton">Enter License Number</button>
                    <?php else: ?>
                        <p>Your current subscription model: <?php echo $subscriptionType; ?></p>
                        <p>Time remaining in subscription: <?php echo $remaining_time; ?></p>
                        <!-- <button class="btn btn-primary">Renew Your Subscription</button> -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- License Number Modal -->
    <div id="licenseModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enter License Number</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="licenseForm" method="POST" action="">
                        <div class="form-group">
                            <label for="license_number">License Number</label>
                            <input type="text" class="form-control" id="license_number" name="license_number" required>
                        </div>
                        <button type="submit" name="check_license" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/matrix.login.js"></script>
    <script src="js/matrix.js"></script>
    <script>
        document.getElementById("attendanceButton").addEventListener("click", function() {
            if ("<?php echo $remaining_time; ?>" === "No active subscription" || "<?php echo $remaining_time; ?>" === "Expired") {
                alert("You have no active subscription. Please buy a subscription plan.");
            } else {
                window.location.href = "attendance.php";
            }
        });

        document.getElementById("subscriptionButton").addEventListener("click", function() {
            $("#subscriptionModal").modal("show");
        });

        document.getElementById("enterLicNumberButton").addEventListener("click", function() {
            $("#licenseModal").modal("show");
        });

        $("#subscriptionModal").on("show.bs.modal", function() {
            $("#mainContent").addClass("blur-background");
        });

        $("#subscriptionModal").on("hide.bs.modal", function() {
            $("#mainContent").removeClass("blur-background");
        });

        $("#licenseModal").on("show.bs.modal", function() {
            $("#mainContent").addClass("blur-background");
        });

        $("#licenseModal").on("hide.bs.modal", function() {
            $("#mainContent").removeClass("blur-background");
        });
    </script>
</body>
</html>
