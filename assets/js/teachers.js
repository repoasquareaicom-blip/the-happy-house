// document.getElementById("startActualGameBtn").addEventListener("click", function() 
function startActualGameBtn_click()
{
    const gameDifficulty = document.getElementById("gameDifficulty");
    const studentCgameScreenontainer = document.getElementById("gameScreen");

    // Fade out login box
    loginContainer.classList.add("fade-out");

    setTimeout(() => {
        gameDifficulty.style.display = "none";
        gameScreen.style.display = "flex";
        gameScreen.classList.add("fade-in");
    }, 500);
}

function continueBtn_click()
{
    document.getElementById("welcomeOverlay").style.display = "none";

    let video = document.getElementById('videoPlayer');
    if (video.paused) {
        video.play();
    }
}
document.addEventListener("DOMContentLoaded", () => {
    const emojis = document.querySelectorAll(".emoji-option");
    const startGameBtn = document.getElementById("startGameBtn");
    const calloutMessage = document.getElementById("calloutMessage");
    let selectedEmoji = "";  // Track selected emoji

    // Emoji click event
    emojis.forEach(emoji => {
        emoji.addEventListener("click", function () {
            // Remove active class from all emojis
            emojis.forEach(e => e.classList.remove("active"));
            
            // Add active class to clicked emoji
            this.classList.add("active");
            const startGameBtn = document.getElementById("startGameBtn");
            if (startGameBtn) {
            startGameBtn.style.display = "block";  // or "inline-block" depending on your layout
            }

            // Update selected emoji value
            selectedEmoji = this.dataset.value;

            // Hide callout message if previously shown
            calloutMessage.style.display = "none";
        });
    });

    // Button click event
    startGameBtn.addEventListener("click", function () {
        if (selectedEmoji === "") {
            // Shake the button if no emoji selected
            startGameBtn.classList.add("shake");
            
            // Show callout message
            calloutMessage.style.display = "block";

            // Remove shake effect after animation
            setTimeout(() => startGameBtn.classList.remove("shake"), 500);
        } else {
            startGameBtn_click(); // Proceed if emoji is selected
        }
    });
});


// document.getElementById("startGameBtn").addEventListener("click", function() {
function startGameBtn_click()
{
    window.location.href="game_settings.html";
}


