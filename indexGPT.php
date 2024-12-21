<?php
class User {
    public $id;
    public $nama;
    public $email;
    public $password;
    public $role;
    protected $filePath = 'users.json';
    private $permintaanPengambilan;
    private $transaksi;
    private $laporan;
    public $notifikasi;

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
        // $this->notifikasi = new Notification();
    }

    public function register($id, $nama, $email, $password, $role) {
        $users = [];
        if (file_exists($this->filePath)) {
            $users = json_decode(file_get_contents($this->filePath), true);
        }

        $users[$id] = [
            "id" => $id,
            "nama" => $nama,
            "email" => $email,
            "password" => $password,
            "role" => $role
        ];

        file_put_contents($this->filePath, json_encode($users));

        // Update properti objek dan sesi
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

                // Update properti objek dan sesi
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

                // Hapus sesi jika pengguna yang dihapus adalah pengguna yang sedang login
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

    // public function getCollectionRequest() {
    //     if ($this->permintaanPengambilan === null) {
    //         $this->permintaanPengambilan = new CollectionRequest();
    //     }
    //     return $this->permintaanPengambilan->getRequest($this->id);
    // }

    // public function getTransactions() {
    //     if ($this->transaksi === null) {
    //         $this->transaksi = new Transactions();
    //     }
    //     return $this->transaksi->generateTransaction($this->id);
    // }

    // public function getReport() {
    //     if ($this->laporan === null) {
    //         $this->laporan = new Report();
    //     }
    //     return $this->laporan->viewReport($this->id);
    // }
}

class Admin extends User {
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

    public function calculatePrice() {
        return $this->weight * $this->pricePerKg;
    }

    public function addItem($id, $type, $weight, $pricePerKg) {
        $items = [];
        if (file_exists($this->filePath)) {
            $items = json_decode(file_get_contents($this->filePath), true);
        }

        $items[$id] = [
            "id" => $id,
            "type" => $type,
            "weight" => $weight,
            "pricePerKg" => $pricePerKg
        ];

        file_put_contents($this->filePath, json_encode($items));
    }

    public function getItem($id) {
        if (file_exists($this->filePath)) {
            $items = json_decode(file_get_contents($this->filePath), true);
            return $items[$id] ?? null;
        }
        return null;
    }

    public function updateItem($id, $type, $weight, $pricePerKg) {
        if (file_exists($this->filePath)) {
            $items = json_decode(file_get_contents($this->filePath), true);
            if (isset($items[$id])) {
                $items[$id] = [
                    "id" => $id,
                    "type" => $type,
                    "weight" => $weight,
                    "pricePerKg" => $pricePerKg
                ];

                file_put_contents($this->filePath, json_encode($items));
            }
        }
    }

    public function deleteItem($id) {
        if (file_exists($this->filePath)) {
            $items = json_decode(file_get_contents($this->filePath), true);
            if (isset($items[$id])) {
                unset($items[$id]);
                file_put_contents($this->filePath, json_encode($items));
            }
        }
    }
}

class OrganicWaste extends WasteItems {
    public $decompositionTime;

    public function __construct($id, $type, $weight, $pricePerKg, $decompositionTime) {
        parent::__construct($id, $type, $weight, $pricePerKg);
        $this->decompositionTime = $decompositionTime;
    }

    public function isCompostable() {
        // Implementasi untuk memeriksa apakah sampah organik dapat dikomposkan
    }
}

class PlasticWaste extends WasteItems {
    public $recyclabilityGrade;

    public function __construct($id, $type, $weight, $pricePerKg, $recyclabilityGrade) {
        parent::__construct($id, $type, $weight, $pricePerKg);
        $this->recyclabilityGrade = $recyclabilityGrade;
    }

    public function calculateRecyclingCost() {
        // Implementasi untuk menghitung biaya daur ulang plastik
        if ($this->recyclabilityGrade > 0) {
            return $this->weight * $this->pricePerKg;
        } else {
            return 0;
        }
    }
}

