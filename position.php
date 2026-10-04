<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options Swing Trading Position Calculator</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9; color: #333; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #1a1a1a; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px; }
        input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 12px; background-color: #0070f3; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        button:hover { background-color: #0051a8; }
        .results { margin-top: 25px; padding: 20px; background-color: #f8f9fa; border-left: 5px solid #0070f3; border-radius: 4px; }
        .results h3 { margin-top: 0; margin-bottom: 15px; color: #0070f3; }
        .result-item { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 15px; }
        .result-item span:last-child { font-weight: bold; }
        .alert-danger { background-color: #fdf2f2; border-left: 5px solid #de350b; padding: 15px; margin-bottom: 20px; border-radius: 4px; color: #de350b; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 Options Risk Calculator</h2>

    <?php
    // Default Values
    $account_size = 2000;
    $risk_percent = 2;
    $stop_loss_percent = 20;
    $take_profit_percent = 40;
    $option_premium = ""; // Default premium ($133 per contract)

    // Process Form Submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $account_size = floatval($_POST['account_size']);
        $risk_percent = floatval($_POST['risk_percent']);
        $stop_loss_percent = floatval($_POST['stop_loss_percent']);
        $take_profit_percent = floatval($_POST['take_profit_percent']);
        $option_premium = floatval($_POST['option_premium']);
    }

    // Calculations
    $allowed_dollar_risk = $account_size * ($risk_percent / 100);
    $stop_loss_decimal = $stop_loss_percent / 100;

    // Prevent Division by Zero if input is cleared
    if ($stop_loss_decimal > 0 && $option_premium > 0) {
        // Step 1: Max capital allocation allowed for the total position based on risk parameters
        $max_position_size = $allowed_dollar_risk / $stop_loss_decimal;

        // Step 2: Calculate target execution boundaries per single contract
        $cost_per_contract = $option_premium * 100;
        $max_contracts = floor($max_position_size / $cost_per_contract);

        // Step 3: Set trade exit prices
        $stop_loss_price = $option_premium * (1 - ($stop_loss_percent / 100));
        $take_profit_price = $option_premium * (1 + ($take_profit_percent / 100));

        // Actual dollar parameters if max contracts are purchased
        $actual_capital_deployed = $max_contracts * $cost_per_contract;
        $actual_dollar_risk = $actual_capital_deployed * ($stop_loss_percent / 100);
        $actual_dollar_reward = $actual_capital_deployed * ($take_profit_percent / 100);
    }
    ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="account_size">Account Balance ($)</label>
            <input type="number" step="0.01" name="account_size" id="account_size" value="<?php echo $account_size; ?>" required>
        </div>
        <div class="form-group">
            <label for="risk_percent">Max Portfolio Risk Per Trade (%)</label>
            <input type="number" step="0.1" name="risk_percent" id="risk_percent" value="<?php echo $risk_percent; ?>" required>
        </div>
        <div class="form-group">
            <label for="option_premium">Option Premium Entry Price (e.g., 1.33)</label>
            <input type="number" step="0.01" name="option_premium" id="option_premium" value="<?php echo $option_premium; ?>" required>
        </div>
        <div class="form-group">
            <label for="stop_loss_percent">Option Stop Loss (%)</label>
            <input type="number" step="1" name="stop_loss_percent" id="stop_loss_percent" value="<?php echo $stop_loss_percent; ?>" required>
        </div>
        <div class="form-group">
            <label for="take_profit_percent">Option Take Profit (%)</label>
            <input type="number" step="1" name="take_profit_percent" id="take_profit_percent" value="<?php echo $take_profit_percent; ?>" required>
        </div>
        <button type="submit">Calculate Position Size</button>
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $stop_loss_decimal > 0 && $option_premium > 0): ?>
        <div class="results">
            <h3>🎯 Execution Blueprint</h3>

            <?php if ($max_contracts == 0): ?>
                <div class="alert-danger">
                    <strong>Warning:</strong> Your account allocation limit ($<?php echo number_format($max_position_size, 2); ?>) is too small to afford a single contract ($<?php echo number_format($cost_per_contract, 2); ?>) at this premium price.
                </div>
            <?php else: ?>
                <div class="result-item">
                    <span>Target Risk Budget (<?php echo $risk_percent; ?>%):</span>
                    <span>$<?php echo number_format($allowed_dollar_risk, 2); ?></span>
                </div>
                <div class="result-item">
                    <span>Max Capital Allocation Cap:</span>
                    <span>$<?php echo number_format($max_position_size, 2); ?></span>
                </div>
                <hr style="border: 0; border-top: 1px dashed #ccc; margin: 15px 0;">
                <div class="result-item" style="font-size: 17px; color: #0070f3;">
                    <span><strong>Contracts to Buy:</strong></span>
                    <span><strong><?php echo $max_contracts; ?> Contract(s)</strong></span>
                </div>
                <div class="result-item">
                    <span>Total Cash Deployed:</span>
                    <span>$<?php echo number_format($actual_capital_deployed, 2); ?></span>
                </div>
                <div class="result-item">
                    <span>Actual Trade Risk if Stopped:</span>
                    <span style="color: #de350b;">-$<?php echo number_format($actual_dollar_risk, 2); ?></span>
                </div>
                <div class="result-item">
                    <span>Actual Target Profit:</span>
                    <span style="color: #00875a;">+$<?php echo number_format($actual_dollar_reward, 2); ?></span>
                </div>
                <hr style="border: 0; border-top: 1px dashed #ccc; margin: 15px 0;">
                <div class="result-item">
                    <span><strong>Set Stop Loss Order At:</strong></span>
                    <span style="color: #de350b;">$<?php echo number_format($stop_loss_price, 2); ?></span>
                </div>
                <div class="result-item">
                    <span><strong>Set Limit Take Profit At:</strong></span>
                    <span style="color: #00875a;">$<?php echo number_format($take_profit_price, 2); ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>

