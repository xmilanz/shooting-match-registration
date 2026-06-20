<?php
$username       = $_SESSION['name'] ?? '';
$passwordOld    = $_POST['password'] ?? '';
$passwordNew    = $_POST['password_new'] ?? '';
$passwordVerify = $_POST['password_new1'] ?? '';

if ($username) {
    $stmt = $conn->prepare("SELECT password FROM $table_admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result && $row = $result->fetch_assoc()) {
        $storedHash = $row['password'];

        // kontrola starého hesla
        if (!password_verify($passwordOld, $storedHash)) {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Původní heslo není správné.',
                'duration' => 2500
            ];
            header("Location: /");
            exit();
        }

        // kontrola shody nových hesel
        if ($passwordNew !== $passwordVerify) {
            $_SESSION['toast'] = [
                'type' => 'warning',
                'message' => 'Hesla se neshodují.',
                'duration' => 2500
            ];
            header("Location: /");
            exit();
        }

        // kontrola shody puvodniho a noveho hesla
        if ($passwordOld == $passwordNew) {
            $_SESSION['toast'] = [
                'type' => 'warning',
                'message' => 'Nové heslo je stejné jako původní.',
                'duration' => 2500
            ];
            header("Location: /");
            exit();
        }

        // kontrola síly nového hesla
        $errorMessage = '';
        if (!isValidPassword($passwordNew, $username, $errorMessage)) {
            $_SESSION['toast'] = [
                'type' => 'warning',
                'message' => $errorMessage,
                'duration' => 2500
            ];
            header("Location: /");
            exit();
        }


        // ulož nový hash
        $hash = password_hash($passwordNew, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("
                UPDATE $table_admins 
                SET password = ?,
                last_password_change = NOW()
                WHERE username = ?");
        $updateStmt->bind_param(
            "ss",
            $hash,
            $username
        );
        $updateStmt->execute();
        $updateStmt->close();

        logAction("user password change");
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Heslo bylo úspěšně změněno.',
            'duration' => 2000
        ];
        header("Location: /");
        exit();
    }
}
