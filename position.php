<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options Trade Tracker</title>
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #16a34a;
            --success-bg: #f0fdf4;
            --danger: #dc2626;
            --danger-bg: #fef2f2;
        }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            margin: 0; 
            padding: 40px 20px;
            background-color: var(--bg-color); 
            color: var(--text-main); 
            display: flex;
            justify-content: center;
        }

        .container { 
            width: 100%;
            max-width: 480px; 
            background: var(--card-bg); 
            padding: 32px; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        h2 { 
            margin: 0 0 24px 0; 
            color: var(--text-main); 
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .form-group { 
            margin-bottom: 20px; 
        }

        label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: 600; 
            font-size: 14px;
            color: var(--text-main);
        }

        input[type="text"], input[type="number"], input[type="date"] { 
            width: 100%; 
            padding: 10px 14px; 
            box-sizing: border-box; 
            border: 1px solid var(--border-color); 
            border-radius: 8px; 
            font-size: 15px;
            background-color: #fff;
            transition: all 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        button { 
            background-color: var(--primary); 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 16px; 
            font-weight: 600;
            transition: background-color 0.2s ease;
            margin-top: 10px;
        }

        button:hover { 
            background-color: var(--primary-hover); 
        }

        /* Redesigned Premium Output Section */
        .results { 
            margin-top: 32px; 
            padding: 24px; 
            background: #fafafa; 
            border: 1px solid var(--border-color);
            border-radius: 12px; 
        }

        .results h3 { 
            margin: 0 0 20px 0; 
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .grid-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .grid-row:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
        }

        .label-text {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .value-text {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-main);
        }

        .ticker-badge {
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
        }

        .badge-danger {
            color: var(--danger);
            background-color: var(--danger-bg);
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 700;
        }

        .badge-success {
            color: var(--success);
            background-color: var(--success-bg);
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Options Trade Inputs</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label>Account Balance ($):</label>
            <input type="number" step="0.01" name="account_balance" required value="<?php echo isset($_POST['account_balance']) ? htmlspecialchars($_POST['account_balance']) : '2000.00'; ?>">
        </div>
        <div class="form-group">
            <label>TICKER:</label>
            <input type="text" name="ticker" required style="text-transform: uppercase;" value="<?php echo isset($_POST['ticker']) ? htmlspecialchars($_POST['ticker']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Option Price ($):</label>
            <input type="number" step="0.01" placeholder="e.g. 1.33" name="option_price" required value="<?php echo isset($_POST['option_price']) ? htmlspecialchars($_POST['option_price']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Expiration Date:</label>
            <input type="date" name="expiration_date" required value="<?php echo isset($_POST['expiration_date']) ? htmlspecialchars($_POST['expiration_date']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Stop Loss Percent (%):</label>
            <input type="number" step="0.01" name="stop_loss_pct" required value="<?php echo isset($_POST['stop_loss_pct']) ? htmlspecialchars($_POST['stop_loss_pct']) : '20'; ?>">
        </div>
        <div class="form-group">
            <label>Take Profit Percent (%):</label>
            <input type="number" step="0.01" name="take_profit_pct" required value="<?php echo isset($_POST['take_profit_pct']) ? htmlspecialchars($_POST['take_profit_pct']) : '40'; ?>">
        </div>
        <button type="submit" name="calculate">Calculate Trade</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['calculate'])) {
        $ticker = strtoupper(htmlspecialchars($_POST['ticker']));
        $option_price = floatval($_POST['option_price']);
        $expiration_date = htmlspecialchars($_POST['expiration_date']);
        $stop_loss_pct = floatval($_POST['stop_loss_pct']);
        $take_profit_pct = floatval($_POST['take_profit_pct']);

        $stop_loss_price = $option_price * (1 - ($stop_loss_pct / 100));
        $take_profit_price = $option_price * (1 + ($take_profit_pct / 100));
        $formatted_date = date("m/d/Y", strtotime($expiration_date));
        ?>

        <div class="results">
            <h3>Trade Outputs</h3>
            
            <div class="grid-row">
                <span class="label-text">TICKER</span>
                <span class="value-text"><span class="ticker-badge"><?php echo $ticker; ?></span></span>
            </div>
            
            <div class="grid-row">
                <span class="label-text">Option Entry Price</span>
                <span class="value-text">$<?php echo number_format($option_price, 2); ?></span>
            </div>
            
            <div class="grid-row">
                <span class="label-text">Expiration Date</span>
                <span class="value-text"><?php echo $formatted_date; ?></span>
            </div>
            
            <div class="grid-row">
                <span class="label-text">Stop Loss (-<?php echo $stop_loss_pct; ?>%)</span>
                <span class="badge-danger">$<?php echo number_format($stop_loss_price, 2); ?></span>
            </div>
            
            <div class="grid-row">
                <span class="label-text">Take Profit (+<?php echo $take_profit_pct; ?>%)</span>
                <span class="badge-success">$<?php echo number_format($take_profit_price, 2); ?></span>
            </div>
        </div>
        
    <?php } ?>
</div>

</body>
</html>
