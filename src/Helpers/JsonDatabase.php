<?php

namespace App\Helpers;

class JsonDatabase
{
    private $dataDir;
    private static $instance = null;

    private function __construct()
    {
        $this->dataDir = ROOT_PATH . '/data';
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getFilePath($table)
    {
        return $this->dataDir . '/' . $table . '.json';
    }

    private function readData($table)
    {
        $file = $this->getFilePath($table);
        if (!file_exists($file)) {
            return [];
        }
        $json = file_get_contents($file);
        return json_decode($json, true) ?? [];
    }

    private function writeData($table, $data)
    {
        $file = $this->getFilePath($table);
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function all($table)
    {
        return $this->readData($table);
    }

    public function find($table, $conditions = [])
    {
        $data = $this->readData($table);
        return array_filter($data, function ($item) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (!isset($item[$key]) || $item[$key] != $value) {
                    return false;
                }
            }
            return true;
        });
    }

    public function findOne($table, $conditions = [])
    {
        $results = $this->find($table, $conditions);
        return !empty($results) ? reset($results) : null;
    }

    public function findById($table, $id)
    {
        return $this->findOne($table, ['id' => $id]);
    }

    public function insert($table, $data)
    {
        $items = $this->readData($table);

        // Auto increment ID
        $lastId = 0;
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] > $lastId) {
                $lastId = $item['id'];
            }
        }
        $data['id'] = $lastId + 1;
        $data['created_at'] = date('Y-m-d H:i:s');

        $items[] = $data;
        $this->writeData($table, $items);

        return $data['id'];
    }

    public function update($table, $id, $data)
    {
        $items = $this->readData($table);
        $updated = false;

        foreach ($items as &$item) {
            if (isset($item['id']) && $item['id'] == $id) {
                foreach ($data as $key => $value) {
                    $item[$key] = $value;
                }
                $item['updated_at'] = date('Y-m-d H:i:s');
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->writeData($table, $items);
        }

        return $updated;
    }

    public function delete($table, $id)
    {
        $items = $this->readData($table);
        $newItems = array_filter($items, function ($item) use ($id) {
            return isset($item['id']) && $item['id'] != $id;
        });

        $this->writeData($table, array_values($newItems));
    }

    // Helper to simulate query/count for simple cases or count
    public function count($table, $conditions = [])
    {
        return count($this->find($table, $conditions));
    }
}
