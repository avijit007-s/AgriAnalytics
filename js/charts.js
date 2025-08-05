// Charts JavaScript functionality for Agricultural Analysis System

// Chart color schemes
const chartColors = {
  primary: [
    "#3498db",
    "#e74c3c",
    "#2ecc71",
    "#f39c12",
    "#9b59b6",
    "#1abc9c",
    "#34495e",
    "#e67e22",
  ],
  secondary: [
    "rgba(52, 152, 219, 0.8)",
    "rgba(231, 76, 60, 0.8)",
    "rgba(46, 204, 113, 0.8)",
    "rgba(243, 156, 18, 0.8)",
    "rgba(155, 89, 182, 0.8)",
    "rgba(26, 188, 156, 0.8)",
    "rgba(52, 73, 94, 0.8)",
    "rgba(230, 126, 34, 0.8)",
  ],
  gradients: [
    "linear-gradient(135deg, #3498db 0%, #2980b9 100%)",
    "linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)",
    "linear-gradient(135deg, #2ecc71 0%, #27ae60 100%)",
    "linear-gradient(135deg, #f39c12 0%, #e67e22 100%)",
    "linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%)",
    "linear-gradient(135deg, #1abc9c 0%, #16a085 100%)",
  ],
};

// Default chart options
const defaultChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "bottom",
      labels: {
        usePointStyle: true,
        padding: 20,
        font: {
          size: 12,
        },
      },
    },
    tooltip: {
      backgroundColor: "rgba(0, 0, 0, 0.8)",
      titleColor: "#fff",
      bodyColor: "#fff",
      borderColor: "#3498db",
      borderWidth: 1,
      cornerRadius: 8,
      displayColors: true,
    },
  },
  animation: {
    duration: 1000,
    easing: "easeInOutQuart",
  },
};

// Initialize all charts when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  initializeCharts();
});

// Main function to initialize all charts
function initializeCharts() {
  // Set Chart.js global defaults
  Chart.defaults.font.family = "Segoe UI, Tahoma, Geneva, Verdana, sans-serif";
  Chart.defaults.color = "#666";

  // Initialize each chart type
  initializePieChart();
  initializeTrendChart();
  initializePriceProductionChart();
  initializeWeatherChart();
  initializeRegionalChart();
  initializeSupplyDemandChart();
}

// Initialize Production Distribution Pie Chart
function initializePieChart() {
  const canvas = document.getElementById("productionPieChart");
  if (!canvas) return;

  try {
    const ctx = canvas.getContext("2d");

    // Get data from PHP (this will be populated by the PHP script)
    if (typeof pieChartData !== "undefined" && pieChartData.labels.length > 0) {
      new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: pieChartData.labels,
          datasets: [
            {
              data: pieChartData.data,
              backgroundColor: chartColors.primary.slice(
                0,
                pieChartData.labels.length
              ),
              borderWidth: 3,
              borderColor: "#fff",
              hoverBorderWidth: 5,
              hoverBorderColor: "#fff",
            },
          ],
        },
        options: {
          ...defaultChartOptions,
          plugins: {
            ...defaultChartOptions.plugins,
            legend: {
              ...defaultChartOptions.plugins.legend,
              position: "right",
            },
            tooltip: {
              ...defaultChartOptions.plugins.tooltip,
              callbacks: {
                label: function (context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.parsed / total) * 100).toFixed(
                    1
                  );
                  return `${
                    context.label
                  }: ${context.parsed.toLocaleString()} tons (${percentage}%)`;
                },
              },
            },
          },
        },
      });
    } else {
      showChartError(canvas, "No production data available");
    }
  } catch (error) {
    console.error("Error creating pie chart:", error);
    showChartError(canvas, "Failed to load chart");
  }
}

