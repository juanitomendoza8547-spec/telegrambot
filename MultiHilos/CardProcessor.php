<?php


class CardProcessor
{
    private $cards;
    private $nodeScriptPath;
    private $executionTime;
    
    public function __construct($cards, $nodeScriptPath)
    {
        $this->cards = $cards;
        $this->nodeScriptPath = $nodeScriptPath;
    }

    // Método para procesar las tarjetas
    public function processCards()
    {
        // Divide la cadena en líneas
        $cardData = explode("\n", $this->cards);

        $startTime = microtime(true);

        // Unir los datos con un separador
        $cardDataString = implode(';', $cardData);

        // Comando para ejecutar el script Node.js
        $command = "node {$this->nodeScriptPath} \"$cardDataString\"";

        // Ejecutar el comando y obtener la respuesta
        $jsonDataString = shell_exec($command);

        $endTime = microtime(true);
        $this->executionTime = substr($endTime - $startTime, 0, 4);

        // Inicializar la variable para almacenar todos los mensajes
        $allMessages = "";

        // Buscar todas las ocurrencias de "message" en el JSON concatenado
        preg_match_all('/"message"\s*:\s*"([^"]+)"/', $jsonDataString, $matches);

        // Iterar sobre cada mensaje encontrado y concatenarlo
        foreach ($matches[1] as $message) {
            $allMessages .= "<b>[<a href='https://t.me/ritabotchk/35'>ϟ</a>]</b> ".$message."\n"."━━━━━━━━━━━━━━━━"."\n";
        }

        // Retornar todos los mensajes concatenados
        return "<b>Gate Special: >_$-BradesCard Balance MX - mass</b>\n━━━━━━━━━━━━━━━━\n".$allMessages."<b>Time:</b> ". $this->getExecutionTime();
    }

    // Método para obtener el tiempo de ejecución
    public function getExecutionTime()
    {
        return $this->executionTime;
    }
}


if (isset($_GET['cards'])) {
    // Verificar si $_GET['cards'] es una cadena
    $data = $_GET['cards'];

    if (is_string($data)) {
        // Dividir los datos por saltos de línea (o por otro delimitador como ';')
        $cardArray = explode("\n", $data); // Aquí puedes usar ';' si las tarjetas están separadas por ';'

        $nodeScript = '/home/arturo/www/MultiHilos/script.js';

        // Crear una instancia de CardProcessor con los datos recibidos
        $processor = new CardProcessor($data, $nodeScript);

        // Procesar las tarjetas y almacenar el mensaje
        $messages = $processor->processCards();

        // Devolver el mensaje
  echo $messages;
    } else {
        echo 'Error: El formato de datos no es válido.';
    }
} else {
    echo 'No se recibieron tarjetas.';
}
   

