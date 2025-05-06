<?php
class Event {
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
        $events = $this->getAll();
        foreach ($events as $event) {
            if ($event['id'] === $id) {
                return $event;
            }
        }
        return null;
    }

    public function create($data) {
        $events = $this->getAll();
        $data['id'] = '#'.rand(1000, 9999); // Simple ID generation
        $data['created_at'] = date('Y-m-d H:i:s'); // Set creation timestamp
        $events[] = $data;
        $this->save($events);
        return $data;
    }

    public function update($id, $data) {
        $events = $this->getAll();
        foreach ($events as &$event) {
            if ($event['id'] === $id) {
                $event = array_merge($event, $data);
                $this->save($events);
                return $event;
            }
        }
        return null;
    }

    public function delete($id) {
        $events = $this->getAll();
        $newEvents = array_filter($events, fn($ev) => $ev['id'] !== $id);
        if (count($newEvents) < count($events)) {
            $this->save($newEvents);
            return true;
        }
        return false;
    }

    private function save($events) {
        file_put_contents($this->dataFile, json_encode(array_values($events), JSON_PRETTY_PRINT));
    }
}
?>