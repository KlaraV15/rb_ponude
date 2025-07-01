<?php
require_once __DIR__ . '/../database/db.php';

class AuthController {
    public static function register($data) {
        // Očekujemo: ime, email, lozinka
        $required = ['ime', 'email', 'lozinka'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }

        // Validacija emaila
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Neispravan email'];
        }

        $db = Database::connect();

        // Provjera postoji li već email u bazi
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email je već registriran'];
        }

        // Hash lozinke
        $hashedPassword = password_hash($data['lozinka'], PASSWORD_DEFAULT);

        // Pripremi podatke za unos u bazu (pretpostavimo da u bazi imamo polja: name, email, password)
        $userData = [
            'name' => trim($data['ime']),
            'email' => trim($data['email']),
            'password' => $hashedPassword
        ];

        // SQL za unos (pretpostavljam da je tablica users s kolumnama name, email, password)
        $columns = implode(', ', array_keys($userData));
        $placeholders = ':' . implode(', :', array_keys($userData));

        try {
            $stmt = $db->prepare("INSERT INTO users ($columns) VALUES ($placeholders)");
            $stmt->execute($userData);

            return [
                'success' => true,
                'message' => 'Registracija uspješna',
                'userId' => $db->lastInsertId()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Registracija nije uspjela: ' . $e->getMessage()
            ];
        }
    }
}
?>
