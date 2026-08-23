<?php

// Read the content of index.php
$indexContent = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/index.php");

// Use regex to find the src attribute inside <img> tag within the banner section
if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*class=["\']banner-image["\']/i', $indexContent, $matches)) {
    $bannerImage = $matches[1]; // Extracted image src
} else {
    $bannerImage = "assets/images/banner-bg.svg"; // Fallback image
}


echo "
<style>
.header_admin {
    margin: 0;
    font-family: Arial, sans-serif;
    color: #333;
    background-image: url('$bannerImage');
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-color: #D3988D;
}

.banner {
    position: relative;
    width: 100%;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    clip-path: polygon(0 100%, 100% 70%, 100% 0, 0 0);
}
        
.banner-background {
    position: fixed;
    width: 100%;
    height:100%;
    display: flex;
    overflow: hidden;
    background-color: #d3988d; /* Background behind the crop */
    z-index: -1;
}  
/* Banner Image */
.banner img.banner-image {
    width: 100%;
    height: 100%;
    object-fit: cover;

}
</style>";
?>