// Main Application JavaScript
class AgriApp {
  constructor() {
    this.currentUser = null;
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.loadInitialData();
    this.showWelcomeSection();
  }

  setupEventListeners() {
    // Modal close events
    window.onclick = (event) => {
      const modal = document.getElementById("modal");
      const dataModal = document.getElementById("data-modal");
      if (event.target === modal) {
        this.closeModal();
      }
      if (event.target === dataModal) {
        this.closeDataModal();
      }
    };

    // Escape key to close modals
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        this.closeModal();
        this.closeDataModal();
      }
    });
  }

  loadInitialData() {
    // Initialize sample data if not exists
    if (!localStorage.getItem("agri_data_initialized")) {
      this.initializeSampleData();
      localStorage.setItem("agri_data_initialized", "true");
    }
  }

  initializeSampleData() {
    // Sample data for demonstration
    const sampleData = {
      farmer_crops: [
        {
          id: 1,
          crop_name: "Rice",
          variety: "BRRI dhan29",
          sowing_date: "2024-01-15",
          harvest_date: "2024-05-15",
          area: "5 acres",
          fertilizer: "Urea, TSP, MOP",
        },
        {
          id: 2,
          crop_name: "Wheat",
          variety: "BARI Gom-26",
          sowing_date: "2024-02-01",
          harvest_date: "2024-06-01",
          area: "3 acres",
          fertilizer: "NPK, Urea",
        },
      ],
      farmer_production: [
        {
          id: 1,
          crop: "Rice",
          production: "25 tons",
          date: "2024-05-15",
          quality: "Grade A",
          price: "25000 BDT/ton",
        },
      ],
      retailer_inventory: [
        {
          id: 1,
          product: "Rice",
          quantity: "500 kg",
          price: "50 BDT/kg",
          supplier: "Local Farmer",
          date: "2024-07-20",
        },
      ],
      weather_data: [
        {
          id: 1,
          date: "2024-07-29",
          temperature: "32°C",
          humidity: "75%",
          rainfall: "5mm",
          location: "Dhaka",
        },
      ],
    };

    // Store sample data
    Object.keys(sampleData).forEach((key) => {
      localStorage.setItem(key, JSON.stringify(sampleData[key]));
    });
  }

  showWelcomeSection() {
    // Hide all user sections
    const userSections = document.querySelectorAll(".user-section");
    userSections.forEach((section) => {
      section.style.display = "none";
    });

    // Show welcome section
    const welcomeSection = document.getElementById("welcome-section");
    if (welcomeSection) {
      welcomeSection.style.display = "block";
    }
  }

  switchUserRole() {
    const roleSelect = document.getElementById("userRole");
    const selectedRole = roleSelect.value;

    // Hide all sections first
    const allSections = document.querySelectorAll(
      ".user-section, .welcome-section"
    );
    allSections.forEach((section) => {
      section.style.display = "none";
    });

    if (selectedRole) {
      // Show selected role section
      const targetSection = document.getElementById(`${selectedRole}-section`);
      if (targetSection) {
        targetSection.style.display = "block";
        this.currentUser = selectedRole;
        this.loadUserDashboard(selectedRole);
      }
    } else {
      // Show welcome section if no role selected
      this.showWelcomeSection();
      this.currentUser = null;
    }
  }

  loadUserDashboard(role) {
    // Load role-specific data and charts
    switch (role) {
      case "farmer":
        this.loadFarmerDashboard();
        break;
      case "consumer":
        this.loadConsumerDashboard();
        break;
      case "retailer":
        this.loadRetailerDashboard();
        break;
      case "supplier":
        this.loadSupplierDashboard();
        break;
      case "wholesaler":
        this.loadWholesalerDashboard();
        break;
      case "distributor":
        this.loadDistributorDashboard();
        break;
      case "demand-analyzer":
        this.loadDemandAnalyzerDashboard();
        break;
      case "bmd":
        this.loadBMDDashboard();
        break;
    }
  }

  loadFarmerDashboard() {
    // Load farmer-specific charts and data
    setTimeout(() => {
      if (window.chartManager) {
        window.chartManager.createFarmerProductionChart();
      }
    }, 100);
  }

  loadConsumerDashboard() {
    setTimeout(() => {
      if (window.chartManager) {
        window.chartManager.createConsumerPriceChart();
      }
    }, 100);
  }

  loadRetailerDashboard() {
    setTimeout(() => {
      if (window.chartManager) {
        window.chartManager.createRetailerSalesChart();
      }
    }, 100);
  }

  loadSupplierDashboard() {
    setTimeout(() => {
      if (window.chartManager) {
        window.chartManager.createSupplierProductionChart();
      }
    }, 100);
  }

  loadWholesalerDashboard() {
    setTimeout(() => {
      if (window.chartManager) {
        window.chartManager.createWholesalerPriceChart();
      }
    }, 100);
  }

  loadDistributorDashboard() {
    setTimeout(() => {
      if (window.chartManager) {
        window.chartManager.createDistributorFlowChart();
      }
    }, 100);
  }

  loadDemandAnalyzerDashboard() {
    setTimeout(() => {
      if (window.chartManager) {
        window.chartManager.createDemandAnalysisChart();
      }
    }, 100);
  }

  loadBMDDashboard() {
    setTimeout(() => {
      if (window.chartManager) {
        window.chartManager.createWeatherChart();
      }
    }, 100);
  }

  showModal(formType) {
    const modal = document.getElementById("modal");
    const modalBody = document.getElementById("modal-body");

    if (window.formManager) {
      const formHTML = window.formManager.getForm(formType);
      modalBody.innerHTML = formHTML;
      modal.style.display = "block";
    }
  }

  closeModal() {
    const modal = document.getElementById("modal");
    modal.style.display = "none";
  }

  showDataTable(tableType) {
    const dataModal = document.getElementById("data-modal");
    const dataModalBody = document.getElementById("data-modal-body");

    if (window.dataManager) {
      const tableHTML = window.dataManager.getTable(tableType);
      dataModalBody.innerHTML = tableHTML;
      dataModal.style.display = "block";
    }
  }

  closeDataModal() {
    const dataModal = document.getElementById("data-modal");
    dataModal.style.display = "none";
  }

  showMessage(message, type = "info") {
    // Create message element
    const messageDiv = document.createElement("div");
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;

    // Add to current section
    const currentSection = document.querySelector(
      '.user-section[style*="block"], .welcome-section[style*="block"]'
    );
    if (currentSection) {
      const container = currentSection.querySelector(".container");
      container.insertBefore(messageDiv, container.firstChild);

      // Auto remove after 5 seconds
      setTimeout(() => {
        if (messageDiv.parentNode) {
          messageDiv.parentNode.removeChild(messageDiv);
        }
      }, 5000);
    }
  }

  // Utility functions for other components
  showWeatherData() {
    this.showMessage("Weather data loaded successfully", "success");
    // In a real app, this would fetch actual weather data
  }

  showMarketTrends() {
    this.showMessage("Market trends data loaded", "info");
    // In a real app, this would show market trend analysis
  }

  showPriceComparison() {
    this.showMessage("Price comparison data loaded", "info");
  }

  showRegionalAnalysis() {
    this.showMessage("Regional analysis data loaded", "info");
  }

  showPriceTrends() {
    this.showMessage("Price trends data loaded", "info");
  }

  showTrendAnalysis() {
    this.showMessage("Trend analysis completed", "success");
  }

  showForecastData() {
    this.showMessage("Forecast data loaded", "info");
  }

  generateReport() {
    this.showMessage("Report generated successfully", "success");
  }

  showRegionalWeather() {
    this.showMessage("Regional weather data loaded", "info");
  }

  showWeatherAlerts() {
    this.showMessage("Weather alerts checked", "info");
  }
}

