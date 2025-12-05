<?php
require_once 'db.php';

// ✅ Insert data
function insert($data, $table)
{
    global $conn;

    // Filtrovat pouze NULL hodnoty - prázdné stringy ponecháme
    // NULL hodnoty nebudou zahrnuty do INSERT (použije se DEFAULT z databáze)
    $filteredData = [];
    foreach ($data as $key => $value) {
        if ($value !== null) {
            $filteredData[$key] = $value;
        }
    }
    
    // Pokud jsou všechna data NULL, použijeme původní data (pro případ, že chceme explicitně vložit NULL)
    if (empty($filteredData) && !empty($data)) {
        // Všechny hodnoty jsou NULL - použijeme původní strukturu, ale musíme použít speciální způsob
        // Pro teď použijeme původní data a necháme MySQL použít DEFAULT
        $filteredData = $data;
    }

    $columns = array_keys($filteredData);
    $placeholders = array_fill(0, count($filteredData), '?');
    $values = array_values($filteredData);

    $sql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $types = str_repeat("s", count($values));
    if (count($values) > 0) {
        $stmt->bind_param($types, ...$values);
    }

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

// ✅ Select data
function select($table, $columns = "*", $where = "")
{
    global $conn;

    $sql = "SELECT $columns FROM $table";
    if ($where) $sql .= " WHERE $where";

    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// ✅ Update data
function update($table, $data, $where)
{
    global $conn;

    $set = implode(" = ?, ", array_keys($data)) . " = ?";
    $values = array_values($data);

    $sql = "UPDATE $table SET $set WHERE $where";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $types = str_repeat("s", count($values));
    $stmt->bind_param($types, ...$values);

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

// ✅ Delete data
function delete($table, $where)
{
    global $conn;

    $sql = "DELETE FROM $table WHERE $where";
    $result = $conn->query($sql);
    if (!$result) {
        die("Delete failed: " . $conn->error);
    }

    return true;
}

// ✅ Validate user credentials
function validateUser($username, $password)
{
    global $conn;

    $sql = "SELECT id, password, password_temp FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($userId, $hashedPassword, $hashedPasswordTemp);
    if ($stmt->fetch()) {
        $stmt->close();

        $matchesPrimary = $hashedPassword ? password_verify($password, $hashedPassword) : false;
        if ($matchesPrimary) {
            return true;
        }

        $matchesTemp = $hashedPasswordTemp ? password_verify($password, $hashedPasswordTemp) : false;
        if ($matchesTemp) {
            return true;
        }
    } else {
        $stmt->close();
    }

    return false;
}

// ✅ Register new user
function registerUser($username, $password, $email = null, $phone = null)
{
    global $conn;

    // Check if username already exists
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // Username already exists
        $stmt->close();
        return false;
    }
    $stmt->close();

    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $data = [
        'username' => $username,
        'password' => $hashedPassword,
        'email' => $email,
        'phone' => $phone,
        'role_id' => 6 // role_id pro čtenáře
    ];
    return insert($data, 'users');
}

function createUserRoles(){
    $data = [
        'role' => 'Administrátor'
    ];
    insert($data, 'users_roles');

    $data = [
        'role' => 'Šéfredaktor'
    ];
    insert($data, 'users_roles');

    $data = [
        'role' => 'Recenzent'
    ];
    insert($data, 'users_roles');

    $data = [
        'role' => 'Redaktor'
    ];
    insert($data, 'users_roles');
    
    $data = [
        'role' => 'Autor'
    ];
    insert($data, 'users_roles');

    $data = [
        'role' => 'Čtenář'
    ];
    insert($data, 'users_roles');
}
