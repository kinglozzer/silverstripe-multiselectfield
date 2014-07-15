<?php

class MultiSelectField extends ListboxField {

	protected $dataClass;

	protected $minHeight = 300;

	protected $maxHeight = 500;

	protected $sort = false;

	protected $searchable = true;

	/**
	 * @param string $name
	 * @param string $title
	 * @param DataObjectInterface $object
	 * @param string $sort
	 */
	public function __construct($name, $title, DataObjectInterface $object, $sort = false) {
		$this->setSort($sort);

		if($object->hasMethod($name) && $object->$name() instanceof ManyManyList) {
			$source = $object->$name();

			// Store the relation's class name
			$class = $source->dataClass();
			$this->dataClass = $class;

			// Sort the items
			if($this->getSort()) {
				$source = $source->sort($this->getSort());
			}

			// Our source needs the currently selected items in the correct sort order first...
			$source = $source->map()->toArray();
			$theRest = $class::get()->exclude("ID", array_keys($source))->map()->toArray();

			// ... we then add the remaining items in whatever order they come
			$source = $source + $theRest;
		} else {
			user_error('MultiSelectField::__construct(): MultiSelectField only supports many-to-many relations');
		}

		parent::__construct($name, $title, $source, '', null, true);
	}

	/**
	 * @param int $minHeight
	 * @return this
	 */
	public function setMinHeight($minHeight) {
		$this->minHeight = $minHeight;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMinHeight() {
		return $this->minHeight;
	}

	/**
	 * @param int $maxHeight
	 * @return this
	 */
	public function setMaxHeight($maxHeight) {
		$this->maxHeight = $maxHeight;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMaxHeight() {
		return $this->maxHeight;
	}

	/**
	 * @param string $sort
	 * @return this
	 */
	public function setSort($sort) {
		$this->sort = $sort;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSort() {
		return $this->sort;
	}

	/**
	 * @param boolean $bool
	 * @return this
	 */
	public function setSearchable($bool) {
		$this->searchable = $bool;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getSearchable() {
		return $this->searchable;
	}

	/**
	 * @param DataObjectInterface $record 
	 * @return void
	 */
	public function saveInto(DataObjectInterface $record) {
		if($sortField = $this->getSort()) {
			// If we're sorting, we'll add items to the ManyManyList manually
			$name = $this->name;
			$list = $record->$name();
			$class = $this->dataClass;

			// Get our selected items
			$selectedList = $class::get()->byIDs(array_values($this->value))->toArray();

			// Clear the list, we're rebuilding it from scratch
			$list->removeAll();

			// Convert our selected items to an ID => Object associative array
			$selected = array();
			foreach($selectedList as $item) {
				$selected[$item->ID] = $item;
			}

			// Now loop through the selected items (as these are in the correct order) and populate the list
			foreach($this->value as $order => $id) {
				$item = $selected[$id];

				$list->add($item, array($sortField => $order));
			}
		} else {
			// If we're not sorting, ListboxField can handle saving the items
			parent::saveInto($record);
		}
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		$attributes = parent::getAttributes();

		// Disable changetracking (we handle that manually) and chosen
		$attributes['class'] .= ' no-change-track multiselectfield no-chzn';
		$attributes['data-searchable'] = $this->getSearchable();
		$attributes['data-sortable'] = (boolean) $this->getSort();
		$attributes['data-min-height'] = $this->getMinHeight();
		$attributes['data-max-height'] = $this->getMaxHeight();

		return $attributes;
	}

	/**
	 * @param array $properties
	 * @return HTMLText
	 */
	public function Field($properties = array()) {
		Requirements::css(MULTISELECT_BASE . '/thirdparty/multiselect/css/ui.multiselect.css');
		Requirements::css(MULTISELECT_BASE . '/css/MultiSelectField.css');
		Requirements::javascript(MULTISELECT_BASE . '/thirdparty/multiselect/js/ui.multiselect.js');
		Requirements::javascript(MULTISELECT_BASE . '/javascript/MultiSelectField.js');

		return parent::Field($properties);
	}

}