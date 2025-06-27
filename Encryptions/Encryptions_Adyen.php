<?php


class Encryptions
{
    private $data;
    private $adyenVersion;
    private $response;

    private $nodeScriptPath = '/home/arturo/www/Encryptions/src/middleware/encryptions/adyen.js';

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function setAdyenVersion($version)
    {
        $this->adyenVersion = $version;
    }

    public function execute()
    {
        if (empty($this->nodeScriptPath)) {
            $this->response = "Error: No se ha definido la ruta del script Node.js.";
            return;
        }

        $adyenkey = escapeshellarg($this->data['adyenkey'] ?? '');
        $card = escapeshellarg($this->data['card'] ?? '');
        $month = escapeshellarg($this->data['month'] ?? '');
        $year = escapeshellarg($this->data['year'] ?? '');
        $cvv = escapeshellarg($this->data['cvv'] ?? '');
        $version = escapeshellarg($this->adyenVersion ?? '');
        $ppkey = escapeshellarg($this->data['ppkey'] ?? '');
        $domain = escapeshellarg($this->data['domain'] ?? '');

        $command = "node {$this->nodeScriptPath} {$adyenkey} {$card} {$month} {$year} {$cvv} {$version} {$ppkey} {$domain}";

        $this->response = shell_exec($command);

        if ($this->response === null) {
            $this->response = "Error: No se pudo ejecutar el comando o no se obtuvo salida.";
        }
    }

    public function getResponse()
    {
        $result = json_decode($this->response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return "Error al decodificar JSON: " . json_last_error_msg();
        } elseif (isset($result['error'])) {
            return "Error: " . $result['error'];
        } else {
            return $result;
        }
    }
}


$encryption = new Encryptions();

$encryption->setData([
    "adyenkey" => "10001|CB416919812EC5ACA088655B3B974D3F35BE5D7AB728466D53FBF6618DDE6AA20A6EB97749AAD36AC5A6A997D7198AFFF860F57A955F7F61F0BC0443E0AC3AB0F5270487AAFEF77EED987A30BFBEB451159E7C52CEE102969295BE17788C073CE15058A747A556CB1F41202B16A70A852302A236C04BB33AC8A732A630F72A2AEC31E446FAA1497EF730C93134E5C624E8C8CB5998DFE257884D76E511B6A2120335C5653559A8DF2BA67BCF67D40B7AAE6025D7A7FAACF967CBC5616AE433BBEA0A11943A39E65C8F9DD0BB2A25663E9C3F70B7C4E4A74E9BC5EA340F9C0C9D017D290E530B4D2A8F2564F85B12DE45E3318FEDEF9D469038C3DC5528E41D45",
    "card" => '4381086433445236',
    "month" => '11',
    "year" => '2025',
    "cvv" => '322',
]);
$encryption->setAdyenVersion('v2');
$encryption->execute();
print_r($response = $encryption->getResponse());



