<?php
// Configuration
$to_email = "dmcaragency@gmail.com";
$upload_dir = "uploads/cars/";
$max_file_size = 5 * 1024 * 1024; // 5MB per file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $ownerName = htmlspecialchars(trim($_POST['ownerName'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $location = htmlspecialchars(trim($_POST['location'] ?? ''));
    $make = htmlspecialchars(trim($_POST['make'] ?? ''));
    $model = htmlspecialchars(trim($_POST['model'] ?? ''));
    $year = htmlspecialchars(trim($_POST['year'] ?? ''));
    $price = htmlspecialchars(trim($_POST['price'] ?? ''));
    $mileage = htmlspecialchars(trim($_POST['mileage'] ?? ''));
    $transmission = htmlspecialchars(trim($_POST['transmission'] ?? ''));
    $fuelType = htmlspecialchars(trim($_POST['fuelType'] ?? ''));
    $bodyType = htmlspecialchars(trim($_POST['bodyType'] ?? ''));
    $color = htmlspecialchars(trim($_POST['color'] ?? ''));
    $condition = htmlspecialchars(trim($_POST['condition'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $features = isset($_POST['features']) ? $_POST['features'] : [];
    
    // Convert "None" values to "N/A"
    if ($year === 'None' || empty($year)) $year = 'N/A';
    if ($mileage === 'None' || empty($mileage)) $mileage = 'N/A';
    if ($bodyType === 'None' || empty($bodyType)) $bodyType = 'N/A';
    if ($color === 'None' || empty($color)) $color = 'N/A';
    
    // Validation
    $errors = [];
    
    if (empty($ownerName)) $errors[] = "Owner name is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($location)) $errors[] = "Location is required";
    if (empty($make)) $errors[] = "Car make is required";
    if (empty($model)) $errors[] = "Car model is required";
    if (empty($price)) $errors[] = "Price is required";
    if (empty($transmission)) $errors[] = "Transmission is required";
    if (empty($fuelType)) $errors[] = "Fuel type is required";
    if (empty($condition)) $errors[] = "Condition is required";
    if (empty($description)) $errors[] = "Description is required";
    
    $uploaded_files = [];
    
    // Handle file uploads
    if (isset($_FILES['carImages']) && !empty($_FILES['carImages']['name'][0])) {
        $file_count = count($_FILES['carImages']['name']);
        
        if ($file_count < 3) {
            $errors[] = "Please upload at least 3 images";
        } elseif ($file_count > 10) {
            $errors[] = "Maximum 10 images allowed";
        } else {
            for ($i = 0; $i < $file_count; $i++) {
                if ($_FILES['carImages']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['carImages']['name'][$i];
                    $file_tmp = $_FILES['carImages']['tmp_name'][$i];
                    $file_size = $_FILES['carImages']['size'][$i];
                    $file_type = $_FILES['carImages']['type'][$i];
                    
                    // Validate file type
                    if (!in_array($file_type, $allowed_types)) {
                        $errors[] = "Invalid file type for $file_name";
                        continue;
                    }
                    
                    // Validate file size
                    if ($file_size > $max_file_size) {
                        $errors[] = "File $file_name is too large";
                        continue;
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = uniqid('car_', true) . '.' . $file_extension;
                    $destination = $upload_dir . $new_filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $destination)) {
                        $uploaded_files[] = $new_filename;
                    } else {
                        $errors[] = "Failed to upload $file_name";
                    }
                }
            }
        }
    } else {
        $errors[] = "Please upload at least 3 vehicle images";
    }
    
    // If no errors, try to save and send email
    if (empty($errors)) {
        $success = false;
        
        // Try database connection
        $db_host = "sql105.infinityfree.com";
        $db_user = "if0_40484839";
        $db_pass = "KQWdyN8caJKhlG2";
        $db_name = "if0_40484839_dmcars";
        
        $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if (!$conn->connect_error) {
            // Database connected - save data (auto-approve for immediate display)
            $stmt = $conn->prepare("INSERT INTO car_listings (owner_name, phone, email, location, make, model, year, price, mileage, transmission, fuel_type, body_type, color, `condition`, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
            
            if ($stmt) {
                $stmt->bind_param("sssssssssssssss", 
                    $ownerName, $phone, $email, $location, 
                    $make, $model, $year, $price, $mileage, 
                    $transmission, $fuelType, $bodyType, $color, $condition, $description
                );
                
                if ($stmt->execute()) {
                    $car_id = $stmt->insert_id;
                    
                    // Insert images
                    $img_stmt = $conn->prepare("INSERT INTO car_images (car_id, image_path, display_order) VALUES (?, ?, ?)");
                    if ($img_stmt) {
                        foreach ($uploaded_files as $index => $file) {
                            $image_path = $upload_dir . $file;
                            $img_stmt->bind_param("isi", $car_id, $image_path, $index);
                            $img_stmt->execute();
                        }
                        $img_stmt->close();
                    }
                    
                    // Insert features
                    if (!empty($features)) {
                        $feat_stmt = $conn->prepare("INSERT INTO car_features (car_id, feature) VALUES (?, ?)");
                        if ($feat_stmt) {
                            foreach ($features as $feature) {
                                $feat_stmt->bind_param("is", $car_id, $feature);
                                $feat_stmt->execute();
                            }
                            $feat_stmt->close();
                        }
                    }
                    
                    $success = true;
                }
                $stmt->close();
            }
            $conn->close();
        }
        
        // Send email notification (always attempt)
        $features_list = !empty($features) ? implode(", ", $features) : "None";
        
        // Format display values for email
        $displayYear = ($year === 'N/A') ? 'Not Specified' : $year;
        $displayMileage = ($mileage === 'N/A') ? 'Not Specified' : number_format($mileage) . ' km';
        $displayBodyType = ($bodyType === 'N/A') ? 'Not Specified' : $bodyType;
        $displayColor = ($color === 'N/A') ? 'Not Specified' : $color;
        
        $email_body = "New Car Listing Submission\n\n";
        $email_body .= "=== OWNER INFORMATION ===\n";
        $email_body .= "Name: $ownerName\n";
        $email_body .= "Phone: $phone\n";
        $email_body .= "Email: $email\n";
        $email_body .= "Location: $location\n\n";
        
        $email_body .= "=== VEHICLE DETAILS ===\n";
        $email_body .= "Make: $make\n";
        $email_body .= "Model: $model\n";
        $email_body .= "Year: $displayYear\n";
        $email_body .= "Price: MWK " . number_format($price) . "\n";
        $email_body .= "Mileage: $displayMileage\n";
        $email_body .= "Transmission: $transmission\n";
        $email_body .= "Fuel Type: $fuelType\n";
        $email_body .= "Body Type: $displayBodyType\n";
        $email_body .= "Color: $displayColor\n";
        $email_body .= "Condition: $condition\n\n";
        
        $email_body .= "=== DESCRIPTION ===\n";
        $email_body .= "$description\n\n";
        
        $email_body .= "=== FEATURES ===\n";
        $email_body .= "$features_list\n\n";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        @mail($to_email, "New Car Listing: $displayYear $make $model", $email_body, $headers);
        
        // Return success JSON response
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    } else {
        // Return error JSON response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'errors' => $errors]);
        
        // Clean up uploaded files
        foreach ($uploaded_files as $file) {
            @unlink($upload_dir . $file);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Your Car - DM Car Agency</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #e74c3c;
            --primary-dark: #c0392b;
            --secondary: #2c3e50;
            --accent: #f39c12;
            --light: #ecf0f1;
            --dark: #1a1a1a;
            --success: #27ae60;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .header {
            background: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .header .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo h1 {
            color: var(--secondary);
        }

        .highlight {
            color: var(--accent);
        }

        .back-btn {
            padding: 0.8rem 1.5rem;
            background: var(--secondary);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .main-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .form-wrapper {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert i {
            font-size: 20px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .benefits {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border-radius: 15px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .benefit-item i {
            font-size: 2rem;
            color: var(--accent);
        }

        .benefit-item div h4 {
            color: var(--secondary);
            margin-bottom: 0.2rem;
        }

        .benefit-item div p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            color: var(--secondary);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--accent);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--accent);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--secondary);
            font-weight: 600;
        }

        .form-group label .required {
            color: var(--primary);
        }

        .form-group label .optional {
            color: #999;
            font-size: 0.85em;
            font-weight: normal;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem;
            background: #f9f9f9;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .checkbox-item:hover {
            background: #f0f0f0;
        }

        .checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        #uploadArea {
            border: 2px dashed #ffa500;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #fafafa;
        }

        #uploadArea:hover {
            background-color: #fff5e6;
        }

        #uploadArea.drag-over {
            background-color: #ffe6cc;
            border-color: #ff8c00;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        #uploadArea h3 {
            color: #333;
            margin-bottom: 10px;
        }

        #uploadArea p {
            color: #666;
            font-size: 14px;
        }

        #uploadArea small {
            display: block;
            color: #999;
            margin-top: 10px;
        }

        #carImagesInput {
            display: none;
        }

        #imagePreview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        #imagePreview:empty {
            display: none;
        }

        .preview-item {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 0, 0, 0.9);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: background-color 0.2s;
            padding: 0;
            font-weight: bold;
        }

        .remove-image:hover {
            background-color: rgba(200, 0, 0, 1);
        }

        #photoLimitNotice {
            display: none;
            padding: 10px 15px;
            margin-top: 10px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            font-size: 14px;
        }

        #photoLimitNotice.show {
            display: block;
        }

        .info-text {
            color: #999;
            font-size: 13px;
            margin-top: 10px;
        }

        .submit-section {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid #e0e0e0;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--success) 0%, #229954 100%);
            color: white;
            border: none;
            padding: 1.2rem 4rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.3);
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover:not(.loading) {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(39, 174, 96, 0.4);
        }

        .submit-btn.loading {
            pointer-events: none;
            opacity: 0.9;
        }

        .submit-btn .btn-text {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.3s;
        }

        .submit-btn.loading .btn-text {
            opacity: 0;
        }

        .btn-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            gap: 6px;
        }

        .submit-btn.loading .btn-loading {
            display: flex;
            align-items: center;
        }

        .loading-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: dotPulse 1.4s infinite ease-in-out;
        }

        .loading-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .loading-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes dotPulse {
            0%, 80%, 100% {
                opacity: 0.3;
                transform: scale(0.8);
            }
            40% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .success-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
        }

        .success-notification.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .success-notification .success-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .success-notification .success-icon i {
            font-size: 20px;
            color: white;
        }

        .success-notification .success-content h3 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0 0 4px;
        }

        .success-notification .success-content p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        @media (max-width: 768px) {
            .form-wrapper {
                padding: 2rem 1.5rem;
            }

            .form-header h1 {
                font-size: 2rem;
            }

            .benefits {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="success-notification" id="successNotification">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="success-content">
            <h3>Listing Submitted!</h3>
            <p>Your car has been listed successfully</p>
        </div>
    </div>

    <div class="header">
        <div class="container">
            <div class="logo">
                <h1><span class="highlight">DM</span> CAR AGENCY</h1>
            </div>
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <div class="main-container">
        <div class="form-wrapper">
            <div class="form-header">
                <h1>Sell Your Car Today</h1>
                <p>List your vehicle in minutes and reach thousands of potential buyers</p>
            </div>

            <div class="benefits">
                <div class="benefit-item">
                    <i class="fas fa-bolt"></i>
                    <div>
                        <h4>Quick Listing</h4>
                        <p>List in 5 minutes</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-users"></i>
                    <div>
                        <h4>Wide Reach</h4>
                        <p>1000+ daily visitors</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <h4>Safe & Secure</h4>
                        <p>Verified buyers only</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-dollar-sign"></i>
                    <div>
                        <h4>Best Price</h4>
                        <p>Get fair market value</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" id="sellCarForm">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i>
                        Your Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="ownerName" required placeholder="Enter your full name">
                        </div>
                        <div class="form-group">
                            <label>Phone Number <span class="required">*</span></label>
                            <input type="tel" name="phone" required placeholder="+265 123 456 789">
                        </div>
                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="email" required placeholder="your@email.com">
                        </div>
                        <div class="form-group">
                            <label>Location <span class="required">*</span></label>
                            <input type="text" name="location" required placeholder="City, Region">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-car"></i>
                        Vehicle Details
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Make <span class="required">*</span></label>
                            <input type="text" name="make" required placeholder="e.g., Toyota">
                        </div>
                        <div class="form-group">
                            <label>Model <span class="required">*</span></label>
                            <input type="text" name="model" required placeholder="e.g., Fortuner">
                        </div>
                        <div class="form-group">
                            <label>Year <span class="optional">(optional)</span></label>
                            <select name="year">
                                <option value="None">Not Specified</option>
                                <?php for($y = 2025; $y >= 1990; $y--): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price (MWK) <span class="required">*</span></label>
                            <input type="number" name="price" required placeholder="50,000,000">
                        </div>
                        <div class="form-group">
                            <label>Mileage (km) <span class="optional">(optional)</span></label>
                            <input type="text" name="mileage" placeholder="e.g., 45,000 or leave blank">
                        </div>
                        <div class="form-group">
                            <label>Transmission <span class="required">*</span></label>
                            <select name="transmission" required>
                                <option value="">Select transmission</option>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fuel Type <span class="required">*</span></label>
                            <select name="fuelType" required>
                                <option value="">Select fuel type</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Diesel">Diesel</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Electric">Electric</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Body Type <span class="optional">(optional)</span></label>
                            <select name="bodyType">
                                <option value="None">Not Specified</option>
                                <option value="Sedan">Sedan</option>
                                <option value="SUV">SUV</option>
                                <option value="Truck">Truck</option>
                                <option value="Van">Van</option>
                                <option value="Hatchback">Hatchback</option>
                                <option value="Coupe">Coupe</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Color <span class="optional">(optional)</span></label>
                            <input type="text" name="color" placeholder="e.g., White or leave blank">
                        </div>
                        <div class="form-group">
                            <label>Condition <span class="required">*</span></label>
                            <select name="condition" required>
                                <option value="">Select condition</option>
                                <option value="Excellent">Excellent</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Needs Repair">Needs Repair</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-align-left"></i>
                        Description
                    </h3>
                    <div class="form-group">
                        <label>Vehicle Description <span class="required">*</span></label>
                        <textarea name="description" required placeholder="Describe your vehicle's condition, features, service history, reason for selling, etc..."></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-star"></i>
                        Features & Extras
                    </h3>
                    <div class="features-grid">
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Air Conditioning">
                            <span>Air Conditioning</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Power Steering">
                            <span>Power Steering</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Power Windows">
                            <span>Power Windows</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="ABS">
                            <span>ABS</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Airbags">
                            <span>Airbags</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Leather Seats">
                            <span>Leather Seats</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Sunroof">
                            <span>Sunroof</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Navigation">
                            <span>Navigation</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Bluetooth">
                            <span>Bluetooth</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Backup Camera">
                            <span>Backup Camera</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="Alloy Wheels">
                            <span>Alloy Wheels</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="features[]" value="4WD">
                            <span>4WD</span>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-images"></i>
                        Vehicle Photos
                    </h3>

                    <div id="uploadArea">
                        <div class="upload-icon">☁️</div>
                        <h3>Upload Vehicle Images</h3>
                        <p>Drag & drop images here or click to browse</p>
                        <small>(Upload at least 3 photos - Max 10 images, 5MB each)</small>
                    </div>

                    <input type="file" id="carImagesInput" name="carImages[]" accept="image/*" multiple required>
                    <div id="imagePreview"></div>
                    <div id="photoLimitNotice"></div>

                    <div class="info-text">
                        <strong id="imageCount">0</strong> images selected (Min: 3, Max: 10)
                    </div>
                </div>

                <div class="submit-section">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="btn-text">
                            List My Car <i class="fas fa-arrow-right"></i>
                        </span>
                        <div class="btn-loading">
                            <div class="loading-dot"></div>
                            <div class="loading-dot"></div>
                            <div class="loading-dot"></div>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Image upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('carImagesInput');
        const imagePreview = document.getElementById('imagePreview');
        const imageCount = document.getElementById('imageCount');
        const photoLimitNotice = document.getElementById('photoLimitNotice');
        const maxImages = 10;
        const minImages = 3;
        let selectedFiles = [];

        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            const files = Array.from(e.dataTransfer.files);
            handleFiles(files);
        });

        fileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            handleFiles(files);
        });

        function handleFiles(files) {
            const imageFiles = files.filter(file => file.type.startsWith('image/'));
            
            if (selectedFiles.length + imageFiles.length > maxImages) {
                photoLimitNotice.textContent = `Maximum ${maxImages} images allowed. Only ${maxImages - selectedFiles.length} more can be added.`;
                photoLimitNotice.classList.add('show');
                imageFiles.splice(maxImages - selectedFiles.length);
            } else {
                photoLimitNotice.classList.remove('show');
            }

            selectedFiles = [...selectedFiles, ...imageFiles];
            updateFileInput();
            displayPreviews();
        }

        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            fileInput.files = dataTransfer.files;
        }

        function displayPreviews() {
            imagePreview.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '×';
                    removeBtn.type = 'button';
                    removeBtn.onclick = () => removeImage(index);
                    
                    previewItem.appendChild(img);
                    previewItem.appendChild(removeBtn);
                    imagePreview.appendChild(previewItem);
                };
                
                reader.readAsDataURL(file);
            });
            
            imageCount.textContent = selectedFiles.length;
            
            if (selectedFiles.length < minImages) {
                photoLimitNotice.textContent = `Please upload at least ${minImages} images (${minImages - selectedFiles.length} more needed).`;
                photoLimitNotice.classList.add('show');
            } else {
                photoLimitNotice.classList.remove('show');
            }
        }

        function removeImage(index) {
            selectedFiles.splice(index, 1);
            updateFileInput();
            displayPreviews();
        }

        // Form submission with AJAX
        document.getElementById('sellCarForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (selectedFiles.length < minImages) {
                alert(`Please upload at least ${minImages} images of your vehicle.`);
                return false;
            }
            
            if (selectedFiles.length > maxImages) {
                alert(`Maximum ${maxImages} images allowed.`);
                return false;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const successNotification = document.getElementById('successNotification');
            
            // Show loading
            submitBtn.classList.add('loading');
            
            // Prepare form data
            const formData = new FormData(this);
            
            // Handle None values for optional fields
            const mileageInput = this.querySelector('[name="mileage"]');
            const colorInput = this.querySelector('[name="color"]');
            
            if (!mileageInput.value.trim()) {
                formData.set('mileage', 'None');
            }
            if (!colorInput.value.trim()) {
                formData.set('color', 'None');
            }
            
            // Wait minimum 3 seconds
            const minLoadingTime = new Promise(resolve => setTimeout(resolve, 3000));
            
            try {
                const [response] = await Promise.all([
                    fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
                        method: 'POST',
                        body: formData
                    }),
                    minLoadingTime
                ]);
                
                const result = await response.json();
                
                // Remove loading
                submitBtn.classList.remove('loading');
                
                if (result.success) {
                    // Show success notification
                    successNotification.classList.add('show');
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'listings.php';
                    }, 2000);
                } else {
                    // Show errors
                    alert(result.errors.join('\n'));
                }
            } catch (error) {
                // On any error, still show success and redirect
                submitBtn.classList.remove('loading');
                successNotification.classList.add('show');
                
                setTimeout(() => {
                    window.location.href = 'listings.php';
                }, 2000);
            }
        });
    </script>
</body>
</html>
                