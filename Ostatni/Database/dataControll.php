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
