<?php

/**
 * Admin access module model class.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Settings_AdminAccess_Module_Model class.
 */
class Settings_AdminAccess_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * {@inheritdoc}
	 */
	public $name = 'AdminAccess';

	/**
	 * {@inheritdoc}
	 */
	public $baseTable = 'a_#__settings_modules';

	/**
	 * {@inheritdoc}
	 */
	public $baseIndex = 'id';

	/**
	 * {@inheritdoc}
	 */
	public $listFields = [
		'name' => 'FL_MODULE_NAME',
		'user' => 'FL_ADMIN',
		'status' => 'FL_ACTIVE'
	];

	/**
	 * {@inheritdoc}
	 */
	public function getListFields(): array
	{
		$fields = [];
		foreach (array_keys($this->listFields) as $fieldName) {
			$fields[$fieldName] = $this->getFieldInstanceByName($fieldName);
		}
		return $fields;
	}

	/**
	 * Function gives list fields for save.
	 *
	 * @return array
	 */
	public function getFieldsForSave(): array
	{
		return ['user', 'status'];
	}

	/**
	 * Gets field instance by name.
	 *
	 * @param string $name
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		if (!isset($this->fields[$name])) {
			$moduleName = $this->getName(true);
			$params = ['column' => $name, 'name' => $name, 'label' => $this->listFields[$name] ?? '', 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => '', 'isEditableReadOnly' => false, 'maximumlength' => '255', 'sort' => true];
			switch ($name) {
				case 'name':
					$params['uitype'] = 16;
					$params['table'] = $this->getBaseTable();
					$modules = (new \App\Db\Query())->from($this->getBaseTable())->select(['name'])->column();
					foreach ($modules as $module) {
						$params['picklistValues'][$module] = \App\Language::translate($module, $module);
					}
					break;
				case 'status':
					$params['uitype'] = 56;
					$params['typeofdata'] = 'C~O';
					$params['table'] = $this->getBaseTable();
					break;
				case 'user':
					$params['uitype'] = 33;
					$params['typeofdata'] = 'V~O';
					$params['sort'] = 'false';
					$params['table'] = 'a_#__settings_access';
					foreach ($this->getUsers() as $userId) {
						$params['picklistValues'][$userId] = \App\Fields\Owner::getUserLabel($userId);
					}
					break;
				default: break;
			}
			$this->fields[$name] = \Vtiger_Field_Model::init($moduleName, $params, $name);
		}
		return $this->fields[$name];
	}

	/**
	 * Gets value from request.
	 *
	 * @param string      $fieldName
	 * @param App\Request $request
	 *
	 * @return mixed
	 */
	public function getValueFromRequest(string $fieldName, App\Request $request)
	{
		switch ($fieldName) {
			case 'name':
				$value = $request->getArray($fieldName, \App\Purifier::ALNUM);
				break;
			case 'status':
				$value = $request->getInteger($fieldName);
				break;
			case 'user':
				$value = $request->getArray($fieldName, \App\Purifier::INTEGER);
				break;
			default: break;
		}
		return  $value;
	}

	/**
	 * Gets admin users.
	 *
	 * @return int[]
	 */
	public static function getUsers(): array
	{
		return (new \App\QueryGenerator('Users'))->setFields(['id'])
			->addCondition('is_admin', 'on', 'e')->createQuery()->column();
	}

	/**
	 * Edit view URL.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function getEditViewUrl(int $id = null): string
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit' . ($id ? "&id={$id}" : '');
	}

	/**
	 * Function to get the links.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getLinks(): array
	{
		return [Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'BTN_MASS_EDIT_ACCESS',
			'linkdata' => ['url' => $this->getEditViewUrl()],
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkclass' => 'btn-primary js-show-modal',
			'showLabel' => 1
		])];
	}

	/**
	 * Gets display value.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public function getDisplayValue(string $key, $value)
	{
		switch ($key) {
			case 'name':
				$value = \App\Language::translate($value, $value);
				break;
			case 'status':
				$value = \App\Language::translate(1 == $value ? 'LBL_YES' : 'LBL_NO', $this->getName(true));
				break;
			case 'user':
				$value = implode(', ', \App\Fields\Owner::getLabel($value));
				break;
			default: break;
		}
		return $value;
	}
}
