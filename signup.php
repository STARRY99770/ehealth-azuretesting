<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "foreign_workers";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate unique Medical ID
function generateMedicalID($conn) {
    do {
        $id = "MED" . str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_LEFT);
        $check = $conn->query("SELECT id FROM registration WHERE medical_id = '$id'");
    } while ($check && $check->num_rows > 0);
    return $id;
}

$generatedMedicalID = generateMedicalID($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicalID = $_POST['medicalID'];
    $fullName = $_POST['fullName'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $nationality = $_POST['nationality'];
    $passportNumber = $_POST['passportNumber'];
    $phoneNumber = $_POST['phoneNumber'];
    $companyName = $_POST['companyName'];
    $companyAddress = $_POST['companyAddress'];
    $employerName = $_POST['employerName'];
    $employerPhone = $_POST['employerPhone'];
    $officePhone = $_POST['officePhone'];
    $email = $_POST['email'];
    $userID = $_POST['userID'];
    $password = $_POST['password'];

    // Check if user already exists
    $stmt = $conn->prepare("SELECT * FROM registration WHERE email = ? OR user_id = ? OR passport_number = ?");
    $stmt->bind_param("sss", $email, $userID, $passportNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('User already exists. Please try again with different credentials.'); window.location.href = 'signup.php';</script>";
        exit();
    } else {
        // Insert new user into registration table
        $stmt = $conn->prepare("INSERT INTO registration (medical_id, full_name, dob, nationality, passport_number, phone_number, company_name, company_address, employer_name, employer_phone, office_phone, email, user_id, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssss", $medicalID, $fullName, $dob, $nationality, $passportNumber, $phoneNumber, $companyName, $companyAddress, $employerName, $employerPhone, $officePhone, $email, $userID, $password);

        if ($stmt->execute()) {
            // Insert placeholder record into forms table
            $placeholderFile = 'no_file_uploaded.pdf';
            $stmtForms = $conn->prepare("INSERT INTO forms (user_id, form_file) VALUES (?, ?)");
            $stmtForms->bind_param("ss", $userID, $placeholderFile);
            $stmtForms->execute();

            // Insert placeholder into appointments table
            $nullDate = '';
            $nullTime = '';
            $emptyPlace = '';
            $stmtAppt = $conn->prepare("INSERT INTO appointments (appointment_date, appointment_time, appointment_place, user_id) VALUES (?, ?, ?, ?)");
            $stmtAppt->bind_param("ssss", $nullDate, $nullTime, $emptyPlace, $userID);
            $stmtAppt->execute();

            echo "<script>alert('Registration successful. Please sign in.'); window.location.href = 'login.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error registering user. Please try again later.'); window.location.href = 'signup.php';</script>";
            exit();
        }
    }
}
?>

<!-- Signup HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="/pageFW/foreign-worker-login.css">
</head>
<body>
    <header>
        <div class="logo-title centered-title">
            <button onclick="history.back()" class="back-button">Back</button>
            <img src="/assets/images/srw.png" alt="Logo" class="logo">
            <h1>Sarawak E-health Management System</h1>
        </div>
    </header>

    <main class="login-main">
        <div class="login-container">
            <h2>Sign Up for E-health Management System</h2>

            <form id="signupForm" action="" method="post">
                <div class="scrollable-form">
                    <div class="input-group">
                        <label for="medicalID">Medical ID</label>
                        <input type="text" id="medicalID" name="medicalID" value="<?php echo $generatedMedicalID; ?>" readonly required>
                    </div>
                    <div class="input-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" required>
                    </div>
                    <div class="input-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="input-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required style="width: 105%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <option value="">-- Select Gender --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="nationality">Nationality</label>
                        <input type="text" id="nationality" name="nationality" required>
                    </div>
                    <div class="input-group">
                        <label for="passportNumber">Passport Number</label>
                        <input type="text" id="passportNumber" name="passportNumber" required>
                    </div>
                    <div class="input-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" id="phoneNumber" name="phoneNumber" required>
                    </div>
                    <div class="input-group">
                        <label for="companyName">Current Working Company Name</label>
                        <input type="text" id="companyName" name="companyName" required>
                    </div>
                    <div class="input-group">
                        <label for="companyAddress">Company Address</label>
                        <input type="text" id="companyAddress" name="companyAddress" required>
                    </div>
                    <div class="input-group">
                        <label for="employerName">Employer Name</label>
                        <input type="text" id="employerName" name="employerName" required>
                    </div>
                    <div class="input-group">
                        <label for="employerPhone">Employer Phone</label>
                        <input type="tel" id="employerPhone" name="employerPhone" required>
                    </div>
                    <div class="input-group">
                        <label for="officePhone">Office Phone</label>
                        <input type="tel" id="officePhone" name="officePhone" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="userID">User ID</label>
                        <input type="text" id="userID" name="userID" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="input-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <button type="submit" class="sign-in-btn">Sign Up</button>
                </div>
            </form>
            <p class="sign-up-text">Already have an account? <a href="/views/login.php">Sign in here</a></p>
        </div>
    </main>

    <script>
        document.getElementById('signupForm').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (password !== confirmPassword) {
                event.preventDefault();
                alert('Passwords do not match.');
            }
        });
    </script>
</body>
</html>
