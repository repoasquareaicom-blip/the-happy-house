<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $content = $_POST["content"];

    // Define the file to edit
    $indexFile = "index.php";

    // Read the existing content of the file
    $existingContent = file_get_contents($indexFile);

    // Use regex to only replace content between <!-- START EDIT --> and <!-- END EDIT -->
    $updatedContent = preg_replace_callback(
        '/(<!-- START EDIT -->)(.*?)(<!-- END EDIT -->)/s',
        function ($matches) use ($content) {
            return $matches[1] . "\n" . 
                '<div id="edit-container" <?php if ($canEdit): ?> contenteditable="true" <?php endif; ?>>' . "\n" .
                $content . "\n" .
                '</div>' . "\n" . 
                $matches[3];
        },
        $existingContent
    );

    // Save back to index.php
    file_put_contents($indexFile, $updatedContent);

    echo "✅ Content updated successfully!";
}
?>
