/**
 * StudentHub Portal - Complete Client-Side Interactivity & Form Validation (Practical 5)
 * Developed by Malkesh Patel (25DCS089)
 */

function toggleMenu() {
  const menu = document.getElementById("menu");
  if (menu) menu.classList.toggle("show");
}

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

function closeBanner() {
  const banner = document.getElementById("banner");
  if (banner) banner.style.display = "none";
}

function openModal(modalId = "modal") {
  const modal = document.getElementById(modalId);
  if (modal) modal.style.display = "flex";
}

function closeModal(modalId = "modal") {
  const modal = document.getElementById(modalId);
  if (modal) modal.style.display = "none";
}

// Form Validation Logic
document.addEventListener("DOMContentLoaded", function () {
  // Theme restoration
  if (localStorage.getItem("studenthub_theme") === "dark") {
    document.body.classList.add("dark");
    const themeBtn = document.querySelector(".theme-btn");
    if (themeBtn) themeBtn.textContent = "☀️";
  }

  // FAQ Accordion
  const faqButtons = document.querySelectorAll(".faq-btn");
  faqButtons.forEach(button => {
    button.addEventListener("click", function () {
      const answer = this.nextElementSibling;
      const isOpen = this.classList.contains("active");
      faqButtons.forEach(btn => {
        btn.classList.remove("active");
        if (btn.nextElementSibling) btn.nextElementSibling.style.display = "none";
      });
      if (!isOpen && answer) {
        this.classList.add("active");
        answer.style.display = "block";
      }
    });
  });

  // Registration Form Validation (Practical 5)
  const regForm = document.getElementById("studentRegistrationForm");
  if (regForm) {
    const rules = {
      fullName: {
        regex: /^[A-Za-z][A-Za-z .'-]{2,39}$/,
        message: "Full name must be 3-40 characters with letters only."
      },
      username: {
        regex: /^[a-z][a-z0-9_]{2,29}$/,
        message: "Username must start with a lowercase letter (3-30 chars, alphanumeric or _)."
      },
      email: {
        regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        message: "Enter a valid institutional email address."
      },
      mobile: {
        regex: /^[6-9]\d{9}$/,
        message: "Mobile number must be a valid 10-digit Indian phone number starting with 6-9."
      },
      password: {
        regex: /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
        message: "Password must be at least 8 chars with at least one letter, number, and special symbol."
      },
      course: {
        validate: val => val && val.trim() !== "",
        message: "Please select an academic program."
      },
      year: {
        validate: val => val && val.trim() !== "",
        message: "Please select your academic year."
      },
      terms: {
        validate: el => el.checked,
        message: "You must accept the academic guidelines to register."
      }
    };

    function validateField(fieldId) {
      const field = document.getElementById(fieldId);
      const errEl = document.getElementById(fieldId + "Error");
      if (!field || !errEl) return true;

      const rule = rules[fieldId];
      let isValid = true;

      if (rule.regex) {
        isValid = rule.regex.test(field.value.trim());
      } else if (rule.validate) {
        isValid = rule.validate(field);
      }

      if (!isValid) {
        field.classList.add("invalid");
        errEl.textContent = rule.message;
        return false;
      } else {
        field.classList.remove("invalid");
        errEl.textContent = "";
        return true;
      }
    }

    // Password Confirm validation
    function validateConfirmPassword() {
      const pwd = document.getElementById("password").value;
      const cpwd = document.getElementById("confirmPassword");
      const errEl = document.getElementById("confirmPasswordError");
      if (!cpwd || !errEl) return true;

      if (cpwd.value !== pwd || cpwd.value.trim() === "") {
        cpwd.classList.add("invalid");
        errEl.textContent = "Passwords do not match.";
        return false;
      } else {
        cpwd.classList.remove("invalid");
        errEl.textContent = "";
        return true;
      }
    }

    // Password Strength Meter
    const pwdInput = document.getElementById("password");
    const meterBar = document.getElementById("passwordMeterBar");
    const meterText = document.getElementById("passwordMeterText");

    if (pwdInput && meterBar && meterText) {
      pwdInput.addEventListener("input", function () {
        const val = this.value;
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        if (score === 0) {
          meterBar.style.width = "0%";
          meterText.textContent = "Password strength";
        } else if (score <= 2) {
          meterBar.style.width = "35%";
          meterBar.style.backgroundColor = "var(--danger)";
          meterText.textContent = "Weak password";
        } else if (score === 3) {
          meterBar.style.width = "70%";
          meterBar.style.backgroundColor = "var(--warning)";
          meterText.textContent = "Medium strength";
        } else {
          meterBar.style.width = "100%";
          meterBar.style.backgroundColor = "var(--success)";
          meterText.textContent = "Strong password";
        }
      });
    }

    // Attach real-time validation listeners
    ["fullName", "username", "email", "mobile", "password", "course", "year"].forEach(id => {
      const input = document.getElementById(id);
      if (input) {
        input.addEventListener("input", () => validateField(id));
        input.addEventListener("blur", () => validateField(id));
      }
    });

    const cpwdInput = document.getElementById("confirmPassword");
    if (cpwdInput) {
      cpwdInput.addEventListener("input", validateConfirmPassword);
      cpwdInput.addEventListener("blur", validateConfirmPassword);
    }

    regForm.addEventListener("submit", function (e) {
      let isFormValid = true;
      ["fullName", "username", "email", "mobile", "password", "course", "year", "terms"].forEach(id => {
        if (!validateField(id)) isFormValid = false;
      });

      if (!validateConfirmPassword()) isFormValid = false;

      if (!isFormValid) {
        e.preventDefault();
      }
    });
  }
});
