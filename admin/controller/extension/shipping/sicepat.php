<?php
class ControllerExtensionShippingsicepat extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/shipping/sicepat');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('sicepat', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_rate'] = $this->language->get('entry_rate');
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_api_key'] = $this->language->get('entry_api_key');				$data['entry_origin'] = $this->language->get('entry_origin');

		$data['help_rate'] = $this->language->get('help_rate');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/sicepat', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/sicepat', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true);

		$this->load->model('localisation/geo_zone');

		$geo_zones = $this->model_localisation_geo_zone->getGeoZones();

		foreach ($geo_zones as $geo_zone) {
			if (isset($this->request->post['sicepat_' . $geo_zone['geo_zone_id'] . '_rate'])) {
				$data['sicepat_' . $geo_zone['geo_zone_id'] . '_rate'] = $this->request->post['sicepat_' . $geo_zone['geo_zone_id'] . '_rate'];
			} else {
				$data['sicepat_' . $geo_zone['geo_zone_id'] . '_rate'] = $this->config->get('sicepat_' . $geo_zone['geo_zone_id'] . '_rate');
			}

			if (isset($this->request->post['sicepat_' . $geo_zone['geo_zone_id'] . '_status'])) {
				$data['sicepat_' . $geo_zone['geo_zone_id'] . '_status'] = $this->request->post['sicepat_' . $geo_zone['geo_zone_id'] . '_status'];
			} else {
				$data['sicepat_' . $geo_zone['geo_zone_id'] . '_status'] = $this->config->get('sicepat_' . $geo_zone['geo_zone_id'] . '_status');
			}
		}

		$data['geo_zones'] = $geo_zones;

		if (isset($this->request->post['sicepat_tax_class_id'])) {
			$data['sicepat_tax_class_id'] = $this->request->post['sicepat_tax_class_id'];
		} else {
			$data['sicepat_tax_class_id'] = $this->config->get('sicepat_tax_class_id');
		}				if (isset($this->request->post['sicepat_origin'])) {			$data['sicepat_origin'] = $this->request->post['sicepat_origin'];		} else {			$data['sicepat_origin'] = $this->config->get('sicepat_origin');		}				$data['origins'] = json_decode(file_get_contents(HTTP_CATALOG."/catalog/view/javascript/sicepat_origin.json"),true);
		
		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['sicepat_api_key'])) {
			$data['sicepat_api_key'] = $this->request->post['sicepat_api_key'];
		} else {
			$data['sicepat_api_key'] = $this->config->get('sicepat_api_key');
		}				if (isset($this->request->post['sicepat_api_key'])) {
			$data['sicepat_api_key'] = $this->request->post['sicepat_api_key'];
		} else {
			$data['sicepat_api_key'] = $this->config->get('sicepat_api_key');
		}				
		if (isset($this->request->post['sicepat_status'])) {
			$data['sicepat_status'] = $this->request->post['sicepat_status'];
		} else {
			$data['sicepat_status'] = $this->config->get('sicepat_status');
		}

		if (isset($this->request->post['sicepat_sort_order'])) {
			$data['sicepat_sort_order'] = $this->request->post['sicepat_sort_order'];
		} else {
			$data['sicepat_sort_order'] = $this->config->get('sicepat_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/sicepat', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/sicepat')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
