<?php
$table = $_POST['table'];
$field = $_POST['field'];
$id = intval($_POST['id']);
$value = $_POST['value'];

//$table_disciplines = 'dev_ssas_match_disciplines'; // TO-DO zjsitit proč je to tady
$allowedTables = [
    $table_admins => ['username', 'email', 'role', 'firstname', 'lastname', 'organizer', 'password', 'force_password_change'],
    $table_disciplines => ['Name', 'Value', 'Description', 'Shift_from', 'Shift_to'],
    $table_categories => ['Name', 'Value'],
    $table_fee => ['Value']
];

if (array_key_exists($table, $allowedTables) && in_array($field, $allowedTables[$table], true)) {

    if ($table === $table_admins && $field === 'password') {
        if (trim($value) === '') {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Při změně hesla jste nezadali žádný text. Opakujte změnu hesla a zadejte vhodný řetězec.',
                'duration' => 3500
            ];
            exit;
        }

        $hash = password_hash($value, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
                UPDATE $table_admins 
                SET password = ?, last_password_change = NOW(), force_password_change = 1
                WHERE id = ?
            ");
        $stmt->bind_param("si", $hash, $id);
    } else {
        if ($table === $table_admins && $field === 'force_password_change') {
            $value = ($value == '1') ? 1 : 0;
        }

        $stmt = $conn->prepare("UPDATE `$table` SET `$field` = ? WHERE id = ?");
        $stmt->bind_param("si", $value, $id);
    }

    $stmt->execute();
    $stmt->close();
}
exit;
