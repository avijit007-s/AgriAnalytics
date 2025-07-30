// Forms Management System
class FormManager {
  constructor() {
    this.forms = this.initializeForms();
  }

  initializeForms() {
    return {
      "crop-form": {
        title: "Crop Information",
        fields: [
          {
            name: "crop_name",
            label: "Crop Name",
            type: "text",
            required: true,
          },
          { name: "variety", label: "Variety", type: "text", required: true },
          {
            name: "sowing_date",
            label: "Sowing Date",
            type: "date",
            required: true,
          },
          {
            name: "harvest_date",
            label: "Expected Harvest Date",
            type: "date",
            required: true,
          },
          { name: "area", label: "Area (acres)", type: "text", required: true },
          {
            name: "fertilizer",
            label: "Fertilizer Used",
            type: "textarea",
            required: false,
          },
        ],
        table: "farmer-crops",
      },
      "production-form": {
        title: "Production Record",
        fields: [
          { name: "crop", label: "Crop", type: "text", required: true },
          {
            name: "production",
            label: "Production Amount",
            type: "text",
            required: true,
          },
          {
            name: "date",
            label: "Production Date",
            type: "date",
            required: true,
          },
          {
            name: "quality",
            label: "Quality Grade",
            type: "select",
            options: ["Grade A", "Grade B", "Grade C"],
            required: true,
          },
          {
            name: "price",
            label: "Price per Unit",
            type: "text",
            required: true,
          },
        ],
        table: "farmer-production",
      },
      "order-form": {
        title: "Place Order",
        fields: [
          { name: "product", label: "Product", type: "text", required: true },
          { name: "quantity", label: "Quantity", type: "text", required: true },
          { name: "price", label: "Total Price", type: "text", required: true },
          {
            name: "order_date",
            label: "Order Date",
            type: "date",
            required: true,
          },
          {
            name: "status",
            label: "Status",
            type: "select",
            options: ["Pending", "Confirmed", "Delivered"],
            required: true,
          },
        ],
        table: "consumer-orders",
      },
      "inventory-form": {
        title: "Inventory Item",
        fields: [
          {
            name: "product",
            label: "Product Name",
            type: "text",
            required: true,
          },
          { name: "quantity", label: "Quantity", type: "text", required: true },
          {
            name: "price",
            label: "Price per Unit",
            type: "text",
            required: true,
          },
          { name: "supplier", label: "Supplier", type: "text", required: true },
          { name: "date", label: "Date Added", type: "date", required: true },
        ],
        table: "retailer-inventory",
      },
      "sales-form": {
        title: "Sales Record",
        fields: [
          { name: "product", label: "Product", type: "text", required: true },
          {
            name: "quantity",
            label: "Quantity Sold",
            type: "text",
            required: true,
          },
          { name: "price", label: "Sale Price", type: "text", required: true },
          {
            name: "customer",
            label: "Customer Name",
            type: "text",
            required: true,
          },
          { name: "date", label: "Sale Date", type: "date", required: true },
        ],
        table: "retailer-sales",
      },
      "customer-form": {
        title: "Customer Information",
        fields: [
          {
            name: "name",
            label: "Customer Name",
            type: "text",
            required: true,
          },
          { name: "phone", label: "Phone Number", type: "tel", required: true },
          { name: "email", label: "Email", type: "email", required: false },
          {
            name: "address",
            label: "Address",
            type: "textarea",
            required: true,
          },
          {
            name: "total_purchases",
            label: "Total Purchases",
            type: "text",
            required: false,
          },
        ],
        table: "retailer-customers",
      },
      "supply-form": {
        title: "Supply Record",
        fields: [
          {
            name: "item",
            label: "Supply Item",
            type: "select",
            options: ["Seeds", "Fertilizer", "Pesticide", "Equipment"],
            required: true,
          },
          { name: "quantity", label: "Quantity", type: "text", required: true },
          {
            name: "farmer",
            label: "Farmer Name",
            type: "text",
            required: true,
          },
          { name: "date", label: "Supply Date", type: "date", required: true },
          { name: "cost", label: "Cost", type: "text", required: true },
        ],
        table: "supplier-supplies",
      },
      "support-form": {
        title: "Farmer Support",
        fields: [
          {
            name: "farmer_name",
            label: "Farmer Name",
            type: "text",
            required: true,
          },
          {
            name: "support_type",
            label: "Support Type",
            type: "select",
            options: ["Financial", "Technical", "Equipment", "Training"],
            required: true,
          },
          {
            name: "amount",
            label: "Amount/Value",
            type: "text",
            required: true,
          },
          { name: "date", label: "Date", type: "date", required: true },
          {
            name: "status",
            label: "Status",
            type: "select",
            options: ["Pending", "Approved", "Completed"],
            required: true,
          },
        ],
        table: "supplier-support",
      },
      "purchase-form": {
        title: "Purchase Record",
        fields: [
          { name: "product", label: "Product", type: "text", required: true },
          { name: "quantity", label: "Quantity", type: "text", required: true },
          { name: "supplier", label: "Supplier", type: "text", required: true },
          {
            name: "purchase_price",
            label: "Purchase Price",
            type: "text",
            required: true,
          },
          {
            name: "date",
            label: "Purchase Date",
            type: "date",
            required: true,
          },
        ],
        table: "wholesaler-purchases",
      },
      "distribution-form": {
        title: "Distribution Record",
        fields: [
          { name: "product", label: "Product", type: "text", required: true },
          { name: "quantity", label: "Quantity", type: "text", required: true },
          { name: "retailer", label: "Retailer", type: "text", required: true },
          {
            name: "sale_price",
            label: "Sale Price",
            type: "text",
            required: true,
          },
          {
            name: "date",
            label: "Distribution Date",
            type: "date",
            required: true,
          },
        ],
        table: "wholesaler-distribution",
      },
      "distributor-purchase-form": {
        title: "Purchase from Supplier",
        fields: [
          { name: "product", label: "Product", type: "text", required: true },
          { name: "quantity", label: "Quantity", type: "text", required: true },
          { name: "supplier", label: "Supplier", type: "text", required: true },
          {
            name: "price",
            label: "Purchase Price",
            type: "text",
            required: true,
          },
          {
            name: "date",
            label: "Purchase Date",
            type: "date",
            required: true,
          },
        ],
        table: "distributor-purchases",
      },
      "distributor-sales-form": {
        title: "Sale to Retailer",
        fields: [
          { name: "product", label: "Product", type: "text", required: true },
          { name: "quantity", label: "Quantity", type: "text", required: true },
          { name: "retailer", label: "Retailer", type: "text", required: true },
          { name: "price", label: "Sale Price", type: "text", required: true },
          { name: "date", label: "Sale Date", type: "date", required: true },
        ],
        table: "distributor-sales",
      },
      "analysis-form": {
        title: "Market Analysis",
        fields: [
          { name: "product", label: "Product", type: "text", required: true },
          {
            name: "demand_level",
            label: "Demand Level",
            type: "select",
            options: ["Very High", "High", "Medium", "Low", "Very Low"],
            required: true,
          },
          {
            name: "market_trend",
            label: "Market Trend",
            type: "select",
            options: ["Increasing", "Stable", "Decreasing"],
            required: true,
          },
          {
            name: "analysis_date",
            label: "Analysis Date",
            type: "date",
            required: true,
          },
          {
            name: "recommendation",
            label: "Recommendation",
            type: "textarea",
            required: true,
          },
        ],
        table: "demand-analysis",
      },
      "weather-form": {
        title: "Weather Data Entry",
        fields: [
          { name: "date", label: "Date", type: "date", required: true },
          { name: "location", label: "Location", type: "text", required: true },
          {
            name: "temperature",
            label: "Temperature (°C)",
            type: "text",
            required: true,
          },
          {
            name: "humidity",
            label: "Humidity (%)",
            type: "text",
            required: true,
          },
          {
            name: "rainfall",
            label: "Rainfall (mm)",
            type: "text",
            required: true,
          },
        ],
        table: "weather-data",
      },
      "forecast-form": {
        title: "Weather Forecast",
        fields: [
          {
            name: "forecast_date",
            label: "Forecast Date",
            type: "date",
            required: true,
          },
          { name: "location", label: "Location", type: "text", required: true },
          {
            name: "predicted_temp",
            label: "Predicted Temperature",
            type: "text",
            required: true,
          },
          {
            name: "predicted_rainfall",
            label: "Predicted Rainfall",
            type: "text",
            required: true,
          },
          {
            name: "confidence",
            label: "Confidence Level (%)",
            type: "text",
            required: true,
          },
        ],
        table: "weather-forecasts",
      },
      "product-form": {
        title: "Available Product",
        fields: [
          {
            name: "product_name",
            label: "Product Name",
            type: "text",
            required: true,
          },
          {
            name: "price",
            label: "Price (BDT/kg)",
            type: "text",
            required: true,
          },
          {
            name: "quantity",
            label: "Available Quantity",
            type: "text",
            required: true,
          },
          { name: "supplier", label: "Supplier", type: "text", required: true },
          { name: "location", label: "Location", type: "text", required: true },
        ],
        table: "available-products",
      },
      "price-form": {
        title: "Price Record",
        fields: [
          { name: "product", label: "Product", type: "text", required: true },
          {
            name: "price",
            label: "Price (BDT/kg)",
            type: "text",
            required: true,
          },
          { name: "date", label: "Date", type: "date", required: true },
          { name: "market", label: "Market", type: "text", required: true },
          {
            name: "trend",
            label: "Trend",
            type: "select",
            options: ["Increasing", "Stable", "Decreasing"],
            required: true,
          },
        ],
        table: "wholesale-prices",
      },
      "production-monitor-form": {
        title: "Production Monitoring",
        fields: [
          {
            name: "farmer",
            label: "Farmer Name",
            type: "text",
            required: true,
          },
          { name: "crop", label: "Crop", type: "text", required: true },
          {
            name: "expected_yield",
            label: "Expected Yield (tons)",
            type: "text",
            required: true,
          },
          {
            name: "actual_yield",
            label: "Actual Yield (tons)",
            type: "text",
            required: false,
          },
          {
            name: "district",
            label: "District",
            type: "select",
            options: [
              "Dhaka",
              "Chittagong",
              "Rajshahi",
              "Khulna",
              "Barisal",
              "Sylhet",
              "Rangpur",
            ],
            required: true,
          },
        ],
        table: "supplier-production",
      },
      "behavior-form": {
        title: "Consumer Behavior Data",
        fields: [
          {
            name: "product_category",
            label: "Product Category",
            type: "text",
            required: true,
          },
          {
            name: "consumer_segment",
            label: "Consumer Segment",
            type: "select",
            options: [
              "Urban",
              "Rural",
              "Middle Class",
              "Low Income",
              "High Income",
            ],
            required: true,
          },
          {
            name: "purchase_frequency",
            label: "Purchase Frequency",
            type: "select",
            options: ["Daily", "Weekly", "Monthly", "Seasonal"],
            required: true,
          },
          {
            name: "seasonal_pattern",
            label: "Seasonal Pattern",
            type: "text",
            required: false,
          },
          {
            name: "price_sensitivity",
            label: "Price Sensitivity",
            type: "select",
            options: ["High", "Medium", "Low"],
            required: true,
          },
        ],
        table: "consumer-behavior",
      },
    };
  }
  getForm(formType) {
    const formConfig = this.forms[formType];
    if (!formConfig) {
      return "<p>Form not found.</p>";
    }

    let html = `<h3>${formConfig.title}</h3><form id="dynamic-form" onsubmit="submitForm(event, '${formType}')">`;

    formConfig.fields.forEach((field) => {
      html += this.generateField(field);
    });

    html += `
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>`;

    // If editing, populate form with existing data
    setTimeout(() => {
      if (window.editingItem && window.editingItem.item) {
        this.populateForm(window.editingItem.item);
      }
    }, 100);

    return html;
  }

