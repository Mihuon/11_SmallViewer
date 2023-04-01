<?php
session_start();
class Employee
{
    public const DB_TABLE = "employee";

    public ?string $login;
    public ?string $password;
    public ?int $admin;
    public ?int $employee_id;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?int $room;

    public function __construct(?string $login = null, ?string $password = null, ?int $admin = null, ?int $employee_id = null, ?string $name = null, ?string $surname = null, ?string $job = null, ?int $wage = null, ?int $room = null)
    {
        $this->login = $login;
        $this->password = $password;
        $this->admin = $admin;
        $this->employee_id = $employee_id;
        $this->name = $name;
        $this->surname = $surname;
        $this->job = $job;
        $this->wage = $wage;
        $this->room = $room;
    }

    public static function findByID(int $id): ?self
    {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `" . self::DB_TABLE . "` WHERE `employee_id`= :employeeId");
        $stmt->execute(['employeeId' => $id]);

        if ($stmt->rowCount() < 1)
            return null;

        $employee = new self();
        $employee->hydrate($stmt->fetch());
        return $employee;
    }

    public static function getAll($sorting = []): array
    {
        $sortSQL = "";
        if (count($sorting)) {
            $SQLchunks = [];
            foreach ($sorting as $field => $direction)
                $SQLchunks[] = "`{$field}` {$direction}";

            $sortSQL = " ORDER BY " . implode(', ', $SQLchunks);
        }

        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `" . self::DB_TABLE . "`" . $sortSQL);
        $stmt->execute([]);

        $employees = [];
        while ($employeeData = $stmt->fetch()) {
            $employee = new Employee();
            $employee->hydrate($employeeData);
            $employees[] = $employee;
        }

        return $employees;
    }

    private function hydrate(array|object $data)
    {
        $fields = ['login', 'password', 'admin', 'employee_id', 'name', 'surname', 'job', 'wage', 'room'];
        if (is_array($data)) {
            foreach ($fields as $field) {
                if (array_key_exists($field, $data))
                    $this->{$field} = $data[$field];
            }
        } else {
            foreach ($fields as $field) {
                if (property_exists($data, $field))
                    $this->{$field} = $data->{$field};
            }
        }
    }

    public function insert(): bool
    {
        $query = "INSERT INTO " . self::DB_TABLE . " (`login`, `password`, `admin`, `employee_id`, `name`, `surname`, `job`, `wage`, `room`) VALUES (:login, :password, :admin, :employee_id, :name, :surname, :job, :wage, :room)";
        $stmt = PDOProvider::get()->prepare($query);
        $result = $stmt->execute(['login' => $this->login, 'password' => $this->password, 'admin' => $this->admin, 'employee_id' => $this->employee_id, 'name' => $this->name, 'surname' => $this->surname, 'job' => $this->job, 'wage' => $this->wage, 'room' => $this->room]);
        if (!$result)
            return false;

        $this->employee_id = PDOProvider::get()->lastInsertId();
        return true;
    }

    public function update(): bool
    {
        if (!isset($this->employee_id) || !$this->employee_id)
            throw new Exception("Cannot update model without ID");

        if ($_SESSION['admin'] == 1) {
            $query = "UPDATE " . self::DB_TABLE . " SET login = :login, password = :password, admin = :admin, name = :name, surname = :surname, job = :job, wage = :wage, room = :room WHERE `employee_id` = :employee_id";
            $stmt = PDOProvider::get()->prepare($query);
            return $stmt->execute(['login' => $this->login, 'password' => $this->password, 'admin' => $this->admin, 'employee_id' => $this->employee_id, 'name' => $this->name, 'surname' => $this->surname, 'job' => $this->job, 'wage' => $this->wage, 'room' => $this->room]);
        } else {
            $query = "UPDATE " . self::DB_TABLE . " SET login = :login, password = :password, name = :name, surname = :surname, job = :job, wage = :wage, room = :room WHERE `employee_id` = :employee_id";
            $stmt = PDOProvider::get()->prepare($query);
            return $stmt->execute(['login' => $this->login, 'password' => $this->password, 'employee_id' => $this->employee_id, 'name' => $this->name, 'surname' => $this->surname, 'job' => $this->job, 'wage' => $this->wage, 'room' => $this->room]);
        }
    }

    public function delete(): bool
    {
        return self::deleteByID($this->employee_id);
    }

    public static function deleteByID(int $employeeId): bool
    {
        $query = "DELETE FROM `" . self::DB_TABLE . "` WHERE `employee_id` = :employeeId";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute(['employeeId' => $employeeId]);
    }

    public function validate(&$errors = []): bool
    {
        if (!isset($this->login) || (!$this->login))
            $errors['login'] = 'Uživatelské jméno nesmí být prázdné';

        if (!isset($this->password) || (!$this->password))
            $errors['password'] = 'Heslo nesmí být prázdné';

        if (!isset($this->admin))
            $errors['admin'] = 'Admin musí být vyplněno';

        if (!isset($this->name) || (!$this->name))
            $errors['name'] = 'Jméno nesmí být prázdné';

        if (!isset($this->surname) || (!$this->surname))
            $errors['surname'] = 'Příjmení nesmí být prázdné';

        if (!isset($this->job) || (!$this->job))
            $errors['job'] = 'Pozice nesmí být prázdné';

        if (!isset($this->wage) || (!$this->wage))
            $errors['wage'] = 'Mzda nesmí být prázdné';

        if (!isset($this->room) || (!$this->room))
            $errors['room'] = 'Místnost nesmí být prázdné';

        return count($errors) === 0;
    }

    public static function readPost(): self
    {
        $employee = new Employee();
        $employee->employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);

        $employee->login = filter_input(INPUT_POST, 'login');
        if ($employee->login)
            $employee->login = trim($employee->login);

        $employee->password = filter_input(INPUT_POST, 'password');
        if ($employee->password)
            $employee->password = trim($employee->password);

        $employee->admin = filter_input(INPUT_POST, 'admin');
        if ($employee->admin)
            $employee->admin = trim($employee->admin);

        $employee->name = filter_input(INPUT_POST, 'name');
        if ($employee->name)
            $employee->name = trim($employee->name);

        $employee->surname = filter_input(INPUT_POST, 'surname');
        if ($employee->surname)
            $employee->surname = trim($employee->surname);

        $employee->job = filter_input(INPUT_POST, 'job');
        if ($employee->job)
            $employee->job = trim($employee->job);

        $employee->wage = filter_input(INPUT_POST, 'wage');
        if ($employee->wage)
            $employee->wage = trim($employee->wage);

        $employee->room = filter_input(INPUT_POST, 'room');
        if ($employee->room)
            $employee->room = trim($employee->room);

        return $employee;
    }
}
