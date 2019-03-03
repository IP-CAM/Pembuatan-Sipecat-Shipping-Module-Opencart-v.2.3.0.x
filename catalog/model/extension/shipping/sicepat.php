<?php
class ModelExtensionShippingsicepat extends Model {
	public function getQuote($address) {
		$this->load->language('extension/shipping/sicepat');

		$quote_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");				
		
		foreach ($query->rows as $result) {									
			if ($this->config->get('sicepat_' . $result['geo_zone_id'] . '_status')) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");								
				if ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}						

			if ($status) {
				$cost = '';
				$weight = $this->cart->getweight();							
				$rates = explode(',', $this->config->get('sicepat_' . $result['geo_zone_id'] . '_rate'));
				
				foreach ($rates as $rate) {
					$data = explode(':', $rate);
					if( trim($address['city']) == trim($data[0]) ){	
					     $origin = $this->config->get('sicepat_origin');	

					     try {
					    	$response = json_decode( $this->getTarif("?origin=$origin&destination=$data[1]&weight=$weight") ,false) ;
					     	
					     	if($response->sicepat->status->code==200){

					     		foreach ($response->sicepat->results as  $value) {
					     			
					     			switch ($value->service) {
					     				case 'REG':
					     					$type = $value->service ;
					     					$description = $value->description ;
					     					$cost =$value->tariff;
					     					break;
					     				case 'BEST':
					     					$type = $value->service ;
					     					$description = $value->description ;
					     					$cost =$value->tariff;
					     					break;
					     				
					     				default:
					     					$type = $value->service ;
					     					$description = $value->description ;
					     					$cost ='';
					     					break;
					     			}

					     			if ((string)$cost != '') {
									$quote_data['sicepat_' . $result['geo_zone_id']. "_$type"] = array(
										'code'         => 'sicepat.sicepat_' . $result['geo_zone_id']. "_$type",
										'title'        => $result['name']." $type ( $description) " . '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->config->get('config_weight_class_id')) . ')',
										'cost'         => $cost,
										'tax_class_id' => $this->config->get('sicepat_tax_class_id'),
										'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('sicepat_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
									);
								}
					     		}
					     		
					     		
					     	}
					     		

					    	
					     }
					      catch (Exception $e) {
					     	
					     }
					     
					 }
					
				}								

			}
		}

		$method_data = array();

		if ($quote_data) {
			$method_data = array(
				'code'       => 'sicepat',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('sicepat_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}

	private function getTarif($params=null, $data=null, $extraHeaders=array()){
	        if(isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME']) ){
	          $uaApp = str_replace( '.', '_', $_SERVER['SERVER_NAME']);
	        }

	        $apiKey = $this->config->get('sicepat_api_key')  ;
	        $headers =  array(
	            'Accept: application/json',
	            'Content-type: application/json',
	            'api-key: '. $apiKey,
	        );

	        // if(!empty($extraHeaders)) $headers = array_merge($headers, $extraHeaders);

	        $url = "http://api.sicepat.com/customer/tariff" . $params ;

	        print_r($headers,$params);

	        $curlOpts = array(
	            CURLOPT_URL => $url,
	            CURLOPT_HEADER => 1,
	            CURLOPT_RETURNTRANSFER => TRUE,
	            CURLOPT_TIMEOUT => 0,
	            CURLOPT_SSL_VERIFYPEER => 0,
	            CURLOPT_ENCODING => 'gzip,deflate',
	            CURLOPT_HTTPHEADER => $headers
	        );

	        $ch = curl_init();
	        curl_setopt_array($ch, $curlOpts);
	        if(!empty($data)){
	            curl_setopt($ch, CURLOPT_POST, true);
	            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	        }

	        $response = curl_exec($ch);
	        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	        $header = substr($response, 0, $header_size);
	        $body = substr($response, $header_size);
	        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	        if(curl_errno($ch) || (int)$status_code !== 200){
	            $this->log->write('=======================================================================');
	            $this->log->write(sprintf("Failed to get tariff data, status code: %s", $status_code));
	            $this->log->write($header);
	            $this->log->write($body);
	            $this->log->write('=======================================================================');
	            curl_close($ch);
	            return false;
	        }
	        curl_close($ch);
	        return (!empty($body) ? $body : false);
	     }
}