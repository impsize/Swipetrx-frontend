<?php
// bonus_engine.php — SwipeTRX Rewards
// Include this file and call grant_bonus() or check_referral_bonuses()
require_once __DIR__ . '/config.php';

function grant_bonus(int $user_id, string $type, float $eur, string $note = ''): void {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO bonuses (user_id,type,eur_amount,note) VALUES (?,?,?,?)")
            ->execute([$user_id, $type, $eur, $note]);
        $pdo->prepare("UPDATE users SET earn_balance=earn_balance+? WHERE id=?")
            ->execute([$eur, $user_id]);
        $pdo->commit();
    } catch (Exception $e) { $pdo->rollBack(); }
}

function bonus_amount(string $key): float {
    static $cache = [];
    if (!$cache) {
        $rows = db()->query("SELECT key_name,eur_amt FROM bonus_config")->fetchAll(PDO::FETCH_KEY_PAIR);
        $cache = $rows;
    }
    return (float)($cache[$key] ?? 0);
}

// Called on registration
function on_register(int $user_id, ?int $referred_by_id): void {
    // Welcome bonus
    $amt = bonus_amount('register');
    if ($amt > 0) grant_bonus($user_id, 'register', $amt, 'Welcome bonus');

    // Referrer gets base referral bonus
    if ($referred_by_id) {
        $ref_amt = bonus_amount('referral_earn');
        if ($ref_amt > 0) {
            $pdo = db();
            $rc = $pdo->prepare('SELECT COUNT(*) FROM users WHERE referred_by=?');
            $rc->execute([$referred_by_id]);
            $count = (int)$rc->fetchColumn();
            grant_bonus($referred_by_id, 'referral_earn', $ref_amt, 'New referral joined');
            // Update referral count
            $pdo->prepare('UPDATE users SET total_referrals=? WHERE id=?')->execute([$count, $referred_by_id]);
            // Check 10-referral milestone
            if ($count >= 10) {
                $mq = $pdo->prepare('SELECT bonus_milestone_10 FROM users WHERE id=?');
                $mq->execute([$referred_by_id]);
                $u = $mq->fetch();
                if (!$u['bonus_milestone_10']) {
                    $m_amt = bonus_amount('referral_10');
                    if ($m_amt > 0) grant_bonus($referred_by_id, 'referral_milestone', $m_amt, '🎯 10 referrals milestone!');
                    $pdo->prepare('UPDATE users SET bonus_milestone_10=1 WHERE id=?')->execute([$referred_by_id]);
                }
            }
        }
    }
}

// Called when a user's VIP level changes (after admin approves upgrade)
function on_vip_upgrade(int $user_id, int $new_level, ?int $referred_by_id): void {
    $pdo = db();
    // VIP unlock bonus for the user
    $keys = ['','','vip2_unlock','vip3_unlock','vip4_unlock','vip5_unlock','vip6_unlock','vip7_unlock'];
    $key  = $keys[$new_level] ?? null;
    if ($key) {
        $amt = bonus_amount($key);
        $lbl = ['','','Bronze','Silver','Gold','Platinum','Diamond','Elite'][$new_level] ?? 'VIP';
        if ($amt > 0) grant_bonus($user_id, 'vip_unlock', $amt, "Reached VIP $new_level ($lbl)");
    }

    // Referrer gets tiered referral bonus
    if ($referred_by_id) {
        $ref_keys = ['','','referral_vip2','referral_vip3','referral_vip4','referral_vip5'];
        $rk = $ref_keys[$new_level] ?? 'referral_vip5';
        $r_amt = bonus_amount($rk);
        if ($r_amt > 0) {
            $uq = $pdo->prepare('SELECT username FROM users WHERE id=?');
            $uq->execute([$user_id]);
            $uname = $uq->fetchColumn();
            grant_bonus($referred_by_id, 'referral_earn', $r_amt, "Referral @$uname reached VIP $new_level");
        }
    }
}
?>
