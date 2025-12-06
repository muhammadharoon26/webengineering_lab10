<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$uploadError = "";
$apiResponse = null;
$uploadedImage = null;

// Configuration
$ROBOFLOW_API_KEY = "YOUR_API_KEY"; // User to replace this
$MODEL_ENDPOINT = "https://detect.roboflow.com/my-first-project-a2pfd/1"; // Assuming version 1

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["leaf_image"])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["leaf_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["leaf_image"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["leaf_image"]["tmp_name"], $target_file)) {
            $uploadedImage = $target_file;
            
            // Call Roboflow API
            $image_data = file_get_contents($target_file);
            $base64_image = base64_encode($image_data);
            
            $url = $MODEL_ENDPOINT . "?api_key=" . $ROBOFLOW_API_KEY;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $base64_image);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/x-www-form-urlencoded"
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $apiResponse = $response;
            } else {
                $uploadError = "API Error: " . $response;
            }
            
        } else {
            $uploadError = "Sorry, there was an error uploading your file.";
        }
    } else {
        $uploadError = "File is not an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaf Detection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #image-container {
            position: relative;
            display: inline-block;
        }
        #detection-canvas {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="welcome.php">Lab System</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Apple Leaf Detection</h4>
            </div>
            <div class="card-body">
                <?php if($uploadError): ?>
                    <div class="alert alert-danger"><?php echo $uploadError; ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" class="mb-4">
                    <div class="mb-3">
                        <label for="leaf_image" class="form-label">Upload Apple Leaf Image</label>
                        <input type="file" class="form-control" id="leaf_image" name="leaf_image" required accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Detect</button>
                    <a href="welcome.php" class="btn btn-secondary">Back</a>
                </form>

                <?php if ($uploadedImage): ?>
                    <hr>
                    <h5>Result:</h5>
                    <div id="image-container">
                        <img id="uploaded-image" src="<?php echo $uploadedImage; ?>" style="max-width: 100%; height: auto;">
                        <canvas id="detection-canvas"></canvas>
                    </div>
                    
                    <?php if ($apiResponse): ?>
                        <script>
                            const apiResponse = <?php echo $apiResponse; ?>;
                            const img = document.getElementById('uploaded-image');
                            const canvas = document.getElementById('detection-canvas');
                            const ctx = canvas.getContext('2d');

                            img.onload = function() {
                                canvas.width = img.width;
                                canvas.height = img.height;
                                drawBoxes();
                            };

                            // In case image is already loaded from cache
                            if (img.complete) {
                                canvas.width = img.width;
                                canvas.height = img.height;
                                drawBoxes();
                            }

                            function drawBoxes() {
                                // Scale factor if image is resized by CSS
                                const scaleX = img.width / img.naturalWidth;
                                const scaleY = img.height / img.naturalHeight;

                                if (apiResponse.predictions) {
                                    apiResponse.predictions.forEach(prediction => {
                                        const x = (prediction.x - prediction.width / 2) * scaleX;
                                        const y = (prediction.y - prediction.height / 2) * scaleY;
                                        const w = prediction.width * scaleX;
                                        const h = prediction.height * scaleY;

                                        // Draw box
                                        ctx.strokeStyle = '#00FF00';
                                        ctx.lineWidth = 3;
                                        ctx.strokeRect(x, y, w, h);

                                        // Draw label
                                        ctx.fillStyle = '#00FF00';
                                        ctx.font = '16px Arial';
                                        ctx.fillText(`${prediction.class} (${Math.round(prediction.confidence * 100)}%)`, x, y - 5);
                                    });
                                }
                            }
                        </script>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