// Initialize Production Trend Line Chart
function initializeTrendChart() {
  const canvas = document.getElementById("productionTrendChart");
  if (!canvas) return;

  try {
    const ctx = canvas.getContext("2d");

    if (
      typeof trendChartData !== "undefined" &&
      trendChartData.labels.length > 0
    ) {
      new Chart(ctx, {
        type: "line",
        data: {
          labels: trendChartData.labels,
          datasets: [
            {
              label: "Average Production (tons)",
              data: trendChartData.data,
              borderColor: "#2c3e50",
              backgroundColor: "rgba(44, 62, 80, 0.1)",
              borderWidth: 3,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: "#2c3e50",
              pointBorderColor: "#fff",
              pointBorderWidth: 2,
              pointRadius: 6,
              pointHoverRadius: 8,
            },
          ],
        },
        options: {
          ...defaultChartOptions,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
              ticks: {
                callback: function (value) {
                  return value.toLocaleString() + " tons";
                },
              },
            },
            x: {
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
            },
          },
        },
      });
    } else {
      showChartError(canvas, "No trend data available");
    }
  } catch (error) {
    console.error("Error creating trend chart:", error);
    showChartError(canvas, "Failed to load chart");
  }
}

// Initialize Price vs Production Chart
function initializePriceProductionChart() {
  const canvas = document.getElementById("priceProductionChart");
  if (!canvas) return;

  try {
    const ctx = canvas.getContext("2d");

    if (
      typeof priceProductionData !== "undefined" &&
      priceProductionData.labels.length > 0
    ) {
      new Chart(ctx, {
        type: "scatter",
        data: {
          datasets: [
            {
              label: "Price vs Production",
              data: priceProductionData.data,
              backgroundColor: "rgba(52, 152, 219, 0.6)",
              borderColor: "#3498db",
              borderWidth: 2,
              pointRadius: 8,
              pointHoverRadius: 10,
            },
          ],
        },
        options: {
          ...defaultChartOptions,
          scales: {
            x: {
              title: {
                display: true,
                text: "Production (tons)",
              },
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
            },
            y: {
              title: {
                display: true,
                text: "Price (BDT)",
              },
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
            },
          },
          plugins: {
            ...defaultChartOptions.plugins,
            tooltip: {
              ...defaultChartOptions.plugins.tooltip,
              callbacks: {
                label: function (context) {
                  return `Production: ${context.parsed.x.toLocaleString()} tons, Price: ৳${context.parsed.y.toLocaleString()}`;
                },
              },
            },
          },
        },
      });
    } else {
      showChartError(canvas, "No price-production data available");
    }
  } catch (error) {
    console.error("Error creating price-production chart:", error);
    showChartError(canvas, "Failed to load chart");
  }
}

// Initialize Weather Impact Chart
function initializeWeatherChart() {
  const canvas = document.getElementById("weatherImpactChart");
  if (!canvas) return;

  try {
    const ctx = canvas.getContext("2d");

    if (
      typeof weatherChartData !== "undefined" &&
      weatherChartData.labels.length > 0
    ) {
      new Chart(ctx, {
        type: "bar",
        data: {
          labels: weatherChartData.labels,
          datasets: [
            {
              label: "Rainfall (mm)",
              data: weatherChartData.rainfall,
              backgroundColor: "rgba(52, 152, 219, 0.8)",
              borderColor: "#3498db",
              borderWidth: 2,
              yAxisID: "y",
            },
            {
              label: "Temperature (°C)",
              data: weatherChartData.temperature,
              backgroundColor: "rgba(231, 76, 60, 0.8)",
              borderColor: "#e74c3c",
              borderWidth: 2,
              yAxisID: "y1",
            },
          ],
        },
        options: {
          ...defaultChartOptions,
          scales: {
            y: {
              type: "linear",
              display: true,
              position: "left",
              title: {
                display: true,
                text: "Rainfall (mm)",
              },
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
            },
            y1: {
              type: "linear",
              display: true,
              position: "right",
              title: {
                display: true,
                text: "Temperature (°C)",
              },
              grid: {
                drawOnChartArea: false,
              },
            },
            x: {
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
            },
          },
        },
      });
    } else {
      showChartError(canvas, "No weather data available");
    }
  } catch (error) {
    console.error("Error creating weather chart:", error);
    showChartError(canvas, "Failed to load chart");
  }
}

