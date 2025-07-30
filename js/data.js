// Data Management System
class DataManager {
  constructor() {
    this.storagePrefix = "agri_";
  }

  // Generic CRUD operations
  create(tableName, data) {
    const items = this.getAll(tableName);
    const newId =
      items.length > 0 ? Math.max(...items.map((item) => item.id)) + 1 : 1;
    const newItem = {
      id: newId,
      ...data,
      created_at: new Date().toISOString(),
    };
    items.push(newItem);
    this.saveAll(tableName, items);

    // Refresh charts when data is updated
    this.refreshCharts();

    return newItem;
  }

  update(tableName, id, data) {
    const items = this.getAll(tableName);
    const index = items.findIndex((item) => item.id === parseInt(id));
    if (index !== -1) {
      items[index] = {
        ...items[index],
        ...data,
        updated_at: new Date().toISOString(),
      };
      this.saveAll(tableName, items);

      // Refresh charts when data is updated
      this.refreshCharts();

      return items[index];
    }
    return null;
  }

  delete(tableName, id) {
    const items = this.getAll(tableName);
    const filteredItems = items.filter((item) => item.id !== parseInt(id));
    this.saveAll(tableName, filteredItems);

    // Refresh charts when data is updated
    this.refreshCharts();

    return filteredItems.length < items.length;
  }

  // Refresh charts based on current user role
  refreshCharts() {
    if (window.agriApp && window.agriApp.currentUser && window.chartManager) {
      setTimeout(() => {
        window.agriApp.loadUserDashboard(window.agriApp.currentUser);
      }, 100);
    }
  }

  getAll(tableName) {
    const data = localStorage.getItem(this.storagePrefix + tableName);
    return data ? JSON.parse(data) : [];
  }

  getById(tableName, id) {
    const items = this.getAll(tableName);
    return items.find((item) => item.id === parseInt(id));
  }

  saveAll(tableName, data) {
    localStorage.setItem(this.storagePrefix + tableName, JSON.stringify(data));
  }

  search(tableName, searchTerm, fields = []) {
    const items = this.getAll(tableName);
    if (!searchTerm) return items;

    return items.filter((item) => {
      if (fields.length === 0) {
        // Search all fields
        return Object.values(item).some((value) =>
          value.toString().toLowerCase().includes(searchTerm.toLowerCase())
        );
      } else {
        // Search specific fields
        return fields.some(
          (field) =>
            item[field] &&
            item[field]
              .toString()
              .toLowerCase()
              .includes(searchTerm.toLowerCase())
        );
      }
    });
  }

  filter(tableName, filterField, filterValue) {
    const items = this.getAll(tableName);
    if (!filterValue) return items;

    return items.filter(
      (item) =>
        item[filterField] &&
        item[filterField].toString().toLowerCase() === filterValue.toLowerCase()
    );
  }

