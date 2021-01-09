<?php
function main(array $args) : array {
	
	require 'config.php';

    if (! array_key_exists("token", $args) || ! array_key_exists("url", $args) ) {
        $json_result = json_decode($emptyForm);
        print_r($json_result);
        return array("body" => $json_result);
    }

	/* parametri per la richiesta */

    // Default values for token e url
    if (! array_key_exists("token", $args)) {
        $args['token'] = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhdXRoX2h1YiIsImlhdCI6MTYwMTk5NjUyMCwiZXhwIjoxNjAxOTk4MzIwLCJqdGkiOiI0NzQ4ZTcxYS1iODkxLTQwZWUtODAxOS04MzUwNmM1ZmZhMzkiLCJjbGllbnRfaWQiOiJhMTY1YzM1YmEzOTgxZGIwMDVkYThlODU4MjQ4M2M1OTM2ZjE3YjUwYjI3M2Y0MzM3ZTM3ZTYzZDFhNDBhMzc0IiwidXNlciI6eyJpZCI6MzI2LCJlbWFpbCI6Im1pbGFub190ZXN0QGp3dC5pdCIsIm5vbWUiOiJ0ZXN0IiwiY29nbm9tZSI6InRlc3QifX0.0poHR1oQKWv5YQGyWU4T9DksBr-GdSc3--jAcFn3Vqs";
    } 
    if (! array_key_exists("url", $args)) {
        $args['url'] = "https://mitest.soluzionipa.it/portal/servizi/pagamenti/ws/10/stato_pagamenti";
    }

	// parametri da inviare con la richiesta
	$params = array(
	  "applicazione" => "pagamenti",
	  "id_univoco_dovuto" => "100",
	  "cf_pagatore" => "sccndr68t05l483l",
	  "stato" => "Disponibile",
	);

	/* chiamata e gestione risposta */

	// chiamo get_pagamenti per ottenere la lista formattata
	$json_result = get_pagamenti($args['token'], $args['url'], $params);

	//if($json_result===false) {
	// si è verificato un errore
	//	echo "Si è verificato un errore durante la chiamata.\n";
	//	} else {
		// restituisco la lista dei pagamenti
		
		$test_result  = array (
			"amount" => 0,
			"due_date" => 44197,
			"fiscal_code" => "ISPXNB32R82Y766F",
			"invalid_after_due_date" => false,
			"markdown" => "# Welcome, Giovanni Rossi\\\\nYour fiscal code is ISPXNB32R82Y766F\\\\nI hope you will enjoy IO.",
			"notice_number" => 1,
			"subject" => "Welcome to IO, Giovanni"
		);
		//$test_result_1['data'] = $test_result;
		$test_result_1 = array ("data" => $test_result);
		$json_result = json_encode($test_result_1);

		print_r($json_result);
		
		return array(
			"body" => $json_result
		);
	//}
}

/* funzione per il recupero della lista pagamenti
 * riceve in input: 
 *  - token di autenticazione
 *  - l'url su cui effettuare la richiesta
 *  - i parametri da utilizzare (array)
 *  - il tipo di output (default array, se true restituisce json)
 * restituisce una lista di pagamenti, oppure false in caso di errore 
 */
function get_pagamenti($token, $url, $params) {
  $curl = curl_init();
  $result = false;

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query($params),
    CURLOPT_HTTPHEADER => array(
      "Content-Type: application/x-www-form-urlencoded",
      "Authorization: Bearer $token"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);

  if(strlen($response)<1) {
    $result = false;
  } else {
    $response = json_decode($response, true);

    if($response["esito"]!="ok") {
      $result = $response;
      $result = json_encode($result); 
    } else {
      $dati_estratti = array();
      foreach($response["lista_pagamenti"] as $pagamento) {
        $dati_estratti[] = array(
          "amount" => round($pagamento["importo"]),
          "due_date" => date("Y-m-dTH:i:s.000Z"),
          "fiscal_code" => $pagamento["cf_versante"],
          "invalid_after_due_date" => false,
          "markdown" => "Buongiorno {$pagamento["nome_versante"]}!<br>E' stato emesso un avviso di pagamento {$pagamento["codice_tipo_dovuto"]} a tuo nome!<br>Puoi pagarlo direttamente dall'app oppure consultarne il dettagli nella tua [area riservata](https://civilianext.soluzionipa.it/portal/servizi/pagamenti) sul nostro sito",
          "notice_number" => "001{$pagamento["iuv"]}",
          "subject" => "Ciao {$pagamento["nome_versante"]}!",
          "time_to_live" => 3600,
        );
      }
      $result = json_encode($dati_estratti); 
    }
  }
  return $result;
}

?>