// Initialize Regional Production Chart
function initializeRegionalChart() {
  const canvas = document.getElementById("regionalChart");
  if (!canvas) return;

  try {
    const ctx = canvas.getContext("2d");

    if (
      typeof regionalChartData !== "undefined" &&
      regionalChartData.labels.length > 0
    ) {
      new Chart(ctx, {
        type: "bar",
        data: {
          labels: regionalChartData.labels,
          datasets: [
            {
              label: "Total Production (tons)",
              data: regionalChartData.data,
              backgroundColor: chartColors.secondary.slice(
                0,
                regionalChartData.labels.length
              ),
              borderColor: chartColors.primary.slice(
                0,
                regionalChartData.labels.length
              ),
              borderWidth: 2,
              borderRadius: 8,
              borderSkipped: false,
            },
          ],
        },
        options: {
          ...defaultChartOptions,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: "Production (tons)",
              },
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
              ticks: {
                callback: function (value) {
                  return value.toLocaleString();
                },
              },
            },
            x: {
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
            },
          },
          plugins: {
            ...defaultChartOptions.plugins,
            tooltip: {
              ...defaultChartOptions.plugins.tooltip,
              callbacks: {
                label: function (context) {
                  return `${
                    context.label
                  }: ${context.parsed.y.toLocaleString()} tons`;
                },
              },
            },
          },
        },
      });
    } else {
      showChartError(canvas, "No regional data available");
    }
  } catch (error) {
    console.error("Error creating regional chart:", error);
    showChartError(canvas, "Failed to load chart");
  }
}

// Initialize Supply vs Demand Chart
function initializeSupplyDemandChart() {
  const canvas = document.getElementById("supplyDemandLocationChart");
  if (!canvas) return;

  try {
    const ctx = canvas.getContext("2d");

    if (
      typeof supplyDemandData !== "undefined" &&
      supplyDemandData.labels.length > 0
    ) {
      new Chart(ctx, {
        type: "bar",
        data: {
          labels: supplyDemandData.labels,
          datasets: [
            {
              label: "Supply (tons)",
              data: supplyDemandData.supply,
              backgroundColor: "rgba(46, 204, 113, 0.8)",
              borderColor: "#2ecc71",
              borderWidth: 2,
            },
            {
              label: "Demand (tons)",
              data: supplyDemandData.demand,
              backgroundColor: "rgba(231, 76, 60, 0.8)",
              borderColor: "#e74c3c",
              borderWidth: 2,
            },
          ],
        },
        options: {
          ...defaultChartOptions,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: "Quantity (tons)",
              },
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
              ticks: {
                callback: function (value) {
                  return value.toLocaleString();
                },
              },
            },
            x: {
              grid: {
                color: "rgba(0, 0, 0, 0.1)",
              },
            },
          },
          plugins: {
            ...defaultChartOptions.plugins,
            tooltip: {
              ...defaultChartOptions.plugins.tooltip,
              callbacks: {
                label: function (context) {
                  return `${
                    context.dataset.label
                  }: ${context.parsed.y.toLocaleString()} tons`;
                },
              },
            },
          },
        },
      });
    } else {
      showChartError(canvas, "No supply-demand data available");
    }
  } catch (error) {
    console.error("Error creating supply-demand chart:", error);
    showChartError(canvas, "Failed to load chart");
  }
}

// Show error message in chart container
function showChartError(canvas, message) {
  const container = canvas.parentElement;
  container.innerHTML = `
        <div class="chart-error">
            <i class="fas fa-exclamation-triangle"></i>
            <p>${message}</p>
        </div>
    `;
}

// Show loading state in chart container
function showChartLoading(canvas) {
  const container = canvas.parentElement;
  container.innerHTML = `
        <div class="chart-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading chart...</span>
        </div>
    `;
}

// Apply filters function
function applyFilters() {
  const year = document.getElementById("year").value;
  const product = document.getElementById("product").value;

  let url = "charts.php?year=" + year;
  if (product) url += "&product=" + product;

  window.location.href = url;
}

// Export chart as image
function exportChart(chartId, filename) {
  const canvas = document.getElementById(chartId);
  if (canvas) {
    const link = document.createElement("a");
    link.download = filename + ".png";
    link.href = canvas.toDataURL();
    link.click();
  }
}

// Print charts
function printCharts() {
  window.print();
}

// Refresh charts data
function refreshCharts() {
  location.reload();
}
