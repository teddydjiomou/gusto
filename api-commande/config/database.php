<?php
class Database {
    private $host = "w1kr9ijlozl9l79i.chr7pe7iynqr.eu-west-1.rds.amazonaws.com";
    private $dbname = "qrwvoqbllzh8wzao"; // ⚠️ à demander si tu ne l’as pas
    private $user = "mpmgeoxc8ty1h18g";
    private $password = "we52fmkrt240ksi";

    protected $pdo;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->password
            );

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


            $this->initTables();

        } catch (PDOException $e) {
            die("Erreur connexion : " . $e->getMessage());
        }
    }

    private function initTables() {
        try {

            $sqls = [

                "etablissement" => "
                    CREATE TABLE IF NOT EXISTS etablissement (
                        id_etablissement INT AUTO_INCREMENT PRIMARY KEY,
                        logo TEXT NOT NULL,
                        nom VARCHAR(50) NOT NULL,
                        type VARCHAR(50) NOT NULL,
                        pays VARCHAR(50) NOT NULL,
                        devise VARCHAR(20) NOT NULL,
                        ville VARCHAR(50) NOT NULL,
                        adresse VARCHAR(50) NOT NULL,
                        email VARCHAR(50) NOT NULL,
                        telephone VARCHAR(50) NOT NULL,
                        country VARCHAR(10) NOT NULL,
                        site_web VARCHAR(50) NOT NULL,
                        description TEXT NOT NULL,
                        date_enreg DATE
                    )",
                "appareil" => "
                    CREATE TABLE IF NOT EXISTS appareil (
                        id_appareil INT AUTO_INCREMENT PRIMARY KEY,
                        id_etablissement INT,
                        marque VARCHAR(50) NOT NULL,
                        model VARCHAR(50) NOT NULL,
                        numero_serie VARCHAR(50) NOT NULL,
                        systeme_exploitation VARCHAR(50) NOT NULL,
                        annee_fabrication VARCHAR(50) NOT NULL,
                        date_fin_support DATE,
                        description TEXT,
                        FOREIGN KEY (id_etablissement) REFERENCES etablissement(id_etablissement)
                        ON DELETE CASCADE ON UPDATE CASCADE
                    )",
                "utilisateur" => "
                    CREATE TABLE IF NOT EXISTS utilisateur (
                        id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
                        nom VARCHAR(50) NOT NULL,
                        adresse VARCHAR(50) NOT NULL,
                        email VARCHAR(50) NOT NULL,
                        telephone VARCHAR(50) NOT NULL,
                        login VARCHAR(50) NOT NULL,
                        password TEXT,
                        id_etablissement INT,
                        code VARCHAR(50) NOT NULL,
                        date_validite DATE,
                        statu VARCHAR(20) NOT NULL,
                        role INT,
                        date_enreg DATE,
                        FOREIGN KEY (id_etablissement) REFERENCES etablissement(id_etablissement)
                        ON DELETE CASCADE ON UPDATE CASCADE
                    )",
                "tables_restaurant" => "
                    CREATE TABLE IF NOT EXISTS tables_restaurant (
                        id_table INT AUTO_INCREMENT PRIMARY KEY,
                        nom VARCHAR(20) NOT NULL,
                        id_etablissement INT,
                        statu VARCHAR(10) NOT NULL,
                        FOREIGN KEY (id_etablissement) REFERENCES etablissement(id_etablissement)
                        ON DELETE CASCADE ON UPDATE CASCADE
                    )",
                "categorie" => "
                    CREATE TABLE IF NOT EXISTS categorie (
                        id_categorie INT AUTO_INCREMENT PRIMARY KEY,
                        id_etablissement INT,
                        libelle VARCHAR(50) NOT NULL,
                        FOREIGN KEY (id_etablissement) REFERENCES etablissement(id_etablissement)
                        ON DELETE CASCADE ON UPDATE CASCADE
                    )",
                "produit" => "
                    CREATE TABLE IF NOT EXISTS produit (
                        id_produit INT AUTO_INCREMENT PRIMARY KEY,
                        id_etablissement INT,
                        nom VARCHAR(50) NOT NULL,
                        image TEXT,
                        id_categorie INT,
                        prix VARCHAR(11) NOT NULL,
                        devise VARCHAR(10) NOT NULL,
                        description TEXT,
                        FOREIGN KEY (id_etablissement) REFERENCES etablissement(id_etablissement)
                        ON DELETE CASCADE ON UPDATE CASCADE,
                        FOREIGN KEY (id_categorie) REFERENCES categorie(id_categorie)
                        ON DELETE CASCADE ON UPDATE CASCADE
                    )",
                "service" => "
                    CREATE TABLE IF NOT EXISTS service (
                        id_service INT AUTO_INCREMENT PRIMARY KEY,
                        id_table INT,
                        id_utilisateur INT,
                        code VARCHAR(6) NOT NULL,
                        id_etablissement INT,
                        date_heure_ouverture DATETIME,
                        date_heure_fermeture DATETIME,
                        FOREIGN KEY (id_table) REFERENCES tables_restaurant(id_table)
                        ON DELETE CASCADE ON UPDATE CASCADE,
                        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
                        ON DELETE CASCADE ON UPDATE CASCADE,
                        FOREIGN KEY (id_etablissement) REFERENCES etablissement(id_etablissement)
                        ON DELETE CASCADE ON UPDATE CASCADE
                    )",
                "commande" => "
                    CREATE TABLE IF NOT EXISTS commande (
                        id_commande INT AUTO_INCREMENT PRIMARY KEY,
                        id_ticket TEXT,
                        id_etablissement INT,
                        id_table INT,
                        commande TEXT,
                        montant_total  VARCHAR(20) NOT NULL,
                        devise VARCHAR(10) NOT NULL,
                        etat  VARCHAR(20) NOT NULL,
                        date_enreg  DATETIME,
                        FOREIGN KEY (id_table) REFERENCES tables_restaurant(id_table)
                        ON DELETE CASCADE ON UPDATE CASCADE,
                        FOREIGN KEY (id_etablissement) REFERENCES etablissement(id_etablissement)
                        ON DELETE CASCADE ON UPDATE CASCADE
                    )"

            ];

            foreach ($sqls as $sql) {
                $this->pdo->exec($sql);
            }

            // Vérifier si admin existe
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM utilisateur");
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO utilisateur 
                    (nom, adresse, email, telephone, login, password, id_etablissement, code, date_validite, statu, role, date_enreg) 
                    VALUES (:nom, :adresse, :email, :telephone, :login, :password, :id_etablissement, :code, :date_validite, :statu, :role, :date_enreg)
                ");

                $stmt->execute([
                    ':nom' => 'Admin',
                    ':adresse' => 'Cameroun',
                    ':email' => 'admin@gmail.com',
                    ':telephone' => '000000000',
                    ':login' => 'admin',
                    ':password' => password_hash("admin", PASSWORD_DEFAULT),
                    ':id_etablissement' => 0,
                    ':code' => '0000',
                    ':date_validite' => null,
                    ':statu' => 'Actif',
                    ':role' => 0,
                    ':date_enreg' => date("Y-m-d")
                ]);

            }

        } catch (PDOException $e) {
            die("Erreur tables : " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}
?>'',