  generateField(field) {
    let html = `<div class="form-group">
            <label for="${field.name}">${field.label}${
      field.required ? " *" : ""
    }</label>`;

    switch (field.type) {
      case "textarea":
        html += `<textarea id="${field.name}" name="${field.name}" ${
          field.required ? "required" : ""
        }></textarea>`;
        break;
      case "select":
        html += `<select id="${field.name}" name="${field.name}" ${
          field.required ? "required" : ""
        }>
                    <option value="">Select ${field.label}</option>`;
        if (field.options) {
          field.options.forEach((option) => {
            html += `<option value="${option}">${option}</option>`;
          });
        }
        html += "</select>";
        break;
      default:
        html += `<input type="${field.type}" id="${field.name}" name="${
          field.name
        }" ${field.required ? "required" : ""}>`;
    }

    html += "</div>";
    return html;
  }

  populateForm(data) {
    Object.keys(data).forEach((key) => {
      const field = document.getElementById(key);
      if (field && data[key] !== undefined) {
        field.value = data[key];
      }
    });
  }

  submitForm(event, formType) {
    event.preventDefault();

    const formConfig = this.forms[formType];
    if (!formConfig) return;

    const formData = new FormData(event.target);
    const data = {};

    // Extract form data
    formConfig.fields.forEach((field) => {
      data[field.name] = formData.get(field.name) || "";
    });

    // Validate required fields
    const missingFields = formConfig.fields
      .filter((field) => field.required && !data[field.name])
      .map((field) => field.label);

    if (missingFields.length > 0) {
      alert(
        `Please fill in the following required fields: ${missingFields.join(
          ", "
        )}`
      );
      return;
    }

    const dataManager = window.dataManager;
    if (!dataManager) return;

    try {
      if (window.editingItem && window.editingItem.id) {
        // Update existing item
        dataManager.update(formConfig.table, window.editingItem.id, data);
        if (window.agriApp) {
          window.agriApp.showMessage("Item updated successfully", "success");
        }
        window.editingItem = null;
      } else {
        // Create new item
        dataManager.create(formConfig.table, data);
        if (window.agriApp) {
          window.agriApp.showMessage("Item added successfully", "success");
        }
      }

      // Close modal and refresh if table is open
      closeModal();

      // Refresh table if it's currently displayed
      const dataModal = document.getElementById("data-modal");
      if (dataModal.style.display === "block") {
        showDataTable(formConfig.table);
      }
    } catch (error) {
      console.error("Error submitting form:", error);
      if (window.agriApp) {
        window.agriApp.showMessage("Error saving data", "error");
      }
    }
  }

  validateForm(formData, formConfig) {
    const errors = [];

    formConfig.fields.forEach((field) => {
      const value = formData[field.name];

      // Required field validation
      if (field.required && (!value || value.trim() === "")) {
        errors.push(`${field.label} is required`);
      }

      // Type-specific validation
      if (value && value.trim() !== "") {
        switch (field.type) {
          case "email":
            if (!this.isValidEmail(value)) {
              errors.push(`${field.label} must be a valid email address`);
            }
            break;
          case "tel":
            if (!this.isValidPhone(value)) {
              errors.push(`${field.label} must be a valid phone number`);
            }
            break;
          case "date":
            if (!this.isValidDate(value)) {
              errors.push(`${field.label} must be a valid date`);
            }
            break;
        }
      }
    });

    return errors;
  }

  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  isValidPhone(phone) {
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return phoneRegex.test(phone);
  }

  isValidDate(date) {
    return !isNaN(Date.parse(date));
  }
}

// Global function for form submission
function submitForm(event, formType) {
  if (window.formManager) {
    window.formManager.submitForm(event, formType);
  }
}

// Initialize form manager
document.addEventListener("DOMContentLoaded", function () {
  window.formManager = new FormManager();
});
