<?php
require_once __DIR__ . '/config/data.php';
$_data = new Data();

if (isset($_POST['prices'])) {
    $priceData = json_decode($_POST['prices'], true);
    $all_success = true;

    foreach ($priceData as $key => $values) {
        // SQL matching your products_master structure
        $sql = "UPDATE products_master SET 
                display_name = :name, 
                stripe_price_id = :price_id, 
                price_amount = :amount 
                WHERE product_key = :key";
        
        $params = [
            ':name'     => $values['display_name'],
            ':price_id' => $values['price_id'],
            ':amount'   => $values['amount'],
            ':key'      => $key
        ];

        // Using the new prepared method
        if (!$_data->executePrepared($sql, $params)) {
            $all_success = false;
        }
    }

    if ($all_success) {
        echo "success";
    } else {
        echo "Error: " . $_data->getLastError();
    }
}
?>