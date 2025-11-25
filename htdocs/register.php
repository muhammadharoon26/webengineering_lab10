<?php
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // File path
    $file = 'users.txt';

    // 1. Validation Rules
    // Username: Alphabets only
    if (!preg_match("/^[a-zA-Z]+$/", $username)) {
        $message = "Error: Username must contain only alphabets.";
        $messageType = "danger";
    } 
    // Password: Must contain both alphabets and numbers
    elseif (!preg_match("/[A-Za-z]/", $password) || !preg_match("/[0-9]/", $password)) {
        $message = "Error: Password must contain both alphabets and numbers.";
        $messageType = "danger";
    } else {
        // 2. Check for Duplicates
        $userExists = false;
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                list($storedUser, $storedPass) = explode('|', $line);
                if ($storedUser === $username) {
                    $userExists = true;
                    break;
                }
            }
        }

        if ($userExists) {
            $message = "Error: Username already exists.";
            $messageType = "danger";
        } else {
            // 3. Save Data (Format: username|password)
            // Note: In a real app, always hash passwords using password_hash()
            $data = $username . "|" . $password . "\n";
            file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
            $message = "Registration successful! You can now login.";
            $messageType = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Task 1: Registration</h4>
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required placeholder="Alphabets only">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="Letters and Numbers">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>Already have an account? <a href="login.php">Login here</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>