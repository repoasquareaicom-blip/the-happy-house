<?php
session_start();
$canEdit = isset($_SESSION["admin_login_status"]) && $_SESSION["admin_login_status"] === "true";
$canEdit = true;
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
            background-color: rgba(255, 255, 255, 0.41); /* #ffffff69 */
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
            background-color: #5484b2;
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
        .product-content {
            position: absolute;
            /* transform: translateX(-40%); */
            text-align: center;
            width: 1400px; /* Ensures proper alignment */
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
        <img src="assets/images/banner-bg.svg" alt="Banner Background" class="banner-image" onclick="changeImage(this,'banner')" style="display: block;">
        <img src="assets/images/The-Happy-House-Logo.svg" width="100%" height="100%" alt="Logo" class="logo aos-init aos-animate" data-aos="zoom-in" onclick="changeImage(this,'banner')" style="display: block;">
        <div class="banner-content">
            <h1>Nurturing Young Minds Through Calm, Well-being, &amp; Engaging Game Activities</h1>
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
            <button class="subscribe-button aos-init aos-animate" id="checkout-button" data-aos="zoom-out">Subscribe</button>
            <!-- Paragraph -->
            <p class="product-description aos-init aos-animate" data-aos="fade-down">
                A well-being app designed to provide engaging activities and games rooted in neuroscience and behavioral science 
                to support primary school-aged children. It is particularly beneficial for children affected by Autism, ADHD, or 
                other conditions that may impact emotional regulation and focus.
            </p>
            </div>
    </section>
     <section class="video-section">
        <video class="fullscreen-video" autoplay="" loop="" muted="" playsinline="" onclick="changeImage(this,'video')" style="display: block;">
            <!-- <source src="assets/images/section_2.mp4" type="video/mp4"> -->
            <source src="uploads/1739704551_video-example.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </section>
    <section class="about-section fade-in visible">
        <!-- Background Image -->
        <img src="uploads/1739704465_Banner-3.png" alt="About Background" class="about-bg" onclick="changeImage(this,'banner')" style="display: block;">
        <!-- Text Content -->
        <div class="about-content">
            <h2>Developed by Youth Dimension:<br> Shaping Futures with Hope and Purpose</h2>
        </div>
    </section>
    <section class="download-section fade-in visible">
        <h2 class="download-heading">Download Our App</h2>
        <div class="download-links aos-init aos-animate" data-aos="zoom-out">
            <a href="https://play.google.com/store" target="_blank">
                <img src="assets/images/play-store-app-icon.svg" width="100%" height="100%" alt="Download on Play Store" class="store-icon" onclick="changeImage(this,'banner')">
            </a>
            <a href="https://www.apple.com/app-store/" target="_blank">
                <img src="assets/images/apple-ios-app-icon.svg" width="100%" height="100%" alt="Download on App Store" class="store-icon" onclick="changeImage(this,'banner')">
            </a>
        </div>
      <div class="bottom-links">
        <a href="uploads/tips.pdf" target="_blank">Helpful Tips</a>
        <span class="dot">•</span>
        <a href="uploads/terms.pdf" target="_blank">Terms Of Use</a>
        <span class="dot">•</span>
        <a href="uploads/privacy.pdf" target="_blank">Privacy Policy</a>
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
        var stripe = Stripe('pk_test_51QPb6I06kM66h4BbvYmIJtsaCCLKH3gG1hXdmAtFr4ELBycqHuJepVOCwKFKwajwdoU4LjCrP8mF4prGrdO3F5hJ00btWPOjnh'); // Replace with your Publishable Key
        document.getElementById('checkout-button').addEventListener('click', function () {
            fetch('checkout.php') // Your backend script
                .then((response) => response.json())
                .then((data) => {
                    return stripe.redirectToCheckout({ sessionId: data.id });
                })
                .then((result) => {
                    if (result.error) {
                        alert(result.error.message);
                    }
                });
        });
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
</html>
