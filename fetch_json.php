<?php

/* parametri per la richiesta */

// url per il recupero del token
$tokenUrl = "https://starttest.soluzionipa.it/auth_hub/oauth/token";

// informazioni di autenticazione per il recupero del token
$tokenParams = array(
  "username" => "milano_test@jwt.it",
  "password" => "PswMilano1",
  "grant_type" => "password",
);

// token di autenticazione
$token = get_token($tokenUrl, $tokenParams);

// url da interrogare
$url = "https://mitest.soluzionipa.it/portal/servizi/pagamenti/ws/10/stato_pagamenti";
// parametri da inviare con la richiesta
$params = array(
  "applicazione" => "pagamenti",
  "id_univoco_dovuto" => "100",
  "cf_pagatore" => "sccndr68t05l483l",
  "stato" => "Disponibile",
);
// formato della risposta (false=array, true=json)
$json = true;


/* chiamata e gestione risposta */

// chiamo get_pagamenti per ottenere la lista formattata
$lista_pagamenti_formattata = get_pagamenti($token, $url, $params, $json);

if($lista_pagamenti_formattata===false) {
  // si è verificato un errore
  echo "Si è verificato un errore durante la chiamata.\n";
} else {
  // mostro la lista dei pagamenti
  if($json) {
    header('Content-Type: application/json');
    echo $lista_pagamenti_formattata;
    echo "\n";
  } else {
    print_r($lista_pagamenti_formattata);
  }
}

/* funzione per il recupero della lista pagamenti
 * riceve in input: 
 *  - token di autenticazione
 *  - l'url su cui effettuare la richiesta
 *  - i parametri da utilizzare (array)
 *  - il tipo di output (default array, se true restituisce json)
 * restituisce una lista di pagamenti, oppure false in caso di errore 
 */
function get_pagamenti($token, $url, $params, $json=false) {
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
      if($json) { $result = json_encode($result); }
    } else {
      $dati_estratti = array();
      foreach($response["lista_pagamenti"] as $pagamento) {
        $dati_estratti[] = array(
          "amount" => str_replace(".","",$pagamento["importo"]),
          "due_date" => date("Y-m-dTH:i:s.000Z"),
          "fiscal_code" => $pagamento["cf_versante"],
          "invalid_after_due_date" => false,
          "markdown" => "Buongiorno {$pagamento["nome_versante"]}!<br>E' stato emesso un avviso di pagamento {$pagamento["codice_tipo_dovuto"]} a tuo nome!<br>Puoi pagarlo direttamente dall'app oppure consultarne il dettagli nella tua [area riservata](https://civilianext.soluzionipa.it/portal/servizi/pagamenti)",
          "notice_number" => "001{$pagamento["iuv"]}",
          "subject" => "Ciao {$pagamento["nome_versante"]}!",
          "time_to_live" => 3600,
        );
      }
      $result = $dati_estratti;
      if($json) {
		$result = array ("data" => $dati_estratti);
		$result = json_encode($result); 
	  }
    }
  }
  return $result;
}

/* funzione per il recupero della lista pagamenti
 * riceve in input: 
 *  - l'url su cui effettuare la richiesta
 *  - i parametri da utilizzare (array)
 * restituisce il token, oppure false in caso di errore 
 */
function get_token($url, $params) {
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
      "Content-Type: application/x-www-form-urlencoded"
    ),
  ));

  $response = curl_exec($curl);
  if(curl_errno($curl))
	{
		echo 'Curl error: ' . curl_error($curl);
	}
  
  curl_close($curl);
  if(strlen($response)<1) {
    $result = false;
  } else {
    $response = json_decode($response, true);
    $result = $response["access_token"];
  }

  return $result;
}

?>