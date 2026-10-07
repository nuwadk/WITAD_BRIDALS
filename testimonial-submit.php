<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cols = getColumns('testimonials');
    $data = array();
    
    $fieldMap = array(
        'full_name' => 'full_name',
        'customer_name' => 'full_name',
        'name' => 'full_name',
        'location' => 'location',
        'place' => 'location',
        'city' => 'location',
        'wedding_date' => 'wedding_date',
        'event_date' => 'wedding_date',
        'date' => 'wedding_date',
        'rating' => 'rating',
        'stars' => 'rating',
        'score' => 'rating',
        'quote' => 'quote',
        'content' => 'quote',
        'review' => 'quote',
        'message' => 'quote',
        'text' => 'quote',
        'status' => 'status',
        'state' => 'status'
    );
    
    foreach ($fieldMap as $postKey => $dbKey) {
        if (in_array($dbKey, $cols) && isset($_POST[$postKey])) {
            $val = $_POST[$postKey];
            if ($dbKey == 'rating') $val = (int)$val;
            $data[$dbKey] = $val;
        }
    }
    
    // Set default status if column exists
    if (in_array('status', $cols) && !isset($data['status'])) {
        $data['status'] = 'active';
    }
    
    if (!empty($data)) {
        $columns = array_keys($data);
        $values = array_values($data);
        $types = '';
        foreach ($values as $val) {
            $types .= is_int($val) ? 'i' : 's';
        }
        
        query("INSERT INTO testimonials (" . implode(', ', $columns) . ") VALUES (" . implode(', ', array_fill(0, count($columns), '?')) . ")", $types, $values);
        
        // Redirect back to testimonials page with success
        header('Location: testimonials.php?submitted=1');
        exit;
    }
}

// If not POST or no data, redirect back
header('Location: testimonials.php');
exit;
?>