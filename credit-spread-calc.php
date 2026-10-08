<?php
// 1. Initialize variables with default values
$premium = 1.00;
$tp_percent = 35;
$sl_percent = 70;
$quantity = 1;
$total_collateral = 0;
$results = null;

// 2. Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $premium = filter_input(INPUT_POST, 'premium', FILTER_VALIDATE_FLOAT);
    $tp_percent = filter_input(INPUT_POST, 'tp_percent', FILTER_VALIDATE_INT);
    $sl_percent = filter_input(INPUT_POST, 'sl_percent', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    // Fallbacks for invalid data
    if ($premium === false || $premium <= 0) { $premium = 1.00; }
    if ($tp_percent === false || $tp_percent < 1 || $tp_percent > 100) { $tp_percent = 35; }
    if ($sl_percent === false || $sl_percent < 1 || $sl_percent > 100) { $sl_percent = 70; }
    if ($quantity === false || $quantity < 1) { $quantity = 1; }

    // 3. Perform Options Math Equations
    // Calculate dollar profit/loss per single contract
    $profit_per_contract = $premium * ($tp_percent / 100);
    $loss_per_contract = $premium * ($sl_percent / 100);

    // Credit spread target exit prices (Option contract values)
    $buy_back_tp_price = $premium - $profit_per_contract;
    $buy_back_sl_price = $premium + $loss_per_contract;

    // Scale totals by Quantity (Options multiplier is 100)
    $total_credit_collected = $premium * 100 * $quantity;
    $total_potential_profit = $profit_per_contract * 100 * $quantity;
    $total_potential_loss = $loss_per_contract * 100 * $quantity;

    // Pack results for the UI
    $results = [
        'buy_back_tp'   => number_format($buy_back_tp_price, 2),
        'buy_back_sl'   => number_format($buy_back_sl_price, 2),
        'total_credit'   => number_format($total_credit_collected, 2),
        'total_profit'   => number_format($total_potential_profit, 2),
        'total_loss'     => number_format($total_potential_loss, 2)
        'total_collateral'  => $quantity * 500
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Spread Calculator</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6f9; color: #333; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #111; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #0070f3; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background: #0051cb; }
        .results { margin-top: 25px; border-top: 2px solid #eaeaea; padding-top: 20px; }
        .result-row { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 8px 0; font-size: 15px; }
        .result-row.highlight-green { color: #2e7d32; font-weight: bold; background: #e8f5e9; padding: 8px; border-radius: 4px; }
        .result-row.highlight-red { color: #c62828; font-weight: bold; background: #ffebee; padding: 8px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Credit Spread Calculator</h2>
    
    <!-- Form posts to itself -->
    <form action="" method="POST">
        <div class="form-group">
            <label for="premium">Premium Collected (Entry Price):</label>
            <input type="number" id="premium" name="premium" step="0.01" value="<?php echo htmlspecialchars(number_format($premium, 2)); ?>" required>
        </div>

        <div class="form-group">
            <label for="tp_percent">Take Profit (% of Premium):</label>
            <input type="number" id="tp_percent" name="tp_percent" min="1" max="100" step="1" value="<?php echo htmlspecialchars($tp_percent); ?>" required>
        </div>

        <div class="form-group">
            <label for="sl_percent">Stop Loss (% of Premium):</label>
            <input type="number" id="sl_percent" name="sl_percent" min="1" max="100" step="1" value="<?php echo htmlspecialchars($sl_percent); ?>" required>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity (Contracts):</label>
            <input type="number" id="quantity" name="quantity" min="1" step="1" value="<?php echo htmlspecialchars($quantity); ?>" required>
        </div>

        <button type="submit">Calculate Execution Targets</button>
    </form>

    <!-- 4. Render Calculated Output -->
    <?php if ($results): ?>
        <div class="results">
            <h3>Trade Plan Blueprint</h3>
            
            <div class="result-row">
                <span>Total Capital Collected:</span>
                <strong>$<?php echo $results['total_credit']; ?></strong>
            </div>

            <div class="result-row">
                <span>Total Collateral Required:</span>
                <strong>$<?php echo $results['total_collateral']; ?></strong>
            </div>
            
            <div class="result-row highlight-green">
                <span>Total Profit:</span>
                <span>+$<?php echo $results['total_profit']; ?></span>
            </div>
            
            <div class="result-row highlight-red">
                <span>Total Loss:</span>
                <span>-$<?php echo $results['total_loss']; ?></span>
            </div>

          
            <div class="result-row">
                <span>Buy-Back Price to Take Profit:</span>
                <strong>$<?php echo $results['buy_back_tp']; ?></strong>
            </div>
            
            <div class="result-row">
                <span>Buy-Back Price to Stop Loss:</span>
                <strong>$<?php echo $results['buy_back_sl']; ?></strong>
            </div>
                        
        </div>
    <?php endif; ?>
</div>

</body>
</html>
