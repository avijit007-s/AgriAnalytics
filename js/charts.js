// Charts and Visualization Management
class ChartManager {
    constructor() {
        this.charts = {};
        this.colors = {
            primary: '#4CAF50',
            secondary: '#2E7D32',
            accent: '#81C784',
            warning: '#FF9800',
            danger: '#F44336',
            info: '#2196F3',
            success: '#4CAF50',
            light: '#E8F5E9'
        };
    }

    // Destroy existing chart if it exists
    destroyChart(chartId) {
        if (this.charts[chartId]) {
            this.charts[chartId].destroy();
            delete this.charts[chartId];
        }
    }

    // Get real data from data manager
    getRealData(tableName, field, aggregateBy = 'month') {
        if (!window.dataManager) return [];
        
        const data = window.dataManager.getAll(tableName);
        if (!data || data.length === 0) {
            // Return empty array if no data
            return new Array(12).fill(0);
        }

        // Group data by month if date field exists
        const monthlyData = new Array(12).fill(0);
        
        data.forEach(item => {
            if (item[field] !== undefined) {
                let value = parseFloat(item[field]) || 0;
                
                // If there's a date field, group by month
                let monthIndex = 0;
                if (item.date || item.sowing_date || item.created_at) {
                    const dateStr = item.date || item.sowing_date || item.created_at;
                    const date = new Date(dateStr);
                    if (!isNaN(date.getTime())) {
                        monthIndex = date.getMonth();
                    }
                }
                
                monthlyData[monthIndex] += value;
            }
        });
        
        return monthlyData;
    }

    // Get product-wise data for charts
    getProductData(tableName) {
        if (!window.dataManager) return { labels: [], data: [] };
        
        const data = window.dataManager.getAll(tableName);
        if (!data || data.length === 0) {
            return { labels: ['No Data'], data: [0] };
        }

        const productMap = {};
        
        data.forEach(item => {
            const product = item.product || item.crop || item.crop_name || 'Unknown';
            const value = parseFloat(item.price || item.production || item.quantity || 1);
            
            if (productMap[product]) {
                productMap[product] += value;
            } else {
                productMap[product] = value;
            }
        });

        return {
            labels: Object.keys(productMap),
            data: Object.values(productMap)
        };
    }

    // Get regional data
    getRegionalData(tableName) {
        if (!window.dataManager) return { labels: [], data: [] };
        
        const data = window.dataManager.getAll(tableName);
        if (!data || data.length === 0) {
            return { 
                labels: ['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Barisal', 'Sylhet', 'Rangpur'], 
                data: [0, 0, 0, 0, 0, 0, 0] 
            };
        }

        const regionMap = {
            'Dhaka': 0, 'Chittagong': 0, 'Rajshahi': 0, 'Khulna': 0, 
            'Barisal': 0, 'Sylhet': 0, 'Rangpur': 0
        };
        
        data.forEach(item => {
            const region = item.location || item.district || item.region || 'Dhaka';
            const value = parseFloat(item.production || item.quantity || item.expected_yield || 1);
            
            if (regionMap[region] !== undefined) {
                regionMap[region] += value;
            } else {
                regionMap['Dhaka'] += value; // Default to Dhaka if region not found
            }
        });

        return {
            labels: Object.keys(regionMap),
            data: Object.values(regionMap)
        };
    }

