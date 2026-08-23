<?php
session_start();

$stripeConfig = require 'config/stripe.php';
$stripeMode   = $stripeConfig['mode'] ?? 'live';
$stripePk     = $stripeConfig[$stripeMode]['publishable_key'] ?? '';

$canEdit = isset($_SESSION["admin_login_status"]) && $_SESSION["admin_login_status"] === "true";
include 'config/data.php';
$dataObj = new Data();

// Fetch all active products
$sql = "SELECT product_key, display_name, price_amount, currency FROM products_master WHERE status = 1";
$productList = $dataObj->getData($sql);

// Re-map the array so we can access it by product_key in JS
$products = [];
foreach($productList as $prod) {$products[$prod['product_key']] = $prod;
}

$products_json = json_encode($products);

$google_play_store_link = $dataObj->getAppSetting('google_store_link') ?? '0';
$app_store_link = $dataObj->getAppSetting('apple_store_link') ?? '0';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Happy House</title>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<!---Styles--->
<link rel="stylesheet" href="./css/animate.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<!-- Favicons -->
<link rel="icon" href="./favicons/favicon.ico" sizes="any" />
<link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
<link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
<link rel="manifest" href="./favicons/manifest.webmanifest" />
  <!--  Essential META Tags -->
	<meta property="og:title" content="The Happy House ">
	<meta property="og:type" content="website" />
	<meta property="og:image" content="https://thehappyhouse.au/dev/assets/images/The-Happy-House-Logo.svg">
	<meta property="og:url" content="https://thehappyhouse.au">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Calibri, sans-serif;
        }
        body {
                background-color:#E6E6E6;
            <?php if ($canEdit): ?>cursor:pointer; <?php endif; ?>
        }
        .banner {
            position: relative;
            width: 100%;
            height: 90vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            clip-path: polygon(0 100%, 100% 70%, 100% 0, 0 0);
        }
