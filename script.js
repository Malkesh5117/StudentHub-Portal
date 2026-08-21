/**
 * StudentHub Portal - Core Interactivity (Practical 4)
 * Developed by Malkesh Patel (25DCS089)
 */

// Mobile Navigation Toggle
function toggleMenu() {
  const menu = document.getElementById("menu");
  if (menu) {
    menu.classList.toggle("show");
  }
}

// Dark/Light Theme Switcher with localStorage
function toggleTheme() {
  document.body.classList.toggle("dark");
  const isDark = document.body.classList.contains("dark");
  localStorage.setItem("studenthub_theme", isDark ? "dark" : "light");

  const themeBtn = document.querySelector(".theme-btn");
  if (themeBtn) {
    themeBtn.textContent = isDark ? "☀️" : "🌙";
    themeBtn.setAttribute("aria-label", isDark ? "Switch to light mode" : "Switch to dark mode");
  }
}

// Banner Dismiss
function closeBanner() {
  const banner = document.getElementById("banner");
  if (banner) {
    banner.style.display = "none";
  }
}

// Modal Dialog Helpers
function openModal(modalId = "modal") {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "flex";
  }
}

function closeModal(modalId = "modal") {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "none";
  }
}

// DOM Initializer
document.addEventListener("DOMContentLoaded", function () {
  // Restore theme preference
  const savedTheme = localStorage.getItem("studenthub_theme");
  if (savedTheme === "dark") {
    document.body.classList.add("dark");
    const themeBtn = document.querySelector(".theme-btn");
    if (themeBtn) themeBtn.textContent = "☀️";
  }

  // Collapsible FAQ Accordion
  const faqButtons = document.querySelectorAll(".faq-btn");
  faqButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const answer = this.nextElementSibling;
      const isOpen = this.classList.contains("active");

      faqButtons.forEach(function (btn) {
        btn.classList.remove("active");
        if (btn.nextElementSibling) {
          btn.nextElementSibling.style.display = "none";
        }
      });

      if (!isOpen && answer) {
        this.classList.add("active");
        answer.style.display = "block";
      }
    });
  });

  // Notice & Announcement Slider
  const slides = document.querySelectorAll(".slide");
  if (slides.length > 0) {
    let currentSlide = 0;
    slides[0].classList.add("active");

    setInterval(function () {
      slides[currentSlide].classList.remove("active");
      currentSlide = (currentSlide + 1) % slides.length;
      slides[currentSlide].classList.add("active");
    }, 4000);
  }
});
