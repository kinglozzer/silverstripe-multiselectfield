<?php

namespace Kinglozzer\MultiSelectField\Forms;

use SilverStripe\Forms\ListboxField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\UnsavedRelationList;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class MultiSelectField
 * @package Kinglozzer\MultiSelectField\Forms
 */
class MultiSelectField extends ListboxField
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var int
     */
    protected $minHeight = 300;

    /**
     * @var int
     */
    protected $maxHeight = 500;

    /**
     * @var bool
     */
    protected $sort = false;

    /**
     * @var bool
     */
    protected $searchable = true;

    /**
     * @param string $name
     * @param string $title
     * @param DataObjectInterface $object
     * @param string $sort
     * @param SS_List|array $source
     * @param string $titleField
     */
    public function __construct(
        $name,
        $title,
        DataObjectInterface $object,
        $sort = false,
        $source = null,
        $titleField = 'Title'
    ) {
        $this->setSort($sort);

        $schema = DataObject::getSchema();
        if ($schema->manyManyComponent(get_class($object), $name)) {
            $dataSource = $object->$name();

            // Check if we're dealing with an UnsavedRelationList
            $unsaved = ($dataSource instanceof UnsavedRelationList);

            // Store the relation's class name
            $class = $dataSource->dataClass();
            $this->dataClass = $class;

            // Sort the items
            if ($this->getSort()) {
                $dataSource = $dataSource->sort($this->getSort());
            }

            // If we're dealing with an UnsavedRelationList, it'll be empty, so we
            // can skip this and just use an array of all available items
            if ($unsaved) {
                $dataSource = $class::get()->map()->toArray();
            } else {
                // If we've been given a list source, filter on those IDs only.
                if ($source) {
                    $dataSource = $dataSource->filter('ID', $source->column('ID'));
                }

                // Start building the data source from scratch. Currently selected items first,
                // in the correct sort order
                $dataSource = $dataSource->map('ID', $titleField)->toArray();

                // Get the other items
                $theRest = $class::get();

                // Exclude items that we've already found
                if (!empty($dataSource)) {
                    $theRest = $theRest->exclude('ID', array_keys($dataSource));
                }

                // If we've been given a list source, filter on those IDs only
                if ($source) {
                    $theRest = $theRest->filter('ID', $source->column('ID'));
                }

                $theRest = $theRest->map('ID', $titleField)->toArray();

                // ... we then add the remaining items in whatever order they come
                $dataSource = $dataSource + $theRest;
            }
        } elseif ($source instanceof SS_List) {
            $dataSource = $source->map('ID', $titleField);

            if (!is_array($dataSource)) {
                $dataSource = $dataSource->toArray();
            }
        } elseif (is_array($source) && ArrayLib::is_associative($source)) {
            $dataSource = $source;
        } else {
            user_error('MultiSelectField::__construct(): MultiSelectField only supports many-to-many relations');
        }

        parent::__construct($name, $title, $dataSource, '', null, true);
    }

    /**
     * @param int $minHeight
     * @return this
     */
    public function setMinHeight($minHeight)
    {
        $this->minHeight = $minHeight;

        return $this;
    }

    /**
     * @return string
     */
    public function getMinHeight()
    {
        return $this->minHeight;
    }

    /**
     * @param int $maxHeight
     * @return this
     */
    public function setMaxHeight($maxHeight)
    {
        $this->maxHeight = $maxHeight;

        return $this;
    }

    /**
     * @return string
     */
    public function getMaxHeight()
    {
        return $this->maxHeight;
    }

    /**
     * @param string $sort
     * @return this
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param boolean $bool
     * @return this
     */
    public function setSearchable($bool)
    {
        $this->searchable = $bool;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * @param DataObjectInterface $record
     * @return void
     */
    public function saveInto(DataObjectInterface $record)
    {
        if ($sortField = $this->getSort()) {
            // If we're sorting, we'll add items to the ManyManyList manually
            $name = $this->name;
            $list = $record->$name();
            $class = $this->dataClass;

            // Clear the list, we're rebuilding it from scratch
            $list->removeAll();

            // If nothing's been added, that's all we need to do
            if (empty($this->value)) {
                return;
            }

            // Get our selected items
            $selectedList = $class::get()->byIDs(array_values($this->value))->toArray();

            // Convert our selected items to an ID => Object associative array
            $selected = [];
            foreach ($selectedList as $item) {
                $selected[$item->ID] = $item;
            }

            // Now loop through the selected items (as these are in the correct order) and populate the list
            foreach ($this->value as $order => $id) {
                $item = $selected[$id];

                $list->add($item, [$sortField => $order]);
            }
        } else {
            // If we're not sorting, ListboxField can handle saving the items
            parent::saveInto($record);
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        // Disable changetracking (we handle that manually) and chosen
        $attributes['class'] .= ' no-change-track multiselectfield no-chosen';
        $attributes['data-searchable'] = $this->getSearchable();
        $attributes['data-sortable'] = (boolean) $this->getSort();
        $attributes['data-min-height'] = $this->getMinHeight();
        $attributes['data-max-height'] = $this->getMaxHeight();

        return $attributes;
    }

    /**
     * @param array $properties
     * @return DBHTMLText
     */
    public function Field($properties = [])
    {
        Requirements::css('kinglozzer/multiselectfield: client/thirdparty/multiselect/css/ui.multiselect.css');
        Requirements::css('kinglozzer/multiselectfield: client/css/MultiSelectField.css');
        Requirements::javascript('kinglozzer/multiselectfield: client/thirdparty/multiselect/js/ui.multiselect.js');
        Requirements::javascript('kinglozzer/multiselectfield: client/javascript/MultiSelectField.js');

        return parent::Field($properties);
    }
}