.banner-background {
  position: relative;
  width: 100%;
 display: flex;
  overflow: hidden;
  background-color: #d3988d; /* Background behind the crop */
  z-index: 1;
}     
        .saveChangesButton
        {
            position:fixed;
            bottom:0px;
            right:0px;
            z-index: 999;;
        }
        /* Banner Image */
        .banner img.banner-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        /* Logo - Fixed Position */
        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 300px; /* Adjusted size */
            height: auto;
        }
        /* Login Button */
        .login-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #4C7AA2;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            backdrop-filter: blur(10px);
            z-index: 999;
        }
        /* Hover Effect */
        .login-button:hover {
            background-color: #7ca7cf;
        }
        /* Text Positioned Bottom-Left for Large Screens */
        .banner-content {
            position: absolute;
            bottom: 150px;
            left: 40px;
            color: white;
            width: auto;
            max-width: 40%;
            text-align: left;
            padding:25px;
            border-radius:25px;
            opacity: 0; /* Initially hidden */
            animation: fadeInUp 1s ease-out forwards; /* Animation added */
            background: #d3988d;
        }
        /* Banner Text Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .banner-content h1 {
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }
        .video-section {
            position: relative;
            width: 100%;
            overflow: hidden;
            background-color: #4C7AA2;
        }
        .fullscreen-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        /* Section with fixed background color */
        .product-section {
            position: relative;
            width: 100%;
            min-height:500px;
            background-color:#d3988d; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 50px 20px;
            overflow: hidden; /* Ensures smooth content flow */
            padding-top:0px;
        }
        .curriculum-section {
            position: relative;
            width: 100%;
            min-height:500px;
            background-color: #4C7AA2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 50px 20px;
            overflow: hidden; /* Ensures smooth content flow */
            padding-top:0px;
            
        }
        .product-content {
            position: absolute;
            /* transform: translateX(-40%); */
            text-align: center;
            width: 100%; /* Ensures proper alignment */
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        /* Background Image */
        .product-bg {
            position: relative;
            top: 0;
            width:260px;
            margin-top:30px;
            display:none;
        }
        /* Heading */
        .product-heading {
            position: relative;
            font-size: 2rem;
            color: #fff;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 20px;
            z-index: 2;
        }
        /* Subscribe Button */
        .subscribe-button {
            position: relative;
            background-color: #4C7AA2;
            color: white;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            margin: 20px 0;
            z-index: 2;
            padding:15px;
            padding-left:50px;
            padding-right:50px;
        }
        .subscribe-button:hover {
            background-color: #2e4961;
        }
        .curriculum-subscribe-button {
            position: relative;
            background-color: #d3988d;;
            color: white;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            margin: 20px 0;
            z-index: 2;
            padding:15px;
            padding-left:50px;
            padding-right:50px;
        }
        .curriculum-subscribe-button:hover {
            background-color: #acbcca;
        }
        /* Paragraph */
        .product-description {
            position: relative;
            /* max-width: 80%; */
            margin-left:25px;
            margin-right:25px;
            color: #fff;
            background-color: transparent;
            padding: 15px;
            border-radius: 15px;
            font-size: 1.5rem;
            z-index: 2;
            margin-top:25px;
        }
        @media (min-width: 992px) {
            .product-description {
                width: 65%;
                margin-left: auto;
                margin-right: auto;
                text-align: center; /* Keeps the text block centered */
            }
        }
        /* About Section */
        .about-section {
            position: relative;
            width: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            overflow: hidden;
            min-height: auto; /* Adjusts based on content */
        }
        .about-bg {
            width: 100%;
            height: auto; /* Ensures image scales properly */
            display: block;
        }
        .about-content {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: bold;
            z-index: 2;
            color: #000;
        }
        .download-section {
            position: relative;
            width: 100%;
            background-color: #B8D1D9; /* Background color */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 10px 10px;
        }
        .download-heading {
            font-size: 2rem;
            color: #333;
            margin-bottom: 20px;
        }
        .download-links {
            display: flex;
            gap: 20px;
        }
        .store-icon {
            width: 180px;
            height: auto;
            transition: transform 0.3s ease;
        }
        .store-icon:hover {
            transform: scale(1.1);
        }
        .footer-section {
            width: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
            color: white;
            text-align: center;
            padding: 15px 0;
            font-size: 1rem;
        }
        /* Fade-in Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.5s ease-out, transform 1s ease-out;
        }
        /* When the section becomes visible */
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .banner
            {
                height:80vh;
            }
            .banner-content {
                bottom: auto;
                left: auto;
                text-align: center;
                width: 80%;
                max-width: 90%;
                bottom:250px;
                padding:10px;
            }
            .banner-content h1 {
                font-size: 1.5rem;
            }
            .logo {
                width: 180px; /* Smaller logo on mobile */
            }
            .login-button {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
                .product-heading {
                font-size: 1.5rem;
            }
            .product-section
            {
                min-height:100px;
            }
            .product-content {
                position: static;
                left: auto;
                right: auto;
                transform: none;
                text-align: center;
                width: 100%;
                }
            .subscribe-button {
                font-size: 0.9rem;
                padding: 10px 20px;
            }
            .product-bg {
                width:180px;
                display:none;
            }
            .product-description {
                max-width: 100%;
                font-size: 1.2rem;
                padding: 10px;
            }
            .about-section {
                flex-direction: column;
                align-items: center;
                text-align: center;
                position: relative;
            }
            .about-content {
                position: relative; /* Moves text above image */
                max-width: 90%;
                margin-bottom: 10px;
                margin-top:10px;
                left: 0px;
                top: auto; /* Prevents text from hiding at the bottom */
                z-index: 2;
                font-size: 0.5rem;
            }
            .about-bg {
                position: relative;
                width: 100%;
                height: auto; /* Ensures full visibility */
            }
            .download-heading {
                font-size: 1.5rem;
            }
            .download-links {
                flex-direction: row;
                gap: 15px;
            }
            .store-icon {
                width: 80px;
            }     
            .footer-section {
                font-size: 0.9rem;
            }       
        }
        .bottom-links {
            text-align: center;
            margin-top: 20px;
            font-family: Arial, sans-serif;
        }

        .bottom-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .bottom-links a:hover {
            opacity: 0.8;
            text-decoration: underline;
            color:#d3988d;
            font-weight: 600;
        }

        .bottom-links .dot {
            color: white;
            margin: 0 10px;
            font-size: 14px;
        }
        .custom-theme {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .custom-theme .modal-header {
            background-color: #d3988d;
            color: white;
        }

        .btn-theme {
            background-color: #d3988d;
            color: white;
            border: none;
            transition: 0.3s;
        }

        .btn-theme:hover {
            background-color: #c2877c; /* Slightly darker shade */
            color: white;
        }

        .preview-link {
            color: #d3988d;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .preview-link:hover {
            text-decoration: underline;
        }

        /* Ensure progress bar matches theme */
        .progress-bar {
            background-color: #d3988d !important;
        }

        /* Input focus color */
        .form-control:focus, .form-select:focus {
            border-color: #d3988d;
            box-shadow: 0 0 0 0.25 cold-rem rgba(211, 152, 141, 0.25);
        }
    </style>
</head>
<body>
<button class="login-button" onclick="window.location.href='school_admin_login.php'">Login</button>
<!-- START EDIT -->
<div id="edit-container" <?php if ($canEdit): ?> contenteditable="true" <?php endif; ?>>






    <section class="banner-background">
    <section class="banner">
        <img src="uploads/1771637354_happy house closeup bg.png" alt="Banner Background" class="banner-image" onclick="changeImage(this,'banner')" style="display: block;">
        <img src="assets/images/The-Happy-House-Logo.svg" width="100%" height="100%" alt="Logo" class="logo aos-init aos-animate" data-aos="zoom-in" onclick="changeImage(this,'banner')" style="display: block;">
        <div class="banner-content">
            <h1>Nurturing Young Minds Through Calm, Wellbeing, &amp; Engaging Game Activities</h1>
        </div>
    </section>
   </section>
    <section class="product-section fade-in visible">
        <!-- Background Image -->
        <img src="uploads/1739620152_The-Happy-House-Logo.svg" alt="Product Background" class="product-bg" onclick="changeImage(this,'banner')">
        <div class="product-content">
        <!-- Heading -->
            <h2 class="product-heading aos-init aos-animate" data-aos="fade-up" data-aos-easing="linear" data-aos-duration="400">
                Transforming Classroom Wellbeing:
                <br><span style="color:#4c7aa2;" data-aos="fade-up" data-aos-easing="linear" data-aos-duration="600" class="aos-init aos-animate">A Revolutionary Support Platform for Primary Schools</span>
            </h2>
            <!-- Subscribe Button -->
            <button class="subscribe-button" id="checkout-button" onclick="openSubModal('wellbeing_games')">Subscribe</button>
            <!-- Paragraph -->
            <p class="product-description aos-init aos-animate" data-aos="fade-down">
                A well-being app designed to provide engaging activities and games rooted in neuroscience and behavioral science 
                to support primary school-aged children. It is particularly beneficial for children affected by Autism, ADHD, or 
                other conditions that may impact emotional regulation and focus.
            </p>
            </div>
    </section>
    <section class="curriculum-section fade-in visible">
        <h2 class="product-heading aos-init aos-animate" data-aos="fade-up" data-aos-easing="linear" data-aos-duration="400">
                The Happy House Curriculum Learning Program
        </h2>
        <p class="product-description aos-init aos-animate" data-aos="fade-down">
            Our comprehensive yearly subscription designed to support schools with a structured, engaging, and age-appropriate learning journey across the entire academic year. Through this subscription, educators gain ongoing access to our complete curriculum resources, interactive activities, and wellbeing-focused learning experiences that nurture curiosity, confidence, young learners.
        </p>
        <button class="curriculum-subscribe-button" id="checkout-button-curriculum" onclick="openSubModal('curriculum')">Subscribe</button>
    </section>
     <section class="video-section">
        <video class="fullscreen-video" autoplay="" loop="" muted="" playsinline="" onclick="changeImage(this,'video')" style="display: block;">
            <!-- <source src="assets/images/section_2.mp4" type="video/mp4"> -->
            <source src="uploads/1772711384_happy-house-splash-1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </section>
    <section class="about-section fade-in visible">
        <!-- Background Image -->
        <img src="uploads/1774584368_The-Happy-House-Poster-home.png" alt="About Background" class="about-bg" onclick="changeImage(this,'banner')" style="display: block;">
        <!-- Text Content -->
        <div class="about-content">
            <h2>Developed by Youth Dimension:<br> Shaping Futures with Hope and Purpose</h2>
        </div>
    </section>
    <section class="download-section fade-in visible">
        <h2 class="download-heading">Download Our App</h2>
        <div class="download-links aos-init aos-animate" data-aos="zoom-out">

            <a href="<?php echo htmlspecialchars($google_play_store_link); ?>" target="_blank">
                <img src="assets/images/play-store-app-icon.svg" 
                    alt="Download on Play Store" 
                    class="store-icon" 
                    onclick="changeImage(this,'banner')">
            </a>

            <a href="<?php echo htmlspecialchars($app_store_link); ?>" target="_blank">
                <img src="assets/images/apple-ios-app-icon.svg" 
                    alt="Download on App Store" 
                    class="store-icon" 
                    onclick="changeImage(this,'banner')">
            </a>

        </div>
    <div class="bottom-links">
        <a href="helpful-tips" target="_blank">Helpful Tips</a>
        <span class="dot">•</span>
        <a href="terms-of-use" target="_blank">Terms Of Use</a>
        <span class="dot">•</span>
        <a href="privacy-policy" target="_blank">Privacy Policy</a>
    </div>
    </section>
    <footer class="footer-section">
        <p class="footer-text">© 2025 The Happy House. All rights reserved.</p>
    </footer>






</div>
<!-- END EDIT -->
    <?php if ($canEdit): ?>
    <input type="file" id="fileInput" style="display:none;" onchange="uploadFile()">
    <button onclick="saveContent()" class="subscribe-button" style="position:fixed;bottom:10px; right:10px">Save Changes</button>
<script>
        function changeImage(element,object) {
            selectedElement = event.target;
            document.getElementById('fileInput').dataset.target = element.tagName.toLowerCase();
            document.getElementById('fileInput').click();
        }
        let xhr; // Declare globally for cancel function
        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB in bytes
        function uploadFile() {
            let fileInput = document.getElementById('fileInput');
            let file = fileInput.files[0];
            let errorMessage = document.getElementById("errorMessage");
            // Clear previous error messages
            errorMessage.textContent = "";
            if (!file) {
                errorMessage.textContent = "Please select a file!";
                return;
            }
            // Validate file size
            if (file.size > MAX_FILE_SIZE) {
                errorMessage.textContent = "File size must be less than 10MB!";
                return;
            }
            let formData = new FormData();
            formData.append("file", file);
            // Show overlay
            document.getElementById("uploadOverlay").style.display = "flex";
            xhr = new XMLHttpRequest();
            xhr.open("POST", "upload.php", true);
            // Track progress
            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable) {
                    let percentComplete = Math.round((event.loaded / event.total) * 100);
                    let progressBar = document.getElementById("progressBar");
                    progressBar.style.width = percentComplete + "%";
                    progressBar.textContent = percentComplete + "%";
                }
            };
            xhr.onload = function () {
                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);
                    if (response.success && selectedElement) {
                        if (selectedElement.tagName.toLowerCase() === "img") {
                            selectedElement.src = response.url; 
                            selectedElement.style.display = "block"; // Show image
                        } else if (selectedElement.tagName.toLowerCase() === "video") {
                            selectedElement.querySelector("source").src = response.url;
                            selectedElement.load();
                            selectedElement.style.display = "block"; // Show video
                        }
                    }
                    closeOverlay();
                } else {
                    console.error("Upload failed");
                    closeOverlay();
                }
            };
            xhr.onerror = function() {
                console.error("Upload error");
                closeOverlay();
            };
            xhr.send(formData);
        }
        function cancelUpload() {
            if (xhr) {
                xhr.abort(); // Cancel the request
                console.log("Upload canceled");
            }
            closeOverlay();
        }
        function closeOverlay() {
            document.getElementById("uploadOverlay").style.display = "none";
            document.getElementById("progressBar").style.width = "0%";
            document.getElementById("progressBar").textContent = "0%";
        }
                function saveContent() {
                    let content = document.getElementById('edit-container').innerHTML;
                    fetch("save.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "content=" + encodeURIComponent(content)
                    })
                    .then(response => response.text())
                    .then(data => showStylishAlert())
                    .catch(error => console.error("Error saving content:", error));
                }
                function showStylishAlert() {
                Swal.fire({
                    title: "Success! 🎉",
                    text: "Your file has been uploaded successfully.",
                    icon: "success",
                    confirmButtonText: "OK",
                    timer: 3000, // Auto-close after 3 seconds
                    showClass: {
                        popup: "animate__animated animate__fadeInDown"
                    },
                    hideClass: {
                        popup: "animate__animated animate__fadeOutUp"
                    }
                });
            }
        </script>
