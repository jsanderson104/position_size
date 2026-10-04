<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options Trade Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f4f4f9; color: #333; }
        .container { max-width: 500px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #222; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], input[type="date"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .results { margin-top: 25px; padding: 15px; background: #e9ecef; border-left: 5px solid #28a745; border-radius: 4px; }
        .results h3 { margin-top: 0; }
        .results p { margin: 8px 0; font-size: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Options Trade Inputs</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label>Account Balance ($):</label>
            <input type="number" step="0.01" value="2000" name="account_balance" required value="<?php echo isset($_POST['account_balance']) ? htmlspecialchars($_POST['account_balance']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>TICKER:</label>
            <input type="text" name="ticker" required style="text-transform: uppercase;" value="<?php echo isset($_POST['ticker']) ? htmlspecialchars($_POST['ticker']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Option Price (eg 1.33):</label>
            <input type="number" step="0.01" name="option_price" required value="<?php echo isset($_POST['option_price']) ? htmlspecialchars($_POST['option_price']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Expiration Date:</label>
            <input type="date" name="expiration_date" required value="<?php echo isset($_POST['expiration_date']) ? htmlspecialchars($_POST['expiration_date']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Stop Loss Percent (%):</label>
            <input type="number" step="0.01" value="20" name="stop_loss_pct" required value="<?php echo isset($_POST['stop_loss_pct']) ? htmlspecialchars($_POST['stop_loss_pct']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Take Profit Percent (%):</label>
            <input type="number" step="0.01" value="40" name="take_profit_pct" required value="<?php echo isset($_POST['take_profit_pct']) ? htmlspecialchars($_POST['take_profit_pct']) : ''; ?>">
        </div>
        <button type="submit" name="calculate">Calculate Trade</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['calculate'])) {
        // Collect and sanitize inputs
        $ticker = strtoupper(htmlspecialchars($_POST['ticker']));
        $option_price = floatval($_POST['option_price']);
        $expiration_date = htmlspecialchars($_POST['expiration_date']);
        $stop_loss_pct = floatval($_POST['stop_loss_pct']);
        $take_profit_pct = floatval($_POST['take_profit_pct']);

        // Calculate Target Prices based on option premium price
        // Stop Loss Price = Option Price * (1 - Stop Loss %)
        $stop_loss_price = $option_price * (1 - ($stop_loss_pct / 100));
        
        // Take Profit Price = Option Price * (1 + Take Profit %)
        $take_profit_price = $option_price * (1 + ($take_profit_pct / 100));

        // Format dates for cleaner readability (Optional, change format if needed)
        $formatted_date = date("m/d/Y", strtotime($expiration_date));
        ?>

        <div class="results">
            <h3>Trade Outputs</h3>
            <p><strong>TICKER:</strong> <?php echo $ticker; ?></p>
            <p><strong>Option Price:</strong> $<?php echo number_format($option_price, 2); ?></p>
            <p><strong>Expiration Date:</strong> <?php echo $formatted_date; ?></p>
            <p><strong>Stop Loss:</strong> $<?php echo number_format($stop_loss_price, 2); echo " $stop_loss_pcnt " . "%"; ?></p>
            <p><strong>Take Profit:</strong> $<?php echo number_format($take_profit_price, 2);  echo " $take_profit_pcnt " . "%"; ?></p>
        </div>
        
    <?php } ?>
</div>

</body>
</html>
