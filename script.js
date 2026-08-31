/**
 * StudentHub Portal - Complete Client-Side Interactivity, Validation & JSON Fetch (Practical 6)
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

  // Dynamic Event Rendering with Fetch API (Practical 6)
  const eventsContainer = document.getElementById("eventsContainer");
  const searchInput = document.getElementById("eventSearchInput");
  const categoryFilter = document.getElementById("eventCategoryFilter");
  const paginationContainer = document.getElementById("eventsPagination");

  if (eventsContainer) {
    let allEvents = [];
    let currentPage = 1;
    const itemsPerPage = 6;

    async function loadEventsData() {
      try {
        const response = await fetch("data/events.json");
        if (!response.ok) throw new Error("Network response was not ok");
        allEvents = await response.json();
        renderFilteredEvents();
      } catch (err) {
        console.warn("Could not load events.json, falling back to static markup:", err);
      }
    }

    function renderFilteredEvents() {
      const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : "";
      const selectedCategory = categoryFilter ? categoryFilter.value : "all";

      const filtered = allEvents.filter(ev => {
        const matchesCategory = selectedCategory === "all" || ev.category.toLowerCase() === selectedCategory.toLowerCase();
        const matchesSearch = ev.title.toLowerCase().includes(searchTerm) || ev.description.toLowerCase().includes(searchTerm);
        return matchesCategory && matchesSearch;
      });

      const totalPages = Math.ceil(filtered.length / itemsPerPage) || 1;
      if (currentPage > totalPages) currentPage = 1;

      const startIndex = (currentPage - 1) * itemsPerPage;
      const pageItems = filtered.slice(startIndex, startIndex + itemsPerPage);

      if (pageItems.length === 0) {
        eventsContainer.innerHTML = '<p class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 2rem;">No matching events found.</p>';
      } else {
        eventsContainer.innerHTML = pageItems.map(ev => `
          <article class="card">
            <span class="badge badge-primary">${ev.category}</span>
            <h3 style="margin-top: 0.5rem;">${ev.title}</h3>
            <p>${ev.description}</p>
            <p><strong>Date:</strong> ${ev.date} • <strong>Venue:</strong> ${ev.location}</p>
            <button class="btn btn-primary" onclick="alert('Successfully registered for: ${ev.title.replace(/'/g, "\\'")}')">Register for Event</button>
          </article>
        `).join("");
      }

      // Render Pagination
      if (paginationContainer) {
        let pagHtml = "";
        for (let i = 1; i <= totalPages; i++) {
          pagHtml += `<button class="btn ${i === currentPage ? 'btn-primary' : 'btn-secondary'}" style="padding: 0.4rem 0.8rem;" onclick="goToPage(${i})">${i}</button>`;
        }
        paginationContainer.innerHTML = totalPages > 1 ? pagHtml : "";
      }
    }

    window.goToPage = function(page) {
      currentPage = page;
      renderFilteredEvents();
    };

    if (searchInput) {
      let debounceTimeout;
      searchInput.addEventListener("input", function () {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
          currentPage = 1;
          renderFilteredEvents();
        }, 250);
      });
    }

    if (categoryFilter) {
      categoryFilter.addEventListener("change", function () {
        currentPage = 1;
        renderFilteredEvents();
      });
    }

    loadEventsData();
  }

  // Registration Validation
  const regForm = document.getElementById("studentRegistrationForm");
  if (regForm) {
    const rules = {
      fullName: { regex: /^[A-Za-z][A-Za-z .'-]{2,39}$/, message: "Full name must be 3-40 letters." },
      username: { regex: /^[a-z][a-z0-9_]{2,29}$/, message: "Username must be 3-30 chars, starting with letter." },
      email: { regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: "Enter a valid email address." },
      mobile: { regex: /^[6-9]\d{9}$/, message: "10-digit mobile starting with 6-9." },
      password: { regex: /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/, message: "Min 8 chars, 1 letter, 1 number, 1 special character." }
    };

    function validate(id) {
      const el = document.getElementById(id);
      const err = document.getElementById(id + "Error");
      if (!el || !err) return true;
      const isValid = rules[id].regex.test(el.value.trim());
      if (!isValid) {
        el.classList.add("invalid");
        err.textContent = rules[id].message;
        return false;
      }
      el.classList.remove("invalid");
      err.textContent = "";
      return true;
    }

    ["fullName", "username", "email", "mobile", "password"].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener("input", () => validate(id));
    });

    regForm.addEventListener("submit", function (e) {
      let valid = true;
      ["fullName", "username", "email", "mobile", "password"].forEach(id => {
        if (!validate(id)) valid = false;
      });
      if (!valid) e.preventDefault();
    });
  }
});
