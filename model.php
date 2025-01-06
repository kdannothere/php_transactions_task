<?php

/**
 * Return list of users.
 */
function get_users_with_transactions($conn)
{
    $sql = "SELECT DISTINCT u.id, u.name 
            FROM users u
            JOIN user_accounts ua ON u.id = ua.user_id
            JOIN transactions t ON ua.id = t.account_from OR ua.id = t.account_to";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function get_user_monthly_balances($user_id, $conn)
{
    // fetch user's transactions
    $sql = "SELECT 
        strftime('%Y-%m', t.trdate) AS month, 
        t.account_from, 
        t.account_to, 
        t.amount 
    FROM 
        transactions t
    JOIN 
        user_accounts ua1 ON t.account_from = ua1.id
    JOIN 
        user_accounts ua2 ON t.account_to = ua2.id
    WHERE 
        (ua1.user_id = :user_id OR ua2.user_id = :user_id)";

    // fetch user's accounts
    $sql2 = "SELECT DISTINCT ua.id, ua.user_id 
            FROM user_accounts ua
            WHERE ua.user_id = :user_id";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt2->execute();
    $userAccounts = $stmt2->fetchAll(PDO::FETCH_ASSOC);


    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $userAccountsArray = [];

    foreach ($userAccounts as $userAccount) {
        array_push($userAccountsArray, $userAccount['id']);
    }

    $monthly_balances = [];

    foreach ($transactions as $transaction) {
        $month = $transaction['month'];
        $amount = $transaction['amount'];

        // Check if the transaction involves only the user's accounts
        if (
            in_array($transaction['account_from'], $userAccountsArray)
            && in_array($transaction['account_to'], $userAccountsArray)
        ) {
            continue; // Skip transactions between the user's own accounts
        }

        if (in_array($transaction['account_from'], $userAccountsArray)) {
            // Outgoing transaction from the user
            $monthly_balances[$month] = isset($monthly_balances[$month]) ? $monthly_balances[$month] - $amount : -$amount;
        } elseif (in_array($transaction['account_to'], $userAccountsArray)) {
            // Incoming transaction to the user
            $monthly_balances[$month] = isset($monthly_balances[$month]) ? $monthly_balances[$month] + $amount : $amount;
        }
    }

    return $monthly_balances;
}

?>