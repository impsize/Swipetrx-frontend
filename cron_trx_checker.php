<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/bonus_engine.php';

if (PAYMENT_GATEWAY !== 'direct') exit("Gateway mode — handled by webhook.\n");

$pending = DB::rows(
    "SELECT * FROM trx_deposits WHERE status='pending' AND created_at > NOW() - INTERVAL 24 HOUR"
);
foreach ($pending as $dep) {
    // Query TRON API for incoming TXs to master wallet
    $url = "https://api.trongrid.io/v1/accounts/" . TRX_MASTER_WALLET . "/transactions/trc20?limit=50";
    $json = @file_get_contents($url);
    if (!$json) continue;
    $data = json_decode($json, true);
    if (empty($data['data'])) continue;
    foreach ($data['data'] as $tx) {
        if (strtolower($tx['to']) !== strtolower(TRX_MASTER_WALLET)) continue;
        $received_trx = $tx['value'] / 1e6;
        $expected     = $dep['expected_trx'];
        // Allow ±2 % tolerance
        if (abs($received_trx - $expected) / $expected > 0.02) continue;
        if ($tx['confirmed'] && $tx['confirmations'] >= TRX_MIN_CONFIRMATIONS) {
            $eur = round($received_trx * $dep['rate_eur_trx'], 2);
            DB::get()->beginTransaction();
            try {
                DB::q("UPDATE trx_deposits SET status='confirmed', received_trx=?, tx_hash=?, confirmed_at=NOW() WHERE id=?",
                      [$received_trx, $tx['transaction_id'], $dep['id']]);
                DB::q("UPDATE users SET deposit_balance = deposit_balance + ? WHERE id=?",
                      [$eur, $dep['user_id']]);
                DB::q("INSERT INTO transactions (user_id,type,amount_eur,ref_id,note) VALUES (?,?,?,?,?)",
                      [$dep['user_id'],'deposit',$eur,$dep['id'],"TRX confirmed: {$tx['transaction_id']}"]);
                // Mark first deposit so referral bonuses can be triggered
                DB::q("UPDATE users SET first_earn_date=COALESCE(first_earn_date,CURDATE()) WHERE id=?",
                      [$dep['user_id']]);
                DB::get()->commit();
                echo date('[Y-m-d H:i:s]') . " Deposit #{$dep['id']} confirmed: €$eur for user {$dep['user_id']}\n";
            } catch (\Exception $e) {
                DB::get()->rollBack();
                echo "ERROR: " . $e->getMessage() . "\n";
            }
            break;
        }
    }
}
?>
