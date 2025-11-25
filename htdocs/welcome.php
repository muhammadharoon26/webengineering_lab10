<?php
session_start();
// Redirect to login if session not set
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-white">
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Lab System</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>

    <!-- Welcome Content -->
    <div class="container mt-5 text-center">
        <div class="p-5 mb-4 bg-light rounded-3 shadow-sm border">
            <div class="container-fluid py-5">
                <h1 class="display-5 fw-bold text-primary">Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h1>
                <p class="col-md-8 fs-4 mx-auto">You have successfully logged into the file-based system. This page is protected by PHP sessions.</p>
                <button class="btn btn-primary btn-lg" type="button">Go to Dashboard</button>
            </div>
        </div>
    </div>
</body>
</html>