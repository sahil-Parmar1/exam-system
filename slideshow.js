let slideIndex = 0; // Initial slide index
let autoSlideInterval; // Variable to hold the automatic slideshow interval

// Initialize the slideshow
function initSlideshow() {
    showSlides(slideIndex); // Display the first slide
    startAutoSlide(); // Start automatic slideshow
}

// Display the current slide and update dots
function showSlides(n) {
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    if (n >= slides.length) slideIndex = 0; // Wrap around to the first slide
    if (n < 0) slideIndex = slides.length - 1; // Wrap around to the last slide

    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none"; // Hide all slides
    }
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", ""); // Remove active class from all dots
    }
    slides[slideIndex].style.display = "block"; // Show the current slide
    dots[slideIndex].className += " active"; // Add active class to the current dot
}

// Move to the next or previous slide manually
function plusSlides(n) {
    slideIndex += n;
    showSlides(slideIndex);
    resetAutoSlide(); // Restart the automatic slideshow
}

// Move to a specific slide manually
function currentSlide(n) {
    slideIndex = n;
    showSlides(slideIndex);
    resetAutoSlide(); // Restart the automatic slideshow
}

// Start the automatic slideshow
function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
        slideIndex++;
        showSlides(slideIndex);
    }, 4000); // Change slide every 2 seconds
}

// Stop the automatic slideshow
function stopAutoSlide() {
    clearInterval(autoSlideInterval);
}

// Restart the automatic slideshow
function resetAutoSlide() {
    stopAutoSlide(); // Stop the current interval
    startAutoSlide(); // Start a new interval
}

// Initialize the slideshow on page load
window.onload = initSlideshow;