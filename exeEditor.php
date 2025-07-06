<?php


class ExeEditor
{
    private $battlEyeDisableBytes;
    private $rsa;
    private $services;

    public function __construct()
    {
        // https://otland.net/threads/disable-battleye-error-12-20.266831/
        $this->battlEyeDisableBytes = [
            "\x84\xC0\x74\x04\xC6\x47\x05\x01" => "\x84\xC0\x90\x90\xC6\x47\x05\x01",
            "\xC6\x45\xD7\x00\xC6\x45\xCF\x00" => "\xC6\x45\xD7\x00\xC6\x45\xCF\x00",
            "\x8D\x8D\x70\xFF\xFF\xFF\x75\x0E" => "\x8D\x8D\x70\xFF\xFF\xFF\xEB\x0E",
        ];

        $this->rsa = '9B646903B45B07AC956568D87353BD7165139DD7940703B03E6DD079399661B4' .
            'A837AA60561D7CCB9452FA0080594909882AB5BCA58A1A1B35F8B1059B72B12' .
            '12611C6152AD3DBB3CFBEE7ADC142A75D3D75971509C321C5C24A5BD51FD460' .
            'F01B4E15BEB0DE1930528A5D3F15C1E3CBF5C401D6777E10ACAAB33DBE8D5B7FF5';

        $this->services = [
            'loginWebService' => 'http://127.0.0.1/login.php',
            'clientWebService' => 'http://127.0.0.1/login.php',
            'tibiaPageUrl' => '',
            'tibiaStoreGetCoinsUrl' => '',
            'getPremiumUrl' => '',
            'createAccountUrl' => '',
            'createTournamentCharacterUrl' => '',
            'accessAccountUrl' => '',
            'lostAccountUrl' => '',
            'manualUrl' => '',
            'faqUrl' => '',
            'premiumFeaturesUrl' => '',
            'limesurveyUrl' => '',
            'hintsUrl' => '',
            'cipSoftUrl' => '',
            'twitchTibiaUrl' => '',
            'youTubeTibiaUrl' => '',
            'crashReportUrl' => '',
            'fpsHistoryRecipient' => '',
            'tutorialProgressWebService' => '',
            'tournamentDetailsUrl' => '',
        ];
    }

    function process($inputFilePath, $outfitFilePath)
    {
        if (strlen($this->getRsa()) !== 256) {
            throw new RuntimeException('RSA key length must be 256 characters');
        }
        foreach ($this->services as $key => $value) {
            if ($value !== '' && substr($value, 0, 7) !== 'http://' && substr($value, 0, 8) !== 'https://') {
                throw new RuntimeException(
                    '"' . $key . '" has invalid value. Every URL must start with "http://" or "https://".'
                );
            }
        }

        $file = file_get_contents($inputFilePath);

        $newClientExe = '';
        $matches = [];
        $lines = explode("\r\n", $file);

        $progressText = '';
        foreach ($lines as $i => $line) {
            foreach ($this->battlEyeDisableBytes as $fromBytes => $toBytes) {
                if (strpos($line, $fromBytes) !== false) {
                    $line = str_replace($fromBytes, $toBytes, $line);
                    $progressText .= '<div class="action">BattlEye warning disabled</div>';
                }
            }

            foreach ($this->services as $key => $value) {
                if ($value !== '') {
                    if (strpos($line, $key) === 0) {
                        $oldValue = substr($line, strlen($key) + 1);
                        $fillBytes = strlen($oldValue) - strlen($value);
                        if ($fillBytes < 0) {
                            throw new RuntimeException(
                                'Defined "' . $key . '" value "' . $value . '" is longer than original value "' . $oldValue . '". Cannot replace it.'
                            );
                        }
                        $line = $key . '=' . $value . str_repeat("\x20", $fillBytes);

                        $progressText .= '<div class="action">"' . $key . '" replaced</div>';
                        $progressText .= '<div class="old_value">Found: ' . $oldValue . '</div>';
                        $progressText .= '<div class="new_value">Replaced with: ' . $value . '</div>';
                    }
                }
            }

            if (preg_match('/[0-9A-F]{256}/', $line, $matches)) {
                foreach ($matches as $possibleRSA) {
                    $possibleRsaWithNulls = "\x00" . $possibleRSA . "\x00";
                    if (strpos($line, $possibleRsaWithNulls) !== false) {
                        $newRsaWithNulls = "\x00" . $this->rsa . "\x00";
                        $line = str_replace($possibleRsaWithNulls, $newRsaWithNulls, $line);

                        $progressText .= '<div class="action">RSA KEY REPLACED</div>';
                        $progressText .= '<div class="old_value">Old RSA: ' . $possibleRSA . '</div>';
                        $progressText .= '<div class="new_value">New RSA: ' . $this->rsa . '</div>';
                    }
                }
            }

            $newClientExe .= $line;
            // some client .exes end with "\r\n" and some not, we must detect it
            if ($i < count($lines) - 1 || empty($line)) {
                $newClientExe .= "\r\n";
            }
        }

        $zip = new ZipArchive;
        $res = $zip->open($outfitFilePath, ZipArchive::CREATE);
        if ($res === true) {
            $zip->addFromString('client.exe', $newClientExe);
            $zip->close();
        } else {
            throw new RuntimeException('Failed to save in ZIP.');
        }
        return $progressText;
    }

    public function getRsa()
    {
        return $this->rsa;
    }

    public function setRsa($rsa)
    {
        $this->rsa = $rsa;
    }

    public function getServices()
    {
        return $this->services;
    }

    public function getService($key)
    {
        return $this->services[$key];
    }

    public function setService($key, $value)
    {
        if (isset($this->services[$key])) {
            $this->services[$key] = $value;
        }
    }
}
