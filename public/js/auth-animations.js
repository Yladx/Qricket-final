// Animated text functionality for Qricket authentication pages
const texts = [
    "Welcome To Qricket",
    "Scan. Enter. Experience.",
    "Secure. Simple. Instant.",
    "Your Event. Your Code."
];

let idx = 0;
const h1 = document.getElementById("changing-text");

function changeText() {
    if (!h1) return;
    
    h1.classList.add("animate");
    setTimeout(() => {
        idx = (idx + 1) % texts.length;
        h1.textContent = texts[idx];
        h1.classList.remove("animate");
    }, 250);
}

// Initialize animated text when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (h1) {
        h1.textContent = texts[0];
        setInterval(changeText, 2000);
    }
});
