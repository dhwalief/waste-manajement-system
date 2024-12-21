<?php
class User {
    public $id;
    public $nama;
    public $email;
    public $password;
    public $role;
    protected $filePath = 'users.json';
    private $collectionRequests = [];
    private $transactions = [];
    private $report;
    private $notifications = [];

    public function __construct() {
        if (file_exists($this->filePath)) {
            $users = json_decode(file_get_contents($this->filePath), true);
            if (isset($_SESSION["id"])) {
                $user = $users[$_SESSION["id"]] ?? null;
                if ($user) {
                    $this->id = $user["id"];
                    $this->nama = $user["nama"];
                    $this->email = $user["email"];
                    $this->password = $user["password"];
                    $this->role = $user["role"];
                }
            }
        }
    }

    public function register($id, $nama, $email, $password, $role) {
        $users = [];
        if (file_exists($this->filePath)) {
            $users = json_decode(file_get_contents($this->filePath), true);
        }

        if (isset($users[$id])) {
            echo "ID pengguna sudah ada.";
            return false;
        }

        foreach ($users as $user) {
            if ($user["email"] === $email) {
                echo "Email pengguna sudah ada.";
                return false;
            }
        }

        if ($role != "admin" && $role != "collector" && $role != "recycler") {
            echo "Role user tidak valid.";
            return false;
        }

        $users[$id] = [
            "id" => $id,
            "nama" => $nama,
            "email" => $email,
            "password" => $password,
            "role" => $role
        ];

        file_put_contents($this->filePath, json_encode($users));

        $_SESSION["id"] = $id;
        $_SESSION["nama"] = $nama;
        $_SESSION["email"] = $email;
        $_SESSION["password"] = $password;
        $_SESSION["role"] = $role;

        $this->id = $id;
        $this->nama = $nama;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function login($email, $password) {
        if (file_exists($this->filePath)) {
            $users = json_decode(file_get_contents($this->filePath), true);
            foreach ($users as $user) {
                if ($user["email"] === $email && $user["password"] === $password) {
                    $_SESSION["id"] = $user["id"];
                    $_SESSION["nama"] = $user["nama"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["password"] = $user["password"];
                    $_SESSION["role"] = $user["role"];

                    $this->id = $user["id"];
                    $this->nama = $user["nama"];
                    $this->email = $user["email"];
                    $this->password = $user["password"];
                    $this->role = $user["role"];
                    return true;
                }
            }
        }
        return false;
    }

    public function updateProfile($id, $nama, $email, $password, $role) {
        if (file_exists($this->filePath)) {
            $users = json_decode(file_get_contents($this->filePath), true);
            if (isset($users[$id])) {
                $users[$id] = [
                    "id" => $id,
                    "nama" => $nama,
                    "email" => $email,
                    "password" => $password,
                    "role" => $role
                ];

                file_put_contents($this->filePath, json_encode($users));

                $_SESSION["id"] = $id;
                $_SESSION["nama"] = $nama;
                $_SESSION["email"] = $email;
                $_SESSION["password"] = $password;
                $_SESSION["role"] = $role;

                $this->id = $id;
                $this->nama = $nama;
                $this->email = $email;
                $this->password = $password;
                $this->role = $role;
            }
        }
    }

    public function deleteUser($id) {
        if (file_exists($this->filePath)) {
            $users = json_decode(file_get_contents($this->filePath), true);
            if (isset($users[$id])) {
                unset($users[$id]);
                file_put_contents($this->filePath, json_encode($users));

                if ($_SESSION["id"] == $id) {
                    session_unset();
                    session_destroy();
                }
            }
        }
    }

    public function isPengambil() {
        return $this->role === 'pengambil';
    }

    public function addCollectionRequest($request) {
        if (!in_array($request, $this->collectionRequests)) {
            $this->collectionRequests[] = $request;
        }
    }

    public function addTransaction($transaction) {
        $this->transactions[] = $transaction;
    }

    public function setReport($report) {
        if ($this->report === null) {
            $this->report = $report;
        } else {
            throw new Exception("Report already exists. Overwrite not allowed.");
        }
    }

    public function addNotification($notification) {
        foreach ($this->notifications as $notif) {
            if ($notif->getId() === $notification->getId()) {
                return; // Notifikasi sudah ada
            }
        }
        $this->notifications[] = $notification;
    }

    public function getCollectionRequests($limit = 10, $offset = 0) {
        return array_slice($this->collectionRequests, $offset, $limit);
    }

    public function getTransactions($limit = 10, $offset = 0) {
        return array_slice($this->transactions, $offset, $limit);
    }

    public function getReport() {
        return $this->report;
    }

    public function getNotifications($limit = 10, $offset = 0) {
        return array_slice($this->notifications, $offset, $limit);
    }
    
}

class Admin extends User {
    // public function __construct($id = null, $nama = null, $email = null, $password = null) {
    //     parent::__construct($id, $nama, $email, $password, 'admin');
    // }

    public function manageUser() {
        // Implementasi untuk mengelola pengguna
    }

    public function viewAllUsers() {
        if (file_exists($this->filePath)) {
            return json_decode(file_get_contents($this->filePath), true);
        }
        return [];
    }
}

class Collector extends User {
    public function addRequest($id, $userId, $pickUpDate, $status) {
        $request = new CollectionRequest($id, $userId, $pickUpDate, $status);
        $request->addRequest();
    }

    public function viewRequests() {
        $request = new CollectionRequest(null, $this->id, null, null);
        return $request->getRequest($this->id);
    }
}

class Recycler extends User {
    public function addRequest($id, $userId, $pickUpDate, $status) {
        $request = new CollectionRequest($id, $userId, $pickUpDate, $status);
        $request->addRequest();
    }

    public function viewRequests() {
        $request = new CollectionRequest(null, $this->id, null, null);
        return $request->getRequest($this->id);
    }
}

class WasteItems {
    private $filePath = 'waste_items.json';
    public $id;
    public $type;
    public $weight;
    public $pricePerKg;

    public function __construct($id, $type, $weight, $pricePerKg) {
        $this->id = $id;
        $this->type = $type;
        $this->weight = $weight;
        $this->pricePerKg = $pricePerKg;
    }

    public function calculatePrice(): float {
        return $this->weight * $this->pricePerKg;
    }

    public function save(): bool {
        $items = [];
        if (file_exists($this->filePath)) {
            $items = json_decode(file_get_contents($this->filePath), true);
        }

        $items[$this->id] = [
            'id' => $this->id,
            'type' => $this->type,
            'weight' => $this->weight,
            'pricePerKg' => $this->pricePerKg
        ];

        return file_put_contents($this->filePath, json_encode($items)) !== false;
    }

    public static function getItem($id) {
        $filePath = 'waste_items.json';
        if (file_exists($filePath)) {
            $items = json_decode(file_get_contents($filePath), true);
            return $items[$id] ?? null;
        }
        return null;
    }

    public static function updateItem($id, $type, $weight, $pricePerKg): bool {
        $filePath = 'waste_items.json';
        if (file_exists($filePath)) {
            $items = json_decode(file_get_contents($filePath), true);
            if (isset($items[$id])) {
                $items[$id] = [
                    'id' => $id,
                    'type' => $type,
                    'weight' => $weight,
                    'pricePerKg' => $pricePerKg
                ];
                return file_put_contents($filePath, json_encode($items)) !== false;
            }
        }
        return false;
    }

    public static function deleteItem($id): bool {
        $filePath = 'waste_items.json';
        if (file_exists($filePath)) {
            $items = json_decode(file_get_contents($filePath), true);
            if (isset($items[$id])) {
                unset($items[$id]);
                return file_put_contents($filePath, json_encode($items)) !== false;
            }
        }
        return false;
    }
}

class OrganicWaste extends WasteItems {
    public $decompositionTime;

    public function __construct($id, $weight, $pricePerKg, $decompositionTime) {
        parent::__construct($id, 'organic', $weight, $pricePerKg);
        $this->decompositionTime = $decompositionTime;
    }

    public function isCompostable(): bool {
        // Implementasi untuk memeriksa apakah sampah organik dapat dikomposkan
        return true;
    }
}

class PlasticWaste extends WasteItems {
    public $recyclabilityGrade;

    public function __construct($id, $weight, $pricePerKg, $recyclabilityGrade) {
        parent::__construct($id, 'plastic', $weight, $pricePerKg);
        $this->recyclabilityGrade = $recyclabilityGrade;
    }

    public function calculateRecyclingCost(): float {
        // Implementasi untuk menghitung biaya daur ulang plastik
        $gradeFactor = match($this->recyclabilityGrade) {
            'A' => 1.0,
            'B' => 1.2,
            'C' => 1.5,
            default => 2.0
        };
        return $this->calculatePrice() * $gradeFactor;
    }
}

class MetalWaste extends WasteItems {
    public $metalType;

    public function __construct($id, $weight, $pricePerKg, $metalType) {
        parent::__construct($id, 'metal', $weight, $pricePerKg);
        $this->metalType = $metalType;
    }

    public function isValuable(): bool {
        // Implementasi untuk memeriksa apakah logam berharga
        return in_array($this->metalType, ['gold', 'silver', 'copper', 'aluminum']);
    }
}

class CollectionRequest {
    private $filePath = 'collection_requests.json';
    public $id;
    public $userId;
    public $pickUpDate;
    public $status;
    public $wasteItems = [];

    public function __construct($id, $userId, $pickUpDate, $status) {
        $this->id = $id;
        $this->userId = $userId;
        $this->pickUpDate = $pickUpDate;
        $this->status = $status;
    }

    public function addRequest(): bool {
        $requests = $this->loadRequests();
        $requests[$this->id] = $this->toArray();
        return file_put_contents($this->filePath, json_encode($requests)) !== false;
    }

    public function updateRequest($status): bool {
        $this->status = $status;
        $requests = $this->loadRequests();
        $requests[$this->id] = $this->toArray();
        return file_put_contents($this->filePath, json_encode($requests)) !== false;
    }

    public function getRequest($id) {
        $requests = $this->loadRequests();
        return $requests[$id] ?? null;
    }

    public function deleteRequest($id): bool {
        $requests = $this->loadRequests();
        if (isset($requests[$id])) {
            unset($requests[$id]);
            return file_put_contents($this->filePath, json_encode($requests)) !== false;
        }
        return false;
    }

    private function loadRequests(): array {
        return file_exists($this->filePath) ? 
               json_decode(file_get_contents($this->filePath), true) : [];
    }

    private function toArray(): array {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'pickUpDate' => $this->pickUpDate,
            'status' => $this->status,
            'wasteItems' => $this->wasteItems
        ];
    }

    public function addWasteItem(WasteItems $item) {
        $this->wasteItems[] = $item;
    }
}

class Transactions {
    private $filePath = 'transactions.json';
    public $id;
    public $userId;
    public $totalAmount;
    public $date;