// Global functions for HTML onclick events
function switchUserRole() {
  if (window.agriApp) {
    window.agriApp.switchUserRole();
  }
}

function showModal(formType) {
  if (window.agriApp) {
    window.agriApp.showModal(formType);
  }
}

function closeModal() {
  if (window.agriApp) {
    window.agriApp.closeModal();
  }
}

function showDataTable(tableType) {
  if (window.agriApp) {
    window.agriApp.showDataTable(tableType);
  }
}

function closeDataModal() {
  if (window.agriApp) {
    window.agriApp.closeDataModal();
  }
}

function showWeatherData() {
  if (window.agriApp) {
    window.agriApp.showWeatherData();
  }
}

function showMarketTrends() {
  if (window.agriApp) {
    window.agriApp.showMarketTrends();
  }
}

function showPriceComparison() {
  if (window.agriApp) {
    window.agriApp.showPriceComparison();
  }
}

function showRegionalAnalysis() {
  if (window.agriApp) {
    window.agriApp.showRegionalAnalysis();
  }
}

function showPriceTrends() {
  if (window.agriApp) {
    window.agriApp.showPriceTrends();
  }
}

function showTrendAnalysis() {
  if (window.agriApp) {
    window.agriApp.showTrendAnalysis();
  }
}

function showForecastData() {
  if (window.agriApp) {
    window.agriApp.showForecastData();
  }
}

function generateReport() {
  if (window.agriApp) {
    window.agriApp.generateReport();
  }
}

function showRegionalWeather() {
  if (window.agriApp) {
    window.agriApp.showRegionalWeather();
  }
}

function showWeatherAlerts() {
  if (window.agriApp) {
    window.agriApp.showWeatherAlerts();
  }
}

// Initialize app when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  window.agriApp = new AgriApp();
});
