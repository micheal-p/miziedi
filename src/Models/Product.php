<?php
namespace Miziedi\Models;

use Database;
use MongoDB\BSON\ObjectId;

class Product {
    private $collection;

    public function __construct() {
        $this->collection = Database::getInstance()->getDb()->products;
    }

    public function getAll($filter = [], $limit = 20) {
        return $this->collection->find($filter, [
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ])->toArray();
    }

    public function getById($id) {
        try {
            return $this->collection->findOne(['_id' => new ObjectId($id)]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function create($data) {
        return $this->collection->insertOne($data);
    }
    
    public function update($id, $data) {
        return $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );
    }
}