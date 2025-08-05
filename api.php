<?php
include 'includes/db_connection.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_production_data':
        $sql = "SELECT ph.*, p.name as product_name, l.district_name, l.division_name 
                FROM production_history ph 
                JOIN products p ON ph.product_id = p.product_id 
                JOIN locations l ON ph.location_id = l.location_id 
                ORDER BY ph.year ASC, ph.production_id ASC";
        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'get_weather_data':
        $sql = "SELECT wh.*, l.district_name, l.division_name 
                FROM weather_history wh 
                JOIN locations l ON wh.location_id = l.location_id 
                ORDER BY wh.date ASC, wh.weather_id ASC";
        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'get_supply_demand_data':
        $selected_year = $_GET['year'] ?? date('Y');
        $selected_product = $_GET['product'] ?? '';
        $selected_location = $_GET['location'] ?? '';

        $where_conditions = ["ph.year = ?"];
        $params = [$selected_year];
        $param_types = "i";

        if ($selected_product) {
            $where_conditions[] = "ph.product_id = ?";
            $params[] = $selected_product;
            $param_types .= "i";
        }

        if ($selected_location) {
            $where_conditions[] = "ph.location_id = ?";
            $params[] = $selected_location;
            $param_types .= "i";
        }

        $where_clause = implode(" AND ", $where_conditions);

        $sql = "SELECT 
                    p.name as product_name,
                    l.district_name,
                    l.division_name,
                    ph.quantity_produced as supply,
                    cd.consumer_purchase_records as demand,
                    (ph.quantity_produced - cd.consumer_purchase_records) as balance,
                    pr.wholesale_price,
                    pr.retail_price
                FROM production_history ph
                JOIN products p ON ph.product_id = p.product_id
                JOIN locations l ON ph.location_id = l.location_id
                LEFT JOIN consumption_data cd ON ph.product_id = cd.product_id 
                    AND ph.location_id = cd.location_id 
                    AND ph.year = cd.year
                LEFT JOIN price_history pr ON ph.product_id = pr.product_id 
                    AND ph.location_id = pr.location_id 
                    AND ph.year = pr.year
                WHERE $where_clause
                ORDER BY p.name, l.district_name";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close();
?>

