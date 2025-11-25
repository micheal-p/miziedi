<?php
namespace Miziedi\Models;

use Database;
use MongoDB\BSON\ObjectId;

class Order {
    private $collection;

    public function __construct() {
        $this->collection = Database::getInstance()->getDb()->orders;
    }

    public function create($data) {
        return $this->collection->insertOne($data);
    }

    public function getByInvoice($invoiceNumber) {
        return $this->collection->findOne(['invoice_number' => $invoiceNumber]);
    }

    public function getAll() {
        return $this->collection->find([], ['sort' => ['created_at' => -1]])->toArray();
    }

    public function updateStatus($id, $status, $note = '') {
        $update = ['$set' => ['status' => $status]];
        if ($note) {
            $update['$push'] = ['history' => ['status' => $status, 'note' => $note, 'date' => new \MongoDB\BSON\UTCDateTime()]];
        }
        
        return $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            $update
        );
    }
}