<style>
   /* Full-screen overlay */
   #uploadOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
        }
        /* Progress bar container */
        #progressContainer {
            width: 50%;
            background: #444;
            border-radius: 5px;
            overflow: hidden;
            height: 20px;
            margin-bottom: 15px;
        }
        /* Progress bar */
        #progressBar {
            width: 0%;
            height: 100%;
            background: #4CAF50;
            text-align: center;
            line-height: 20px;
            color: white;
        }
        /* Cancel button */
        #cancelUpload {
            padding: 10px 15px;
            background: red;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        /* Error message */
        #errorMessage {
            color: red;
            margin-top: 10px;
            position:fixed;
            bottom:25px;
            left:25px;
            background-color:yellow;
        }
</style>
 <!-- Overlay with progress bar -->
 <div id="uploadOverlay">
        <div id="progressContainer">
            <div id="progressBar">0%</div>
        </div>
        <button id="cancelUpload" onclick="cancelUpload()">Cancel</button>
    </div>
<p id="errorMessage"></p>
    <?php endif; ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const fadeElements = document.querySelectorAll(".fade-in");
        function fadeInOnScroll() {
            fadeElements.forEach((element) => {
                const rect = element.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    element.classList.add("visible");
                }
            });
        }
        // Run on page load and scroll
        fadeInOnScroll();
        window.addEventListener("scroll", fadeInOnScroll);
    });

    var stripe = Stripe("<?= htmlspecialchars($stripePk) ?>");

    // document.getElementById('checkout-button').addEventListener('click', function () {
    //     fetch('checkout.php')
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.error) {
    //                 alert(data.error);
    //                 return;
    //             }
    //             return stripe.redirectToCheckout({ sessionId: data.id });
    //         })
    //         .then(result => {
    //             if (result && result.error) {
    //                 alert(result.error.message);
    //             }
    //         });
    // });

    // document.getElementById('checkout-button-curriculum').addEventListener('click', function () {
    //     fetch('checkout_curriculum.php')
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.error) {
    //                 alert(data.error);
    //                 return;
    //             }
    //             return stripe.redirectToCheckout({ sessionId: data.id });
    //         })
    //         .then(result => {
    //             if (result && result.error) {
    //                 alert(result.error.message);
    //             }
    //         });
    // });
       
</script>
<script>
//AOS animate on scroll
  document.addEventListener("DOMContentLoaded", function() {
    // Only initialize AOS if reduced motion is not enabled
    if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      AOS.init({
        once: true // Ensure animations happen only once
      });
      // Refresh AOS in case content is dynamically loaded
      AOS.refresh();
    }
  });
