<?php
require __DIR__ . '/vendor/autoload.php';

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP Quickstart');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $client->setAuthConfig('client_secret.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = expandHomeDirectory('credentials.json');
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path)
{
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Sheets($client);


$spreadsheetId = 'google_sheet_id';
$range = 'consumer app!B1:H';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

$lang = $values[0];

$num2 = 2;

array_shift($lang);
array_shift($lang);
array_shift($lang);
array_shift($values);




foreach ($lang as $language) {

  $num = 0;
  $num1 = 0;
  $num2 = $num2 + 1;

  $Android = fopen("Android/" . $language . "/strings.xml", "w");
  $IOS = fopen("IOS/" . $language . "/Localizable.strings", "w");

  fwrite($Android, "<resources> \n");

  


  foreach (array_values(array_unique(array_column($values, 1))) as $sec) {

    $num = $num - $num1;

    fwrite($Android, "\n" . "<!-- " . $sec . " -->\n\n");
    fwrite($IOS, "\n" . "/* " . $sec . " */\n\n");

    while ($num <= sizeof($values)) {

      $var = $values[$num1];

      if(!isset($var[1])) {
        $num1 = $num1 + 1;
        $num = $num + 1;
        continue;}

      if(!isset($var[$num2])) {$var[$num2]=null;} 

      if ($var[1] === $sec) {
        if ($var[2] === "ios") {
          fwrite($IOS, '"' . $var[0] . '" = "' . $var[$num2] . '";' . "\n" );
        } elseif ($var[2] === "android") {
          fwrite($Android, '    <string name="' . $var[0] . '">' . $var[$num2] . '</string>' . "\n");
        } else {
          fwrite($Android, '    <string name="' . $var[0] . '">' . $var[$num2] . '</string>' . "\n");
          fwrite($IOS, '"' . $var[0] . '" = "' . $var[$num2] . '";' . "\n" );
        }



          $num1 = $num1 + 1;
        }

      $num = $num + 1;
    }
    
   
    
    
  }
  fwrite($Android, "</resources>");
  fclose($Android);
  fclose($IOS);
}


