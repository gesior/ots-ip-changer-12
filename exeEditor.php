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
            '12.20' => [
                // 12.20
                "\x84\xC0\x74\x04\xC6\x47\x05\x01" => "\x84\xC0\x90\x90\xC6\x47\x05\x01",
            ],
            '12.40' => [
                // 12.40.9997
                "\xC6\x45\xD7\x00\xC6\x45\xCF\x00" => "\xC6\x45\xD7\x00\xC6\x45\xCF\x01",
            ],
            '12.72' => [
                // 12.72.11272
                "\x8D\x8D\x70\xFF\xFF\xFF\x75\x0E" => "\x8D\x8D\x70\xFF\xFF\xFF\xEB\x0E",
            ],
            '12.81' => [
                // 12.81.11476
                "\x8D\x8D\x70\xFF\xFF\xFF\x75\x0E" => "\x8D\x8D\x70\xFF\xFF\xFF\xEB\x0E",
            ],
            '12.85' => [
                // 12.85
                "\x8D\x8D\x70\xFF\xFF\xFF\x75\x0E" => "\x8D\x8D\x70\xFF\xFF\xFF\xEB\x0E",
            ],
            '12.86' => [
                // 12.86
                "\x8D\x4D\x84\x75\x0E\xE8\x1B\x41" => "\x8D\x4D\x84\xEB\x0E\xE8\x1B\x41",
            ],
            '12.87' => [
                // 12.87
                "\x8D\x4D\x84\x75\x0E\xE8\xF8\x24" => "\x8D\x4D\x84\xEB\x0E\xE8\xF8\x24",
            ],
            '12.90' => [
                // 12.90
                "\x8D\x4D\x8C\x75" => "\x8D\x4D\x8C\xEB",
            ],
            '12.91' => [
                // 12.91.12329
                "\x00\x00\x00\x8D\x4D\x80\x75\x0E\xE8" => "\x00\x00\x00\x8D\x4D\x80\xEB\x0E\xE8",
            ],
            '13.05' => [
                // 13.05.12715
                "\x8D\x4D\xB4\x75" => "\x8D\x4D\xB4\xEB",
            ],
            '13.10' => [
                // 13.10.12858
                "\x8D\x4D\xB8\x75" => "\x8D\x4D\xB8\xEB",
                // 13.10.12892
                "\x8D\x4D\xB8\x75\x0E\xE8\xEA\x42\xF3" => "\x8D\x4D\xB8\xEB\x0E\xE8\xEA\x42\xF3",
                // 13.11.12985
                "\x8D\x4D\xC0\x51\x3B\x45\xE4\x74\x0D\x8B\xC8" => "\x8D\x4D\xC0\x51\x3B\x45\xE4\xEB\x0D\x8B\xC8",
            ],
            '13.20' => [
                // 13.20.unknown
                "\x75\x0E\xE8\xB5" => "\xEB\x0E\xE8\xB5",
                // 13.20.13709
                "\xFF\xFF\xFF\x75\x0E\xE8\xDF" => "\xFF\xFF\xFF\xEB\x0E\xE8\xDF",
            ],
            '13.21' => [
                // 13.21.13810
                "\xFF\xFF\xFF\x75\x0E\xE8\xCF" => "\xFF\xFF\xFF\xEB\x0E\xE8\xCF",
            ],
            '13.22' => [
                // 13.22.14242
                "\x8D\x4D\xB4\x75\x0E\xE8" => "\x8D\x4D\xB4\xEB\x0E\xE8",
            ],
            '13.34' => [
                // 13.34.14631
                "\x8D\x4D\xB4\x75\x0E\xE8" => "\x8D\x4D\xB4\xEB\x0E\xE8",
            ],
            '13.40' => [
                // 13.40.54ea79
                "\x8D\x4D\xB4\x75\x0E\xE8" => "\x8D\x4D\xB4\xEB\x0E\xE8",
            ],
            '14.11' => [
                // 14.11.0fbf6c
                "\x00\x00\x00\x75\x0F\xE8\xC3\x43\xEF\xFF" => "\x00\x00\x00\xEB\x0F\xE8\xC3\x43\xEF\xFF",
            ],
            '15.03' => [
                // 15.03.afe753
                "\x75\x0F\xE8\xDF" => "\xEB\x0F\xE8\xDF",
            ],
            '15.11' => [
                // 15.11.57e218
                "\x75\x0F\xE8\xC1\xEE\xFF" => "\xEB\x0F\xE8\xC1\xEE\xFF",
            ],
            '15.20' => [
                // 15.20.8007a5
                "\x75\x0F\xE8\x73\x20\xEE\xFF" => "\xEB\x0F\xE8\x73\x20\xEE\xFF",
            ],
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

        $progressText = '';
        foreach ($this->battlEyeDisableBytes as $tibiaClientVersion => $patches) {
            if (strpos($file, $tibiaClientVersion) !== false) {
                foreach ($patches as $fromBytes => $toBytes) {
                    if (strpos($file, $fromBytes) !== false) {
                        $file = str_replace($fromBytes, $toBytes, $file);
                        $progressText .= '<div class="action">BattlEye warning disabled for version ' . $tibiaClientVersion . '</div>';
                        break;
                    }
                }
                break;
            }
        }

        $newClientExe = '';
        $matches = [];
        $lines = explode("\r\n", $file);

        foreach ($lines as $i => $line) {
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
