<?php

abstract class UserActions {
    abstract public function addCollectionRequest($request);
    abstract public function addTransaction($transaction);
    abstract public function addNotification($notification);
    abstract public function setReport($report);
}

abstract class DataHandler {
    abstract public static function getItem($id);
    abstract public static function updateItem($id, $type, $weight, $pricePerKg, $noteItem);
    abstract public static function deleteItem($id);
}

?>