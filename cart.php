<?php
$pageTitle = 'Shopping Cart';
$pageDesc = 'Review your selected bridal items and proceed to checkout.';
require_once 'header.php';

$cart = getCart();
$cartItems = array_values($cart);
?>

<div class="breadcrumbs">
  <div class="container">
    <h1>Shopping Cart</h1>
    <div class="crumb">
      <a href="index.php">Home</a> <i class="fa fa-chevron-right"></i>
      <span>Cart</span>
    </div>
  </div>
</div>

<section class="section" style="padding-top:60px;">
  <div class="container">
    <?php if (!empty($cartItems)): ?>
    <div class="cart-layout">
      <div class="cart-items">
        <div class="cart-header">
          <span>Product</span>
          <span>Price</span>
          <span>Qty</span>
          <span>Total</span>
          <span></span>
        </div>
        <?php foreach ($cartItems as $key => $item): 
          $itemTotal = $item['price'] * $item['qty'];
        ?>
        <div class="cart-item" data-key="<?php echo $key; ?>">
          <div class="cart-product">
            <img src="<?php echo $item['image']; ?>" alt="<?php echo sanitize($item['name']); ?>" />
            <div>
              <h4><?php echo sanitize($item['name']); ?></h4>
              <?php if (!empty($item['size'])): ?><p>Size: <?php echo $item['size']; ?></p><?php endif; ?>
              <?php if (!empty($item['color'])): ?><p>Color: <?php echo $item['color']; ?></p><?php endif; ?>
              <?php if (!empty($item['type'])): ?><p>Type: <?php echo ucfirst($item['type']); ?></p><?php endif; ?>
            </div>
          </div>
          <div class="cart-price"><?php echo formatPrice($item['price']); ?></div>
          <div class="cart-qty">
            <button onclick="updateQty('<?php echo $key; ?>', -1)">-</button>
            <span><?php echo $item['qty']; ?></span>
            <button onclick="updateQty('<?php echo $key; ?>', 1)">+</button>
          </div>
          <div class="cart-total"><?php echo formatPrice($itemTotal); ?></div>
          <div class="cart-remove">
            <button onclick="removeItem('<?php echo $key; ?>')" title="Remove"><i class="fa fa-trash"></i></button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="cart-summary">
        <h3>Order Summary</h3>
        <div class="summary-row">
          <span>Subtotal</span>
          <span><?php echo formatPrice(cartTotal()); ?></span>
        </div>
        <div class="summary-row">
          <span>Shipping</span>
          <span style="color:var(--success);">Free Pickup</span>
        </div>
        <div class="summary-row">
          <span>Tax</span>
          <span>Included</span>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-row total">
          <span>Total</span>
          <span><?php echo formatPrice(cartTotal()); ?></span>
        </div>
        <a href="checkout.php" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:20px;">
          <i class="fa fa-credit-card"></i> Proceed to Checkout
        </a>
        <a href="products.php" class="btn btn-outline btn-lg" style="width:100%;justify-content:center;margin-top:10px;">
          <i class="fa fa-arrow-left"></i> Continue Shopping
        </a>
        <div class="cart-note">
          <i class="fa fa-info-circle"></i>
          <p>All items are available for pickup at our studio. Fitting appointments can be scheduled during checkout.</p>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="empty-cart">
      <i class="fa fa-shopping-bag"></i>
      <h2>Your Cart is Empty</h2>
      <p>Browse our collection and find your dream gown.</p>
      <a href="products.php" class="btn btn-primary btn-lg">
        <i class="fa fa-shopping-bag"></i> Shop Collection
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
function updateQty(key, delta) {
  fetch('cart-action.php?action=update&key=' + key + '&delta=' + delta)
    .then(r => r.json())
    .then(data => {
      if (data.success) location.reload();
    });
}
function removeItem(key) {
  if (confirm('Remove this item from cart?')) {
    fetch('cart-action.php?action=remove&key=' + key)
      .then(r => r.json())
      .then(data => {
        if (data.success) location.reload();
      });
  }
}
</script>

<style>
.cart-layout {
  display: grid; grid-template-columns: 1fr 360px;
  gap: 40px;
}
.cart-items { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; }
.cart-header {
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 60px;
  padding: 16px 24px; background: linear-gradient(135deg, var(--text), #2a1a25);
  color: #fff; font-size: 0.82rem; font-weight: 600; letter-spacing: 0.5px;
}
.cart-item {
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 60px;
  align-items: center; padding: 20px 24px;
  border-bottom: 1px solid var(--border);
}
.cart-item:last-child { border-bottom: none; }
.cart-product { display: flex; align-items: center; gap: 16px; }
.cart-product img { width: 80px; height: 100px; object-fit: cover; border-radius: 10px; }
.cart-product h4 { font-size: 0.95rem; margin-bottom: 4px; }
.cart-product p { font-size: 0.78rem; color: var(--text-lt); margin: 2px 0; }
.cart-price, .cart-total { font-weight: 600; color: var(--accent); }
.cart-qty { display: flex; align-items: center; gap: 8px; }
.cart-qty button {
  width: 32px; height: 32px; border-radius: 8px;
  border: 1.5px solid var(--border); background: var(--bg);
  cursor: pointer; font-size: 1rem; transition: all 0.3s;
}
.cart-qty button:hover { border-color: var(--accent); color: var(--accent); }
.cart-qty span { font-weight: 600; min-width: 24px; text-align: center; }
.cart-remove button {
  width: 36px; height: 36px; border-radius: 10px;
  border: none; background: rgba(239,68,68,0.1); color: var(--error);
  cursor: pointer; transition: all 0.3s;
}
.cart-remove button:hover { background: var(--error); color: #fff; }

.cart-summary {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 20px; padding: 28px; height: fit-content;
  position: sticky; top: 100px;
}
.cart-summary h3 { font-size: 1.2rem; margin-bottom: 20px; }
.summary-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 10px 0; font-size: 0.9rem; color: var(--text-lt);
}
.summary-row.total { font-size: 1.2rem; font-weight: 700; color: var(--text); }
.summary-divider { height: 1px; background: var(--border); margin: 12px 0; }
.cart-note {
  margin-top: 20px; padding: 14px;
  background: rgba(201,168,76,0.08); border-radius: 12px;
  display: flex; gap: 10px; align-items: flex-start;
}
.cart-note i { color: var(--accent); margin-top: 2px; }
.cart-note p { font-size: 0.8rem; color: var(--text-lt); line-height: 1.6; }

.empty-cart {
  text-align: center; padding: 100px 20px;
}
.empty-cart i { font-size: 5rem; color: var(--border); margin-bottom: 24px; }
.empty-cart h2 { font-size: 1.8rem; margin-bottom: 10px; }
.empty-cart p { color: var(--text-lt); margin-bottom: 28px; }

@media (max-width: 1024px) {
  .cart-layout { grid-template-columns: 1fr; }
  .cart-summary { position: static; }
}
@media (max-width: 768px) {
  .cart-header { display: none; }
  .cart-item {
    grid-template-columns: 1fr;
    gap: 12px; padding: 20px;
  }
}
</style>

<?php require_once 'footer.php'; ?>
