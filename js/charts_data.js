// Charts Data and Initialization for Advanced Analytics
document.addEventListener("DOMContentLoaded", function () {
  // Initialize all charts when page loads
  initializeAdvancedCharts();
});

function initializeAdvancedCharts() {
  // Check if we have chart containers
  const pieChartCanvas = document.getElementById("productionPieChart");
  const trendChartCanvas = document.getElementById("productionTrendChart");
  const priceProductionCanvas = document.getElementById("priceProductionChart");
  const weatherImpactCanvas = document.getElementById("weatherImpactChart");
  const regionalCanvas = document.getElementById("regionalChart");
  const supplyDemandCanvas = document.getElementById(
    "supplyDemandLocationChart"
  );

  // Sample data for charts (this will be replaced by PHP data when available)
  const sampleProductionData = {
    labels: ["Rice", "Wheat", "Potato", "Jute", "Sugarcane"],
    data: [4500, 2400, 12000, 800, 15000],
  };

  const sampleTrendData = {
    labels: ["2020", "2021", "2022", "2023"],
    data: [32000, 35000, 38000, 34300],
  };

  const sampleRegionalData = {
    labels: ["Dhaka", "Chittagong", "Rajshahi", "Khulna", "Sylhet"],
    data: [8500, 7200, 6800, 5900, 5900],
  };

  const samplePriceProductionData = [
    { x: 4500, y: 45 },
    { x: 2400, y: 35 },
    { x: 12000, y: 25 },
    { x: 800, y: 55 },
    { x: 15000, y: 40 },
  ];

  const sampleWeatherData = {
    labels: ["Low Rainfall", "Medium Rainfall", "High Rainfall"],
    production: [25000, 35000, 30000],
    rainfall: [50, 150, 250],
  };

  // Production Distribution Pie Chart
  if (pieChartCanvas) {
    const pieCtx = pieChartCanvas.getContext("2d");
    new Chart(pieCtx, {
      type: "pie",
      data: {
        labels: sampleProductionData.labels,
        datasets: [
          {
            data: sampleProductionData.data,
            backgroundColor: [
              "#FF6384",
              "#36A2EB",
              "#FFCE56",
              "#4BC0C0",
              "#9966FF",
            ],
            borderWidth: 2,
            borderColor: "#fff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 20,
              usePointStyle: true,
            },
          },
          title: {
            display: true,
            text: "Production Distribution by Product (2023)",
            font: {
              size: 16,
              weight: "bold",
            },
          },
        },
      },
    });
  }

  // Production Trend Line Chart
  if (trendChartCanvas) {
    const trendCtx = trendChartCanvas.getContext("2d");
    new Chart(trendCtx, {
      type: "line",
      data: {
        labels: sampleTrendData.labels,
        datasets: [
          {
            label: "Total Production (tons)",
            data: sampleTrendData.data,
            borderColor: "#36A2EB",
            backgroundColor: "rgba(54, 162, 235, 0.1)",
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: "#36A2EB",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Production (tons)",
              font: {
                size: 14,
                weight: "bold",
              },
            },
            grid: {
              color: "rgba(0,0,0,0.1)",
            },
          },
          x: {
            title: {
              display: true,
              text: "Year",
              font: {
                size: 14,
                weight: "bold",
              },
            },
            grid: {
              color: "rgba(0,0,0,0.1)",
            },
          },
        },
        plugins: {
          title: {
            display: true,
            text: "Production Trend Over Years",
            font: {
              size: 16,
              weight: "bold",
            },
          },
          legend: {
            display: false,
          },
        },
      },
    });
  }

  // Price vs Production Scatter Chart
  if (priceProductionCanvas) {
    const priceCtx = priceProductionCanvas.getContext("2d");
    new Chart(priceCtx, {
      type: "scatter",
      data: {
        datasets: [
          {
            label: "Price vs Production",
            data: samplePriceProductionData,
            backgroundColor: "#FF6384",
            borderColor: "#FF6384",
            pointRadius: 8,
            pointHoverRadius: 10,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            title: {
              display: true,
              text: "Production (tons)",
              font: {
                size: 14,
                weight: "bold",
              },
            },
            grid: {
              color: "rgba(0,0,0,0.1)",
            },
          },
          y: {
            title: {
              display: true,
              text: "Price (৳)",
              font: {
                size: 14,
                weight: "bold",
              },
            },
            grid: {
              color: "rgba(0,0,0,0.1)",
            },
          },
        },
        plugins: {
          title: {
            display: true,
            text: "Price vs Production Analysis",
            font: {
              size: 16,
              weight: "bold",
            },
          },
          legend: {
            display: false,
          },
        },
      },
    });
  }

  // Weather Impact Chart
  if (weatherImpactCanvas) {
    const weatherCtx = weatherImpactCanvas.getContext("2d");
    new Chart(weatherCtx, {
      type: "bar",
      data: {
        labels: sampleWeatherData.labels,
        datasets: [
          {
            label: "Production (tons)",
            data: sampleWeatherData.production,
            backgroundColor: "#4BC0C0",
            borderColor: "#4BC0C0",
            borderWidth: 1,
            yAxisID: "y",
          },
          {
            label: "Rainfall (mm)",
            data: sampleWeatherData.rainfall,
            type: "line",
            borderColor: "#FFCE56",
            backgroundColor: "rgba(255, 206, 86, 0.2)",
            borderWidth: 3,
            fill: false,
            yAxisID: "y1",
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            type: "linear",
            display: true,
            position: "left",
            title: {
              display: true,
              text: "Production (tons)",
              font: {
                size: 14,
                weight: "bold",
              },
            },
          },
          y1: {
            type: "linear",
            display: true,
            position: "right",
            title: {
              display: true,
              text: "Rainfall (mm)",
              font: {
                size: 14,
                weight: "bold",
              },
            },
            grid: {
              drawOnChartArea: false,
            },
          },
        },
        plugins: {
          title: {
            display: true,
            text: "Weather Impact on Production",
            font: {
              size: 16,
              weight: "bold",
            },
          },
        },
      },
    });
  }

  // Regional Production Chart
  if (regionalCanvas) {
    const regionalCtx = regionalCanvas.getContext("2d");
    new Chart(regionalCtx, {
      type: "doughnut",
      data: {
        labels: sampleRegionalData.labels,
        datasets: [
          {
            data: sampleRegionalData.data,
            backgroundColor: [
              "#FF6384",
              "#36A2EB",
              "#FFCE56",
              "#4BC0C0",
              "#9966FF",
            ],
            borderWidth: 3,
            borderColor: "#fff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 20,
              usePointStyle: true,
            },
          },
          title: {
            display: true,
            text: "Regional Production Distribution",
            font: {
              size: 16,
              weight: "bold",
            },
          },
        },
      },
    });
  }

  // Supply vs Demand Chart
  if (supplyDemandCanvas) {
    const supplyDemandCtx = supplyDemandCanvas.getContext("2d");
    new Chart(supplyDemandCtx, {
      type: "bar",
      data: {
        labels: sampleRegionalData.labels,
        datasets: [
          {
            label: "Supply (tons)",
            data: sampleRegionalData.data,
            backgroundColor: "#36A2EB",
            borderColor: "#36A2EB",
            borderWidth: 1,
          },
          {
            label: "Demand (tons)",
            data: sampleRegionalData.data.map((val) => val * 0.85),
            backgroundColor: "#FF6384",
            borderColor: "#FF6384",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Quantity (tons)",
              font: {
                size: 14,
                weight: "bold",
              },
            },
          },
        },
        plugins: {
          title: {
            display: true,
            text: "Supply vs Demand Analysis by Location",
            font: {
              size: 16,
              weight: "bold",
            },
          },
        },
      },
    });
  }
}

// Function to update charts with new data (can be called when filters change)
function updateChartsWithData(data) {
  // This function can be used to update charts when new data is available
  console.log("Updating charts with new data:", data);
  // Implementation for updating charts dynamically
}
