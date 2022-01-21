<?php

include_once(__DIR__ . '/exeEditor.php');

$generatedExePath = './generated-exe/';

function cleanupDownloadDirectory($path)
{
    $list = scandir($path);
    $archiveFilesList = [];
    foreach ($list as $file) {
        if (substr($file, -4, 4) === '.zip') {
            $archiveFilesList[$file] = $file;
        }
    }
    sort($archiveFilesList);

    // remove older than 1 hour
    foreach ($archiveFilesList as $archiveFile) {
        $archiveFilePath = $path . $archiveFile;
        $timeSinceFileCreation = time() - filemtime($archiveFilePath);
        if ($timeSinceFileCreation > 3600) {
            unlink($archiveFilePath);
        }
    }

    // remove oldest, if there is over 10 files
    while (count($archiveFilesList) > 10) {
        $archiveFile = array_shift($archiveFilesList);
        $archiveFilePath = $path . $archiveFile;
        unlink($archiveFilePath);
    }
}

cleanupDownloadDirectory($generatedExePath);

$exeEditor = new ExeEditor();
$error = '';

if (isset($_POST['rsa'])) {
    $exeEditor->setRsa($_POST['rsa']);

    foreach ($exeEditor->getServices() as $key => $value) {
        if (isset($_POST[$key])) {
            $exeEditor->setService($key, (string)$_POST[$key]);
        }
    }

    if (isset($_FILES['client_exe']['tmp_name']) && is_uploaded_file($_FILES['client_exe']['tmp_name'])) {
        $exeFilePath = $_FILES['client_exe']['tmp_name'];

        $newExeZipFilePath = $generatedExePath . 'client_' . microtime(true) . '.zip';
        try {
            $progressText = $exeEditor->process($exeFilePath, $newExeZipFilePath);

            echo '<html><head><title>OTS IP Changer for Tibia 12+</title>' .
                '<link rel="stylesheet" type="text/css" href="style.css" media="screen"></head><body>';
            echo '<h2>CLICK TO DOWNLOAD CLIENT: <a href="' . $newExeZipFilePath . '">' . $newExeZipFilePath . '</a></h2>';
            echo '<br /><br />';
            echo '<h2>REPORT: </h2>' . $progressText;
            echo '</body></html>';
            return;
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        }
    } else {
        $error = 'You did not pick client.exe file or it was too big to upload';
    }
}

$serviceCategories = [
    'Basic' => [
        'loginWebService',
        'clientWebService',
    ],
    'Account' => [
        'createAccountUrl',
        'tibiaPageUrl',
        'accessAccountUrl',
        'tibiaStoreGetCoinsUrl',
        'getPremiumUrl',
        'lostAccountUrl',
    ],
    'Social' => [
        'twitchTibiaUrl',
        'youTubeTibiaUrl',
    ],
    'Other' => [
        'createTournamentCharacterUrl',
        'manualUrl',
        'faqUrl',
        'premiumFeaturesUrl',
        'limesurveyUrl',
        'hintsUrl',
        'cipSoftUrl',
        'crashReportUrl',
        'fpsHistoryRecipient',
        'tutorialProgressWebService',
        'tournamentDetailsUrl',
    ]
];

echo '<html>
<head>
<title>OTS IP Changer for Tibia 12+</title>
<link rel="stylesheet" type="text/css" href="style.css" media="screen">
</head>
<body>';
echo '<div class="title">OTS IP Changer for Tibia Client 12+</div>';

if (!empty($error)) {
    echo '<div class="error"><h3>Error occured!</h3><p>' . $error . '</p></div>';
}

echo '<form action="index.php" method="post" enctype="multipart/form-data">
Select file <b>client.exe</b> file:<br/>
<input type="file" name="client_exe"><br/>';

echo '<h3>RSA</h3><input type="text" class="rsa" name="rsa" value="' . htmlspecialchars($exeEditor->getRsa()) . '" />';

foreach ($serviceCategories as $category => $services) {
    echo '<h3>' . $category . '</h3>';
    foreach ($services as $key) {
        echo '<div class="service">' . $key . ':';
        echo '<input type="text" class="service" name="' . $key . '" value="' .
            htmlspecialchars($exeEditor->getService($key)) . '" /></div>';
    }
}
echo '<br>';
echo '<div class="submit"><input type="submit" value="Change IP in client.exe" name="submit"></div>';
echo '</form>';
echo '<br />Code available on <a href="https://github.com/gesior/ots-ip-changer-12">https://github.com/gesior/ots-ip-changer-12</a>';
echo '</body></html>';
