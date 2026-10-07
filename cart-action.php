<?php
require_once 'functions.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add':
        $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['product_id']) ? intval($_POST['product_id']) : 0);
        $product = fetchOne("SELECT id, name, price, sale_price, rental_price, image FROM products WHERE id = ? AND status = 'active'", "i", array($id));
        if ($product) {
            $price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
            $type = isset($_POST['type']) ? $_POST['type'] : 'purchase';
            if ($type == 'rental' && $product['rental_price']) {
                $price = $product['rental_price'];
            }
            $name = $product['name'];
            if ($type == 'rental') $name .= ' (Rental)';
            addToCart($id, $name, $price, $product['image'], isset($_POST['qty']) ? intval($_POST['qty']) : 1);
            $key = 'p' . $id;
            if (isset($_POST['size'])) $_SESSION['cart'][$key]['size'] = $_POST['size'];
            if (isset($_POST['color'])) $_SESSION['cart'][$key]['color'] = $_POST['color'];
            if (isset($_POST['type'])) $_SESSION['cart'][$key]['type'] = $_POST['type'];
        }
        echo json_encode(array('success' => true, 'count' => cartCount(), 'total' => cartTotal()));
        break;

    case 'update':
        $key = isset($_GET['key']) ? $_GET['key'] : '';
        $delta = isset($_GET['delta']) ? intval($_GET['delta']) : 0;
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $delta;
            if ($_SESSION['cart'][$key]['qty'] < 1) $_SESSION['cart'][$key]['qty'] = 1;
            if ($_SESSION['cart'][$key]['qty'] > 5) $_SESSION['cart'][$key]['qty'] = 5;
        }
        echo json_encode(array('success' => true, 'count' => cartCount(), 'total' => cartTotal()));
        break;

    case 'remove':
        $key = isset($_GET['key']) ? $_GET['key'] : '';
        removeFromCart($key);
        echo json_encode(array('success' => true, 'count' => cartCount(), 'total' => cartTotal()));
        break;

    case 'clear':
        clearCart();
        echo json_encode(array('success' => true, 'count' => 0, 'total' => 0));
        break;

    default:
        echo json_encode(array('success' => false, 'message' => 'Invalid action'));
}
?>