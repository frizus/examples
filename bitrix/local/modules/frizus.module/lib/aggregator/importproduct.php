<?php
namespace Frizus\Module\Aggregator;

use Frizus\Module\Aggregator\ProductChanges\FieldResolver;

class ImportProduct
{
    protected $importData;

    protected $element;

    protected $product;

    protected $price;

    protected $discount;

    protected $changes;

    /**
     * @var ExchangeProcess
     */
    protected $exchangeProcess;

    public function __construct(&$importData, &$existingProduct, $exchangeProcess)
    {
        $this->exchangeProcess = $exchangeProcess;
        $this->importData = &$importData;

        if (isset($existingProduct)) {
            if (isset($existingProduct['element'])) {
                $this->element = &$existingProduct['element'];
            }

            if (isset($existingProduct['product'])) {
                $this->product = &$existingProduct['product'];
            }

            if (isset($existingProduct['price'])) {
                $this->price = &$existingProduct['price'];
            }

            if (isset($existingProduct['discount'])) {
                $this->discount = &$existingProduct['discount'];
            }
        }
    }

    public function skipping()
    {
        return $this->element['PROPERTIES']['AGGREGATOR_SYNC']['VALUE'] === 'N';
    }

    public function existedBefore()
    {
        return isset($this->element);
    }

    protected function originalExists()
    {
        return $this->existedBefore();
    }

    public function save()
    {
        if (!$this->prepareChanges()) {
            return false;
        }

        if (!$this->haveChanges()) {
            return null;
        }

        if ($this->originalExists()) {
            if (!$this->backupOriginalFiles()) {
                return false;
            }
        }

        if ($saved = $this->continueSaving()) {
            if ($this->originalExists()) {
                $this->deleteOriginalFilesBackup();
            }
        }

        return $saved;
    }

    public function restore()
    {
        if (!$this->needRestoring()) {
            return null;
        }

        if (!$this->existedBefore()) {
            return $this->deleteBrokenProduct();
        }

        $this->prepareRestoreData();

        if ($restored = $this->continueRestoring()) {
            $this->deleteOriginalFilesBackup();
        }

        return $restored;
    }

    protected function prepareChanges()
    {
        $this->changes = [];

        if (!isset($this->element)) {
            $this->changes['element']['new'] = true;
        }

        $fieldResolver = FieldResolver::getInstance();

        foreach ($this->exchangeProcess->exchangeOptions['allowedFields'] as $field) {
            $fieldChanges = $fieldResolver->resolve('field', $field);
            $fieldChanges->normalizeOriginalValue($this->element);
            $fieldChanges->normalizeImportValue($this->importData);
            $fieldChanges->diff();

            if ($fieldChanges->haveDiff()) {
                $this->changes['element']['fields'][$field] = $fieldChanges;
            }
        }

        foreach ($this->exchangeProcess->exchangeOptions['allowedProperties'] as $propertyCode) {
            $property = $this->exchangeProcess->exchangeOptions['importProperties'][$propertyCode];
            $propertyType = $fieldResolver->resolvePropertyType($property['PROPERTY_TYPE'], $property['USER_TYPE']);

            if ($propertyType === false) {
                continue;
            }

            $fieldChanges = $fieldResolver->resolve('property', $propertyCode, $property['MULTIPLE']);
            $fieldChanges->normalizeOriginalValue($this->element);
            $fieldChanges->normalizeImportValue($this->importData);
            $fieldChanges->diff();

            if ($fieldChanges->haveDiff()) {
                $this->changes['element']['properties'][$propertyCode] = $fieldChanges;
            }
        }

        if (!isset($this->product)) {
            $this->changes['product']['new'] = true;
        }

        if (!isset($this->price)) {
            $this->changes['price']['new'] = true;
        }

        $fieldChanges = $fieldResolver->resolve('price');
        $fieldChanges->normalizeOriginalValue($this->price);
        $fieldChanges->normalizeImportValue($this->importData);
        $fieldChanges->diff();

        if (!isset($this->price) || $fieldChanges->haveDiff()) {
            $this->changes['price']['changes'] = $fieldChanges;
        }

        $fieldChanges = $fieldResolver->resolve('discount');
        $fieldChanges->normalizeOriginalValue($this->discount);
        $fieldChanges->normalizeImportValue($this->importData);
        $fieldChanges->diff();

        if ($fieldChanges->haveDiff()) {
            $this->changes['discount']['changes'] = $fieldChanges;
        }
    }

    protected function haveChanges()
    {

    }

    protected function backupOriginalFiles()
    {

    }

    protected function continueSaving()
    {

    }

    protected function deleteOriginalFilesBackup()
    {

    }

    protected function needRestoring()
    {
        if (!$this->haveChanges()) {
            return false;
        }

        return $this->somethingWasSaved();
    }

    protected function somethingWasSaved()
    {

    }

    protected function deleteBrokenProduct()
    {

    }

    protected function prepareRestoreChanges()
    {

    }

    protected function continueRestoring()
    {

    }
}
