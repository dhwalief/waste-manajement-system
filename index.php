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

    public function updateUser($id, $nama, $email, $password, $role) {
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
    public function __construct($email, $password) {
        parent::login($email, $password);
    }

    public function viewAllUsers() {
        if (file_exists($this->filePath)) {
            $data = json_decode(file_get_contents($this->filePath), true);
    
            // Pastikan data adalah array
            if (is_array($data)) {
                $output = "";
                $userIndex = 1;
                foreach ($data as $key => $user) {
                    $output .= "User {$userIndex}:\n";
                    foreach ($user as $field => $value) {
                        $output .= ucfirst($field) . " : " . $value . "\n";
                    }
                    $output .= "\n";
                    $userIndex++;
                }
                return $output;
            }
            return "Invalid data format in file.";
        }
        return "File not found.";
    }
    

    public function deleteUser($id) {
        if (file_exists($this->filePath)) {
            $users = json_decode(file_get_contents($this->filePath), true);
            if (isset($users[$id])) {
                unset($users[$id]);
                file_put_contents($this->filePath, json_encode($users));
                echo "Pengguna id {$users[$id]} berhasil dihapus.\n";
            } else {
                echo "Pengguna id {$users[$id]} tidak ditemukan.\n";

            }
        }
    }

    public function manageRequest($requestId, $status) {
        $request = new CollectionRequest($requestId, null, null, null);
        $request->getRequest($requestId);
        if ($request->updateRequest($status)) {
            echo "Request berhasil diperbarui.\n";
        } else {
            echo "Gagal memperbarui request.\n";
        }
    }

    public function viewAllRequests() {
        $filePath = "collection_requests.json";
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
    
            // Pastikan data adalah array
            if (is_array($data)) {
                $output = "";
                $id = 1;
                foreach ($data as $key => $user) {
                    $output .= "ID Permintaan {$id}:\n";
                    foreach ($user as $field => $value) {
                        $output .= ucfirst($field) . " : " . $value . "\n";
                    }
                    $output .= "\n";
                    $id++;
                }
                return $output;
            }
            return "Invalid data format in file.";
        }
        return "File not found.";
    }
    

    public function generateMonthlyReport($userId, $month) {
        $reportId = generateId();
        $report = new Report($reportId, $userId, $month);
        $report->generateMonthlyReport();
        echo "Laporan bulanan berhasil dibuat.\n";
    }

    public function manageWasteItems() {
        while (true) {
            echo "1. Tambah item sampah\n";
            echo "2. Lihat semua item sampah\n";
            echo "3. Ubah item sampah\n";
            echo "4. Hapus item sampah\n";
            echo "5. Kembali\n";
            echo "Pilih opsi: ";
            $option = trim(fgets(STDIN));
            
            switch ($option) {
                case 1:
                    $id = getInput("ID: ");
                    $type = getInput("Tipe (organic/plastic/metal): ");
                    $weight = getInput("Berat: ");
                    $pricePerKg = getInput("Harga per Kg: ");
                    if ($type == 'organic') {
                        $decompositionTime = getInput("Waktu dekomposisi: ");
                        $item = new OrganicWaste($id, $type, $weight, $pricePerKg, $decompositionTime);
                    } elseif ($type == 'plastic') {
                        $recyclabilityGrade = getInput("Grade daur ulang: ");
                        $item = new PlasticWaste($id, $type, $weight, $pricePerKg, $recyclabilityGrade);
                    } elseif ($type == 'metal') {
                        $metalType = getInput("Tipe logam: ");
                        $item = new MetalWaste($id, $type, $weight, $pricePerKg, $metalType);
                    } else {
                        echo "Tipe tidak valid.\n";
                        break;
                    }
                    if ($item->save()) {
                        echo "Item sampah berhasil ditambahkan.\n";
                    } else {
                        echo "Gagal menambahkan item sampah.\n";
                    }
                    break;
                case 2:
                    $items = json_decode(file_get_contents('waste_items.json'), true);
                    foreach ($items as $item) {
                        echo "ID: {$item['id']}\n";
                        echo "Tipe: {$item['type']}\n";
                        echo "Berat: {$item['weight']}\n";
                        echo "Harga per Kg: {$item['pricePerKg']}\n";
                        if ($item['type'] == 'organic') {
                            echo "Waktu dekomposisi: {$item['decompositionTime']}\n";
                        } elseif ($item['type'] == 'plastic') {
                            echo "Grade daur ulang: {$item['recyclabilityGrade']}\n";
                        } elseif ($item['type'] == 'metal') {
                            echo "Tipe logam: {$item['metalType']}\n";
                        }
                        echo "=====================\n";
                    }
                    break;
                case 3:
                    $id = getInput("ID: ");
                    $type = getInput("Tipe (organic/plastic/metal): ");
                    $weight = getInput("Berat: ");
                    $pricePerKg = getInput("Harga per Kg: ");
                    if ($type == 'organic') {
                        $decompositionTime = getInput("Waktu dekomposisi: ");
                        $success = OrganicWaste::updateItem($id, $type, $weight, $pricePerKg, $decompositionTime);
                    } elseif ($type == 'plastic') {
                        $recyclabilityGrade = getInput("Grade daur ulang: ");
                        $success = PlasticWaste::updateItem($id, $type, $weight, $pricePerKg, $recyclabilityGrade);
                    } elseif ($type == 'metal') {
                        $metalType = getInput("Tipe logam: ");
                        $success = MetalWaste::updateItem($id, $type, $weight, $pricePerKg, $metalType);
                    } else {
                        echo "Tipe tidak valid.\n";
                        break;
                    }
                    if ($success) {
                        echo "Item sampah berhasil diubah.\n";
                    } else {
                        echo "Gagal mengubah item sampah.\n";
                    }
                    break;
                case 4:
                    $id = getInput("ID: ");
                    if (WasteItems::deleteItem($id)) {
                        echo "Item sampah berhasil dihapus.\n";
                    } else {
                        echo "Gagal menghapus item sampah.\n";
                    }
                    break;
                case 5:
                    return;
                default:
                    echo "Opsi tidak valid. Silakan coba lagi.\n";
                    break;
            }
        }
    }
}

