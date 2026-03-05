<?php

error_reporting(E_ALL);

ini_set('display_errors', 1);

ini_set('log_errors', 1);

// Rest of your code...

// Database configuration
$db_host = "sql105.infinityfree.com";
$db_user = "if0_40484839";
$db_pass = "KQWdyN8caJKhlG2";
$db_name = "if0_40484839_dmcars";

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get sort parameter
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build ORDER BY clause based on sort
switch($sort) {
    case 'price_low':
        $order_by = "cl.price ASC";
        break;
    case 'price_high':
        $order_by = "cl.price DESC";
        break;
    case 'featured':
        $order_by = "cl.is_featured DESC, cl.created_at DESC";
        break;
    case 'oldest':
        $order_by = "cl.created_at ASC";
        break;
    case 'newest':
    default:
        $order_by = "cl.created_at DESC";
        break;
}

// Fetch all approved car listings with their images and tracking stats
$sql = "SELECT cl.*, 
        (SELECT image_path FROM car_images WHERE car_id = cl.id ORDER BY display_order LIMIT 1) as main_image,
        (SELECT COUNT(*) FROM car_images WHERE car_id = cl.id) as image_count
        FROM car_listings cl 
     
        ORDER BY " . $order_by;

$result = $conn->query($sql);
$cars = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Fetch all images for this car
        $car_id = $row['id'];
        $images_sql = "SELECT image_path FROM car_images WHERE car_id = $car_id ORDER BY display_order";
        $images_result = $conn->query($images_sql);
        
        $images = [];
        if ($images_result && $images_result->num_rows > 0) {
            while($img = $images_result->fetch_assoc()) {
                $images[] = $img['image_path'];
            }
        }
        
        // Fetch tracking statistics for this car (with error handling)
        $row['view_count'] = 0;
        $row['like_count'] = 0;
        $row['comment_count'] = 0;
        
        // Check if tracking tables exist before querying
        $tables_check = $conn->query("SHOW TABLES LIKE 'car_views'");
        if ($tables_check && $tables_check->num_rows > 0) {
            $views_sql = "SELECT COUNT(*) as view_count FROM car_views WHERE car_id = $car_id";
            $views_result = $conn->query($views_sql);
            if ($views_result) {
                $row['view_count'] = $views_result->fetch_assoc()['view_count'];
            }
        }
        
        $tables_check = $conn->query("SHOW TABLES LIKE 'car_likes'");
        if ($tables_check && $tables_check->num_rows > 0) {
            $likes_sql = "SELECT COUNT(*) as like_count FROM car_likes WHERE car_id = $car_id";
            $likes_result = $conn->query($likes_sql);
            if ($likes_result) {
                $row['like_count'] = $likes_result->fetch_assoc()['like_count'];
            }
        }
        
        $tables_check = $conn->query("SHOW TABLES LIKE 'car_comments'");
        if ($tables_check && $tables_check->num_rows > 0) {
            $comments_sql = "SELECT COUNT(*) as comment_count FROM car_comments WHERE car_id = $car_id";
            $comments_result = $conn->query($comments_sql);
            if ($comments_result) {
                $row['comment_count'] = $comments_result->fetch_assoc()['comment_count'];
            }
        }
        
        $row['all_images'] = $images;
        $cars[] = $row;
    }
}

// Get unique values for filters
$makes = array_unique(array_column($cars, 'make'));
$body_types = array_unique(array_column($cars, 'body_type'));
$fuel_types = array_unique(array_column($cars, 'fuel_type'));
$transmissions = array_unique(array_column($cars, 'transmission'));