    // Generate month labels
    getMonthLabels(count = 12) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return months.slice(0, count);
    }

    // Farmer Production Chart
    createFarmerProductionChart() {
        const ctx = document.getElementById('farmer-production-chart');
        if (!ctx) return;

        this.destroyChart('farmer-production-chart');

        // Get real production data
        const riceData = this.getRealData('farmer-production', 'production');
        const wheatData = this.getRealData('farmer-crops', 'area'); // Use area as secondary metric

        const data = {
            labels: this.getMonthLabels(),
            datasets: [{
                label: 'Production (tons)',
                data: riceData,
                borderColor: this.colors.primary,
                backgroundColor: this.colors.light,
                fill: true,
                tension: 0.4
            }, {
                label: 'Cultivated Area (acres)',
                data: wheatData,
                borderColor: this.colors.warning,
                backgroundColor: 'rgba(255, 152, 0, 0.1)',
                fill: true,
                tension: 0.4
            }]
        };

        this.charts['farmer-production-chart'] = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Production Trends',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Production/Area'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }

    // Consumer Price Chart
    createConsumerPriceChart() {
        const ctx = document.getElementById('consumer-price-chart');
        if (!ctx) return;

        this.destroyChart('consumer-price-chart');

        // Get real product price data
        const productData = this.getProductData('available-products');

        const data = {
            labels: productData.labels,
            datasets: [{
                label: 'Current Price (BDT/kg)',
                data: productData.data,
                backgroundColor: [
                    this.colors.primary,
                    this.colors.secondary,
                    this.colors.accent,
                    this.colors.info,
                    this.colors.warning,
                    this.colors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        };

        this.charts['consumer-price-chart'] = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Current Market Prices',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }

    // Retailer Sales Chart
    createRetailerSalesChart() {
        const ctx = document.getElementById('retailer-sales-chart');
        if (!ctx) return;

        this.destroyChart('retailer-sales-chart');

        // Get real sales data
        const salesData = this.getRealData('retailer-sales', 'price');

        const data = {
            labels: this.getMonthLabels(),
            datasets: [{
                label: 'Sales Revenue (BDT)',
                data: salesData,
                backgroundColor: this.colors.primary,
                borderColor: this.colors.secondary,
                borderWidth: 2
            }]
        };

        this.charts['retailer-sales-chart'] = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Sales Revenue',
                        font: { size: 16, weight: 'bold' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue (BDT)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }

    // Supplier Production Chart
    createSupplierProductionChart() {
        const ctx = document.getElementById('supplier-production-chart');
        if (!ctx) return;

        this.destroyChart('supplier-production-chart');

        // Get real regional production data
        const regionalData = this.getRegionalData('supplier-production');

        const data = {
            labels: regionalData.labels,
            datasets: [{
                label: 'Production (tons)',
                data: regionalData.data,
                backgroundColor: this.colors.primary,
                borderColor: this.colors.secondary,
                borderWidth: 2
            }]
        };

        this.charts['supplier-production-chart'] = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Regional Production Overview',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Production (tons)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Division'
                        }
                    }
                }
            }
        });
    }

    // Wholesaler Price Chart
    createWholesalerPriceChart() {
        const ctx = document.getElementById('wholesaler-price-chart');
        if (!ctx) return;

        this.destroyChart('wholesaler-price-chart');

        const data = {
            labels: this.getMonthLabels(),
            datasets: [{
                label: 'Purchase Price (BDT/kg)',
                data: this.generateSampleData('price'),
                borderColor: this.colors.info,
                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Sale Price (BDT/kg)',
                data: this.generateSampleData('price').map(x => x + 5),
                borderColor: this.colors.success,
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                fill: true,
                tension: 0.4
            }]
        };

        this.charts['wholesaler-price-chart'] = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Wholesale Price Trends',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Price (BDT/kg)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }

    // Distributor Flow Chart
    createDistributorFlowChart() {
        const ctx = document.getElementById('distributor-flow-chart');
        if (!ctx) return;

        this.destroyChart('distributor-flow-chart');

        const data = {
            labels: ['Supplier A', 'Supplier B', 'Supplier C', 'Retailer X', 'Retailer Y', 'Retailer Z'],
            datasets: [{
                label: 'Volume (tons)',
                data: [120, 150, 100, 180, 140, 150],
                backgroundColor: [
                    this.colors.primary,
                    this.colors.secondary,
                    this.colors.accent,
                    this.colors.info,
                    this.colors.warning,
                    this.colors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        };

        this.charts['distributor-flow-chart'] = new Chart(ctx, {
            type: 'polarArea',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribution Flow Analysis',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }

    // Demand Analysis Chart
    createDemandAnalysisChart() {
        const ctx = document.getElementById('demand-analysis-chart');
        if (!ctx) return;

        this.destroyChart('demand-analysis-chart');

        // Get real supply and demand data
        const supplyData = this.getRealData('farmer-production', 'production');
        const demandData = this.getRealData('consumer-orders', 'quantity');
        const priceData = this.getRealData('available-products', 'price');

        const data = {
            labels: this.getMonthLabels(),
            datasets: [{
                label: 'Supply',
                data: supplyData,
                borderColor: this.colors.primary,
                backgroundColor: 'rgba(76, 175, 80, 0.2)',
                fill: false,
                tension: 0.4
            }, {
                label: 'Demand',
                data: demandData,
                borderColor: this.colors.danger,
                backgroundColor: 'rgba(244, 67, 54, 0.2)',
                fill: false,
                tension: 0.4
            }, {
                label: 'Price Impact',
                data: priceData,
                type: 'bar',
                backgroundColor: this.colors.warning,
                yAxisID: 'y1'
            }]
        };

        this.charts['demand-analysis-chart'] = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Supply vs Demand Analysis',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Quantity (tons)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Price (BDT/kg)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    // Weather Chart
    createWeatherChart() {
        const ctx = document.getElementById('weather-chart');
        if (!ctx) return;

        this.destroyChart('weather-chart');

        const data = {
            labels: this.getMonthLabels(),
            datasets: [{
                label: 'Temperature (°C)',
                data: [20, 22, 25, 28, 30, 32, 31, 30, 28, 25, 22, 20],
                borderColor: this.colors.danger,
                backgroundColor: 'rgba(244, 67, 54, 0.1)',
                yAxisID: 'y',
                tension: 0.4
            }, {
                label: 'Rainfall (mm)',
                data: [50, 40, 60, 120, 200, 250, 300, 280, 200, 100, 70, 45],
                type: 'bar',
                backgroundColor: this.colors.info,
                yAxisID: 'y1'
            }, {
                label: 'Humidity (%)',
                data: [65, 68, 70, 75, 80, 85, 88, 85, 80, 75, 70, 67],
                borderColor: this.colors.success,
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                yAxisID: 'y2',
                tension: 0.4
            }]
        };

        this.charts['weather-chart'] = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Weather Data Analysis',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Temperature (°C)'
                        },
                        min: 0,
                        max: 40
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Rainfall (mm)'
                        },
                        min: 0,
                        max: 350,
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    y2: {
                        type: 'linear',
                        display: false,
                        min: 0,
                        max: 100
                    }
                }
            }
        });
    }

    // Market Trends Chart (Generic)
    createMarketTrendsChart(canvasId, title, datasets) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        this.destroyChart(canvasId);

        const data = {
            labels: this.getMonthLabels(),
            datasets: datasets
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: title,
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Destroy all charts
    destroyAllCharts() {
        Object.keys(this.charts).forEach(chartId => {
            this.destroyChart(chartId);
        });
    }

    // Update chart data (for real-time updates)
    updateChartData(chartId, newData) {
        if (this.charts[chartId]) {
            this.charts[chartId].data = newData;
            this.charts[chartId].update();
        }
    }

    // Export chart as image
    exportChart(chartId, filename) {
        if (this.charts[chartId]) {
            const url = this.charts[chartId].toBase64Image();
            const a = document.createElement('a');
            a.href = url;
            a.download = filename || `${chartId}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    }
}

// Initialize chart manager
document.addEventListener('DOMContentLoaded', function() {
    window.chartManager = new ChartManager();
});