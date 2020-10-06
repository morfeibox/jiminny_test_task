<?php
include '../includes/AnalizeText.php';
$analyzer = new AnalizeText();

echo "Start \n";
$user_string = file_get_contents("https://raw.githubusercontent.com/jiminny/join-the-team/master/assets/user-channel.txt");
$customer_string = file_get_contents("https://raw.githubusercontent.com/jiminny/join-the-team/master/assets/customer-channel.txt");

if ($user_string === false) {
    echo "Could not read the user-channel.txt";
}
if ($customer_string === false) {
    echo "Could not read the customer-channel.txt";
}

$document = array(
    'user_channel' => $user_string,
    'customer_channel' => $customer_string

);
$analyzer->detectInput($document);

echo "Finish importing data \n";

?>
