<?php


if (isset($_GET['cards'])) {
    $data = $_GET['cards'];

    if (is_string($data)) {
        // Divide las tarjetas en un array usando salto de línea como delimitador
        $cardArray = explode("\n", $data); // O ';' si están separadas por ';'

        // Une los datos de tarjetas en una sola cadena separada por ';'
        $cardDataString = implode(';', $cardArray);

        // Ruta del script Node.js
        $nodeScript = '/home/arturo/www/MultiHilos/script2.js';

        // Comando para ejecutar el script Node.js
        $command = "node $nodeScript \"$cardDataString\"";

        // Ejecutar el comando
        echo $jsonDataString = shell_exec($command);
    }
}