class Collector extends User {
    public function __construct($email, $password) {
        parent::login($email, $password);
    }
    public function addRequest($id, $userId, $pickUpDate, $status) {
        $request = new CollectionRequest($id, $userId, $pickUpDate, $status);
        $request->addRequest();
    }

    public function viewRequests() {
        $request = new CollectionRequest(null, $this->id, null, null);
        return $request->getRequest($this->id);
    }

    public function addWasteItemToRequest($requestId, $itemId) {
        $request = new CollectionRequest($requestId, $this->id, null, null);
        $request->getRequest($requestId);
        $item = WasteItems::getItem($itemId);
        if ($item) {
            $request->addWasteItem($item);
            $request->updateRequest($request->status);
            echo "Item sampah berhasil ditambahkan ke permintaan.\n";
        } else {
            echo "Item sampah tidak ditemukan.\n";
        }
    }

    public function completeRequest($requestId) {
        $request = new CollectionRequest($requestId, $this->id, null, null);
        $request->getRequest($requestId);
        $totalAmount = 0;
        foreach ($request->wasteItems as $item) {
            $totalAmount += $item->calculatePrice();
        }
        $transaction = new Transactions(generateId(), $this->id, $totalAmount);
        $transaction->save();
        echo "Permintaan selesai. Total pembayaran: {$totalAmount}\n";
    }
}

class Recycler extends User {
    public function __construct($email, $password) {
        parent::login($email, $password);
    }

    public function addRequest($id, $userId, $pickUpDate, $status) {
        $request = new CollectionRequest($id, $userId, $pickUpDate, $status);
        $request->addRequest();
    }

    public function viewRequests() {
        $request = new CollectionRequest(null, $this->id, null, null);
        return $request->getRequest($this->id);
    }

    public function processRequest($requestId) {
        $request = new CollectionRequest($requestId, $this->id, null, null);
        $request->getRequest($requestId);
        $totalAmount = 0;
        foreach ($request->wasteItems as $item) {
            $totalAmount += $item->calculatePrice();
        }
        $transaction = new Transactions(generateId(), $this->id, $totalAmount);
        $transaction->save();
        echo "Permintaan diproses. Total pembayaran: {$totalAmount}\n";
    }
}

