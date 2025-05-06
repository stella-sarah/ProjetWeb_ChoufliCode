<?php
class Inscription {
    private $dataFile;

    public function __construct() {
        $this->dataFile = DATA_PATH;
    }

    public function getAll() {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        $data = file_get_contents($this->dataFile);
        return json_decode($data, true) ?: [];
    }

    public function getById($id) {
        $inscriptions = $this->getAll();
        foreach ($inscriptions as $inscription) {
            if ($inscription['id'] === $id) {
                return $inscription;
            }
        }
        return null;
    }

    public function create($data) {
        $inscriptions = $this->getAll();
        $data['id'] = '#'.rand(1000, 9999); // Simple ID generation
        $inscriptions[] = $data;
        $this->save($inscriptions);
        return $data;
    }

    public function update($id, $data) {
        $inscriptions = $this->getAll();
        foreach ($inscriptions as &$inscription) {
            if ($inscription['id'] === $id) {
                $inscription = array_merge($inscription, $data);
                $this->save($inscriptions);
                return $inscription;
            }
        }
        return null;
    }

    public function delete($id) {
        $inscriptions = $this->getAll();
        $newInscriptions = array_filter($inscriptions, fn($ins) => $ins['id'] !== $id);
        if (count($newInscriptions) < count($inscriptions)) {
            $this->save($newInscriptions);
            return true;
        }
        return false;
    }

    private function save($inscriptions) {
        file_put_contents($this->dataFile, json_encode(array_values($inscriptions), JSON_PRETTY_PRINT));
    }
}