  // Generate table HTML
  getTable(tableType) {
    const data = this.getAll(tableType);
    const tableConfig = this.getTableConfig(tableType);

    if (!tableConfig) {
      return "<p>Table configuration not found.</p>";
    }

    let html = `
            <div class="table-header">
                <h3>${tableConfig.title}</h3>
                <div class="table-controls">
                    <div class="search-box">
                        <input type="text" placeholder="Search..." onkeyup="filterTable('${tableType}', this.value)">
                    </div>
                    <div class="filter-controls">
                        <button class="btn btn-primary" onclick="showModal('${tableConfig.addForm}')">
                            <i class="fas fa-plus"></i> Add New
                        </button>
                        <button class="btn btn-secondary" onclick="exportData('${tableType}')">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table" id="table-${tableType}">
                    <thead>
                        <tr>
        `;

    // Add headers
    tableConfig.columns.forEach((column) => {
      html += `<th>${column.label}</th>`;
    });
    html += "<th>Actions</th></tr></thead><tbody>";

    // Add data rows
    data.forEach((item) => {
      html += "<tr>";
      tableConfig.columns.forEach((column) => {
        const value = item[column.field] || "-";
        html += `<td>${value}</td>`;
      });
      html += `
                <td class="actions">
                    <button class="btn-edit" onclick="editItem('${tableType}', ${item.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-delete" onclick="deleteItem('${tableType}', ${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
    });

    html += "</tbody></table></div>";

    if (data.length === 0) {
      html +=
        '<div class="text-center p-3"><p>No data available. Click "Add New" to get started.</p></div>';
    }

    return html;
  }

  getTableConfig(tableType) {
    const configs = {
      "farmer-crops": {
        title: "Crop Management",
        addForm: "crop-form",
        columns: [
          { field: "crop_name", label: "Crop Name" },
          { field: "variety", label: "Variety" },
          { field: "sowing_date", label: "Sowing Date" },
          { field: "harvest_date", label: "Harvest Date" },
          { field: "area", label: "Area" },
          { field: "fertilizer", label: "Fertilizer Used" },
        ],
      },
      "farmer-production": {
        title: "Production Records",
        addForm: "production-form",
        columns: [
          { field: "crop", label: "Crop" },
          { field: "production", label: "Production" },
          { field: "date", label: "Date" },
          { field: "quality", label: "Quality" },
          { field: "price", label: "Price" },
        ],
      },
      "available-products": {
        title: "Available Products",
        addForm: "product-form",
        columns: [
          { field: "product_name", label: "Product" },
          { field: "price", label: "Price" },
          { field: "quantity", label: "Available Quantity" },
          { field: "supplier", label: "Supplier" },
          { field: "location", label: "Location" },
        ],
      },
      "consumer-orders": {
        title: "My Orders",
        addForm: "order-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "quantity", label: "Quantity" },
          { field: "price", label: "Total Price" },
          { field: "order_date", label: "Order Date" },
          { field: "status", label: "Status" },
        ],
      },
      "retailer-inventory": {
        title: "Inventory Management",
        addForm: "inventory-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "quantity", label: "Quantity" },
          { field: "price", label: "Price" },
          { field: "supplier", label: "Supplier" },
          { field: "date", label: "Date Added" },
        ],
      },
      "retailer-sales": {
        title: "Sales Records",
        addForm: "sales-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "quantity", label: "Quantity Sold" },
          { field: "price", label: "Sale Price" },
          { field: "customer", label: "Customer" },
          { field: "date", label: "Sale Date" },
        ],
      },
      "retailer-customers": {
        title: "Customer Management",
        addForm: "customer-form",
        columns: [
          { field: "name", label: "Customer Name" },
          { field: "phone", label: "Phone" },
          { field: "email", label: "Email" },
          { field: "address", label: "Address" },
          { field: "total_purchases", label: "Total Purchases" },
        ],
      },
      "supplier-supplies": {
        title: "Supply Records",
        addForm: "supply-form",
        columns: [
          { field: "item", label: "Supply Item" },
          { field: "quantity", label: "Quantity" },
          { field: "farmer", label: "Farmer" },
          { field: "date", label: "Supply Date" },
          { field: "cost", label: "Cost" },
        ],
      },
      "supplier-support": {
        title: "Farmer Support",
        addForm: "support-form",
        columns: [
          { field: "farmer_name", label: "Farmer" },
          { field: "support_type", label: "Support Type" },
          { field: "amount", label: "Amount" },
          { field: "date", label: "Date" },
          { field: "status", label: "Status" },
        ],
      },
      "supplier-production": {
        title: "Production Monitoring",
        addForm: "production-monitor-form",
        columns: [
          { field: "farmer", label: "Farmer" },
          { field: "crop", label: "Crop" },
          { field: "expected_yield", label: "Expected Yield" },
          { field: "actual_yield", label: "Actual Yield" },
          { field: "district", label: "District" },
        ],
      },
      "wholesaler-purchases": {
        title: "Purchase Records",
        addForm: "purchase-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "quantity", label: "Quantity" },
          { field: "supplier", label: "Supplier" },
          { field: "purchase_price", label: "Purchase Price" },
          { field: "date", label: "Purchase Date" },
        ],
      },
      "wholesaler-distribution": {
        title: "Distribution Records",
        addForm: "distribution-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "quantity", label: "Quantity" },
          { field: "retailer", label: "Retailer" },
          { field: "sale_price", label: "Sale Price" },
          { field: "date", label: "Distribution Date" },
        ],
      },
      "wholesale-prices": {
        title: "Price History",
        addForm: "price-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "price", label: "Price" },
          { field: "date", label: "Date" },
          { field: "market", label: "Market" },
          { field: "trend", label: "Trend" },
        ],
      },
      "distributor-purchases": {
        title: "Purchases from Suppliers",
        addForm: "distributor-purchase-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "quantity", label: "Quantity" },
          { field: "supplier", label: "Supplier" },
          { field: "price", label: "Price" },
          { field: "date", label: "Purchase Date" },
        ],
      },
      "distributor-sales": {
        title: "Sales to Retailers",
        addForm: "distributor-sales-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "quantity", label: "Quantity" },
          { field: "retailer", label: "Retailer" },
          { field: "price", label: "Sale Price" },
          { field: "date", label: "Sale Date" },
        ],
      },
      "demand-analysis": {
        title: "Market Analysis",
        addForm: "analysis-form",
        columns: [
          { field: "product", label: "Product" },
          { field: "demand_level", label: "Demand Level" },
          { field: "market_trend", label: "Market Trend" },
          { field: "analysis_date", label: "Analysis Date" },
          { field: "recommendation", label: "Recommendation" },
        ],
      },
      "consumer-behavior": {
        title: "Consumer Behavior Data",
        addForm: "behavior-form",
        columns: [
          { field: "product_category", label: "Product Category" },
          { field: "consumer_segment", label: "Consumer Segment" },
          { field: "purchase_frequency", label: "Purchase Frequency" },
          { field: "seasonal_pattern", label: "Seasonal Pattern" },
          { field: "price_sensitivity", label: "Price Sensitivity" },
        ],
      },
      "weather-data": {
        title: "Weather Data",
        addForm: "weather-form",
        columns: [
          { field: "date", label: "Date" },
          { field: "location", label: "Location" },
          { field: "temperature", label: "Temperature" },
          { field: "humidity", label: "Humidity" },
          { field: "rainfall", label: "Rainfall" },
        ],
      },
      "weather-forecasts": {
        title: "Weather Forecasts",
        addForm: "forecast-form",
        columns: [
          { field: "forecast_date", label: "Forecast Date" },
          { field: "location", label: "Location" },
          { field: "predicted_temp", label: "Predicted Temperature" },
          { field: "predicted_rainfall", label: "Predicted Rainfall" },
          { field: "confidence", label: "Confidence Level" },
        ],
      },
    };

    return configs[tableType];
  }

  // Export data functionality
  exportData(tableType) {
    const data = this.getAll(tableType);
    const config = this.getTableConfig(tableType);

    if (data.length === 0) {
      alert("No data to export");
      return;
    }

    // Convert to CSV
    const headers = config.columns.map((col) => col.label);
    const csvContent = [
      headers.join(","),
      ...data.map((item) =>
        config.columns
          .map(
            (col) =>
              `"${(item[col.field] || "").toString().replace(/"/g, '""')}"`
          )
          .join(",")
      ),
    ].join("\n");

    // Download file
    const blob = new Blob([csvContent], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `${tableType}-${new Date().toISOString().split("T")[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  }
}

// Global functions for table operations
function filterTable(tableType, searchTerm) {
  const dataManager = window.dataManager;
  if (!dataManager) return;

  const table = document.getElementById(`table-${tableType}`);
  if (!table) return;

  const rows = table
    .getElementsByTagName("tbody")[0]
    .getElementsByTagName("tr");

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const cells = row.getElementsByTagName("td");
    let found = false;

    for (let j = 0; j < cells.length - 1; j++) {
      // -1 to exclude actions column
      if (
        cells[j].textContent.toLowerCase().includes(searchTerm.toLowerCase())
      ) {
        found = true;
        break;
      }
    }

    row.style.display = found ? "" : "none";
  }
}

function editItem(tableType, id) {
  const dataManager = window.dataManager;
  if (!dataManager) return;

  const item = dataManager.getById(tableType, id);
  if (!item) return;

  const config = dataManager.getTableConfig(tableType);
  if (!config) return;

  // Store item for editing
  window.editingItem = { tableType, id, item };

  // Show form with pre-filled data
  showModal(config.addForm);
}

function deleteItem(tableType, id) {
  if (!confirm("Are you sure you want to delete this item?")) return;

  const dataManager = window.dataManager;
  if (!dataManager) return;

  const success = dataManager.delete(tableType, id);
  if (success) {
    // Refresh the table
    showDataTable(tableType);
    if (window.agriApp) {
      window.agriApp.showMessage("Item deleted successfully", "success");
    }
  } else {
    if (window.agriApp) {
      window.agriApp.showMessage("Failed to delete item", "error");
    }
  }
}

function exportData(tableType) {
  const dataManager = window.dataManager;
  if (dataManager) {
    dataManager.exportData(tableType);
  }
}

// Initialize data manager
document.addEventListener("DOMContentLoaded", function () {
  window.dataManager = new DataManager();
});
