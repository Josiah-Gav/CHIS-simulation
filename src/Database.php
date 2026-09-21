<?php

declare(strict_types=1);

/**
 * MySQL/MariaDB storage for this app's two tables: patients (the fixture
 * CHIS record set) and activity_log (every inbound call Telemed made into
 * this app's API, and every outbound call this app made into Telemed).
 * Same engine as the main Telemed app's local dev setup (XAMPP MySQL), its
 * own database, on purpose — two separate systems, two separate databases.
 * No ORM, no migrations framework — this is a demo prop, not a system of
 * record.
 */
final class Database
{
    public static function connect(array $config): PDO
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $config['db_name'])) {
            throw new RuntimeException('CHIS_DB_NAME must be a plain identifier (letters, digits, underscore).');
        }

        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $database = $config['db_name'];
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$database}`");

        return $pdo;
    }

    public static function migrate(PDO $pdo): void
    {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS patients (
                clsu_id VARCHAR(30) PRIMARY KEY,
                full_name VARCHAR(191) NOT NULL,
                department VARCHAR(191) NULL,
                eligibility_status VARCHAR(50) NOT NULL DEFAULT 'active',
                blood_type VARCHAR(10) NULL,
                height_cm SMALLINT UNSIGNED NULL,
                weight_kg SMALLINT UNSIGNED NULL,
                emergency_contact_name VARCHAR(191) NULL,
                emergency_contact_relationship VARCHAR(100) NULL,
                emergency_contact_number VARCHAR(30) NULL,
                known_allergies JSON NULL,
                chronic_conditions JSON NULL,
                current_medications JSON NULL,
                past_injuries_surgeries JSON NULL,
                immunization_history JSON NULL,
                family_medical_history TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);

        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS activity_log (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                direction VARCHAR(20) NOT NULL,
                method VARCHAR(10) NOT NULL,
                path VARCHAR(255) NOT NULL,
                remote VARCHAR(191) NULL,
                status_code SMALLINT UNSIGNED NULL,
                detail VARCHAR(500) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            SQL);
    }

    /**
     * Seeds the same two clsu_ids CLSU Telemedicine's own
     * ChisMockPatientRecordSeeder uses, so a demo run lines up with the
     * QA patient accounts on the Telemed side (patient.test1 / patient.test2).
     */
    public static function seedIfEmpty(PDO $pdo): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $patients = [
            [
                'clsu_id' => '2021012345',
                'full_name' => 'QA Patient',
                'department' => null,
                'eligibility_status' => 'active',
                'blood_type' => 'O+',
                'height_cm' => 165,
                'weight_kg' => 58,
                'emergency_contact_name' => 'Maria Santos',
                'emergency_contact_relationship' => 'Mother',
                'emergency_contact_number' => '09171234567',
                'known_allergies' => ['Penicillin', 'Shellfish'],
                'chronic_conditions' => ['Asthma'],
                'current_medications' => ['Salbutamol inhaler (as needed)'],
                'past_injuries_surgeries' => ['Appendectomy (2019)'],
                'immunization_history' => ['COVID-19 (Pfizer, 3 doses)', 'Tetanus (2022)'],
                'family_medical_history' => 'Father: hypertension. Mother: none reported.',
            ],
            [
                'clsu_id' => '2022067890',
                'full_name' => 'QA Patient',
                'department' => null,
                'eligibility_status' => 'active',
                'blood_type' => 'A+',
                'height_cm' => 172,
                'weight_kg' => 70,
                'emergency_contact_name' => 'Juan Dela Cruz',
                'emergency_contact_relationship' => 'Father',
                'emergency_contact_number' => '09189876543',
                'known_allergies' => [],
                'chronic_conditions' => [],
                'current_medications' => [],
                'past_injuries_surgeries' => [],
                'immunization_history' => ['COVID-19 (Pfizer, 2 doses)'],
                'family_medical_history' => null,
            ],
        ];

        $stmt = $pdo->prepare(<<<'SQL'
            INSERT INTO patients (
                clsu_id, full_name, department, eligibility_status, blood_type,
                height_cm, weight_kg, emergency_contact_name,
                emergency_contact_relationship, emergency_contact_number,
                known_allergies, chronic_conditions, current_medications,
                past_injuries_surgeries, immunization_history, family_medical_history
            ) VALUES (
                :clsu_id, :full_name, :department, :eligibility_status, :blood_type,
                :height_cm, :weight_kg, :emergency_contact_name,
                :emergency_contact_relationship, :emergency_contact_number,
                :known_allergies, :chronic_conditions, :current_medications,
                :past_injuries_surgeries, :immunization_history, :family_medical_history
            )
            SQL);

        foreach ($patients as $patient) {
            $stmt->execute([
                'clsu_id' => $patient['clsu_id'],
                'full_name' => $patient['full_name'],
                'department' => $patient['department'],
                'eligibility_status' => $patient['eligibility_status'],
                'blood_type' => $patient['blood_type'],
                'height_cm' => $patient['height_cm'],
                'weight_kg' => $patient['weight_kg'],
                'emergency_contact_name' => $patient['emergency_contact_name'],
                'emergency_contact_relationship' => $patient['emergency_contact_relationship'],
                'emergency_contact_number' => $patient['emergency_contact_number'],
                'known_allergies' => json_encode($patient['known_allergies']),
                'chronic_conditions' => json_encode($patient['chronic_conditions']),
                'current_medications' => json_encode($patient['current_medications']),
                'past_injuries_surgeries' => json_encode($patient['past_injuries_surgeries']),
                'immunization_history' => json_encode($patient['immunization_history']),
                'family_medical_history' => $patient['family_medical_history'],
            ]);
        }
    }

    public static function findPatient(PDO $pdo, string $clsuId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM patients WHERE clsu_id = :clsu_id');
        $stmt->execute(['clsu_id' => $clsuId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function allPatients(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM patients ORDER BY clsu_id')->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Shapes a patients row into exactly the array Telemed's ChisClient
     * contract expects from getIdentity(): {clsu_id, full_name, department,
     * eligibility_status}.
     */
    public static function toIdentity(array $patient): array
    {
        return [
            'clsu_id' => $patient['clsu_id'],
            'full_name' => $patient['full_name'],
            'department' => $patient['department'],
            'eligibility_status' => $patient['eligibility_status'],
        ];
    }

    /**
     * Shapes a patients row into exactly the array Telemed's ChisClient
     * contract expects from getMedicalProfile().
     */
    public static function toMedicalProfile(array $patient): array
    {
        return [
            'clsu_id' => $patient['clsu_id'],
            'blood_type' => $patient['blood_type'],
            'height_cm' => $patient['height_cm'] !== null ? (int) $patient['height_cm'] : null,
            'weight_kg' => $patient['weight_kg'] !== null ? (int) $patient['weight_kg'] : null,
            'emergency_contact' => [
                'name' => $patient['emergency_contact_name'],
                'relationship' => $patient['emergency_contact_relationship'],
                'contact_number' => $patient['emergency_contact_number'],
            ],
            'known_allergies' => json_decode($patient['known_allergies'] ?? '[]', true) ?? [],
            'chronic_conditions' => json_decode($patient['chronic_conditions'] ?? '[]', true) ?? [],
            'current_medications' => json_decode($patient['current_medications'] ?? '[]', true) ?? [],
            'past_injuries_surgeries' => json_decode($patient['past_injuries_surgeries'] ?? '[]', true) ?? [],
            'immunization_history' => json_decode($patient['immunization_history'] ?? '[]', true) ?? [],
            'family_medical_history' => $patient['family_medical_history'],
        ];
    }
}
