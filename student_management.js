/**
 * StudentHub Portal - Asynchronous Fetch Client for Student Management (Practical 13)
 * Developed by Malkesh Patel (25DCS089)
 */

document.addEventListener("DOMContentLoaded", function () {
  const apiStatus = document.getElementById("apiStatus");
  const studentListBody = document.getElementById("studentListBody");
  const searchInput = document.getElementById("apiSearchInput");

  function showApiStatus(msg, isSuccess = true) {
    if (apiStatus) {
      apiStatus.textContent = msg;
      apiStatus.style.display = msg ? "block" : "none";
      apiStatus.style.background = isSuccess ? "#dcfce7" : "#fee2e2";
      apiStatus.style.color = isSuccess ? "#166534" : "#991b1b";
    }
  }

  async function fetchStudents(search = "") {
    try {
      showApiStatus("Loading records from server...", true);
      const url = search ? `students_api.php?search=${encodeURIComponent(search)}` : "students_api.php";
      const res = await fetch(url);
      const json = await res.json();

      if (!res.ok) throw new Error(json.error || "Failed to load");

      renderTable(json.data || []);
      showApiStatus("", true);
    } catch (err) {
      showApiStatus("API Error: " + err.message, false);
    }
  }

  function renderTable(students) {
    if (!studentListBody) return;
    if (students.length === 0) {
      studentListBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No student records found.</td></tr>';
      return;
    }

    studentListBody.innerHTML = students.map(s => `
      <tr>
        <td>#${s.student_id}</td>
        <td><strong>${escapeHtml(s.full_name)}</strong></td>
        <td>${escapeHtml(s.email)}</td>
        <td>${escapeHtml(s.mobile)}</td>
        <td><span class="badge badge-primary">${escapeHtml(s.course)}</span></td>
        <td>
          <button class="btn btn-danger" style="padding: 0.35rem 0.65rem; font-size: 0.8rem;" onclick="deleteStudentAsync(${s.student_id})">Delete</button>
        </td>
      </tr>
    `).join("");
  }

  function escapeHtml(str) {
    return String(str || "").replace(/[&<>'"]/g, tag => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    }[tag]));
  }

  window.deleteStudentAsync = async function(id) {
    if (!confirm(`Are you sure you want to delete student #${id}?`)) return;
    try {
      showApiStatus("Deleting record...", true);
      const res = await fetch("students_api.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ student_id: id })
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.error || "Delete failed");
      showApiStatus(`Student #${id} deleted successfully.`, true);
      fetchStudents(searchInput ? searchInput.value : "");
    } catch (err) {
      showApiStatus(err.message, false);
    }
  };

  if (searchInput) {
    let timeout;
    searchInput.addEventListener("input", function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => fetchStudents(this.value.trim()), 300);
    });
  }

  if (studentListBody) {
    fetchStudents();
  }
});
