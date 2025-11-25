<?php
namespace Miziedi\Models;

use Database;

class Category {
    private $collection;

    public function __construct() {
        $this->collection = Database::getInstance()->getDb()->categories;
    }

    public function getAll() {
        return $this->collection->find()->toArray();
    }

    public function create($name, $slug) {
        return $this->collection->insertOne([
            'name' => $name, 
            'slug' => $slug,
            'created_at' => new \MongoDB\BSON\UTCDateTime()
        ]);
    }
}