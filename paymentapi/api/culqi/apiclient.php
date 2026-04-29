<?php
/**
 * Cliente HTTP minimalista para la API REST de Culqi.
 *
 * No depende de Composer ni de ningún SDK. Usa cURL (extensión estándar de PHP).
 * En éxito devuelve el cuerpo decodificado (objeto JSON).
 * En error lanza una Exception cuyo mensaje es el cuerpo JSON crudo de Culqi
 * (con campos como "user_message", "merchant_message", "code", "type"...).
 * Esto preserva el contrato que ya espera el resto del código (getErrorMsg,
 * getErrorData, etc.) sin requerir try/catch nuevos.
 */
class mwmod_mw_paymentapi_api_culqi_apiclient {
	const BASE_URL = "https://api.culqi.com/v2";
	private $apiKey;

	function __construct($apiKey) {
		$this->apiKey = $apiKey;
	}

	/**
	 * Ejecuta una petición HTTP contra la API de Culqi.
	 *
	 * @param string $method GET|POST|PATCH|DELETE
	 * @param string $path   Ruta relativa, ej. "/charges"
	 * @param mixed  $body   Datos a enviar (array u objeto). Para GET se usa como query string.
	 * @return mixed Cuerpo JSON decodificado.
	 * @throws Exception Con el JSON crudo del error en getMessage().
	 */
	function request($method, $path, $body = null) {
		$url = self::BASE_URL . $path;
		$method = strtoupper($method);
		if ($method === "GET" && is_array($body) && !empty($body)) {
			$url .= (strpos($path, "?") === false ? "?" : "&") . http_build_query($body);
			$body = null;
		}
		$ch = curl_init($url);
		$headers = array(
			"Authorization: Bearer " . $this->apiKey,
			"Content-Type: application/json",
			"Accept: application/json",
		);
		$opts = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
		);
		if ($body !== null) {
			$opts[CURLOPT_POSTFIELDS] = json_encode($body);
		}
		curl_setopt_array($ch, $opts);
		$raw    = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err    = curl_error($ch);
		curl_close($ch);

		if ($err) {
			throw new Exception(json_encode(array(
				"object" => "error",
				"type"   => "network_error",
				"merchant_message" => $err,
				"user_message" => "No se pudo conectar con la pasarela de pago.",
			)), 0);
		}
		if ($status >= 200 && $status < 300) {
			$decoded = json_decode($raw);
			if ($decoded === null && $raw !== "" && strtolower($raw) !== "null") {
				throw new Exception(json_encode(array(
					"object" => "error",
					"type"   => "decode_error",
					"merchant_message" => "Respuesta no es JSON válido.",
					"user_message" => "Respuesta inválida de la pasarela de pago.",
					"raw" => substr($raw, 0, 500),
				)), $status);
			}
			return $decoded;
		}
		// Error HTTP. Culqi devuelve JSON con la estructura de error.
		if (!$raw) {
			$raw = json_encode(array(
				"object" => "error",
				"type"   => "http_error",
				"merchant_message" => "HTTP $status sin cuerpo de respuesta.",
				"user_message" => "Error en la pasarela de pago.",
			));
		}
		throw new Exception($raw, $status);
	}
}
?>
