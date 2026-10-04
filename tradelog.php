<?php
// --- CONFIGURATION ---
$csv_filename = 'tradelog.csv';

// --- INITIALIZE VARIABLES & DEFAULTS ---
$account_size = 2000;
$risk_percent = 2;
$premium = 0;
$contracts = 0;
$stop_loss_percent = 0;

$risk_amount = 0;
$total_position_cost = 0;
$max_contracts = 0;
$log_message = "";
$error_message = "";

// --- PROCESS FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and grab inputs
    $account_size = isset($_POST['account_size']) ? floatval($_POST['account_size']) : 2000;
    $risk_percent = isset($_POST['risk_percent']) ? floatval($_POST['risk_percent']) : 2;
    $premium = isset($_POST['premium']) ? floatval($_POST['premium']) : 0;
    $stop_loss_percent = isset($_POST['stop_loss_percent']) ? floatval($_POST['stop_loss_percent']) : 0;
    $take_profit_percent = floatval($_POST['take_profit_percent']);
    $option_premium = floatval($_POST['option_premium']);
    
    // Core Risk Calculations
    if ($account_size > 0 && $risk_percent > 0 && $premium > 0 && $stop_loss_percent > 0) {
        // Cash amount willing to lose on this trade
        $risk_amount = $account_size * ($risk_percent / 100);
        
        // Loss per single options contract based on the stop loss % (1 contract = 100 shares)
        $loss_per_contract = ($premium * 100) * ($stop_loss_percent / 100);
        
        // Calculate max contracts allowed based on risk rules
        if ($loss_per_contract > 0) {
            $max_contracts = floor($risk_amount / $loss_per_contract);
        }
        
        $total_position_cost = $max_contracts * ($premium * 100);
        
        // Check if user wants to log this trade to the CSV
        if (isset($_POST['log_trade']) && $_POST['log_trade'] == '1' && $max_contracts > 0) {
            
            // Check if file exists to determine if we need a header row
            $file_exists = file_exists($csv_filename);
            
            $file = fopen($csv_filename, 'a');
            if ($file) {
                // If it's a brand new file, write the headers first
                if (!$file_exists) {
                    fputcsv($file, ['Date', 'Account Size ($)', 'Risk %', 'Risk Amt ($)', 'Option Premium ($)', 'Stop Loss %', 'Take Profit %', Max Contracts', 'Total Cost ($)']);
                }
                
                // Write the trade data row
                $trade_data = [
                    date('Y-m-d H:i:s'),
                    $account_size,
                    $risk_percent,
                    $risk_amount,
                    $premium,
                    $stop_loss_percent,
                    $take_profit_percent,
                    $max_contracts,
                    $total_position_cost
                ];
                
                fputcsv($file, $trade_data);
                fclose($file);
                
                $log_message = "✅ Trade successfully logged to CSV!";
            } else {
                $error_message = "❌ Error: Could not open CSV file for writing. Check server permissions.";
            }
        }
    } else if (isset($_POST['calculate'])) {
        $error_message = "⚠️ Please fill in all fields with valid numbers greater than zero.";
    }
}
?>
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
        
        /* New Checkbox Styling */
        .checkbox-group { display: flex; align-items: center; background: #fdfdfd; border: 1px dashed #0070f3; padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        .checkbox-group input[type="checkbox"] { width: 18px; height: 18px; margin-right: 10px; cursor: pointer; }
        .checkbox-group label { display: inline; margin-bottom: 0; cursor: pointer; font-weight: normal; }
        
        button { width: 100%; padding: 12px; background-color: #0070f3; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        button:hover { background-color: #0051a8; }
        .results { margin-top: 25px; padding: 20px; background-color: #f8f9fa; border-left: 5px solid #0070f3; border-radius: 4px; }
        .results h3 { margin-top: 0; margin-bottom: 15px; color: #0070f3; }
        .result-item { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 15px; }
        .result-item span:last-child { font-weight: bold; }
        .alert-danger { background-color: #fdf2f2; border-left: 5px solid #de350b; padding: 15px; margin-bottom: 20px; border-radius: 4px; color: #de350b; font-size: 14px; }
        .alert-success { background-color: #f2fdf4; border-left: 5px solid #24a148; padding: 15px; margin-bottom: 20px; border-radius: 4px; color: #24a148; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 Options Risk Calculator</h2>

    <!-- Notifications -->
    <?php if (!empty($error_message)): ?>
        <div class="alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($log_message)): ?>
        <div class="alert-success"><?= htmlspecialchars($log_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="account_size">Account Size ($)</label>
            <input type="number" step="0.01" name="account_size" id="account_size" value="<?= htmlspecialchars($account_size) ?>" required>
        </div>

        <div class="form-group">
            <label for="risk_percent">Risk Per Trade (%)</label>
            <input type="number" step="0.1" name="risk_percent" id="risk_percent" value="<?= htmlspecialchars($risk_percent) ?>" required>
        </div>

        <div class="form-group">
            <label for="premium">Option Price (eg. 1.33)</label>
            <input type="number" step="0.01" name="premium" id="premium" value="<?= htmlspecialchars($premium) ?>" required placeholder="e.g. 2.50">
        </div>

        <div class="form-group">
            <label for="stop_loss_percent">Stop Loss % </label>
            <input type="number" step="1" name="stop_loss_percent" id="stop_loss_percent" value="<?= htmlspecialchars($stop_loss_percent) ?>" required placeholder="e.g. 20">
        </div>

        <div class="form-group">
            <label for="take_profit_percent">Take Profit % </label>
            <input type="number" step="1" name="take_profit_percent" id="take_profit_percent" value="<?= htmlspecialchars($take_profit_percent) ?>" required placeholder="e.g. 40">
        </div>

        <!-- Logging Trigger -->
        <div class="checkbox-group">
            <input type="checkbox" name="log_trade" id="log_trade" value="1">
            <label for="log_trade"><strong>Log Trade</strong> into server CSV spreadsheet</label>
        </div>

        <button type="submit" name="calculate">Calculate &amp; Process</button>
    </form>

    <!-- Calculation Outputs -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_message)): ?>
        <div class="results">
            <h3>Calculated Position Size</h3>
            <div class="result-item">
                <span>Total Cash Risked:</span>
                <span>$<?= number_format($risk_amount, 2) ?></span>
            </div>
            <div class="result-item">
                <span>Recommended Max Contracts:</span>
                <span style="font-size: 18px; color: #0070f3;"><?= intval($max_contracts) ?></span>
            </div>
            <div class="result-item">
                <span>Total Capital Required:</span>
                <span>$<?= number_format($total_position_cost, 2) ?></span>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
