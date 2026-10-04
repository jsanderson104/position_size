<?php
// Set default values
$ticker = isset($_POST['ticker']) ? htmlspecialchars($_POST['ticker']) : '';
$balance = isset($_POST['balance']) ? (float)$_POST['balance'] : 2000.00;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$max_risk_pct = isset($_POST['max_risk_pct']) ? (float)$_POST['max_risk_pct'] : 2;
$option_price = isset($_POST['option_price']) ? (float)$_POST['option_price'] : 0.00;
$stop_loss_pct = isset($_POST['stop_loss_pct']) ? (float)$_POST['stop_loss_pct'] : 20.00;
$take_profit_pct = isset($_POST['take_profit_pct']) ? (float)$_POST['take_profit_pct'] : 40.00;

// Calculations
$multiplier = 100; // Standard option contract multiplier

// 1. Max dollar risk allowed based on account balance
$max_risk_dollars = $balance * ($max_risk_pct / 100);

// 2. Risk per single contract based on stop loss percentage
$risk_per_contract_dollars = $option_price * ($stop_loss_pct / 100) * $multiplier;

// 3. Determine recommended contracts to stay within risk parameter
$recommended_contracts = $risk_per_contract_dollars > 0 ? floor($max_risk_dollars / $risk_per_contract_dollars) : 0;

// 4. Check if a single contract exceeds the maximum risk allowed
$is_too_expensive = ($risk_per_contract_dollars > $max_risk_dollars);

// 5. Collateral / Capital required for the requested quantity
$collateral_needed = $option_price * $quantity * $multiplier;

// 6. Stop Loss and Take Profit prices
$stop_loss_price = $option_price * (1 - ($stop_loss_pct / 100));
$take_profit_price = $option_price * (1 + ($take_profit_pct / 100));

// 7. Actual dollar risk for the current position size
$actual_risk_dollars = $quantity * $risk_per_contract_dollars;

// 8. Potential Profit
$potential_profit_per_contract = $option_price * ($take_profit_pct/100) * 100;
$potential_profit = $potential_profit_per_contract * $quantity;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Options Position Size Calculator</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .container { max-width: 600px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .results { margin-top: 20px; padding: 15px; background-color: #e9ecef; border-radius: 4px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>Options Position Size Calculator</h2>
    
    <form method="post" action="">
        <div class="form-group">
            <label>Account Balance ($):</label>
            <input type="number" step="0.01" name="balance" value="<?php echo $balance; ?>" required>
        </div>
        <div class="form-group">
            <label>Max Risk % of Balance per Trade:</label>
            <input type="number" step="0.01" name="max_risk_pct" value="<?php echo $max_risk_pct; ?>" required>
        </div>

        <div class="form-group">
            <label>Ticker:</label>
            <input type="text" name="ticker" value="<?php echo $ticker; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Option Price (eg 1.33):</label>
            <input type="number" step="0.01" name="option_price" value="<?php echo $option_price; ?>" required>
        </div>

        <div class="form-group">
            <label>Quantity:</label>
            <input type="number" name="quantity" value="<?php echo $quantity; ?>" required>
        </div>
        
        
        <div class="form-group">
            <label>Stop Loss (%):</label>
            <input type="number" step="0.01" name="stop_loss_pct" value="<?php echo $stop_loss_pct; ?>" required>
        </div>
        <div class="form-group">
            <label>Take Profit (%):</label>
            <input type="number" step="0.01" name="take_profit_pct" value="<?php echo $take_profit_pct; ?>" required>
        </div>
        <button type="submit">Calculate</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <div class="results">
            <h3>Calculation Results for <?php echo strtoupper($ticker); ?></h3>
            
            <!-- Risk Validation Alert -->
            <?php if ($is_too_expensive): ?>
                <div class="alert alert-danger">
                    ⚠️ WARNING: The price/risk of this option is too big for your defined risk parameters! A single contract risks $<?php echo number_format($risk_per_contract_dollars, 2); ?>, which exceeds your max allowed risk of $<?php echo number_format($max_risk_dollars, 2); ?>.
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    ✅ Option price fits within your risk parameters.
                </div>
            <?php endif; ?>

            <ul>
                <li><strong>Max Allowed Risk:</strong> $<?php echo number_format($max_risk_dollars, 2); ?> (<?php echo $max_risk_pct; ?>% of balance)</li>
                <li><strong>Recommended Contracts to Buy:</strong> <?php echo $recommended_contracts; ?> contract(s)</li>
                <li><strong>Collateral/Capital Needed (for <?php echo $quantity; ?> contracts):</strong> $<?php echo number_format($collateral_needed, 2); ?></li>
                
                <li class="divider"><hr></li>
                <li><strong>Stop Loss Price:</strong> $<?php echo number_format($stop_loss_price, 2); ?> (-<?php echo $stop_loss_pct; ?>%)</li>
                <li><strong>Take Profit Price:</strong> $<?php echo number_format($take_profit_price, 2); ?> (+<?php echo $take_profit_pct; ?>%)</li>
                <li class="divider"><hr></li>
                <li><strong>Total Position Risk (for <?php echo $quantity; ?> contracts):</strong> $<?php echo number_format($actual_risk_dollars, 2); ?></li>
                <li><strong>Potential Gain (for <?php echo $quantity; ?> contracts):</strong> $<?php echo number_format($potential_profit, 2); ?></li>

            </ul>
        </div>
    <?php endif; ?>
</div>

</body>
</html>

