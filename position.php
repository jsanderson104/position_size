<?php
// Initialize variables with default values
$account_size = 25000;
$risk_percent = 2;
$entry_price = 3.50;
$stop_loss = 2.50;
$contract_multiplier = 100;

$results = null;
$error = null;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_size = filter_input(INPUT_POST, 'account_size', FILTER_VALIDATE_FLOAT);
    $risk_percent = filter_input(INPUT_POST, 'risk_percent', FILTER_VALIDATE_FLOAT);
    $entry_price = filter_input(INPUT_POST, 'entry_price', FILTER_VALIDATE_FLOAT);
    $stop_loss = filter_input(INPUT_POST, 'stop_loss', FILTER_VALIDATE_FLOAT);

    // Validation
    if ($account_size <= 0 || $risk_percent <= 0 || $entry_price <= 0 || $stop_loss <= 0) {
        $error = "All values must be greater than zero.";
    } elseif ($stop_loss >= $entry_price) {
        $error = "Stop loss price must be lower than the entry price for a long position.";
    } else {
        // Calculations
        $max_risk_dollars = $account_size * ($risk_percent / 100);
        $risk_per_option = $entry_price - $stop_loss;
        $risk_per_contract = $risk_per_option * $contract_multiplier;
        
        // Calculate max contracts based on risk limit
        $contracts_by_risk = floor($max_risk_dollars / $risk_per_contract);
        
        // Calculate max contracts based on total buying power (capital constraints)
        $cost_per_contract = $entry_price * $contract_multiplier;
        $contracts_by_capital = floor($account_size / $cost_per_contract);
        
        // Final position size is the bottleneck between risk limit and capital limit
        $final_contracts = min($contracts_by_risk, $contracts_by_capital);
        
        $total_cost = $final_contracts * $cost_per_contract;
        $actual_risk_dollars = $final_contracts * $risk_per_contract;
        $capital_allocation_pct = ($total_cost / $account_size) * 100;

        if ($final_contracts <= 0) {
            $error = "Your account size or risk tolerance is too small to purchase even 1 contract under these parameters.";
        } else {
            $results = [
                'max_risk_dollars' => $max_risk_dollars,
                'risk_per_contract' => $risk_per_contract,
                'final_contracts' => $final_contracts,
                'total_cost' => $total_cost,
                'actual_risk_dollars' => $actual_risk_dollars,
                'capital_allocation_pct' => $capital_allocation_pct
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options Position Size Calculator</title>
    <script src="https://jsdelivr.net"></script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full bg-white rounded-xl shadow-md overflow-hidden grid md:grid-cols-2">
        
        <!-- Form Section -->
        <div class="p-6 md:p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Position Parameter Inputs</h2>
            
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1" for="account_size">Total Account Size ($)</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                           type="number" step="0.01" id="account_size" name="account_size" value="<?php echo htmlspecialchars($account_size); ?>" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1" for="risk_percent">Risk Per Trade (%)</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                           type="number" step="0.1" id="risk_percent" name="risk_percent" value="<?php echo htmlspecialchars($risk_percent); ?>" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1" for="entry_price">Option Entry Premium ($)</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                           type="number" step="0.01" id="entry_price" name="entry_price" value="<?php echo htmlspecialchars($entry_price); ?>" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1" for="stop_loss">Option Stop Loss Price ($)</label>
                    <input class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                           type="number" step="0.01" id="stop_loss" name="stop_loss" value="<?php echo htmlspecialchars($stop_loss); ?>" required>
                </div>

                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold p-3 rounded-lg transition duration-200 mt-2 cursor-pointer" type="submit">
                    Calculate Size
                </button>
            </form>
        </div>

        <!-- Output Results Section -->
        <div class="p-6 md:p-8 bg-gray-50 flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Calculated Position Sizing</h2>
                
                <?php if ($results): ?>
                    <div class="space-y-5">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                            <span class="block text-xs uppercase tracking-wide font-bold text-blue-600">Recommended Size</span>
                            <span class="text-4xl font-extrabold text-blue-900"><?php echo number_format($results['final_contracts']); ?></span>
                            <span class="block text-sm font-medium text-blue-700 mt-1">Contracts</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-2xs">
                                <span class="block text-xs text-gray-500 font-medium">Total Capital Cost</span>
                                <span class="text-lg font-bold text-gray-800">$<?php echo number_format($results['total_cost'], 2); ?></span>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-2xs">
                                <span class="block text-xs text-gray-500 font-medium">Account Allocated</span>
                                <span class="text-lg font-bold text-gray-800"><?php echo number_format($results['capital_allocation_pct'], 1); ?>%</span>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-2xs">
                                <span class="block text-xs text-gray-500 font-medium">Risk Per Contract</span>
                                <span class="text-lg font-bold text-gray-800">$<?php echo number_format($results['risk_per_contract'], 2); ?></span>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-2xs">
                                <span class="block text-xs text-gray-500 font-medium">Actual Dollar Risk</span>
                                <span class="text-lg font-bold text-gray-800">$<?php echo number_format($results['actual_risk_dollars'], 2); ?></span>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500 space-y-1 pt-2 border-t border-gray-200">
                            <p>• Max planned risk cap based on percentage: <strong>$<?php echo number_format($results['max_risk_dollars'], 2); ?></strong></p>
                            <p>• Calculations assume standard equity option multiplier ($100 per contract point).</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="h-full flex items-center justify-center text-center text-gray-400 py-12">
                        <div>
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 002-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm">Submit the parameters to see sizing allocation results.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-6 text-2xs text-gray-400 text-center">
                Always check liquidity, slippage, and spread variables before final execution.
            </div>
        </div>

    </div>

</body>
</html>
