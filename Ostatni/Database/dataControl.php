<?php
require_once 'db.php';

// ✅ Insert data
function insert($data, $table)
{
    global $conn;

    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_fill(0, count($data), "?"));
    $values = array_values($data);

    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $types = str_repeat("s", count($values)); // all as strings (you can customize this)
    $stmt->bind_param($types, ...$values);

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

    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    if ($stmt->fetch()) {
        $stmt->close();
        return password_verify($password, $hashedPassword);
    } else {
        $stmt->close();
        return false;
    }
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
        'role_id' => 1 // role_id pro čtenáře
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