    public function __construct($id, $userId, $totalAmount) {
        $this->id = $id;
        $this->userId = $userId;
        $this->totalAmount = $totalAmount;
        $this->date = date('Y-m-d H:i:s');
    }

    public function save() {
        $transactions = [];
        if (file_exists($this->filePath)) {
            $transactions = json_decode(file_get_contents($this->filePath), true);
        }

        $transactions[$this->id] = [
            'id' => $this->id,
            'userId' => $this->userId,
            'totalAmount' => $this->totalAmount,
            'date' => $this->date
        ];

        return file_put_contents($this->filePath, json_encode($transactions)) !== false;
    }

    public function generateReceipt() {
        return "Receipt #{$this->id}\n" .
               "Date: {$this->date}\n" .
               "Amount: {$this->totalAmount}\n";
    }

    public function viewHistory() {
        if (file_exists($this->filePath)) {
            $transactions = json_decode(file_get_contents($this->filePath), true);
            $userTransactions = [];
            foreach ($transactions as $transaction) {
                if ($transaction["userId"] == $this->userId) {
                    $userTransactions[] = $transaction;
                }
            }
            return $userTransactions;
        }
        return [];
    }
}

class Report {
    private $filePath = 'reports.json';
    public $id;
    public $userId;
    public $month;
    public $totalCollected;