sort($makes);
sort($body_types);
sort($fuel_types);
sort($transmissions);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - DM Car Agency</title>
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
            --whatsapp: #25D366;
            --light: #ecf0f1;
            --dark: #1a1a1a;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-bottom: 3rem;
        }

        /* Header */
        .header {
            background: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header .container {
            max-width: 1400px;
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

        .header-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        .btn-whatsapp {
            background: var(--whatsapp);
            color: white;
        }

        .btn-whatsapp:hover {
            background: #1faa52;
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Page Title */
        .page-title {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .page-title h1 {
            color: var(--secondary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: #666;
            font-size: 1.1rem;
        }

        .car-count {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-top: 1rem;
            font-weight: 600;
        }

        /* Sort Controls */
        .sort-controls {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .sort-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .sort-controls select {
            padding: 0.8rem 1.2rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            color: var(--secondary);
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 200px;
            font-weight: 600;
        }

        .sort-controls select:hover {
            border-color: var(--accent);
        }

        .sort-controls select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        }

        /* Advanced Search */
        .search-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .search-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .search-header h2 {
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toggle-filters {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .toggle-filters:hover {
            background: #e67e22;
        }

        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .search-field {
            display: flex;
            flex-direction: column;
        }

        .search-field label {
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .search-field input,
        .search-field select {
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .search-field input:focus,
        .search-field select:focus {
            outline: none;
            border-color: var(--accent);
        }

        .search-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .btn-search {
            background: var(--secondary);
            color: white;
        }

        .btn-search:hover {
            background: var(--primary);
        }

        .btn-reset {
            background: #95a5a6;
            color: white;
        }

        .btn-reset:hover {
            background: #7f8c8d;
        }

        .advanced-filters {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .advanced-filters.show {
            max-height: 1000px;
        }

        /* Cars Grid */
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        /* Car Card */
        .car-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .car-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .car-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            cursor: pointer;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .car-image .no-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 4rem;
        }

        .image-count {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Badges */
        .car-badges {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 2;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .badge-featured {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: white;
        }

        .badge-new {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
        }

        .badge-hot {
            background: linear-gradient(135deg, #FF5722 0%, #D32F2F 100%);
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Share Button */
        .share-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 2;
        }

        .share-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        .share-btn i {
            color: var(--secondary);
            font-size: 1.2rem;
        }

        .car-info {
            padding: 1.5rem;
        }

        .car-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        .car-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .car-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.8rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .detail-item i {
            color: var(--accent);
            width: 20px;
        }

        .car-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Car Statistics */
        .car-stats {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 15px;
            font-size: 0.85rem;
        }

        .car-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        .contact-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0.5rem;
        }

        .contact-btn {
            padding: 1rem;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .contact-btn:hover {
            background: var(--primary);
        }

        /* Scroll to Top Button */
        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            z-index: 999;
        }

        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            background: #e67e22;
            transform: translateY(-5px);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: var(--primary-dark);
        }

        .modal-images {
            position: relative;
            height: 400px;
            background: #000;
        }

        .modal-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: none;
        }

        .modal-image.active {
            display: block;
        }

        .image-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 2;
        }

        .image-nav:hover {
            background: rgba(0,0,0,0.9);
            transform: translateY(-50%) scale(1.1);
        }

        .image-nav.prev {
            left: 20px;
        }

        .image-nav.next {
            right: 20px;
        }

        .image-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 2;
        }

        .indicator-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .indicator-dot.active {
            background: white;
            transform: scale(1.3);
        }

        .image-counter {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            z-index: 2;
        }

        .modal-info {
            padding: 2rem;
        }

        .modal-title {
            font-size: 2rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }

        .modal-price {
            font-size: 2.5rem;
            color: var(--accent);
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .modal-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .modal-detail {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modal-detail i {
            color: var(--accent);
            font-size: 1.5rem;
            width: 30px;
        }

        .modal-detail-info h4 {
            color: #999;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .modal-detail-info p {
            color: var(--secondary);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .modal-description {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .modal-description h3 {
            color: var(--secondary);
            margin-bottom: 1rem;
        }

        .modal-description p {
            color: #666;
            line-height: 1.8;
        }

        .modal-location {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .modal-location i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .modal-contact {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Comment Section */
        .comment-section {
            margin: 30px 0;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .comment-form {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }

        .comment-form input,
        .comment-form textarea {
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
        }

        .comment-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .comments-list {
            display: grid;
            gap: 15px;
        }

        /* Share Modal */
        .share-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .share-modal.show {
            display: flex;
        }

        .share-modal-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            position: relative;
        }

        .share-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 30px;
            height: 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .share-modal h3 {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .share-options {
            display: grid;
            gap: 1rem;
        }

        .share-option {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .share-option:hover {
            border-color: var(--accent);
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .share-option-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .share-option-icon.whatsapp {
            background: var(--whatsapp);
            color: white;
        }

        .share-option-icon.copy {
            background: var(--secondary);
            color: white;
        }

        .share-option-text h4 {
            color: var(--secondary);
            margin-bottom: 0.25rem;
        }

        .share-option-text p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty-state h2 {
            color: var(--secondary);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .cars-grid {
                grid-template-columns: 1fr;
            }

            .search-grid {
                grid-template-columns: 1fr;
            }

            .page-title h1 {
                font-size: 2rem;
            }

            .header-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .modal-details {
                grid-template-columns: 1fr;
            }

            .modal-contact {
                grid-template-columns: 1fr;
            }

            .contact-buttons {
                grid-template-columns: 1fr;
            }

            .sort-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .sort-controls select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">
                <h1><span class="highlight">DM</span> CAR AGENCY</h1>
            </div>
            <div class="header-buttons">
                <a href="sell-car.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Sell Your Car
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
        </div>
    </div>

    <div class="main-container">
        <div class="page-title">
            <h1>Available Cars</h1>
            <p>Browse our collection of quality vehicles</p>
            <span class="car-count">
                <i class="fas fa-car"></i> <span id="carCount"><?php echo count($cars); ?></span> Cars Available
            </span>
        </div>

        <?php if (!empty($cars)): ?>
            <!-- Sort Controls -->
            <div class="sort-controls">
                <div class="sort-label">
                    <i class="fas fa-sort"></i>
                    <span>Sort by:</span>
                </div>
                <select id="sort-select" onchange="window.location.href='?sort=' + this.value">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="featured" <?php echo $sort == 'featured' ? 'selected' : ''; ?>>Featured</option>
                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                </select>
            </div>

            <!-- Advanced Search -->
            <div class="search-container">
                <div class="search-header">
                    <h2><i class="fas fa-search"></i> Search Cars</h2>
                    <button class="toggle-filters" onclick="toggleAdvancedFilters()">
                        <i class="fas fa-sliders-h"></i> <span id="filterText">Show Filters</span>
                    </button>
                </div>

                <div class="search-grid">
                    <div class="search-field">
                        <label>Search</label>
                        <input type="text" id="searchText" placeholder="Search by make, model..." onkeyup="filterCars()">
                    </div>
                    <div class="search-field">
                        <label>Price Range</label>
                        <select id="priceRange" onchange="filterCars()">
                            <option value="">All Prices</option>
                            <option value="0-5000000">Under MWK 5M</option>
                            <option value="5000000-10000000">MWK 5M - 10M</option>
                            <option value="10000000-20000000">MWK 10M - 20M</option>
                            <option value="20000000-999999999">Above MWK 20M</option>
                        </select>
                    </div>
                </div>

                <div class="advanced-filters" id="advancedFilters">
                    <div class="search-grid">
                        <div class="search-field">
                            <label>Make</label>
                            <select id="make" onchange="filterCars()">
                                <option value="">All Makes</option>
                                <?php foreach ($makes as $make): ?>
                                    <option value="<?php echo htmlspecialchars($make); ?>">
                                        <?php echo htmlspecialchars($make); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-field">
                            <label>Body Type</label>
                            <select id="bodyType" onchange="filterCars()">
                                <option value="">All Types</option>
                                <?php foreach ($body_types as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>">
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-field">
                            <label>Fuel Type</label>
                            <select id="fuelType" onchange="filterCars()">
                                <option value="">All Fuel Types</option>
                                <?php foreach ($fuel_types as $fuel): ?>
                                    <option value="<?php echo htmlspecialchars($fuel); ?>">
                                        <?php echo htmlspecialchars($fuel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-field">
                            <label>Transmission</label>
                            <select id="transmission" onchange="filterCars()">
                                <option value="">All Transmissions</option>
                                <?php foreach ($transmissions as $trans): ?>
                                    <option value="<?php echo htmlspecialchars($trans); ?>">
                                        <?php echo htmlspecialchars($trans); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-field">
                            <label>Year From</label>
                            <input type="number" id="yearFrom" placeholder="e.g., 2015" onchange="filterCars()">
                        </div>
                        <div class="search-field">
                            <label>Year To</label>
                            <input type="number" id="yearTo" placeholder="e.g., 2024" onchange="filterCars()">
                        </div>
                    </div>
                </div>

                <div class="search-actions">
                    <button class="btn btn-reset" onclick="resetFilters()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($cars)): ?>
            <div class="empty-state">
                <i class="fas fa-car"></i>
                <h2>No Cars Available Yet</h2>
                <p>Be the first to list your car!</p>
                <a href="sell_car_fixed.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> List Your Car
                </a>
            </div>
        <?php else: ?>
            <div class="cars-grid" id="carsGrid">
                <?php foreach ($cars as $car): ?>
                    <div class="car-card" id="car-card-<?php echo $car['id']; ?>"
                         data-make="<?php echo htmlspecialchars($car['make']); ?>"
                         data-model="<?php echo htmlspecialchars($car['model']); ?>"
                         data-price="<?php echo $car['price']; ?>"
                         data-year="<?php echo $car['year']; ?>"
                         data-body="<?php echo htmlspecialchars($car['body_type']); ?>"
                         data-fuel="<?php echo htmlspecialchars($car['fuel_type']); ?>"
                         data-transmission="<?php echo htmlspecialchars($car['transmission']); ?>">
                        
                        <div class="car-image" onclick="showCarDetails(<?php echo $car['id']; ?>)">
                            <?php if (!empty($car['main_image'])): ?>
                                <img src="<?php echo htmlspecialchars($car['main_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>"
                                     onerror="this.parentElement.innerHTML='<div class=\'no-image\'><i class=\'fas fa-car\'></i></div>'">
                                <?php if ($car['image_count'] > 1): ?>
                                    <span class="image-count">
                                        <i class="fas fa-images"></i>
                                        <?php echo $car['image_count']; ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-car"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badges -->
                            <div class="car-badges">
                                <?php if ($car['is_featured']): ?>
                                    <span class="badge badge-featured">
                                        <i class="fas fa-star"></i> Featured
                                    </span>
                                <?php endif; ?>
                                
                                <?php
                                // Check if car is new (less than 7 days old)
                                $created = strtotime($car['created_at']);
                                $days_old = floor((time() - $created) / 86400);
                                if ($days_old <= 7):
                                ?>
                                    <span class="badge badge-new">
                                        <i class="fas fa-sparkles"></i> New
                                    </span>
                                <?php endif; ?>
                                
                                <?php
                                // Hot deal for cars under 20M MWK
                                if ($car['price'] < 20000000):
                                ?>
                                    <span class="badge badge-hot">
                                        <i class="fas fa-fire"></i> Hot Deal
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Share Button -->
                            <button class="share-btn" onclick="event.stopPropagation(); openShareModal(<?php echo $car['id']; ?>, '<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>', <?php echo $car['price']; ?>)">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>

                        <div class="car-info">
                            <h3 class="car-title">
                                <?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?>
                            </h3>
                            
                            <div class="car-price">
                                MWK <?php echo number_format($car['price']); ?>
                            </div>

                            <div class="car-details">
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo htmlspecialchars($car['year']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-road"></i>
                                    <span><?php echo number_format($car['mileage']); ?> km</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-cog"></i>
                                    <span><?php echo htmlspecialchars($car['transmission']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-gas-pump"></i>
                                    <span><?php echo htmlspecialchars($car['fuel_type']); ?></span>
                                </div>
                            </div>

                            <p class="car-description">
                                <?php echo htmlspecialchars($car['description']); ?>
                            </p>

                            <!-- Car Statistics -->
                            <div class="car-stats">
                                <div class="stat-item">
                                    <span style="font-size: 1rem;">👁️</span>
                                    <span><strong><?php echo number_format($car['view_count']); ?></strong> views</span>
                                </div>
                                <div class="stat-item">
                                    <span style="font-size: 1rem;">❤️</span>
                                    <span><strong><?php echo number_format($car['like_count']); ?></strong> likes</span>
                                </div>
                                <div class="stat-item">
                                    <span style="font-size: 1rem;">💬</span>
                                    <span><strong><?php echo number_format($car['comment_count']); ?></strong> comments</span>
                                </div>
                            </div>

                            <div class="car-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($car['location']); ?></span>
                            </div>

                            <div class="contact-buttons">
                                <button class="btn btn-secondary" onclick="showCarDetails(<?php echo $car['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                                <button class="btn btn-primary" onclick="toggleCarLike(<?php echo $car['id']; ?>, this)" id="like-btn-<?php echo $car['id']; ?>">
                                    <i class="fas fa-heart"></i> Like
                                </button>
                                <button class="btn btn-whatsapp" onclick="contactWhatsApp(event, '<?php echo htmlspecialchars($car['phone']); ?>', '<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>')">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scroll to Top Button -->
    <button class="scroll-top" id="scrollTop" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Car Details Modal -->
    <div class="modal" id="carModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
            <div id="modalContent"></div>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="share-modal" id="shareModal">
        <div class="share-modal-content">
            <button class="share-modal-close" onclick="closeShareModal()">
                <i class="fas fa-times"></i>
            </button>
            <h3>Share this car</h3>
            <div class="share-options">
                <div class="share-option" id="shareWhatsApp">
                    <div class="share-option-icon whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="share-option-text">
                        <h4>WhatsApp</h4>
                        <p>Share to WhatsApp contacts or groups</p>
                    </div>
                </div>
                <div class="share-option" id="shareCopyLink">
                    <div class="share-option-icon copy">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="share-option-text">
                        <h4>Copy Link</h4>
                        <p>Copy link to clipboard</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Car data for modal
        const carsData = <?php echo json_encode($cars, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        console.log('Cars loaded:', carsData.length);

        // Share functionality
        let currentShareCar = null;

        function openShareModal(carId, carName, price) {
            currentShareCar = { id: carId, name: carName, price: price };
            document.getElementById('shareModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeShareModal() {
            document.getElementById('shareModal').classList.remove('show');
            document.body.style.overflow = 'auto';
            currentShareCar = null;
        }

        // Share to WhatsApp
        document.getElementById('shareWhatsApp').addEventListener('click', function() {
            if (!currentShareCar) return;
            
            const url = window.location.origin + window.location.pathname + '?carId=' + currentShareCar.id;
            const message = encodeURIComponent(
                `🚗 Check out this amazing car!\n\n` +
                `${currentShareCar.name}\n` +
                `💰 MWK ${Number(currentShareCar.price).toLocaleString()}\n\n` +
                `View details: ${url}\n\n` +
                `#DMCarAgency #CarsForSale`
            );
            
            window.open(`https://wa.me/?text=${message}`, '_blank');
            closeShareModal();
        });

        // Copy link to clipboard
        document.getElementById('shareCopyLink').addEventListener('click', function() {
            if (!currentShareCar) return;
            
            const url = window.location.origin + window.location.pathname + '?carId=' + currentShareCar.id;
            
            navigator.clipboard.writeText(url).then(function() {
                const btn = document.getElementById('shareCopyLink');
                const originalHTML = btn.innerHTML;
                
                btn.innerHTML = `
                    <div class="share-option-icon copy" style="background: #27ae60;">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="share-option-text">
                        <h4>Link Copied!</h4>
                        <p>Share it anywhere you like</p>
                    </div>
                `;
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    closeShareModal();
                }, 2000);
            }).catch(function(err) {
                alert('Failed to copy link. Please try again.');
            });
        });

        // Close share modal when clicking outside
        document.getElementById('shareModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeShareModal();
            }
        });

        // Handle direct car link (when someone clicks a shared link)
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const carId = urlParams.get('carId');
            if (carId) {
                setTimeout(function() {
                    showCarDetails(carId);
                }, 500);
            }
        });

        // Toggle Advanced Filters
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            const text = document.getElementById('filterText');
            
            if (filters.classList.contains('show')) {
                filters.classList.remove('show');
                text.textContent = 'Show Filters';
            } else {
                filters.classList.add('show');
                text.textContent = 'Hide Filters';
            }
        }

        // Filter Cars
        function filterCars() {
            const searchText = document.getElementById('searchText').value.toLowerCase();
            const priceRange = document.getElementById('priceRange').value;
            const make = document.getElementById('make').value.toLowerCase();
            const bodyType = document.getElementById('bodyType').value.toLowerCase();
            const fuelType = document.getElementById('fuelType').value.toLowerCase();
            const transmission = document.getElementById('transmission').value.toLowerCase();
            const yearFrom = document.getElementById('yearFrom').value;
            const yearTo = document.getElementById('yearTo').value;

            const cards = document.querySelectorAll('.car-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const cardMake = card.dataset.make.toLowerCase();
                const cardModel = card.dataset.model.toLowerCase();
                const cardPrice = parseInt(card.dataset.price);
                const cardYear = parseInt(card.dataset.year);
                const cardBody = card.dataset.body.toLowerCase();
                const cardFuel = card.dataset.fuel.toLowerCase();
                const cardTransmission = card.dataset.transmission.toLowerCase();

                let show = true;

                if (searchText && !(cardMake.includes(searchText) || cardModel.includes(searchText))) {
                    show = false;
                }

                if (priceRange) {
                    const [min, max] = priceRange.split('-').map(Number);
                    if (cardPrice < min || cardPrice > max) {
                        show = false;
                    }
                }

                if (make && cardMake !== make) {
                    show = false;
                }

                if (bodyType && cardBody !== bodyType) {
                    show = false;
                }

                if (fuelType && cardFuel !== fuelType) {
                    show = false;
                }

                if (transmission && cardTransmission !== transmission) {
                    show = false;
                }

                if (yearFrom && cardYear < parseInt(yearFrom)) {
                    show = false;
                }
                if (yearTo && cardYear > parseInt(yearTo)) {
                    show = false;
                }

                if (show) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('carCount').textContent = visibleCount;
        }

        // Reset Filters
        function resetFilters() {
            document.getElementById('searchText').value = '';
            document.getElementById('priceRange').value = '';
            document.getElementById('make').value = '';
            document.getElementById('bodyType').value = '';
            document.getElementById('fuelType').value = '';
            document.getElementById('transmission').value = '';
            document.getElementById('yearFrom').value = '';
            document.getElementById('yearTo').value = '';
            
            filterCars();
        }

        // Show Car Details Modal
        function showCarDetails(carId) {
            console.log('Opening details for car ID:', carId);
            const car = carsData.find(c => c.id == carId);
            
            if (!car) {
                console.error('Car not found:', carId);
                return;
            }

            const modal = document.getElementById('carModal');
            const modalContent = document.getElementById('modalContent');

            let imagesHTML = '';
            const images = car.all_images || [];
            
            if (images.length > 0) {
                imagesHTML = `
                    <div class="image-counter">1/${images.length}</div>
                    ${images.length > 1 ? `
                        <button class="image-nav prev" onclick="event.stopPropagation(); changeImage(-1);">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="image-nav next" onclick="event.stopPropagation(); changeImage(1);">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    ` : ''}
                    ${images.map((img, index) => `
                        <img src="${img}" alt="${car.make} ${car.model}" class="modal-image ${index === 0 ? 'active' : ''}" onerror="console.error('Failed to load image:', this.src);">
                    `).join('')}
                    ${images.length > 1 ? `
                        <div class="image-indicators">
                            ${images.map((_, index) => `
                                <div class="indicator-dot ${index === 0 ? 'active' : ''}" onclick="event.stopPropagation(); goToImage(${index});"></div>
                            `).join('')}
                        </div>
                    ` : ''}
                `;
            } else if (car.main_image) {
                imagesHTML = `<img src="${car.main_image}" alt="${car.make} ${car.model}" class="modal-image active">`;
            } else {
                imagesHTML = `<div class="no-image" style="height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;"><i class="fas fa-car"></i></div>`;
            }

            modalContent.innerHTML = `
                <div class="modal-images">
                    ${imagesHTML}
                </div>
                <div class="modal-info">
                    <h2 class="modal-title">${car.year} ${car.make} ${car.model}</h2>
                    <div class="modal-price">MWK ${Number(car.price).toLocaleString()}</div>
                    
                    <!-- Car Statistics -->
                    <div class="car-stats" style="display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap;">
                        <div class="stat-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; background: #f8f9fa; border-radius: 20px; font-size: 0.9rem;">
                            <span style="font-size: 1.2rem;">👁️</span>
                            <span><strong id="modal-view-count">0</strong> views</span>
                        </div>
                        <div class="stat-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; background: #f8f9fa; border-radius: 20px; font-size: 0.9rem;">
                            <span style="font-size: 1.2rem;">❤️</span>
                            <span><strong id="modal-like-count">0</strong> likes</span>
                        </div>
                        <div class="stat-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; background: #f8f9fa; border-radius: 20px; font-size: 0.9rem;">
                            <span style="font-size: 1.2rem;">💬</span>
                            <span><strong id="modal-comment-count">0</strong> comments</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons" style="display: flex; gap: 10px; margin: 20px 0; flex-wrap: wrap;">
                        <button class="btn btn-primary" id="modal-like-btn" onclick="toggleModalLike(${car.id})">
                            <i class="fas fa-heart"></i> <span id="modal-like-text">Like</span>
                        </button>
                        <button class="btn btn-secondary" onclick="scrollToComments()">
                            <i class="fas fa-comment"></i> View Comments
                        </button>
                    </div>
                    
                    <div class="modal-details">
                        <div class="modal-detail">
                            <i class="fas fa-calendar"></i>
                            <div class="modal-detail-info">
                                <h4>Year</h4>
                                <p>${car.year}</p>
                            </div>
                        </div>
                        <div class="modal-detail">
                            <i class="fas fa-road"></i>
                            <div class="modal-detail-info">
                                <h4>Mileage</h4>
                                <p>${Number(car.mileage).toLocaleString()} km</p>
                            </div>
                        </div>
                        <div class="modal-detail">
                            <i class="fas fa-cog"></i>
                            <div class="modal-detail-info">
                                <h4>Transmission</h4>
                                <p>${car.transmission}</p>
                            </div>
                        </div>
                        <div class="modal-detail">
                            <i class="fas fa-gas-pump"></i>
                            <div class="modal-detail-info">
                                <h4>Fuel Type</h4>
                                <p>${car.fuel_type}</p>
                            </div>
                        </div>
                        <div class="modal-detail">
                            <i class="fas fa-car-side"></i>
                            <div class="modal-detail-info">
                                <h4>Body Type</h4>
                                <p>${car.body_type}</p>
                            </div>
                        </div>
                        <div class="modal-detail">
                            <i class="fas fa-palette"></i>
                            <div class="modal-detail-info">
                                <h4>Color</h4>
                                <p>${car.color}</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-description">
                        <h3>Description</h3>
                        <p>${car.description}</p>
                    </div>

                    <div class="modal-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4 style="color: #999; font-size: 0.85rem; margin-bottom: 0.25rem;">Location</h4>
                            <p style="color: var(--secondary); font-size: 1.1rem; font-weight: 600;">${car.location}</p>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="comment-section" id="modal-comments" style="margin: 30px 0; padding: 25px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h3>💬 Comments</h3>
                        
                        <form class="comment-form" id="modal-comment-form" onsubmit="submitModalComment(event, ${car.id})" style="display: grid; gap: 15px; margin-bottom: 30px;">
                            <input type="text" id="modal-comment-name" placeholder="Your Name *" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 1rem;">
                            <input type="email" id="modal-comment-email" placeholder="Your Email *" required style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 1rem;">
                            <input type="tel" id="modal-comment-phone" placeholder="Your Phone" style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 1rem;">
                            <textarea id="modal-comment-text" placeholder="Write your comment..." required style="min-height: 100px; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 1rem; resize: vertical;"></textarea>
                            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; width: fit-content;">Submit Comment</button>
                        </form>

                        <div class="comments-list" id="modal-comments-list" style="display: grid; gap: 15px;">
                            <!-- Comments will be loaded here -->
                        </div>
                    </div>

                    <div class="modal-contact">
                        <button class="btn btn-whatsapp" style="padding: 1.2rem;" onclick="contactWhatsApp(event, '${car.phone}', '${car.make} ${car.model}')">
                            <i class="fab fa-whatsapp"></i> WhatsApp Seller
                        </button>
                        <button class="btn btn-secondary" style="padding: 1.2rem;" onclick="contactSeller(event, '${car.phone}', '${car.email}')">
                            <i class="fas fa-phone"></i> Call Seller
                        </button>
                    </div>
                </div>
            `;

            // Load car statistics and comments
            loadModalStats(car.id);
            loadModalComments(car.id);

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            window.currentImageIndex = 0;
        }

        // Toggle car like
        function toggleCarLike(carId, button) {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true;
            
            fetch('analytics.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_like&car_id=${carId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'liked') {
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-success');
                        button.innerHTML = '<i class="fas fa-heart"></i> Liked';
                    } else {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-primary');
                        button.innerHTML = '<i class="fas fa-heart"></i> Like';
                    }
                    
                    // Update like count in stats if visible
                    const statElement = document.querySelector(`#car-card-${carId} .stat-item:nth-child(2) strong`);
                    if (statElement) {
                        statElement.textContent = Number(data.like_count).toLocaleString();
                    }
                } else {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    alert(data.error || 'Failed to update like status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalHTML;
                button.disabled = false;
                alert('An error occurred. Please try again.');
            });
        }

        // Modal tracking functions
        function loadModalStats(carId) {
            // Track view
            fetch('analytics.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=track_view&car_id=${carId}`
            });
            
            // Get stats
            fetch(`analytics.php?action=get_stats&car_id=${carId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-view-count').textContent = Number(data.view_count || 0).toLocaleString();
                    document.getElementById('modal-like-count').textContent = Number(data.like_count || 0).toLocaleString();
                    document.getElementById('modal-comment-count').textContent = Number(data.comment_count || 0).toLocaleString();
                    
                    const likeBtn = document.getElementById('modal-like-btn');
                    const likeText = document.getElementById('modal-like-text');
                    if (data.user_has_liked) {
                        likeBtn.classList.remove('btn-primary');
                        likeBtn.classList.add('btn-success');
                        likeText.textContent = 'Liked';
                    }
                });
        }

        function toggleModalLike(carId) {
            const button = document.getElementById('modal-like-btn');
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true;
            
            fetch('analytics.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_like&car_id=${carId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeText = document.getElementById('modal-like-text');
                    const likeCount = document.getElementById('modal-like-count');
                    
                    if (data.action === 'liked') {
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-success');
                        likeText.textContent = 'Liked';
                    } else {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-primary');
                        likeText.textContent = 'Like';
                    }
                    
                    likeCount.textContent = Number(data.like_count).toLocaleString();
                    button.innerHTML = originalHTML.replace('Like', likeText.textContent).replace('Liked', likeText.textContent);
                    button.disabled = false;
                } else {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    alert(data.error || 'Failed to update like status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalHTML;
                button.disabled = false;
                alert('An error occurred. Please try again.');
            });
        }

        function loadModalComments(carId) {
            fetch(`analytics.php?action=get_comments&car_id=${carId}`)
                .then(response => response.json())
                .then(comments => {
                    const commentsList = document.getElementById('modal-comments-list');
                    commentsList.innerHTML = '';
                    
                    if (comments.length === 0) {
                        commentsList.innerHTML = '<p style="color: #999;">No comments yet. Be the first to comment!</p>';
                        return;
                    }
                    
                    comments.forEach(comment => {
                        const commentDiv = document.createElement('div');
                        commentDiv.style.cssText = 'padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #667eea;';
                        commentDiv.innerHTML = `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="font-weight: 600; color: #2c3e50;">${comment.user_name}</span>
                                <span style="font-size: 0.85rem; color: #7f8c8d;">${new Date(comment.created_at).toLocaleDateString()}</span>
                            </div>
                            <div style="color: #555; line-height: 1.6;">${comment.comment_text}</div>
                        `;
                        commentsList.appendChild(commentDiv);
                    });
                })
                .catch(error => {
                    console.error('Error loading comments:', error);
                    document.getElementById('modal-comments-list').innerHTML = '<p style="color: #999;">Failed to load comments.</p>';
                });
        }

        function submitModalComment(event, carId) {
            event.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'add_comment');
            formData.append('car_id', carId);
            formData.append('name', document.getElementById('modal-comment-name').value);
            formData.append('email', document.getElementById('modal-comment-email').value);
            formData.append('phone', document.getElementById('modal-comment-phone').value);
            formData.append('comment', document.getElementById('modal-comment-text').value);
            
            fetch('analytics.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Comment submitted for review!');
                    document.getElementById('modal-comment-form').reset();
                    loadModalComments(carId);
                    loadModalStats(carId); // Reload stats to update comment count
                } else {
                    alert(data.error || 'Failed to submit comment');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        function scrollToComments() {
            document.getElementById('modal-comments').scrollIntoView({ behavior: 'smooth' });
        }

        // Image Gallery Navigation
        let currentImageIndex = 0;

        function changeImage(direction) {
            const images = document.querySelectorAll('.modal-image');
            const indicators = document.querySelectorAll('.indicator-dot');
            const counter = document.querySelector('.image-counter');
            
            if (images.length === 0) return;

            images[currentImageIndex].classList.remove('active');
            if (indicators[currentImageIndex]) {
                indicators[currentImageIndex].classList.remove('active');
            }

            currentImageIndex += direction;
            
            if (currentImageIndex >= images.length) {
                currentImageIndex = 0;
            } else if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1;
            }

            images[currentImageIndex].classList.add('active');
            if (indicators[currentImageIndex]) {
                indicators[currentImageIndex].classList.add('active');
            }
            if (counter) {
                counter.textContent = `${currentImageIndex + 1}/${images.length}`;
            }
        }

        function goToImage(index) {
            const images = document.querySelectorAll('.modal-image');
            const indicators = document.querySelectorAll('.indicator-dot');
            const counter = document.querySelector('.image-counter');
            
            if (images.length === 0) return;

            images[currentImageIndex].classList.remove('active');
            if (indicators[currentImageIndex]) {
                indicators[currentImageIndex].classList.remove('active');
            }

            currentImageIndex = index;

            images[currentImageIndex].classList.add('active');
            if (indicators[currentImageIndex]) {
                indicators[currentImageIndex].classList.add('active');
            }
            if (counter) {
                counter.textContent = `${currentImageIndex + 1}/${images.length}`;
            }
        }

        // Close Modal
        function closeModal() {
            const modal = document.getElementById('carModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            currentImageIndex = 0;
        }

        document.getElementById('carModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Contact via WhatsApp
        function contactWhatsApp(event, phone, carName) {
            event.stopPropagation();
            const cleanPhone = phone.replace(/\D/g, '');
            const message = encodeURIComponent(`Hello, I'm interested in the ${carName}. Is it still available?`);
            window.open(`https://wa.me/${cleanPhone}?text=${message}`, '_blank');
        }

        // Contact Seller
        function contactSeller(event, phone, email) {
            event.stopPropagation();
            const message = `Contact Seller:\n\nPhone: ${phone}\nEmail: ${email}\n\nWhat would you like to do?`;
            if (confirm(message + '\n\nClick OK to call, Cancel to email')) {
                window.location.href = `tel:${phone}`;
            } else {
                window.location.href = `mailto:${email}`;
            }
        }

        // Scroll to Top
        window.addEventListener('scroll', function() {
            const scrollTop = document.getElementById('scrollTop');
            if (window.pageYOffset > 300) {
                scrollTop.classList.add('show');
            } else {
                scrollTop.classList.remove('show');
            }
        });

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('carModal');
            if (modal.classList.contains('show')) {
                if (e.key === 'Escape') {
                    closeModal();
                } else if (e.key === 'ArrowLeft') {
                    changeImage(-1);
                } else if (e.key === 'ArrowRight') {
                    changeImage(1);
                }
            }
            
            const shareModal = document.getElementById('shareModal');
            if (shareModal.classList.contains('show') && e.key === 'Escape') {
                closeShareModal();
            }
        });
        
        // Override functions with fixed versions

function submitModalComment(event, carId) {

    event.preventDefault();

    

    const name = document.getElementById('modal-comment-name').value.trim();

    const email = document.getElementById('modal-comment-email').value.trim();

    const phone = document.getElementById('modal-comment-phone').value.trim();

    const comment = document.getElementById('modal-comment-text').value.trim();

    

    if (!name || !email || !comment) {

        alert('Please fill in all required fields');

        return;

    }

    

    const submitBtn = event.target.querySelector('button[type="submit"]');

    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    submitBtn.disabled = true;

    

    const formData = new FormData();

    formData.append('action', 'add_comment');

    formData.append('car_id', carId);

    formData.append('name', name);

    formData.append('email', email);

    formData.append('phone', phone);

    formData.append('comment', comment);

    

    fetch('analytics.php', {

        method: 'POST',

        body: formData

    })

    .then(response => response.json())

    .then(data => {

        submitBtn.innerHTML = originalText;

        submitBtn.disabled = false;

        

        if (data.success) {

            alert('Comment submitted successfully!');

            document.getElementById('modal-comment-form').reset();

            loadModalComments(carId);

            loadModalStats(carId);

        } else {

            alert(data.error || 'Failed to submit comment');

        }

    })

    .catch(error => {

        console.error('Error:', error);

        submitBtn.innerHTML = originalText;

        submitBtn.disabled = false;

        alert('Error: ' + error.message);

    });

}

function escapeHtml(text) {

    const div = document.createElement('div');

    div.textContent = text;

    return div.innerHTML;

}
        
        // === OVERRIDE FUNCTIONS - ADD THIS AT THE END ===

function loadModalStats(carId) {

    fetch('analytics.php', {

        method: 'POST',

        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },

        body: `action=track_view&car_id=${carId}`

    }).catch(e => console.error('View error:', e));

    

    fetch(`analytics.php?action=get_stats&car_id=${carId}`)

        .then(r => r.json())

        .then(data => {

            document.getElementById('modal-view-count').textContent = data.view_count || 0;

            document.getElementById('modal-like-count').textContent = data.like_count || 0;

            document.getElementById('modal-comment-count').textContent = data.comment_count || 0;

            

            if (data.user_has_liked) {

                const btn = document.getElementById('modal-like-btn');

                btn.classList.remove('btn-primary');

                btn.classList.add('btn-success');

                document.getElementById('modal-like-text').textContent = 'Liked';

            }

        })

        .catch(e => console.error('Stats error:', e));

}

function toggleModalLike(carId) {

    const button = document.getElementById('modal-like-btn');

    const original = button.innerHTML;

    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    button.disabled = true;

    

    fetch('analytics.php', {

        method: 'POST',

        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },

        body: `action=toggle_like&car_id=${carId}`

    })

    .then(r => r.json())

    .then(data => {

        if (data.success) {

            if (data.action === 'liked') {

                button.classList.remove('btn-primary');

                button.classList.add('btn-success');

                document.getElementById('modal-like-text').textContent = 'Liked';

            } else {

                button.classList.remove('btn-success');

                button.classList.add('btn-primary');

                document.getElementById('modal-like-text').textContent = 'Like';

            }

            document.getElementById('modal-like-count').textContent = data.like_count;

        }

        button.innerHTML = original;

        button.disabled = false;

    })

    .catch(e => {

        alert('Error: ' + e.message);

        button.innerHTML = original;

        button.disabled = false;

    });

}

function loadModalComments(carId) {

    const list = document.getElementById('modal-comments-list');

    list.innerHTML = 'Loading...';

    

    fetch(`analytics.php?action=get_comments&car_id=${carId}`)

        .then(r => r.json())

        .then(comments => {

            list.innerHTML = '';

            if (!comments.length) {

                list.innerHTML = '<p style="color:#999">No comments yet</p>';

                return;

            }

            comments.forEach(c => {

                const div = document.createElement('div');

                div.style.cssText = 'padding:15px;background:#f8f9fa;border-radius:8px;margin-bottom:10px;border-left:3px solid #667eea';

                div.innerHTML = `<strong>${c.user_name}</strong><br><p>${c.comment_text}</p>`;

                list.appendChild(div);

            });

        })

        .catch(e => {

            list.innerHTML = '<p style="color:red">Error loading comments</p>';

            console.error('Comments error:', e);

        });

}

function submitModalComment(event, carId) {

    event.preventDefault();

    

    const formData = new FormData();

    formData.append('action', 'add_comment');

    formData.append('car_id', carId);

    formData.append('name', document.getElementById('modal-comment-name').value);

    formData.append('email', document.getElementById('modal-comment-email').value);

    formData.append('phone', document.getElementById('modal-comment-phone').value);

    formData.append('comment', document.getElementById('modal-comment-text').value);

    

    fetch('analytics.php', { method: 'POST', body: formData })

        .then(r => r.json())

        .then(data => {

            if (data.success) {

                alert('Comment submitted!');

                document.getElementById('modal-comment-form').reset();

                loadModalComments(carId);

                loadModalStats(carId);

            } else {

                alert('Error: ' + data.error);

            }

        })

        .catch(e => alert('Error: ' + e.message));

}

console.log('Functions loaded!');
        
    </script>
</body>
</html>
