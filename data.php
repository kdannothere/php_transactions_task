<?php
include_once 'db.php';
include_once 'model.php';

// formats date, ex. from 2024-01 into January 2024
function convert_date_string($date_string) {
    $month_names = [
        '01' => 'January',
        '02' => 'February',
        '03' => 'March'
    ];
    // Split the date string into year and month
    list($year, $month) = explode('-', $date_string);
  
    // Get the month name from the array
    $month_name = $month_names[$month];
  
    // Return the formatted date string
    return "$month_name $year";
  }

$user_id = isset($_GET['user'])
    ? (int) $_GET['user']
    : null;

    $user_name = isset($_GET['name'])
    ? (string) $_GET['name']
    : 'Name';

if ($user_id) {
    // represents a connection to the database
    $conn = get_connect();

    // Get transactions balances
    $transactions = get_user_monthly_balances($user_id, $conn);

    // Generate the table content (assuming the function returns an array)
    $table_content = "<h2>Transactions of $user_name</h2>";
    $table_content .= "<table>";
    $table_content .= "<tr><th>Month</th><th>Amount</th></tr>";
    foreach ($transactions as $month => $balance) {
        $date = convert_date_string($month);
        $table_content .= "<tr><td>$date</td><td>$balance</td></tr>";
    }
    $table_content .= "</table>";

    // Echo the table content (this will be sent as the response in the AJAX request)
    echo $table_content;
}
?>