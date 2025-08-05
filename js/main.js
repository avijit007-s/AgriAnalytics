// Main JavaScript file for Agricultural Analysis System

// Set active navigation link
document.addEventListener("DOMContentLoaded", function () {
  const currentPage = window.location.pathname.split("/").pop();
  const navLinks = document.querySelectorAll(".nav-link");

  navLinks.forEach((link) => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active");
    }
  });

  // Add fade-in animation to content
  const contentArea = document.querySelector(".content-area");
  if (contentArea) {
    contentArea.classList.add("fade-in");
  }
});

// Confirm delete actions
function confirmDelete(message = "Are you sure you want to delete this item?") {
  return confirm(message);
}

// Show loading state
function showLoading(element) {
  element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
  element.disabled = true;
}

// Hide loading state
function hideLoading(element, originalText) {
  element.innerHTML = originalText;
  element.disabled = false;
}

// Format numbers with commas
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Create chart with Chart.js
function createChart(canvasId, type, data, options = {}) {
  const ctx = document.getElementById(canvasId).getContext("2d");
  return new Chart(ctx, {
    type: type,
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      ...options,
    },
  });
}

// Export table to CSV
function exportTableToCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  const rows = table.querySelectorAll("tr");
  let csv = [];

  for (let i = 0; i < rows.length; i++) {
    const row = [];
    const cols = rows[i].querySelectorAll("td, th");

    for (let j = 0; j < cols.length; j++) {
      row.push(cols[j].innerText);
    }

    csv.push(row.join(","));
  }

  downloadCSV(csv.join("\n"), filename);
}

// Download CSV file
function downloadCSV(csv, filename) {
  const csvFile = new Blob([csv], { type: "text/csv" });
  const downloadLink = document.createElement("a");
  downloadLink.download = filename;
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
}

// Mobile sidebar toggle
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar");
  sidebar.classList.toggle("active");
}

// Search functionality
function searchTable(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  const rows = table.getElementsByTagName("tr");

  input.addEventListener("keyup", function () {
    const filter = input.value.toLowerCase();

    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      const cells = row.getElementsByTagName("td");
      let found = false;

      for (let j = 0; j < cells.length; j++) {
        if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
          found = true;
          break;
        }
      }

      row.style.display = found ? "" : "none";
    }
  });
}