class MetalWaste extends WasteItems {
    public $metalType;

    public function __construct($id, $type, $weight, $pricePerKg, $metalType) {
        parent::__construct($id, $type, $weight, $pricePerKg);
        $this->metalType = $metalType;
    }

    public function isValuable() {
        // Implementasi untuk memeriksa apakah logam berharga
        if ($this->metalType == "gold" || $this->metalType == "silver") {
            return true;
        } else if ($this->metalType == "iron" || $this->metalType == "aluminium") {
            return false;
        } else {
            return null;
        }
    }
}

class CollectionRequest {
    private $filePath = 'collection_requests.json';
    public $id;
    public $userId;
    public $pickUpDate;
    public $status;

    public function __construct($id, $userId, $pickUpDate, $status) {
        $this->id = $id;
        $this->userId = $userId;
        $this->pickUpDate = $pickUpDate;
        $this->status = $status;
    }

    public function addRequest() {
        $requests = [];
        if (file_exists($this->filePath)) {
            $requests = json_decode(file_get_contents($this->filePath), true);
        }

        $requests[$this->id] = [
            "id" => $this->id,
            "userId" => $this->userId,
            "pickUpDate" => $this->pickUpDate,
            "status" => $this->status
        ];

        file_put_contents($this->filePath, json_encode($requests));
    }

    public function updateRequest() {
        if (file_exists($this->filePath)) {
            $requests = json_decode(file_get_contents($this->filePath), true);
            if (isset($requests[$this->id])) {
                $requests[$this->id] = [
                    "id" => $this->id,
                    "userId" => $this->userId,
                    "pickUpDate" => $this->pickUpDate,
                    "status" => $this->status
                ];

                file_put_contents($this->filePath, json_encode($requests));
            }
        }
    }

    public function getRequest($id) {
        if (file_exists($this->filePath)) {
            $requests = json_decode(file_get_contents($this->filePath), true);
            return $requests[$id] ?? null;
        }
        return null;
    }

    public function deleteRequest($id) {
        if (file_exists($this->filePath)) {
            $requests = json_decode(file_get_contents($this->filePath), true);
            if (isset($requests[$id])) {
                unset($requests[$id]);
                file_put_contents($this->filePath, json_encode($requests));
            }
        }
    }
}

class Transactions {
    private $filePath = 'transactions.json';
    public $id;
    public $userId;
    public $totalAmount;
    public $date;

    public function __construct($id, $userId, $totalAmount, $date) {
        $this->id = $id;
        $this->userId = $userId;
        $this->totalAmount = $totalAmount;
        $this->date = $date;
    }
    public function generateTransaction($id) {
        // Implementasi untuk menghasilkan transaksi
    }
    public function generateReceipt() {
        // Implementasi untuk menghasilkan tanda terima
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

    public function __construct($id, $userId, $month, $totalCollected) {
        $this->id = $id;
        $this->userId = $userId;
        $this->month = $month;
        $this->totalCollected = $totalCollected;
    }
    
    public function generateMonthlyReport() {
        $transactions = new Transactions(null, $this->userId, null, null);
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

    public function viewReport($userId) {
        if (file_exists($this->filePath)) {
            $reports = json_decode(file_get_contents($this->filePath), true);
            return $reports[$this->id] ?? null;
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

    public function __construct($id, $userId, $message, $createdAt) {
        $this->id = $id;
        $this->userId = $userId;
        $this->message = $message;
        $this->createdAt = $createdAt;
    }

    public function sendNotification() {
        $notifications = [];
        if (file_exists($this->filePath)) {
            $notifications = json_decode(file_get_contents($this->filePath), true);
        }

        $notifications[$this->id] = [
            "id" => $this->id,
            "userId" => $this->userId,
            "message" => $this->message,
            "createdAt" => $this->createdAt
        ];

        file_put_contents($this->filePath, json_encode($notifications));
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


?>