</script>
</body>

<div id="subscriptionModal" class="modal-overlay hidden">
    <div class="modal-card">
        <button class="close-modal" onclick="closeModal()">&times;</button>
        
        <div class="modal-content">
            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="product-box">
                    <span id="displayProductName" class="product-name">Wellbeing Games</span>
                    <span id="displayProductPrice" class="product-price">£XX.XX/mo</span>
                </div>
                <p class="summary-note">We will verify your email before proceeding to secure payment via Stripe.</p>
            </div>

            <div class="auth-section">
                <h2>Get Started</h2>
                
                <div id="emailSection">
                    <p>Enter your email to receive a verification code.</p>
                    <div class="input-group">
                        <input type="email" id="subscriberEmail" placeholder="School Admin Email" required>
                    </div>
                    <button class="action-btn" id="sendOtpBtn" onclick="sendOTP()">
                        <span class="btn-text">Send Code</span>
                        <div class="loader hidden"></div>
                    </button>
                </div>

                <div id="otpSection" class="hidden">
                    <div id="schoolWelcome" class="hidden" style="background: #eefbff; padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 5px solid #004666;">
                        <p style="margin: 0; font-size: 0.85rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Recognized School:</p>
                        <strong id="modalSchoolName" style="color: #004666; font-size: 1.1rem; display: block; margin-top: 2px;"></strong>
                    </div>
                    <p>Enter the 6-digit code sent to your email.</p>
                    <div class="otp-input-wrapper">
                        <input type="text" maxlength="1" class="otp-digit" id="otp-1">
                        <input type="text" maxlength="1" class="otp-digit" id="otp-2">
                        <input type="text" maxlength="1" class="otp-digit" id="otp-3">
                        <input type="text" maxlength="1" class="otp-digit" id="otp-4">
                        <input type="text" maxlength="1" class="otp-digit" id="otp-5">
                        <input type="text" maxlength="1" class="otp-digit" id="otp-6">
                    </div>
                    
                    <button class="action-btn" id="verifyOtpBtn" onclick="verifyOTP()">
                        <span class="btn-text">Verify & Pay</span>
                        <div class="loader hidden"></div>
                    </button>

                    <div class="resend-container">
                        <p id="timerText">Resend code in <span id="countdown">60</span>s</p>
                        <button id="resendBtn" class="resend-link hidden" onclick="sendOTP()">
                            <span class="btn-text">Resend Code</span>
                            <div class="loader hidden"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .loader {
        width: 20px; height: 20px;
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
        display: inline-block; margin-left: 10px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .resend-container { margin-top: 15px; font-size: 0.9rem; }
    .resend-link { 
        background: none; border: none; color: #b77c72; 
        text-decoration: underline; cursor: pointer; font-weight: bold;
    }
    .hidden { display: none !important; }
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); backdrop-filter: blur(5px);
        display: flex; justify-content: center; align-items: center; z-index: 1000;
    }
    .modal-card {
        background: #fff; width: 90%; max-width: 800px; border-radius: 20px;
        position: relative; overflow: hidden; animation: slideUp 0.3s ease-out;
    }
    .modal-content { display: flex; flex-wrap: wrap; }
    
    /* Summary Side */
    .order-summary {
        flex: 1; background: #f8f9fa; padding: 40px; min-width: 300px;
        border-right: 1px solid #eee;
    }
    .product-box { 
        background: #fff; padding: 20px; border-radius: 12px; 
        border: 2px solid #b77c72; margin: 20px 0;
    }
    .product-name { display: block; font-weight: bold; font-size: 1.2rem; }
    .product-price { color: #b77c72; font-size: 1.5rem; font-weight: bold; }

    /* Auth Side */
    .auth-section { flex: 1.2; padding: 40px; min-width: 300px; text-align: center; }
    .input-group input {
        width: 100%; padding: 15px; border-radius: 10px; 
        border: 1px solid #ddd; margin-bottom: 20px; font-size: 1rem;
    }
    .action-btn {
        background: #b77c72; color: white; border: none; padding: 15px 40px;
        border-radius: 30px; font-weight: bold; cursor: pointer; width: 100%;
    }

    /* OTP Inputs */
    .otp-input-wrapper { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
    .otp-digit {
        width: 45px; height: 55px; text-align: center; font-size: 1.5rem;
        border: 2px solid #ddd; border-radius: 10px;
    }

    
    @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .close-modal { position: absolute; top: 15px; right: 20px; font-size: 2rem; border: none; background: none; cursor: pointer; }
</style>
<script>
    let selectedType = "";

    // Open Modal for Games
    document.getElementById('checkout-button').addEventListener('click', function() {
        openSubModal("wellbeing_games", "Wellbeing Games", "£10.00");
    });

    // Open Modal for Curriculum
    document.getElementById('checkout-button-curriculum').addEventListener('click', function() {
        openSubModal("curriculum", "Curriculum Access", "£25.00");
    });
    const productData = <?php echo $products_json; ?>;
    
    function openSubModal(type) {
        const product = productData[type];
        
        if(!product) {
            alert("Error: Product configuration missing.");
            return;
        }

        selectedType = type;

        // --- RESET MODAL STATE ---
        // 1. Clear text inputs
        document.getElementById('subscriberEmail').value = '';
        
        // 2. Clear all 6 OTP digit boxes
        document.querySelectorAll('.otp-digit').forEach(input => {
            input.value = '';
        });

        // 3. Reset Section Visibility (Show email, hide OTP)
        document.getElementById('emailSection').classList.remove('hidden');
        document.getElementById('otpSection').classList.add('hidden');
        document.getElementById('schoolWelcome').classList.add('hidden');
        
        // 4. Reset Button States (In case they were stuck on 'Loading')
        const sendBtn = document.getElementById('sendOtpBtn');
        sendBtn.disabled = false;
        sendBtn.querySelector('.btn-text').innerText = 'Send Code';
        sendBtn.querySelector('.loader').classList.add('hidden');

        // --- UPDATE UI ---
        document.getElementById('displayProductName').innerText = product.display_name;
        document.getElementById('displayProductPrice').innerText = 
            product.currency + ' ' + parseFloat(product.price_amount).toFixed(2);

        document.getElementById('subscriptionModal').classList.remove('hidden');
    }
    let timerInterval;

    // --- 1. OTP Input Handling (Backspace & Auto-focus) ---
    const otpInputs = document.querySelectorAll('.otp-digit');
    otpInputs.forEach((input, index) => {
        // Handle typing
        input.addEventListener('input', (e) => {
            if (e.inputType === "deleteContentBackward") return; // Skip if backspacing
            if (input.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        // Handle Backspace
        input.addEventListener('keydown', (e) => {
            if (e.key === "Backspace" && input.value === "" && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email.toLowerCase());
    }

    // This is the function we'll call when they click "Send Code"
    async function sendOTP() {
        const email = document.getElementById('subscriberEmail').value;
        const sendBtn = document.getElementById('sendOtpBtn');
        const resendBtn = document.getElementById('resendBtn');
        
        // Elements for the School Name display
        const schoolWelcomeDiv = document.getElementById('schoolWelcome');
        const schoolNameSpan = document.getElementById('modalSchoolName');

        if(!email) {
            showAlert("Please enter a valid school email.");
            
            return;
        }
        if (!validateEmail(email)) {
            showAlert("Please enter a valid email format (e.g., name@school.com).");
            emailInput.style.borderColor = "red";
            emailInput.focus();
            return;
        }
        // 1. Determine which button triggered the call for the loader
        const activeBtn = !resendBtn.classList.contains('hidden') ? resendBtn : sendBtn;

        // 2. UI Reset: Clear OTP boxes and show loading
        const otpInputs = document.querySelectorAll('.otp-digit'); // Ensure this is defined
        otpInputs.forEach(input => input.value = ""); 
        toggleLoader(activeBtn, true);

        const formData = new FormData();
        formData.append('email', email);
        formData.append('product_key', selectedType);

        try {
            const response = await fetch('process_otp_request.php', { method: 'POST', body: formData });
            const result = await response.json();

            if(result.status === 'success') {
                // --- NEW LOGIC: Show School Name if it exists ---
                if (result.school_name) {
                    schoolNameSpan.innerText = result.school_name;
                    schoolWelcomeDiv.classList.remove('hidden');
                } else {
                    // Hide it if it's a new user (in case they previously typed an existing email)
                    schoolWelcomeDiv.classList.add('hidden');
                }
                // --- END NEW LOGIC ---

                // Switch views
                document.getElementById('emailSection').classList.add('hidden');
                document.getElementById('otpSection').classList.remove('hidden');
                
                // Auto-focus the first box
                if(otpInputs.length > 0) otpInputs[0].focus();

                // 3. Restart the countdown timer
                startResendTimer();
            } else {
                showAlert(result.message);
            }
        } catch (e) {
            console.error(e);
            alert("Connection error. Please try again.");
        } finally {
            toggleLoader(activeBtn, false);
        }
    }
    function startResendTimer() {
        let timeLeft = 60;
        const countdownEl = document.getElementById('countdown');
        const timerText = document.getElementById('timerText');
        const resendBtn = document.getElementById('resendBtn');

        resendBtn.classList.add('hidden');
        timerText.classList.remove('hidden');

        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timeLeft--;
            countdownEl.innerText = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timerText.classList.add('hidden');
                resendBtn.classList.remove('hidden');
            }
        }, 1000);
    }

    // Helper for Button Loading
    function toggleLoader(btn, show) {
        const loader = btn.querySelector('.loader');
        const text = btn.querySelector('.btn-text');
        if(show) {
            loader.classList.remove('hidden');
            btn.disabled = true;
        } else {
            loader.classList.add('hidden');
            btn.disabled = false;
        }
    }
    async function verifyOTP() {
        const otpInputs = document.querySelectorAll('.otp-digit');
        let otpCode = "";
        
        // 1. Collect and concatenate the 6 digits
        otpInputs.forEach(input => {
            otpCode += input.value;
        });

        if (otpCode.length < 6) {
            alert("Please enter the full 6-digit code.");
            return;
        }

        // 2. Show loading state on the button
        const verifyBtn = document.querySelector('#otpSection .action-btn');
        const originalText = verifyBtn.innerText;
        verifyBtn.innerText = "Verifying...";
        verifyBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('otp', otpCode);
            // selectedType was set when the modal opened
            formData.append('product_key', selectedType); 

            // 3. Send to backend
            const response = await fetch('verify_otp_and_pay.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();

            if (result.status === 'success') {
                // 4. Redirect to Stripe Checkout URL
                window.location.href = result.checkout_url;
            } else {
                alert(result.message || "Invalid code. Please try again.");
                verifyBtn.innerText = originalText;
                verifyBtn.disabled = false;
            }
        } catch (error) {
            console.error("Verification error:", error);
            alert("Connection error. Please check your internet.");
            verifyBtn.innerText = originalText;
            verifyBtn.disabled = false;
        }
    }

    function closeModal() {
        document.getElementById('subscriptionModal').classList.add('hidden');
    }

    // Auto-tab for OTP inputs
    document.querySelectorAll('.otp-digit').forEach((input, index, inputs) => {
        input.addEventListener('input', () => {
            if (input.value.length === 1 && index < inputs.length - 1) inputs[index + 1].focus();
        });
    });

    let alertTimeout;

    function showAlert(message, type = 'error') {
        const container = document.getElementById('globalAlertContainer');
        const msgBox = document.getElementById('globalAlertMessage');
        const iconBox = document.getElementById('globalAlertIcon');
        const content = container.querySelector('.global-alert-content');

        // 1. Reset Classes
        content.classList.remove('alert-success', 'alert-error');
        
        // 2. Set Type & Icon
        if (type === 'success') {
            content.classList.add('alert-success');
            iconBox.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else {
            content.classList.add('alert-error');
            iconBox.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
        }

        // 3. Set Message
        msgBox.innerText = message;

        // 4. Show Alert
        container.classList.remove('global-alert-hidden');

        // 5. Auto-hide after 5 seconds
        clearTimeout(alertTimeout);
        alertTimeout = setTimeout(() => {
            closeGlobalAlert();
        }, 5000);
    }

    function closeGlobalAlert() {
        document.getElementById('globalAlertContainer').classList.add('global-alert-hidden');
    }
</script>
<div id="globalAlertContainer" class="global-alert-hidden">
    <div class="global-alert-content">
        <div id="globalAlertIcon"></div>
        <div id="globalAlertMessage"></div>
        <button type="button" class="global-alert-close" onclick="closeGlobalAlert()">&times;</button>
    </div>
</div>
<style>
#globalAlertContainer {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999; /* Higher than Bootstrap Modals */
    width: 90%;
    max-width: 400px;
    transition: all 0.3s ease-in-out;
}

.global-alert-hidden {
    top: -100px !important; /* Hides it above the screen */
    opacity: 0;
    pointer-events: none;
}

.global-alert-content {
    background: white;
    padding: 15px 40px 15px 20px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    border-left: 6px solid #ccc;
}

.global-alert-close {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #999;
}

/* Success State */
.alert-success { border-left-color: #28a745 !important; background-color: #f2fff5 !important; color: #155724 !important; }
/* Error State */
.alert-error { border-left-color: #dc3545 !important; background-color: #fff2f2 !important; color: #721c24 !important; }
</style>
</html>