class WasteItems {
    private $filePath = 'waste_items.json';
    public $id;
    public $type;
    public $weight;
    public $pricePerKg;
    public $noteItem;
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

    public static function updateItem($id, $type, $weight, $pricePerKg, $noteItem): bool {
        $filePath = 'waste_items.json';
        if (file_exists($filePath)) {
            $items = json_decode(file_get_contents($filePath), true);
            if (isset($items[$id])) {
                $items[$id] = [
                    'id' => $id,
                    'type' => $type,
                    'weight' => $weight,
                    'pricePerKg' => $pricePerKg,
                    'condition'=> $noteItem
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

    public function __construct($id, $type, $weight, $pricePerKg, $decompositionTime) {
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

    public function __construct($id, $type, $weight, $pricePerKg, $recyclabilityGrade) {
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

    public function __construct($id, $type, $weight, $pricePerKg, $metalType) {
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

//pengacakan id
function generateId($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $id = '';
    for ($i = 0; $i < $length; $i++) {
        $id .= $characters[rand(0, $charactersLength - 1)];
    }
    return $id;
}

function registerUser($nama, $email, $password, $role) {
    $id = generateId();
    $user = new User();
    $user->register($id, $nama, $email, $password, $role);
}

function loginUser($email, $password) {
    $user = new User();
    return $user->login($email, $password) ? $user : null;
}

function pageEnterBehavior($user){
    if ($user == null){
        echo "Silahkan login terlebih dahulu\n";
    } else {
        if ($user->role == 'admin'){
            pageAdmin($user);
        } elseif ($user->role == 'collector'){
            pageCollector($user);
        } elseif ($user->role == 'recycler'){
            pageRecycler($user);
        }
    }
}

function getInput($prompt) {
    echo $prompt;
    return trim(fgets(STDIN));
}

function validateRole($role) {
    $validRoles = ['admin', 'collector', 'recycler'];
    return in_array($role, $validRoles);
}

function mainMenu() {
    echo "Sistem Manajemen Pengelolaan Sampah Daur Ulang\n";
    echo "==============================================\n";
    while (true) {
        echo "1. Register\n";
        echo "2. Login\n";
        echo "3. Update Profile\n";
        // echo "4. Delete User\n";
        echo "5. Exit\n";
        echo "Pilih opsi: ";
        $option = trim(fgets(STDIN));
        
        switch ($option) {
            case 1:
                $nama = getInput("Nama: ");
                $email = getInput("Email: ");
                $password = getInput("Password: ");
                do {
                    $role = getInput("Role (admin/collector/recycler): ");
                    if (!validateRole($role)) {
                        echo "Role tidak valid. Silakan coba lagi.\n";
                    }
                } while (!validateRole($role));
                registerUser($nama, $email, $password, $role);
                break;
            case 2:
                $email = getInput("Email: ");
                $password = getInput("Password: ");
                $user = loginUser($email, $password);
                if ($user == null) {
                    echo "Login gagal. Email atau password salah.\n";
                } else {
                    pageEnterBehavior($user);
                }
                break;
            case 3:
                $id = getInput("ID: ");
                $nama = getInput("Nama: ");
                $email = getInput("Email: ");
                $password = getInput("Password: ");
                do {
                    $role = getInput("Role (admin/collector/recycler): ");
                    if (!validateRole($role)) {
                        echo "Role tidak valid. Silakan coba lagi.\n";
                    }
                } while (!validateRole($role));
                $user = new User();
                $user->updateUser($id, $nama, $email, $password, $role);
                break;
            case 4:
                // $id = getInput("ID: ");
                // $user = new User();
                // $user->deleteUser($id);
                // break;
            case 5:
                exit("Terima kasih!\n");
            default:
                echo "Opsi tidak valid. Silakan coba lagi.\n";
                break;
        }
    }
}

function pageAdmin($user) {
    $admin = new Admin($user->email, $user->password);
    echo "Selamat datang Admin, {$user->nama}\n";
    echo "=====================\n";
    while (true) {
        echo "1. Lihat semua pengguna\n";
        echo "2. Menghapus pengguna\n";
        echo "3. Lihat semua permintaan\n";
        echo "4. Ubah status permintaan\n";
        echo "5. Hasilkan laporan bulanan pengguna\n";
        echo "6. Tampilkan laporan bulanan\n";
        echo "7. Kelola item sampah\n";
        echo "8. Keluar\n";
        echo "Pilih opsi: ";
        $option = trim(fgets(STDIN));
        
        switch ($option) {
            case 1:
                echo $admin->viewAllUsers();
                break;
            case 2:
                $id = getInput("Masukkan ID pengguna: ");
                $admin->deleteUser($id);
                break;
            case 3:
                echo $admin->viewAllRequests();
                break;
            case 4:
                $id = getInput("Masukkan ID permintaan: ");
                $status = getInput("Masukkan status baru: ");
                $admin->manageRequest($id, $status);
                break;
            case 5:
                $id = getInput("Masukkan ID pengguna: ");
                $month = getInput("Masukkan bulan: ");
                $admin->generateMonthlyReport($id, $month);
                break;
            case 6:
                $id = getInput("Masukkan ID laporan: ");
                $report = new Report($id, null, null);
                print_r($report->viewReport());
                break;
            case 7:
                $admin->manageWasteItems();
                break;
            case 8:
                exit("Terima kasih!\n");
            default:
                echo "Opsi tidak valid. Silakan coba lagi.\n";
                break;
        }
    }
}

function pageCollector($user) {
    $collector = new Collector($user->email, $user->password);
    echo "Selamat datang Collector, {$user->nama}\n";
    echo "=====================\n";
    while (true) {
        echo "1. Tambah permintaan\n";
        echo "2. Lihat permintaan\n";
        echo "3. Tambah item sampah ke permintaan\n";
        echo "4. Selesaikan permintaan\n";
        echo "5. Keluar\n";
        echo "Pilih opsi: ";
        $option = trim(fgets(STDIN));
        
        switch ($option) {
            case 1:
                $id = getInput("ID: ");
                $userId = getInput("User ID: ");
                $pickUpDate = getInput("Tanggal Pengumpulan: ");
                $status = getInput("Status: ");
                $collector->addRequest($id, $userId, $pickUpDate, $status);
                break;
            case 2:
                print_r($collector->viewRequests());
                break;
            case 3:
                $requestId = getInput("ID Permintaan: ");
                $itemId = getInput("ID Item Sampah: ");
                $collector->addWasteItemToRequest($requestId, $itemId);
                break;
            case 4:
                $requestId = getInput("ID Permintaan: ");
                $collector->completeRequest($requestId);
                break;
            case 5:
                exit("Terima kasih!\n");
            default:
                echo "Opsi tidak valid. Silakan coba lagi.\n";
                break;
        }
    }
}

function pageRecycler($user) {
    $recycler = new Recycler($user->email, $user->password);
    echo "Selamat datang Recycler, {$user->nama}\n";
    echo "=====================\n";
    while (true) {
        echo "1. Tambah permintaan\n";
        echo "2. Lihat permintaan\n";
        echo "3. Proses permintaan\n";
        echo "4. Keluar\n";
        echo "Pilih opsi: ";
        $option = trim(fgets(STDIN));
        
        switch ($option) {
            case 1:
                $id = getInput("ID: ");
                $userId = getInput("User ID: ");
                $pickUpDate = getInput("Tanggal Pengumpulan: ");
                $status = getInput("Status: ");
                $recycler->addRequest($id, $userId, $pickUpDate, $status);
                break;
            case 2:
                print_r($recycler->viewRequests());
                break;
            case 3:
                $requestId = getInput("ID Permintaan: ");
                $recycler->processRequest($requestId);
                break;
            case 4:
                exit("Terima kasih!\n");
            default:
                echo "Opsi tidak valid. Silakan coba lagi.\n";
                break;
        }
    }
}

session_start();
mainMenu();
session_unset()
?>