    public function __construct($id, $userId, $month) {
        $this->id = $id;
        $this->userId = $userId;
        $this->month = $month;
        $this->totalCollected = 0;
    }

    public function generateMonthlyReport() {
        $transactions = new Transactions(null, $this->userId, null);
        $userTransactions = $transactions->viewHistory();

        $monthlyReport = [];
        foreach ($userTransactions as $transaction) {
            $transactionMonth = date('Y-m', strtotime($transaction['date']));
            if (!isset($monthlyReport[$transactionMonth])) {
                $monthlyReport[$transactionMonth] = 0;
            }
            $monthlyReport[$transactionMonth] += $transaction['totalAmount'];
        }

        $this->totalCollected = $monthlyReport[$this->month] ?? 0;

        $reports = [];
        if (file_exists($this->filePath)) {
            $reports = json_decode(file_get_contents($this->filePath), true);
        }

        $reports[$this->id] = [
            "id" => $this->id,
            "userId" => $this->userId,
            "month" => $this->month,
            "totalCollected" => $this->totalCollected
        ];

        file_put_contents($this->filePath, json_encode($reports));
    }

    public function viewReport() {
        if (file_exists($this->filePath)) {
            $reports = json_decode(file_get_contents($this->filePath), true);
            return $reports[$this->id] = [
                "id" => $this->id,
                "userId" => $this->userId,
                "month" => $this->month,
                "totalCollected" => $this->totalCollected        
            ] ?? null;
        }
        return null;
    }
}

class Notification {
    private $filePath = 'notifications.json';
    public $id;
    public $userId;
    public $message;
    public $createdAt;

    public function __construct($id, $userId, $message) {
        $this->id = $id;
        $this->userId = $userId;
        $this->message = $message;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function sendNotification() {
        $notifications = [];
        if (file_exists($this->filePath)) {
            $notifications = json_decode(file_get_contents($this->filePath), true);
        }

        $notifications[$this->id] = [
            'id' => $this->id,
            'userId' => $this->userId,
            'message' => $this->message,
            'createdAt' => $this->createdAt
        ];

        return file_put_contents($this->filePath, json_encode($notifications)) !== false;
    }

    public function getUserNotifications($userId) {
        if (file_exists($this->filePath)) {
            $notifications = json_decode(file_get_contents($this->filePath), true);
            $userNotifications = [];
            foreach ($notifications as $notification) {
                if ($notification["userId"] == $userId) {
                    $userNotifications[] = $notification;
                }
            }
            return $userNotifications;
        }
        return [];
    }
}

session_start();
