<?php
class PerformanceReviewsModel {
    private $conn;
    private $table_name = "performance_reviews";

    public $id;
    public $user_id;
    public $review_period;
    public $score;
    public $reviewer_id;
    public $comments;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $this->id = substr(md5(rand()), 0, 16);

        $query = "INSERT INTO " . $this->table_name . "
                (id, user_id, review_period, score, reviewer_id, comments, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->user_id = htmlspecialchars(strip_tags($data['user_id']));
        $this->review_period = htmlspecialchars(strip_tags($data['review_period']));
        $this->score = htmlspecialchars(strip_tags($data['score']));
        $this->reviewer_id = htmlspecialchars(strip_tags($data['reviewer_id']));
        $this->comments = isset($data['comments']) ? htmlspecialchars(strip_tags($data['comments'])) : null;

        // Bind data
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->user_id);
        $stmt->bindParam(3, $this->review_period);
        $stmt->bindParam(4, $this->score);
        $stmt->bindParam(5, $this->reviewer_id);
        $stmt->bindParam(6, $this->comments);

        if($stmt->execute()) {
            return $this->getById($this->id);
        }
        return false;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUserId($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByReviewerId($reviewer_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE reviewer_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reviewer_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . "
                SET user_id = ?, review_period = ?, score = ?, 
                    reviewer_id = ?, comments = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $user_id = htmlspecialchars(strip_tags($data['user_id']));
        $review_period = htmlspecialchars(strip_tags($data['review_period']));
        $score = htmlspecialchars(strip_tags($data['score']));
        $reviewer_id = htmlspecialchars(strip_tags($data['reviewer_id']));
        $comments = isset($data['comments']) ? htmlspecialchars(strip_tags($data['comments'])) : null;

        // Bind data
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $review_period);
        $stmt->bindParam(3, $score);
        $stmt->bindParam(4, $reviewer_id);
        $stmt->bindParam(5, $comments);
        $stmt->bindParam(6, $id);

        if($stmt->execute()) {
            return $this->getById($id);
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    public function getMonthlyStats($year, $month) {
        $startDate = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
        $endDate = date("Y-m-t", mktime(0, 0, 0, $month, 1, $year));

        $query = "SELECT * FROM " . $this->table_name . "
                WHERE created_at BETWEEN ? AND ?
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $startDate);
        $stmt->bindParam(2, $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuarterlyStats($year, $quarter) {
        switch ($quarter) {
            case 1: $startMonth = 1; $endMonth = 3; break;
            case 2: $startMonth = 4; $endMonth = 6; break;
            case 3: $startMonth = 7; $endMonth = 9; break;
            case 4: $startMonth = 10; $endMonth = 12; break;
            default: throw new Exception("Invalid quarter");
        }

        $startDate = date("Y-m-d", mktime(0, 0, 0, $startMonth, 1, $year));
        $endDate = date("Y-m-t", mktime(0, 0, 0, $endMonth, 1, $year));

        $query = "SELECT * FROM " . $this->table_name . "
                WHERE created_at BETWEEN ? AND ?
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $startDate);
        $stmt->bindParam(2, $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getYearlyStats($year) {
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";

        $query = "SELECT * FROM " . $this->table_name . "
                WHERE created_at BETWEEN ? AND ?
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $startDate);
        $stmt->bindParam(2, $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>